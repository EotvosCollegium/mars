<?php

namespace App\Models;

use App\Enums\PrintJobStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use App\Utils\Process;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Support\Facades\DB;
use App\Utils\PrinterHelper;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Contracts\Container\BindingResolutionException;

/*
 * A printer configuration describes the price and lp flags for a given printer configuration (like single-sided or double-sided printing, black-and-white or color printing).
 * We assume that a single CUPS instance is used to orchestrate all of the printers.
 * Fields:
 *  - id: a unique identifier for the printer
 *  - description_hun: a text describing the printer configuration in Hungarian, it is displayed to the end user if there language is set to Hungarian
 *  - description_eng: a text describing the printer configuration in English, it is displayed to the end user if there language is not set to Hungarian
 *  - paper_out_at: the last time an error was reported for the printer
 *  - lp_flags: describes what arguments the lp command should be given using the following replacements:
 *    - {{cups_address}} would be replaced with the cups address set in the configuration file
 *    - {{path}} would be replaced with the path of the file that is going to be printed
 *    - {{num_copies}} would be replaced by the number of copies the user requested to be printed
 *    - lp flags are split by space characters and given to lp as seperate arguments
 *  - one_sided_cost, two_sided_cost: for single-sided printing, it is quite trivial. For double-sided printing, the price / sheet is two_sided_cost normally, the last page might cost one_sided_cost if there are an odd number of pages
 *  - active: if the printer should be available for collegists
*/
class PrinterConfiguration extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'paper_out_at',
        'active',
    ];

    protected $casts = [
        'paper_out_at' => 'datetime',
    ];

    /**
     * Returns the `PrintJob`s that were executed by this printer.
     * @return HasMany
     */
    public function printJobs()
    {
        return $this->hasMany(PrintJob::class);
    }


    /**
     * Starts a new print job for the current user and saves it.
     * @param bool $useFreePrintingCredits
     * @param int $cost
     * @param string $filePath
     * @param string $originalName
     * @param int $copyNumber
     * @return PrintJob
     * @throws AuthenticationException
     * @throws PrinterException
     * @throws MassAssignmentException
     */
    public function createPrintJob(bool $useFreePrintingCredits, int $cost, string $filePath, string $originalName, int $copyNumber)
    {
        $jobId = $this->print($copyNumber, $filePath);

        return user()->printJobs()->create([
            'printer_configuration_id' => $this->id,
            'state' => PrintJobStatus::QUEUED,
            'job_id' => $jobId,
            'cost' => $cost,
            'used_free_printing_credits' => $useFreePrintingCredits,
            'filename' => $originalName,
        ]);
    }

    /**
     * Asks the printer to print a document with the given configuration.
     * @param int $copies
     * @param string $path
     * @return string The `jobId` belonging to the printjob
     * @throws PrinterException If the printing fails
     */
    public function print(int $copies, string $path)
    {
        if (config('app.debug')) {
            return "not_submitted";
        }
        $jobId = null;
        try {
            $process = new Process(array_merge([
                'lp',
                explode(
                    ' ',
                    str_replace(
                        ['{{cups_address}}', '{{path}}', '{{num_copies}}'],
                        [config('print.cups_address'), $path, $copies],
                        $this->lp_flags
                    )
                )
            ]));
            $process->run();
            if (!$process->isSuccessful()) {
                Log::error("Printing error at line: " . __FILE__ . ":" . __LINE__ . " (in function " . __FUNCTION__ . "). " . $process->getErrorOutput());
                throw new PrinterException($process->getErrorOutput());
            }
            $result = $process->getOutput();
            if (!preg_match("/^request id is ([^\s]*) \\([0-9]* file\\(s\\)\\)$/", $result, $matches)) {
                Log::error("Printing error at line: " . __FILE__ . ":" . __LINE__ . " (in function " . __FUNCTION__ . "). result:"
                    . print_r($result, true));
                throw new PrinterException($result);
            }
            $jobId = $matches[1];
        } catch (\Exception $e) {
            Log::error("Printing error at line: " . __FILE__ . ":" . __LINE__ . " (in function " . __FUNCTION__ . "). " . $e->getMessage());
            throw new PrinterException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        return $jobId;
    }

    /**
     * Returns the amount of money needed to print with given configuration.
     * @param int $pages
     * @param int $copies
     * @return mixed
     * @throws BindingResolutionException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function getPrice(int $pages, int $copies)
    {
        $pageTypesNeeded = PrinterHelper::getPageTypesNeeded($pages, $this->twoSided());

        return $pageTypesNeeded['one_sided'] * $this->one_sided_cost * $copies +
            $pageTypesNeeded['two_sided'] * $this->two_sided_cost * $copies;
    }

    public function twoSided(): bool
    {
        return $this->two_sided_cost != null;
    }

    /**
     *
     */
    public function description(): Attribute
    {
        return Attribute::make(
            get: fn () => App::getLocale() === 'hu' ? $this->description_hun : $this->description_eng,
        );
    }
}

class PrinterException extends \Exception
{
    //
}
