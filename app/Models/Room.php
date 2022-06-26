<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $primaryKey = 'name';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    public function personalInformations()
    {
        return $this->belongsTo(PersonalInformation::class, 'name', 'room');
    }

    public function residentNumber()
    {
        return $this->personalInformations()->count();
    }
}
