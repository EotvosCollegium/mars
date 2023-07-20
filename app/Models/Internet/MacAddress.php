<?php

namespace App\Models\Internet;

use App\Models\User;
use App\Utils\NotificationCounter;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $user_id
 * @property string $mac_address
 * @property string $comment
 * @property string $state
 * @property string $ip
 * @property User $user
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
