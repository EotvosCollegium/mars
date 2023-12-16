<?php

namespace App\Models;

use App\Utils\NotificationCounter;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Fault
 *
 * @property int $id
 * @property int|null $reporter_id
 * @property string $location
 * @property string $description
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $reporter
 * @method static \Illuminate\Database\Eloquent\Builder|Fault newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fault newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fault query()
 * @method static \Illuminate\Database\Eloquent\Builder|Fault whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fault whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fault whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fault whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fault whereReporterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fault whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fault whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Fault extends Model
{
    use NotificationCounter;

    public $incrementing = true;
    public $timestamps = true;
    protected $fillable = [
        'reporter_id',
        'location',
        'description',
        'status',
        'created_at',
        'updated_at',
    ];

    public const UNSEEN = 'UNSEEN';
    public const SEEN = 'SEEN';
    public const DONE = 'DONE';
    public const WONT_FIX = 'WONT_FIX';
    public const STATES = [self::UNSEEN, self::SEEN, self::DONE, self::WONT_FIX];

    public static function getState($value)
    {
        return strtoupper($value);
    }

    public static function notifications()
    {
        return self::where('status', self::UNSEEN)->count();
    }

    public function reporter()
    {
        return $this->belongsTo('App\Models\User', 'reporter_id', 'id');
    }
}
