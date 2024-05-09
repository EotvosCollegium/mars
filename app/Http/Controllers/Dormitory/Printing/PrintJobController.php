<?php

namespace App\Http\Controllers\Dormitory\Printing;

use App\Enums\PrintJobStatus;
use App\Http\Controllers\Controller;
use App\Models\FreePages;
use App\Models\PrintAccount;
use App\Models\Printer;
use App\Models\PrinterCancelResult;
use App\Models\PrinterHelper;
use App\Models\PrintJob;
use App\Utils\TabulatorPaginator;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class PrintJobController extends Controller
{
    /**
     * Returns a paginated list of `PrintJob`s.
     * @param null|string $filter Decides wether all `PrintJob`s or just the user's `PrintJob`s should be listed.
     * @return LengthAwarePaginator
     */
    public function indexPrintJobs(?string $filter = null)
    {
        if ($filter === "all") {
            $this->authorize('viewAny', PrintJob::class);

            PrinterHelper::updateCompletedPrintJobs();

            return $this->paginatorFrom(
                printJobs: PrintJob::query()
                    ->with('user')
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

        $this->authorize('viewSelf', PrintJob::class);

        PrinterHelper::updateCompletedPrintJobs();
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
     * Prints a document, then stores the corresponding `PrintJob`.
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        $request->validate([
            'file' => 'required|file',
            'copies' => 'required|integer|min:1',
            'two_sided' => 'in:on,off',
            'printer_id' => 'exists:printers,id',
            'use_free_pages' => 'in:on,off',
        ]);

        $useFreePages = $request->boolean('use_free_pages');
        $copyNumber = $request->input('copies');
        $twoSided = $request->boolean('two_sided');
        $file = $request->file('file');

        /** @var Printer */
        $printer = $request->has('printer_id') ? Printer::find($request->input("printer_id")) : Printer::firstWhere('name', config('print.printer_name'));

        $path = $file->store('print-documents');
        $pageNumber = PrinterHelper::getDocumentPageNumber($path);

        /** @var PrintAccount */
        $printAccount = user()->printAccount;

        if (!(($useFreePages && $printAccount->hasEnoughFreePages($pageNumber, $copyNumber, $twoSided)) ||
            (!$useFreePages && $printAccount->hasEnoughBalance($pageNumber, $copyNumber, $twoSided)))
        ) {
            return back()->with('error', __('print.no_balance'));
        }

        $jobId = null;
        try {
            $jobId = $printer->print($twoSided, $copyNumber, $path, user());
        } catch (\Exception $e) {
            return back()->with('error', __('print.error_printing'));
        } finally {
            Storage::delete($path);
        }

        $cost = $useFreePages ?
            PrinterHelper::getFreePagesNeeeded($pageNumber, $copyNumber, $twoSided) :
            PrinterHelper::getBalanceNeeded($pageNumber, $copyNumber, $twoSided);

        Log::info("Printjob cost: $cost");

        user()->printJobs()->create([
            'state' => PrintJobStatus::QUEUED,
            'job_id' => $jobId,
            'cost' => $cost,
            'used_free_pages' => $useFreePages,
            'filename' => $file->getClientOriginalName(),
        ]);

        // Update the print account history
        $printAccount->last_modified_by = user()->id;

        if ($useFreePages) {
            $freePagesToSubtract = $cost;
            $freePages = $printAccount->available_free_pages->where('amount', '>', 0);

            /** @var FreePages */
            foreach ($freePages as $pages) {
                $subtractablePages = min($freePagesToSubtract, $pages->amount);
                $pages->update([
                    'last_modified_by' => user()->id,
                    'amount' => $pages->amount - $subtractablePages,
                ]);

                $freePagesToSubtract -= $subtractablePages;

                if ($freePagesToSubtract <= 0) { // < should not be necessary, but better safe than sorry
                    break;
                }
            }
            // Set value in the session so that free page checkbox stays checked
            session()->put('use_free_pages', true);
        } else {
            $printAccount->balance -= $cost;

            // Remove value regarding the free page checkbox from the session
            session()->remove('use_free_pages');
        }

        $printAccount->save();

        DB::commit();

        return back()->with('message', __('print.success'));
    }

    /**
     * Cancels a `PrintJob`
     * @param PrintJob $job
     * @return RedirectResponse
     */
    public function update(PrintJob $job)
    {
        $this->authorize('update', $job);

        if ($job->state === PrintJobStatus::QUEUED) {
            $result = $job->cancel();
            switch ($result) {
                case PrinterCancelResult::Success:
                    $job->update([
                        'state' => PrintJobStatus::CANCELLED,
                    ]);
                    $printAccount = $job->printAccount;
                    $printAccount->last_modified_by = user()->id;

                    if ($job->used_free_pages) {
                        $pages = $printAccount->available_free_pages->first();
                        $pages->update([
                            'last_modified_by' => user()->id,
                            'amount' => $pages->amount + $job->cost,
                        ]);
                    } else {
                        $printAccount->balance += $job->cost;
                    }

                    $job->save();
                    return back()->with('message', __('general.successful_modification'));
                case PrinterCancelResult::AlreadyCompleted:
                    $job->update([
                        'state' => PrintJobStatus::SUCCESS,
                    ]);
                    break;
                case PrinterCancelResult::AlreadyCancelled:
                    $job->update([
                        'state' => PrintJobStatus::CANCELLED,
                    ]);
                    break;
            }

            return back()->with('error', __("print.$result->value"));
        }

        return back();
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
        $paginator->getCollection()->append([
            'translated_cost',
            'translated_state',
        ]);

        return $paginator;
    }
}
