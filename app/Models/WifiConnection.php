<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property string $ip
 * @property string $mac_address
 * @property string $wifi_username
 * @property string $lease_start
 * @property string $lease_end
 * @property string $radius_timestamp
 * @property string $note
 * @property InternetAccess $internetAccess
 * @property User $user
 * @method getColor
 */
class WifiConnection extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'ip',
        'mac_address',
        'wifi_username',
        'lease_start',
        'lease_end',
        'radius_timestamp',
        'note',
    ];

    public function internetAccess() : BelongsTo
    {
        return $this->belongsTo(InternetAccess::class, 'wifi_username', 'wifi_username');
    }

    public function user() : HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            InternetAccess::class,
            'wifi_username', // Foreign key on InternetAccess table...
            'id', // Foreign key on Users table...
            'wifi_username', // Local key on WifiConnection table...
            'user_id' // Local key on InternetAccess table...
        );
    }

    public function getColor() : string
    {
        if ($this->created_at > Carbon::now()->subDays(5)) {
            return 'red';
        }
        if ($this->created_at > Carbon::now()->subDays(10)) {
            return 'orange';
        }

        return 'green';
    }

}
