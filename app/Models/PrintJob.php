<?php

namespace App\Models;

use App\Enums\PrintJobStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
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
        'used_free_pages'
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
     * Gets the printjob-status with every printer, updates the status of the completed printjobs.
     */
    public static function checkAndUpdateStatuses()
    {
        foreach(PrintJob::query()->where('state', PrintJobStatus::QUEUED)->whereNotNull('printer_id')->pluck('printer_id')->unique() as $printer_id) {
            $printJobs = Printer::find($printer_id)->getCompletedPrintJobs();
            PrintJob::whereIn('job_id', $printJobs)->update(['state' => PrintJobStatus::SUCCESS]);
        }
    }
}
