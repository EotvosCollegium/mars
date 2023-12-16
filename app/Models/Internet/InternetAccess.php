<?php

namespace App\Models\Internet;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * App\Models\Internet\InternetAccess
 *
 * @property mixed $user_id
 * @property string $wifi_username
 * @property Carbon $has_internet_until
 * @property string $wifi_password
 * @property int $auto_approved_mac_slots
 * @property User $user
 * @property WifiConnection[]|Collection $wifiConnections
 * @method $isActive
 * @method $setWifiCredentials
 * @method $resetPassword
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int|null $wifi_connections_count
 * @method static \Illuminate\Database\Eloquent\Builder|InternetAccess newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InternetAccess newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InternetAccess query()
 * @method static \Illuminate\Database\Eloquent\Builder|InternetAccess whereAutoApprovedMacSlots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternetAccess whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternetAccess whereHasInternetUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternetAccess whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternetAccess whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternetAccess whereWifiPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternetAccess whereWifiUsername($value)
 * @mixin \Eloquent
 */
class InternetAccess extends Model
{
    protected $primaryKey = 'user_id';

    protected $fillable = ['user_id', 'wifi_username', 'has_internet_until', 'wifi_password', 'auto_approved_mac_slots'];
    protected $hidden = ['wifi_password'];

    protected $dates = [
        'has_internet_until',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wifiConnections(): HasMany
    {
        return $this->hasMany(WifiConnection::class, 'wifi_username', 'wifi_username');
    }

    public function isActive(): bool
    {
        return $this->has_internet_until != null && $this->has_internet_until > date('Y-m-d');
    }

    /**
     * Set wifi username based on neptun code or user_id and set random wifi password.
     */
    public function setWifiCredentials($username = null): string
    {
        if ($username === null) {
            $username = $this->user?->educationalInformation?->neptun ?? 'guest_'.Str::random(6);
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
    public function resetPassword(): void
    {
        $this->update(['wifi_password' => Str::random(8)]);
    }
}
