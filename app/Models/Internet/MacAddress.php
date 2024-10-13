<?php

namespace App\Models\Internet;

use App\Models\User;
use App\Utils\NotificationCounter;
use Database\Factories\Internet\MacAddressFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Internet\MacAddress
 *
 * @property string $user_id
 * @property string $mac_address
 * @property string $comment
 * @property string $state
 * @property-read string $translated_state
 * @property string $ip
 * @property User $user
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read InternetAccess $internetAccess
 * @method static MacAddressFactory factory(...$parameters)
 * @method static Builder|MacAddress newModelQuery()
 * @method static Builder|MacAddress newQuery()
 * @method static Builder|MacAddress query()
 * @method static Builder|MacAddress whereComment($value)
 * @method static Builder|MacAddress whereCreatedAt($value)
 * @method static Builder|MacAddress whereId($value)
 * @method static Builder|MacAddress whereMacAddress($value)
 * @method static Builder|MacAddress whereState($value)
 * @method static Builder|MacAddress whereUpdatedAt($value)
 * @method static Builder|MacAddress whereUserId($value)
 * @mixin Eloquent
 */
class MacAddress extends Model
{
    use SoftDeletes;
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

    // Return translated state every time the model is retrieved.
    protected $appends = ['translated_state'];

    /**
     * Get the translated_state attribute.
     *
     * @return Attribute
     */
    public function translatedState(): Attribute
    {
        return Attribute::make(
            get: fn () => __('internet.' . strtolower($this->state))
        );
    }

    /**
     * The user's internet access model.
     *
     * @return BelongsTo
     */
    public function internetAccess(): BelongsTo
    {
        return $this->belongsTo(InternetAccess::class, 'user_id', 'user_id');
    }

    /**
     * The user that this mac address belongs to.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            InternetAccess::class,
            'user_id', // Foreign key on InternetAccess table...
            'id', // Foreign key on Users table...
            'user_id', // Local key on MacAddresses table...
            'user_id' // Local key on InternetAccess table...
        );
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

    /**
     * The number of mac addresses waiting for approval.
     * @return int
     */
    public static function notificationCount(): int
    {
        return self::where('state', self::REQUESTED)->count();
    }
}
