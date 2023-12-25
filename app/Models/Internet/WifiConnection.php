<?php

namespace App\Models\Internet;

use App\Models\User;
use Carbon\Carbon;
use Database\Factories\Internet\WifiConnectionFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * App\Models\Internet\WifiConnection
 *
 * This model collects the wifi connections of users over time.
 * It updates through App\Jobs\ProcessWifiConnections.
 *
 * @property string $ip
 * @property string $mac_address
 * @property string $wifi_username
 * @property string $lease_start
 * @property string $lease_end
 * @property string $radius_timestamp
 * @property string $note
 * @property InternetAccess $internetAccess
 * @property User $user
 * @property int $id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static WifiConnectionFactory factory(...$parameters)
 * @method static Builder|WifiConnection newModelQuery()
 * @method static Builder|WifiConnection newQuery()
 * @method static Builder|WifiConnection query()
 * @method static Builder|WifiConnection whereCreatedAt($value)
 * @method static Builder|WifiConnection whereId($value)
 * @method static Builder|WifiConnection whereIp($value)
 * @method static Builder|WifiConnection whereLeaseEnd($value)
 * @method static Builder|WifiConnection whereLeaseStart($value)
 * @method static Builder|WifiConnection whereMacAddress($value)
 * @method static Builder|WifiConnection whereNote($value)
 * @method static Builder|WifiConnection whereRadiusTimestamp($value)
 * @method static Builder|WifiConnection whereUpdatedAt($value)
 * @method static Builder|WifiConnection whereWifiUsername($value)
 * @mixin Eloquent
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

    /**
     * The internet access that this Wi-Fi connection belongs to.
     *
     * @return BelongsTo
     */
    public function internetAccess(): BelongsTo
    {
        return $this->belongsTo(InternetAccess::class, 'wifi_username', 'wifi_username');
    }

    /**
     * The user that this Wi-Fi connection belongs to.
     *
     * @return HasOneThrough
     */
    public function user(): HasOneThrough
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
}
