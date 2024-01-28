<?php

namespace App\Models;

use App\Models\Internet\Router;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Room
 *
 * @property string $name
 * @property int $capacity
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Room newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Room newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Room query()
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereName($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Router> $routers
 * @property-read int|null $routers_count
 * @mixin \Eloquent
 */
class Room extends Model
{
    protected $primaryKey = 'name';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['name', 'capacity'];

    protected $with = ['users'];

    protected $withCount = ['users'];

    public static array $roomColors = [
        3 => '#76ff03', //light-green accent-3
        2 => '#ffee58', //yellow lighten-2
        1 => '#ff9100', //orange accent-2
        0 => '#e0e0e0', //grey lighten-2
    ];

    /**
     * Returns the users assigned to this room
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'room', 'name');
    }

    /**
     * Returns the router(s) in the room
     */
    public function routers(): HasMany
    {
        return $this->hasMany(Router::class, 'room', 'name');
    }


    /**
     * Returns the number of users that are assigned to this room
     */
    public function residentNumber(): int
    {
        //check if count already eager loaded
        return $this->users_count ?? $this->users()->count();
    }

    /**
     * Assigns colors depending on the empty spaces left in the room
     */
    public function color(): string
    {
        return match ($this->capacity - $this->residentNumber()) {
            2 => Room::$roomColors[2],
            1 => Room::$roomColors[1],
            0 => Room::$roomColors[0],
            default => Room::$roomColors[3],
        };
    }
}
