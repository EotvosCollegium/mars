<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $table = 'print_jobs';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    public const QUEUED = 'QUEUED';
    public const ERROR = 'ERROR';
    public const CANCELLED = 'CANCELLED';
    public const SUCCESS = 'SUCCESS';
    public const STATES = [
        self::QUEUED,
        self::ERROR,
        self::CANCELLED,
        self::SUCCESS,
    ];

    protected $fillable = [
        'filename', 'filepath', 'user_id', 'state', 'job_id', 'cost',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public static function translateStates(): \Closure
    {
        return function ($data) {
            $data->state = __('print.'.strtoupper($data->state));

            return $data;
        };
    }

    public static function addCurrencyTag(): \Closure
    {
        return function ($data) {
            $data->cost = "{$data->cost} HUF";

            return $data;
        };
    }
}
