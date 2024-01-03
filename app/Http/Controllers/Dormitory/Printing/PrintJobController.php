<?php

namespace App\Http\Controllers\Dormitory\Printing;

use App\Enums\PrintJobStatus;
use App\Http\Controllers\Controller;
use App\Models\FreePages;
use App\Models\PrintAccount;
use App\Models\Printer;
use App\Models\PrinterCancelResult;
use App\Models\PrintJob;
use App\Utils\TabulatorPaginator;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Log;
use Storage;

class PrintJobController extends Controller {

    /**
     * Returns a paginated list of `PrintJob`s.
     * @param null|string $filter Decides wether all `PrintJob`s or just the user's `PrintJob`s should be listed.
     * @return LengthAwarePaginator
     */
    public function indexPrintJobs(?string $filter = null) {
        if ($filter === "all") {
            $this->authorize('viewAny', PrintJob::class);

            PrintJob::checkAndUpdateStatuses();

            return $this->paginatorFrom(
                printJobs: PrintJob::query()
                    ->with('user')
                    ->orderBy('print_jobs.created_at', 'desc'), // TODO: test if it works without join
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

        PrintJob::checkAndUpdateStatuses();
        return $this->paginatorFrom(
            printJobs: user()->printJobs()->orderBy('created_at', 'desc'),
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
    public function store(Request $request) {
        $request->validate([
            'file' => 'required|file',
            'copies' => 'required|integer|min:1',
            'two_sided' => 'boolean',
            'printer_id' => 'nullable|exists:printers,id',
            'use_free_pages' => 'nullable|boolean',
        ]);

        $useFreePages = $request->boolean('use_free_pages');
        $copyNumber = $request->get('copies');
        $twoSided = $request->get('two_sided');
        $file = $request->file('file');

        /** @var Printer */
        $printer = $request->printer_id ? Printer::find($request->get("printer_id")) : Printer::firstWhere('name', config('print.printer_name'));

        $path = $file->store('print-documents');
        $pageNumber = Printer::getDocumentPageNumber($path);

        /** @var PrintAccount */
        $printAccount = user()->printAccount;

        if (($useFreePages && $printAccount->hasEnoughFreePages($pageNumber, $copyNumber, $twoSided)) ||
            (!$useFreePages && $printAccount->hasEnoughBalance($pageNumber, $copyNumber, $twoSided))
        ) {
            return back()->with('error', __('print.no_balance'));
        }

        $jobId = null;
        try {
            $jobId = $printer->print($twoSided, $copyNumber, $path);
        } catch (\Exception $e) {
            return back()->with('error', __('print.error_printing'));
        } finally {
            Storage::delete($path);
        }

        $cost = $useFreePages ? 
            PrintAccount::getFreePagesNeeeded($pageNumber, $copyNumber, $twoSided) :
            PrintAccount::getBalanceNeeded($pageNumber, $copyNumber, $twoSided);

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
        } else {
            $printAccount->balance -= $cost;
        }

        $printAccount->save();

        return back()->with('message', __('print.success'));
    }

    /**
     * Cancels a `PrintJob`
     * @param PrintJob $job 
     * @return RedirectResponse 
     */
    public function update(PrintJob $job) {
        Log::info('asd');
        $this->authorize('update', $job);
        Log::info($job->state->value);

        if ($job->state === PrintJobStatus::QUEUED ) {
            $result = ($job->printer ?? Printer::firstWhere('name', config('print.printer_name')))->cancelPrintJob($job);
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
    }



    private function paginatorFrom(Builder $printJobs, array $columns) {
        $paginator = TabulatorPaginator::from($printJobs)->sortable($columns)->filterable($columns)->paginate();

        // Process the data before showing it in a table.
        $paginator->getCollection()->transform(function (PrintJob $printJob) {
            $printJob->translatedState = __("print." . strtoupper($printJob->state->value));
            $printJob->cost = "$printJob->cost HUF";
            return $printJob;
        });

        return $paginator;
    }
}
