<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * @return string the status based on the approved attribute
     */

    public function getStatusAttribute(): string
    {
        if ($this->approved === null) {
            return __('community-service.pending');
        } elseif ($this->approved==1) {
            return __('community-service.approved');
        } else {
            return __('community-service.rejected');
        }
    }

    /**
     * @return string the badge color based on the approved attribute
     */

    public function getStatusColor(): string
    {
        if ($this->approved === null) {
            return 'orange';
        } elseif ($this->approved==1) {
            return 'green';
        } else {
            return 'red';
        }
    }
}
