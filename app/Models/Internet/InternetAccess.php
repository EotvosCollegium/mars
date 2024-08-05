<?php

namespace App\Models\Internet;

use App\Models\Semester;
use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * App\Models\Internet\InternetAccess
 *
 * The InternetAccess model connects the user to the internet related models.
 * For Wi-Fi connections,
 *
 * @property mixed $user_id
 * @property string $wifi_username
 * @property Carbon $has_internet_until
 * @property string $wifi_password
 * @property User $user
 * @property WifiConnection[]|Collection $wifiConnections
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int|null $wifi_connections_count
 * @method static Builder|InternetAccess newModelQuery()
 * @method static Builder|InternetAccess newQuery()
 * @method static Builder|InternetAccess query()
 * @method static Builder|InternetAccess whereAutoApprovedMacSlots($value)
 * @method static Builder|InternetAccess whereCreatedAt($value)
 * @method static Builder|InternetAccess whereHasInternetUntil($value)
 * @method static Builder|InternetAccess whereUpdatedAt($value)
 * @method static Builder|InternetAccess whereUserId($value)
 * @method static Builder|InternetAccess whereWifiPassword($value)
 * @method static Builder|InternetAccess whereWifiUsername($value)
 * @property-read Collection|MacAddress[] $macAddresses
 * @property-read int|null $mac_addresses_count
 * @mixin Eloquent
 */
class InternetAccess extends Model
{
    protected $primaryKey = 'user_id';

    protected $fillable = ['user_id', 'wifi_username', 'has_internet_until', 'wifi_password'];

    protected $casts = [
        'has_internet_until' => 'datetime',
    ];

    protected const PASSWORD_LENGTH = 8;

    /**
     * The user who has internet access.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The Wi-Fi connections that the user made.
     *
     * @return HasMany
     */
    public function wifiConnections(): HasMany
    {
        return $this->hasMany(WifiConnection::class, 'wifi_username', 'wifi_username');
    }

    /**
     * The mac addresses that the user uploaded for wired internet access.
     *
     * @return HasMany
     */
    public function macAddresses(): HasMany
    {
        return $this->hasMany(MacAddress::class, 'user_id', 'user_id');
    }


    /**
     * Check if the user has internet access enabled.
     *
     * @return bool
     */
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
            $username = $this->user?->educationalInformation?->neptun ?? 'guest_' . Str::random(6);
        }
        $this->update([
            'wifi_username' => $username,
            'wifi_password' => self::generateWifiPassword()
        ]);

        return $username;
    }

    /**
     * Set a random wifi password.
     */
    public function resetPassword(): void
    {
        $this->update(['wifi_password' => self::generateWifiPassword()]);
    }

    /**
     * @param string|Carbon|null $newDate
     * @return Carbon
     */
    public function extendInternetAccess(Carbon|string $newDate = null): Carbon
    {
        if ($newDate != null) {
            $newDate = Carbon::parse($newDate);
        } else {
            $newDate = InternetAccess::getInternetDeadline();
        }
        $this->update(['has_internet_until' => $newDate]);

        return $newDate;
    }

    /**
     * Get the current date until the internet accesses should be set.
     * @return \Carbon\Carbon
     */
    public static function getInternetDeadline(): \Carbon\Carbon
    {
        return Semester::next()->getStartDate()->addMonth();
    }

    /**
     * Generate a "random" alphanumeric string, with the confusing characters filtered out.
     * This is a modified version of Str::random() from Laravel.
     *
     * @return string
     */
    private static function generateWifiPassword(): string
    {
        $disabled_chars = ['/', '+', '=', 'o', '0', 'z', 'Z', 'y', 'Y', 'l', '1', 'i', 'I'];

        $string = '';

        while (($len = strlen($string)) < self::PASSWORD_LENGTH) {
            $size = self::PASSWORD_LENGTH - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace($disabled_chars, '', base64_encode($bytes)), 0, $size);
        }
        return $string;
    }
}
