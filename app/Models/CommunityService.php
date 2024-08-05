<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\CommunityService
 *
 * @property boolean|null $approved
 * @property string $status
 * @property int $id
 * @property int $requester_id
 * @property int $approver_id
 * @property string $description
 * @property int $semester_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $approver
 * @property-read \App\Models\User $requester
 * @property-read \App\Models\Semester $semester
 * @method static \Illuminate\Database\Eloquent\Builder|CommunityService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommunityService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommunityService query()
 * @method static \Illuminate\Database\Eloquent\Builder|CommunityService whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunityService whereApproverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunityService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunityService whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunityService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunityService whereRequesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunityService whereSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunityService whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CommunityService extends Model
{
    protected $fillable = ['requester_id', 'approver_id', 'description', 'approved', 'semester_id'];
    protected $casts = [
        'approved' => 'boolean',
    ];


    /**
     * @return BelongsTo the requester of the CommunityService
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * @return BelongsTo the approver of the CommunityService
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }


    /**
     * @return BelongsTo the semester the CommunityService was made in
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Semester::class);
    }


    /**
     * Get the status attribute based on the approved attribute.
     *
     * @return Attribute
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->approved) {
                null =>  'függőben',
                true => 'jóváhagyott',
                false => 'elutasított',
            },
        );
    }

    /**
     * @return string the badge color based on the approved attribute
     */
    public function getStatusColor(): string
    {
        if ($this->approved === null) {
            return 'orange';
        } elseif ($this->approved == 1) {
            return 'green';
        } else {
            return 'red';
        }
    }
}
