<?php

namespace App\Models\Internet;

use App\Models\User;
use App\Utils\NotificationCounter;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Internet\MacAddress
 *
 * @property string $user_id
 * @property string $mac_address
 * @property string $comment
 * @property string $state
 * @property string $ip
 * @property User $user
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\Internet\MacAddressFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|MacAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MacAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MacAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder|MacAddress whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MacAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MacAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MacAddress whereMacAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MacAddress whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MacAddress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MacAddress whereUserId($value)
 * @mixin \Eloquent
 */
class MacAddress extends Model
{
    use HasFactory;
    use NotificationCounter;

    public const REQUESTED = 'REQUESTED';
    public const APPROVED = 'APPROVED';
    public const REJECTED = 'REJECTED';
    public const STATES = [self::APPROVED, self::REJECTED, self::REQUESTED];

    protected $table = 'mac_addresses';

    protected $fillable = [
        'user_id', 'mac_address', 'comment', 'state', 'ip',
    ];

    protected $attributes = [
        'comment' => '',
        'state' => self::REQUESTED,
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Format and set the mac_address attribute.
     *
     * @return Attribute
     */
    public function macAddress(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => str_replace('-', ':', strtoupper($value)),
        );
    }

    public static function notifications()
    {
        return self::where('state', self::REQUESTED)->count();
    }
}
