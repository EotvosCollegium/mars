<?php

namespace App\Http\Controllers\Dormitory;

use App\Console\Commands;
use App\Mail\NoPaper;
use App\Models\Role;
use App\Models\User;
use App\Models\FreePages;
use App\Models\PrintAccount;
use App\Models\PrintJob;
use App\Models\PrintAccountHistory;
use App\Mail\ChangedPrintBalance;
use App\Utils\Printer;
use App\Utils\TabulatorPaginator;
use App\Models\Transaction;
use App\Models\Checkout;
use App\Models\Semester;
use App\Models\PaymentType;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class PrintController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:use,App\Models\PrintAccount');
    }

    /**
     * Returns the printer page.
     */
    public function index()
    {
        return view('dormitory.print.app', [
                "users" => User::printers(),
                "free_pages" => user()->sumOfActiveFreePages()
            ]);
    }

    /**
     * Handles when a user clicks the 'no paper' button.
     */
    public function noPaper()
    {
        $reporterName = user()->name;
        $admins = User::withRole(Role::SYS_ADMIN)->get();
        foreach ($admins as $admin) {
            Mail::to($admin)->send(new NoPaper($admin->name, $reporterName));
        }
        Cache::put('print.no-paper', now(), 3600);
        return redirect()->back()->with('message', __('mail.email_sent'));
    }

    /**
     * Handles when the admin clicks the 'added paper' button.
     * after someone has signed paper shortage.
     */
    public function addedPaper()
    {
        $this->authorize('handleAny', PrintAccount::class);

        Cache::forget('print.no-paper');
        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    /**
     * Returns the admin page.
     */
    public function admin()
    {
        $this->authorize('handleAny', PrintAccount::class);

        return view('dormitory.print.manage.app', ["users" => User::printers()]);
    }

    /**
     * Handles a print request.
     */
    public function print(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_to_upload' => 'required|file|mimes:pdf|max:' . config('print.pdf_size_limit'),
            'number_of_copies' => 'required|integer|min:1'
        ]);
        $validator->validate();

        $is_two_sided = $request->has('two_sided');
        $number_of_copies = $request->number_of_copies;
        $use_free_pages = $request->use_free_pages;
        $file = $request->file_to_upload;
        $filename = $file->getClientOriginalName();
        $path = $this->storeFile($file);

        $printer = new Printer($filename, $path, $use_free_pages, $is_two_sided, $number_of_copies);

        return $printer->print();
    }

    /**
     * Handles when one user sends printer money to another.
     */
    public function transferBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'balance' => 'required|integer|min:1',
            'user_to_send' => 'required|integer|exists:users,id'
        ]);
        $validator->validate();

        $balance = $request->balance;
        $user = User::find($request->user_to_send);
        $from_account = user()->printAccount;
        $to_account = $user->printAccount;

        if (!$from_account->hasEnoughMoney($balance)) {
            return $this->handleNoBalance();
        }
        $to_account->update(['last_modified_by' => user()->id]);
        $from_account->update(['last_modified_by' => user()->id]);

        $from_account->decrement('balance', $balance);
        $to_account->increment('balance', $balance);

        // Send notification mail
        Mail::to($user)->queue(new ChangedPrintBalance($user, $balance, user()->name));

        return redirect()->back()->with('message', __('general.successful_transaction'));
    }

    /**
     * Handles balance modification by an admin.
     */
    public function modifyBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id_modify' => 'required|integer|exists:users,id',
            'balance' => 'required|integer',
        ]);
        $validator->validate();

        $balance = $request->balance;
        $user = User::find($request->user_id_modify);
        $print_account = $user->printAccount;

        $this->authorize('modify', $print_account);

        if ($balance < 0 && !$print_account->hasEnoughMoney($balance)) {
            return $this->handleNoBalance();
        }
        $print_account->update(['last_modified_by' => user()->id]);
        $print_account->increment('balance', $balance);

        $admin_checkout = Checkout::admin();
        Transaction::create([
            'checkout_id' => $admin_checkout->id,
            'receiver_id' => user()->id,
            'payer_id' => $user->id,
            'semester_id' => Semester::current()->id,
            'amount' => $request->balance,
            'payment_type_id' => PaymentType::print()->id,
            'comment' => null,
            'moved_to_checkout' => null,
        ]);

        // Send notification mail
        Mail::to($user)->queue(new ChangedPrintBalance($user, $balance, user()->name));

        return redirect()->back()->with('message', __('general.successful_modification'));
    }

    /**
     * Handles when an admin adds free pages.
     */
    public function addFreePages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id_free' => 'required|integer|exists:users,id',
            'free_pages' => 'required|integer|min:1',
            'deadline' => 'required|date|after:now',
        ]);
        $validator->validate();

        $this->authorize('create', FreePages::class);

        FreePages::create([
            'user_id' => $request->user_id_free,
            'amount' => $request->free_pages,
            'deadline' => $request->deadline,
            'last_modified_by' => user()->id,
            'comment' => $request->comment,
        ]);

        return redirect()->back()->with('message', __('general.successfully_added'));
    }

    /**
     * Lists every print job (only available for authorized users).
     */
    public function listAllPrintJobs()
    {
        $this->authorize('viewAny', PrintJob::class);

        $this->updateCompletedPrintingJobs();

        $columns = ['created_at', 'filename', 'cost', 'state', 'user.name'];
        // @phpstan-ignore-next-line
        $printJobs = PrintJob::join('users as user', 'user.id', '=', 'user_id')
            ->select('print_jobs.*')
            ->with('user')
            ->orderby('print_jobs.created_at', 'desc');

        return $this->printJobsPaginator($printJobs, $columns);
    }

    /**
     * Lists the authenticated user's own print jobs.
     */
    public function listPrintJobs()
    {
        $this->authorize('viewSelf', PrintJob::class);

        $this->updateCompletedPrintingJobs();

        $columns = ['created_at', 'filename', 'cost', 'state'];
        $printJobs = user()->printJobs()->orderby('created_at', 'desc');

        return $this->printJobsPaginator($printJobs, $columns);
    }

    /**
     * Lists every free page pack given (only available for authorized users).
     */
    public function listAllFreePages()
    {
        $this->authorize('viewAny', FreePages::class);

        $columns = ['amount', 'deadline', 'modifier', 'comment', 'user.name', 'created_at'];

        $freePages = FreePages::join('users as user', 'user.id', '=', 'user_id');

        return $this->freePagesPaginator($freePages, $columns);
    }

    /**
     * Lists the authenticated user's own free page packs.
     */
    public function listFreePages()
    {
        $this->authorize('viewSelf', FreePages::class);

        $columns = ['amount', 'deadline', 'modifier', 'comment'];
        $freePages = user()->freePages();

        return $this->freePagesPaginator($freePages, $columns);
    }

    /**
     * Returns another, more detailed table for checking all print jobs
     * (only for authorized users).
     */
    public function listPrintAccountHistory()
    {
        $this->authorize('viewAny', PrintJob::class);

        $columns = ['user_name', 'balance_change', 'free_page_change', 'deadline_change', 'modifier_name', 'modified_at'];
        $paginator = TabulatorPaginator::from(
            PrintAccountHistory::join('users as user', 'user.id', '=', 'user_id')
                ->join('users as modifier', 'modifier.id', '=', 'modified_by')
                ->select(['user.name as user_name', 'balance_change', 'free_page_change', 'deadline_change', 'modifier.name as modifier_name', 'modified_at'])
        )->sortable($columns)
            ->filterable($columns)
            ->paginate();
        return $paginator;
    }

    /**
     * Handles cancelling a print job.
     */
    public function cancelPrintJob($id)
    {
        $printJob = PrintJob::findOrFail($id);

        $this->authorize('update', $printJob);

        if ($printJob->state === PrintJob::QUEUED) {
            $result = Commands::cancelPrintJob($printJob->job_id);

            if ($result['exit_code'] == 0) {
                // Command was successful, job cancelled.
                $printJob->state = PrintJob::CANCELLED;
                // Reverting balance change
                // TODO: test what happens when cancelled right before the end
                $printAccount = $printJob->user->printAccount;
                $printAccount->update(['last_modified_by' => user()->id]);
                $printAccount->increment('balance', $printJob->cost);
            } else {
                if (strpos($result['output'], "already canceled") !== false) {
                    return redirect()->back()->with('error', __('print.already_cancelled'));
                } elseif (strpos($result['output'], "already completed") !== false) {
                    $printJob->state = PrintJob::SUCCESS;
                    return redirect()->back()->with('message', __('general.successful_modification'));
                } else {
                    Log::warning("cannot cancel print job " . $printJob->job_id .".", [$result]);
                    return redirect()->back()->with('error', __('general.unknown_error'));
                }
            }
            $printJob->save();
        }
    }

    /** Private helper functions */

    /**
     * Returns a paginator for a print job history table.
     */
    private function printJobsPaginator($printJobs, $columns)
    {
        $paginator = TabulatorPaginator::from($printJobs)->sortable($columns)->filterable($columns)->paginate();

        $paginator->getCollection()->transform(PrintJob::translateStates());
        $paginator->getCollection()->transform(PrintJob::addCurrencyTag());

        return $paginator;
    }

    /**
     * Returns a paginator for a free page history table.
     */
    private function freePagesPaginator($freePages, $columns)
    {
        $paginator = TabulatorPaginator::from(
            $freePages->join('users as creator', 'creator.id', '=', 'last_modified_by')
                       ->select('creator.name as modifier', 'printing_free_pages.*')
                       ->with('user')
        )->sortable($columns)->filterable($columns)->paginate();
        return $paginator;
    }

    /**
     * Updates the state of jobs that have been completed.
     */
    private function updateCompletedPrintingJobs()
    {
        try {
            $result = Commands::getCompletedPrintingJobs();
            PrintJob::whereIn('job_id', $result)->update(['state' => PrintJob::SUCCESS]);
        } catch (\Exception $e) {
            Log::error("Printing error at line: " . __FILE__ . ":" . __LINE__ . " (in function " . __FUNCTION__ . "). " . $e->getMessage());
        }
    }

    /**
     * Stores the uploaded file on the server
     * with a hash as the filename,
     * then returns the path.
     */
    private function storeFile($file)
    {
        $path = $file->storePubliclyAs(
            '',
            hash('sha256', rand(0, 100000) . date('c')) . '.pdf',
            'printing'
        );
        $path = Storage::disk('printing')->path($path);

        return $path;
    }

    /**
     * Responds to requests
     * where the user does not have enough balance.
     */
    private function handleNoBalance()
    {
        return back()->withInput()->with('error', __('print.no_balance'));
    }
}
