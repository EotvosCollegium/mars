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
     * Returns the completed printjobs for this printer.
     * @return array 
     * @throws NotFoundExceptionInterface 
     * @throws ContainerExceptionInterface 
     */
    public function getCompletedPrintJobs() {
        try {
            $process = new Process(['lpstat', '-W', 'completed', '-o', $this->name, '-h', "$this->ip:$this->port", '|', 'awk', "'{print $1}'"]);
            $process->run();
            $result = explode("\n", $process->getOutput());
            return $result;
        } catch (\Exception $e) {
            Log::error("Printing error at line: " . __FILE__ . ":" . __LINE__ . " (in function " . __FUNCTION__ . "). " . $e->getMessage());
        }
    }

    /**
     * Updates the state of the completed printjobs to `PrintJobStatus::SUCCESS`.
     */
    public function updateCompletedPrintJobs() {
        PrintJob::whereIn(
            'job_id', 
            $this->getCompletedPrintJobs()
        )->update(['state' => PrintJobStatus::SUCCESS]);
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
