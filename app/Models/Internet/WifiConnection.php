<?php

namespace App\Models\Internet;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * App\Models\Internet\WifiConnection
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
 * @method getColor
 * @property int $id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static \Database\Factories\Internet\WifiConnectionFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|WifiConnection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WifiConnection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WifiConnection query()
 * @method static \Illuminate\Database\Eloquent\Builder|WifiConnection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WifiConnection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WifiConnection whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WifiConnection whereLeaseEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WifiConnection whereLeaseStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WifiConnection whereMacAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WifiConnection whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WifiConnection whereRadiusTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WifiConnection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WifiConnection whereWifiUsername($value)
 * @mixin \Eloquent
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

    public function internetAccess(): BelongsTo
    {
        return $this->belongsTo(InternetAccess::class, 'wifi_username', 'wifi_username');
    }

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

    public function getColor(): string
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
