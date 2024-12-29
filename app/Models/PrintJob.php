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
        'printer_id',
        'used_free_pages',
        'filename',
    ];

    protected $casts = [
        'state' => PrintJobStatus::class,
        'used_free_pages' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * `Printer` which this `PrintJob` was sent to.
     * @return BelongsTo
     */
    public function printer()
    {
        return $this->belongsTo(Printer::class);
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
            get: fn () => $this->used_free_pages ? "$this->cost ingyenes oldal" : "$this->cost HUF"
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
     * @param PrintJob $this
     * @return PrinterCancelResult
     */
    public function cancel()
    {
        return DB::transaction(function () {
            $printer = $this->printer;
            $process = new Process([config('commands.cancel'), $this->job_id, '-h', "$printer->ip:$printer->port"]);
            $process->run();
            $result = ['output' => $process->getOutput(), 'exit_code' => $process->getExitCode()];

            if ($result['exit_code'] == 0) {
                $this->update([
                    'state' => PrintJobStatus::CANCELLED,
                ]);
                $printAccount = $this->printAccount;
                $printAccount->last_modified_by = user()->id;

                if ($this->used_free_pages) {
                    $pages = $printAccount->availableFreePages()->first();
                    $pages->update([
                        'last_modified_by' => user()->id,
                        'amount' => $pages->amount + $this->cost,
                    ]);
                } else {
                    $printAccount->balance += $this->cost;
                }

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
}
