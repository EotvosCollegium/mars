<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property User $user
 * @property Semester $semester
 * @property string $status
 * @property string $comment
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
    public const INACTIVE = 'INACTIVE';
    public const DEACTIVATED = 'DEACTIVATED';
    public const PASSIVE = 'PASSIVE';
    public const PENDING = 'PENDING';
    public const STATUSES = [
        self::ACTIVE,
        self::INACTIVE,
        self::DEACTIVATED,
        self::PASSIVE,
        self::PENDING,
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function semester(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }


    /**
     * Returns the color belonging to the status.
     */
    public static function color($status): string
    {
        switch ($status) {
            case self::ACTIVE:
                return 'green';
            case self::INACTIVE:
                return 'grey';
            case self::DEACTIVATED:
                return 'brown';
            case self::PASSIVE:
                return 'orange';
            case self::PENDING:
                return 'lime';
            default:
                return 'black';
        }
    }
}
