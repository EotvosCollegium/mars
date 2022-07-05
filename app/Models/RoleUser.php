<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RoleUser extends Pivot
{
    protected $fillable = ['workshop_id', 'object_id'];

    protected $with = ['workshop', 'object'];

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }
    public function object()
    {
        return $this->belongsTo(RoleObject::class);
    }
}
