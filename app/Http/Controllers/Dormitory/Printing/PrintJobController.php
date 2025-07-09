<?php

namespace App\Http\Controllers\Dormitory\Printing;

use App\Enums\PrintJobStatus;
use App\Http\Controllers\Controller;
use App\Models\FreePrintingCredits;
use App\Models\PrintAccount;
use App\Models\PrinterConfiguration;
use App\Enums\PrinterCancelResult;
use App\Utils\PrinterHelper;
use App\Models\PrintJob;
use App\Utils\TabulatorPaginator;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class PrintJobController extends Controller
{
    /**
     * Returns a paginated list of the current user's `PrintJob`s.
     * @return LengthAwarePaginator
     */
    public function index()
    {
        $this->authorize('viewSelf', PrintJob::class);

        return $this->paginatorFrom(
            printJobs: user()
                ->printJobs()
                ->orderBy('created_at', 'desc'),
            columns: [
                'created_at',
                'filename',
                'cost',
                'state',
            ]
        );
    }

    /**
     * Returns a paginated list of all `PrintJob`s.
     * @return LengthAwarePaginator
     */
    public function adminIndex()
    {
        $this->authorize('viewAny', PrintJob::class);

        return $this->paginatorFrom(
            printJobs: PrintJob::with('user')
                ->orderBy('print_jobs.created_at', 'desc'),
            columns: [
                'created_at',
                'filename',
                'cost',
                'state',
                'user.name',
            ]
        );
    }

    /**
     * Prints a document, then stores the corresponding `PrintJob`.
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file',
            'copies' => 'required|integer|min:1|max:99',
            'use_free_printing_credits' => 'sometimes|accepted',
            'printer_configuration' => 'required|integer|exists:printer_configurations,id'
        ]);

        $useFreePrintingCredits = isset($validated['use_free_printing_credits']) && $validated['use_free_printing_credits'];
        $copyNumber = $validated['copies'];
        $file = $validated['file'];
        $originalName = $file->getClientOriginalName();
        $path = $file->store('', 'printing');

        $printerConfiguration = PrinterConfiguration::find($validated['printer_configuration']);

        $pageNumber = PrinterHelper::getDocumentPageNumber(Storage::disk('printing')->path($path));

        return self::printDocument($printerConfiguration, $copyNumber, $useFreePrintingCredits, $path, $originalName);
    }

    public static function printDocument(PrinterConfiguration $printerConfiguration, int $copyNumber, bool $useFreePrintingCredits, string $path, string $originalName)
    {

        Gate::authorize('use', $printerConfiguration);

        $pageNumber = PrinterHelper::getDocumentPageNumber(Storage::disk('printing')->path($path));

        DB::beginTransaction();

        $printAccount = user()->printAccount;

        if (!$printAccount->hasEnoughBalanceOrFreePrintingCredits($useFreePrintingCredits, $pageNumber, $copyNumber, $printerConfiguration)) {
            DB::rollBack();
            return back()->with('error', __('print.no_balance'));
        }

        $cost = $printerConfiguration->getPrice($pageNumber, $copyNumber);

        $printAccount->updateHistory($useFreePrintingCredits, $cost);

        try {
            $printJob = $printerConfiguration->createPrintJob($useFreePrintingCredits, $cost, Storage::disk('printing')->path($path), $originalName, $copyNumber);
            Log::info("User $printAccount->user_id started print job a document for $cost. Job ID: $printJob->job_id. Used free printing credits: $useFreePrintingCredits. File: $originalName");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error while creating print job: " . $e->getMessage());
            return back()->with('error', __('print.error_printing'));
        } finally {
            /*
             * Let's keep around the documents for easier troubleshooting
             * TODO: clean up after an interval (e.g. 7 days)
             */
            // Storage::disk('printing')->delete($path);
        }

        DB::commit();

        return back()->with('message', __('print.success'));
    }

    /**
     * Cancels a `PrintJob`
     * @param PrintJob $job
     * @return RedirectResponse
     */
    public function update(PrintJob $job, Request $request)
    {
        $this->authorize('update', $job);

        $data = $request->validate([
            'state' => ['required', Rule::enum(PrintJobStatus::class)->only(PrintJobStatus::CANCELLED)],
        ]);

        /** @var PrintJobStatus */
        $newState = $data['state'];

        switch ($newState->value) {
            case PrintJobStatus::CANCELLED:
                if ($job->state === PrintJobStatus::QUEUED) {
                    /** @var PrinterCancelResult */
                    $result = $job->cancel();

                    if ($result === PrinterCancelResult::Success) {
                        return back()->with('message', __('general.successful_modification'));
                    }
                    return back()->with('error', __("print.$result->value"));
                }
                return back()->with('error', __('print.cannot_cancel'));
            default:
                abort(422);
        }
    }

    /**
     * Returns a paginated list of `PrintJob`s.
     * @param Builder $printJobs
     * @param array $columns
     * @return LengthAwarePaginator
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    private function paginatorFrom(Builder $printJobs, array $columns)
    {
        $paginator = TabulatorPaginator::from($printJobs)->sortable($columns)->filterable($columns)->paginate();

        // Process the data before showing it in a table.
        // @phpstan-ignore-next-line
        $paginator->getCollection()->append([
            'translated_cost',
            'translated_state',
        ]);

        return $paginator;
    }
}
