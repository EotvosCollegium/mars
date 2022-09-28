<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
     * @return Semester the semester the CommunityService was made in
     */
    public function semester(): Semester
    {
        return $this->belongsTo(\App\Models\Semester::class);
    }


}
