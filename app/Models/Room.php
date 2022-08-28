<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $primaryKey = 'name';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['name', 'capacity'];

    public function users()
    {
        return $this->belongsTo(User::class, 'name', 'room');
    }

    public function residentNumber()
    {
        return $this->users()->count();
    }
}
