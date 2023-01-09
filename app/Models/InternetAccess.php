<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        return $this->belongsTo('App\Models\User');
    }

    public function isActive()
    {
        return $this->has_internet_until != null && $this->has_internet_until > date('Y-m-d');
    }

    public function setWifiCredentials($username = null)
    {
        if ($username === null) {
            if ($this->user->isCollegist() && isset($this->user->educationalInformation)) {
                $username = $this->user->educationalInformation->neptun;
            } else {
                $username = 'wifiuser'.$this->user_id;
            }
        }
        $this->update([
            'wifi_username' => $username, 
            'wifi_password' => Str::random(8)
        ]);

        return $username;
    }

    public function resetPassword()
    {
        $this->update(['wifi_password' => Str::random(8)]);
    }

    public function wifiConnections()
    {
        return $this->hasMany('App\Models\WifiConnection', 'wifi_username', 'wifi_username');
    }

    public function reachedWifiConnectionLimit(): bool
    {
        return $this->wifiConnections->count() > $this->wifi_connection_limit;
    }
}
