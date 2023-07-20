<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WifiConnection extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'mac_address',
        'wifi_username',
        'lease_start',
        'lease_end',
        'radius_timestamp',
        'note',
    ];

    public function internetAccess()
    {
        return $this->belongsTo(InternetAccess::class, 'wifi_username', 'wifi_username');
    }

    public function user()
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

    public function getColor()
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
