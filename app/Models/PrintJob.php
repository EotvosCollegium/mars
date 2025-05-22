<?php

namespace App\Models;

use App\Enums\PrinterCancelResult;
use App\Enums\PrintJobStatus;
use App\Utils\Process;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\DB;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use Log;

/**
 * App\Models\PrintJob
 *
 * @property mixed $user_id
 * @property int $id
 * @property string $filename
 * @property string $filepath
 * @property mixed $state
 * @property int $cost
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $job_id
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\PrintJobFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintJob newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrintJob newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrintJob query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrintJob whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintJob whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintJob whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintJob whereFilepath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintJob whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintJob whereJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintJob whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintJob whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintJob whereUserId($value)
 * @mixin \Eloquent
 */
class PrintJob extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'state',
        'job_id',
        'cost',
        'printer_configuration_id',
        'used_free_printing_credits',
        'filename',
    ];

    protected $casts = [
        'state' => PrintJobStatus::class,
        'used_free_printing_credits' => 'boolean',
    ];

    /**
     * `User` that sent this `PrintJob`.
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * `PrinterConfiguration` which this `PrintJob` was sent to.
     * @return BelongsTo
     */
    public function printerConfiguration()
    {
        return $this->belongsTo(PrinterConfiguration::class);
    }

    /**
     * `PrintAccount` which is related to this `PrintJob` through the `User`.
     * The `PrintJob` and the `PrintAccount` both belong to the `User`, in this sense this relationship is articifial.
     * Trying to fix the decision made for the database a few years ago.
     * @return HasOneThrough
     */
    public function printAccount()
    {
        return $this->hasOneThrough(
            PrintAccount::class,
            User::class,
            'id', // Foreign key on users
            'user_id', // Foreign key on print_accounts
            'user_id', // Local key on print_jobs
            'id', // Local key on users
        );
    }

    /**
     * Attribute for the translated cost.
     */
    public function translatedCost(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->used_free_printing_credits ? "$this->cost nyomtatási kredit" : "$this->cost HUF"
        );
    }

    /**
     * Attribute for the translated state.
     */
    public function translatedState(): Attribute
    {
        return Attribute::make(
            get: fn () => __("print." . strtoupper($this->state->value))
        );
    }

    /**
     * Attemts to cancel the given `PrintJob`. Returns wether it was successful.
     * @return PrinterCancelResult
     */
    public function cancel()
    {
        return DB::transaction(function () {
            $process = new Process([config('commands.cancel'), $this->job_id, '-h', config('print.cups_address')]);
            $process->run();
            $result = ['output' => $process->getOutput(), 'exit_code' => $process->getExitCode()];

            if ($result['exit_code'] == 0) {
                $this->update([
                    'state' => PrintJobStatus::CANCELLED,
                ]);

                $this->save();
                return PrinterCancelResult::Success;
            }
            if (strpos($result['output'], "already canceled") !== false) {
                $this->update([
                    'state' => PrintJobStatus::CANCELLED,
                ]);
                return PrinterCancelResult::AlreadyCancelled;
            }
            if (strpos($result['output'], "already completed") !== false) {
                $this->update([
                    'state' => PrintJobStatus::SUCCESS,
                ]);
                return PrinterCancelResult::AlreadyCompleted;
            }
            return PrinterCancelResult::CannotCancel;
        });
    }

    /**
     * Returns the completed printjobs. Running this function is really expensive.
     * @return array
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    private static function getCompletedPrintJobsFromLpstat() : array
    {
        try {
            $process = new Process([config('commands.lpstat'), '-h', config('print.cups_address'), '-W', 'completed']);
            $process->run(log: false);
            $result = explode("\n", $process->getOutput());
            $firstWords = array_map(function ($line) {
                return strtok($line, " ");
            }, $result);
            return $firstWords;
        } catch (\Exception $e) {
            Log::error("Printing error at line: " . __FILE__ . ":" . __LINE__ . " (in function " . __FUNCTION__ . "). " . $e->getMessage());
            throw new PrinterException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * Updates the state of the completed printjobs to `PrintJobStatus::SUCCESS`.  Running this function is really expensive.
     */
    public static function updateCompletedPrintJobs()
    {
        $completedPrintJobs = self::getCompletedPrintJobsFromLpstat();
        DB::transaction(function () use ($completedPrintJobs) {
            PrintJob::where('state', PrintJobStatus::QUEUED)->whereIn(
                'job_id',
                $completedPrintJobs
            )->update(['state' => PrintJobStatus::SUCCESS]);
        });
    }
}
