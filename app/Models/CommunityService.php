<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property boolean|null $approved
 * @property string $status
 */
class CommunityService extends Model
{
    protected $fillable = ['requester_id', 'approver_id', 'description', 'approved', 'semester_id'];


    /**
     * @return User the requester of the CommunityService
     */
    public function requester()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * @return User the approver of the CommunityService
     */
    public function approver()
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
                1 => 'jóváhagyott',
                0 => 'elutasított',
                default => throw new \Exception('Unexpected match value')
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
