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

    public function color()
    {
        $color="#ffffff";
        // Assigns colors depending on the empty spaces left in the room
        switch ($this->capacity - $this->residentNumber()) {
            case 3:
                $color="#11f709";
                break;
            case 2:
                $color="#2bb505";
                break;
            case 1:
                $color="#f0fc0a";
                break;
            case 0:
                $color="#fc4f05";
                break;
            default:
                $color="#11f709";
                break;
        }
        return $color;
    }
}
