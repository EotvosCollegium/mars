<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\SemesterStatus
 *
 * @property User $user
 * @property Semester $semester
 * @property string $status
 * @property string $comment
 * @property int $user_id
 * @property int $semester_id
 * @property int $verified
 * @method static \Illuminate\Database\Eloquent\Builder|SemesterStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SemesterStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SemesterStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|SemesterStatus whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SemesterStatus whereSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SemesterStatus whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SemesterStatus whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SemesterStatus whereVerified($value)
 * @mixin \Eloquent
 */
class SemesterStatus extends Pivot
{
    protected $table = 'semester_status';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'user_id',
        'semester_id',
        'status',
        'comment',
        'verified'
    ];

    public const ACTIVE = 'ACTIVE';
    public const PASSIVE = 'PASSIVE';
    public const STATUSES = [
        self::ACTIVE,
        self::PASSIVE
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function semester(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function translatedStatus(): string
    {
        return __('user.'.$this->status)
            . ($this->comment ? ' (' . $this->comment . ')' : '');
    }


    /**
     * Returns the color belonging to the status.
     */
    public static function color($status): string
    {
        switch ($status) {
            case self::ACTIVE:
                return 'green';
            case self::PASSIVE:
                return 'grey';
            default:
                return 'black';
        }
    }
}
