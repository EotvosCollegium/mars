<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * @property mixed $user_id
 */
class InternetAccess extends Model
{
    protected $primaryKey = 'user_id';

    protected $fillable = ['user_id', 'wifi_username', 'wifi_connection_limit', 'has_internet_until', 'wifi_password'];
    protected $hidden = ['wifi_password'];

    protected $dates = [
        'has_internet_until',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive()
    {
        return $this->has_internet_until != null && $this->has_internet_until > date('Y-m-d');
    }

    /**
     * Set wifi username based on neptun code or user_id and set random wifi password.
     */
    public function setWifiCredentials($username = null)
    {
        if ($username === null) {
            $username = $this->user?->educationalInformation?->neptun ?? 'wifiuser'.$this->user_id;
        }
        $this->update([
            'wifi_username' => $username,
            'wifi_password' => Str::random(8)
        ]);

        return $username;
    }

    /**
     * Set a random wifi password.
     */
    public function resetPassword()
    {
        $this->update(['wifi_password' => Str::random(8)]);
    }

    public function wifiConnections()
    {
        return $this->hasMany('App\Models\WifiConnection', 'wifi_username', 'wifi_username');
    }

    /**
     * Scope a query to only include internet accesses that have reached their wifi connection limit.
     * Usage: ...->reachedWifiConnectionLimit()->...
     */
    public function scopeReachedWifiConnectionLimit(Builder $query)
    {
        return $query->whereHas('wifiConnections', function ($subquery) {
            $subquery->groupBy('user_id')->havingRaw('COUNT(*) > wifi_connection_limit');
        });
    }

}
