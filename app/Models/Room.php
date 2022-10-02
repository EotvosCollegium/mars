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

    protected $with = ['users'];

    protected $withCount = ['users'];

    public static $roomColors = [
        3 => '#76ff03', //light-green accent-3
        2 => '#ffee58', //yellow lighten-2
        1 => '#ff9100', //orange accent-2
        0 => '#e0e0e0', //grey lighten-2
    ];

    /**
     * Returns the users assigned to this room
     */
    public function users()
    {
        return $this->hasMany(User::class, 'room', 'name');
    }

    /**
     * Returns the number of users that are assigned to this room
     */
    public function residentNumber()
    {
        //check if count already eager loaded
        return $this->users_count ?? $this->users()->count();
    }

    /**
     * Assigns colors depending on the empty spaces left in the room
     */
    public function color()
    {
        $color="#ffffff";
        switch ($this->capacity - $this->residentNumber()) {
            case 3:
                $color=Room::$roomColors[3];
                break;
            case 2:
                $color=Room::$roomColors[2];
                break;
            case 1:
                $color=Room::$roomColors[1];
                break;
            case 0:
                $color=Room::$roomColors[0];
                break;
            default:
                $color=Room::$roomColors[3];
                break;
        }
        return $color;
    }
}
