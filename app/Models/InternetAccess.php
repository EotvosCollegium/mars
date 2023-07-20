<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @property mixed $user_id
 * @property mixed $wifi_username
 * @property mixed $has_internet_until
 * @property mixed $wifi_password
 * @property mixed $auto_approved_mac_slots
 */
class InternetAccess extends Model
{
    protected $primaryKey = 'user_id';

    protected $fillable = ['user_id', 'wifi_username', 'has_internet_until', 'wifi_password', 'auto_approved_mac_slots'];
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
        return $this->hasMany(WifiConnection::class, 'wifi_username', 'wifi_username');
    }
}
