<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use Symfony\Component\Process\Process;

class Printer extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'ip',
        'port',
        'paper_out_at',
    ];

    protected $casts = [
        'paper_out_at' => 'datetime',
    ];

    /**
     * Returns the `PrintJob`s that were executed by this printer.
     * @return HasMany 
     */
    public function printJobs() {
        return $this->hasMany(PrintJob::class);
    }

    /**
     * Attemts to cancel the given `PrintJob`. Returns wether it was successful.
     * @param PrintJob $printJob 
     * @return PrinterCancelResult
     */
    public function cancelPrintJob(PrintJob $printJob) {
        $process = new Process(['cancel', $printJob->job_id, '-h', "$this->ip:$this->port"]);
        if (config('app.debug')) {
            // cancel(1) exits with status code 0 if it succeeds
            $result = ['output' => '', 'exit_code' => 0];
        } else {
            $process->run();
            $result = ['output' => $process->getOutput(), 'exit_code' => $process->getExitCode()];
        }
        Log::info([$process->getCommandLine(), $result]);
        if ($result['exit_code'] == 0) {
            return PrinterCancelResult::Success;
        }
        if (strpos($result['output'], "already canceled") !== false) {
            return PrinterCancelResult::AlreadyCancelled;
        }
        if (strpos($result['output'], "already completed") !== false) {
            return PrinterCancelResult::AlreadyCompleted;
        }
        return PrinterCancelResult::CannotCancel;
    }

    /**
     * Asks the printer to print a document with the given configuration.
     * @param bool $twoSided 
     * @param int $copies 
     * @param string $path 
     * @return int The `jobId` belonging to the printjob
     * @throws PrinterException If the printing fails
     */
    public function print(bool $twoSided, int $copies, string $path) {
        if (config('app.debug')) {
            return -1;
        }
        $jobId = null;
        try {
            $process = new Process([
                'lp', 
                '-d', $this->name, 
                '-h', "$this->ip:$this->port", 
                ($twoSided ? '-o sides=two-sided-long-edge' : ''),
                 '-n', $copies, 
                 $path
            ]);
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
            $jobId = intval($matches[1]);
        } catch (\Exception $e) {
            Log::error("Printing error at line: " . __FILE__ . ":" . __LINE__ . " (in function " . __FUNCTION__ . "). " . $e->getMessage());
            throw new PrinterException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        return $jobId;
    }

    /**
     * Returns the number of pages in the PDF document at the given path.
     * @param string $path 
     * @return int 
     */
    public static function getDocumentPageNumber(string $path): int {
        $process = new Process(['pdfinfo', $path, '|', 'grep', "'^Pages'", '|', 'awk', "'{print $2}'"]);
        if (config('app.debug')) {
            $result = rand(1, 10);
        } else {
            $process->run();
            $result = intval($process->getOutput());
        }
        Log::info([$process->getCommandLine(), $result]);
        return $result;
    }

    /**
     * Returns the completed printjobs for this printer.
     * @return void 
     * @throws NotFoundExceptionInterface 
     * @throws ContainerExceptionInterface 
     */
    public function getCompletedPrintJobs() {
        try {
            $process = new Process(['lpstat', '-W', 'completed', '-o', $this->name, '-h', "$this->ip:$this->port", '|', 'awk', "'{print $1}'"]);
            if (config('app.debug')) {
                $result = [];
            } else {
                $process->run();
                $result = $process->getOutput();
            }
            Log::info([$process->getCommandLine(), $result]);
        } catch (\Exception $e) {
            Log::error("Printing error at line: " . __FILE__ . ":" . __LINE__ . " (in function " . __FUNCTION__ . "). " . $e->getMessage());
        }
    }
}

enum PrinterCancelResult: string {
    case AlreadyCancelled = "already-cancelled";
    case AlreadyCompleted = "already-completed";
    case CannotCancel = "cannot-cancel";
    case Success = "successfully-cancelled";
}

class PrinterException extends \Exception {
    //
}
