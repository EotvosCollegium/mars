<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Cache;

/**
 * RoleUser pivot model. Represents a role assigned to a user with a roleObject or Workshop in the pivot.
 *
 * @property Role $role
 * @property RoleObject $object
 * @property Workshop $workshop
 * @property User $user
 * @property string $translatedName of the roleObject or workshop
 * @property integer|null $object_id
 * @property integer|null $workshop_id
 * @property int $user_id
 * @property int $role_id
 * @property-read string $translated_name
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser whereObjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser whereWorkshopId($value)
 * @mixin \Eloquent
 */
class RoleUser extends Pivot
{
    protected $table = 'role_users';

    protected $fillable = ['workshop_id', 'object_id', 'user_id', 'role_id'];

    /**
     * Always eager load workshop and object relations.
     */
    protected $with = ['workshop', 'object'];

    /**
     * Get the belonging workshop.
     */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    /**
     * Get the belonging RoleObject.
     */
    public function object(): BelongsTo
    {
        return $this->belongsTo(RoleObject::class);
    }

    /**
     * Get the belonging user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the belonging role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the role object's translated_name attribute.
     * Uses Cache.
     *
     * @return Attribute
     */
    public function translatedName(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->object_id) {
                    return $this->object->translatedName;
                }
                if ($this->workshop_id) {
                    return $this->workshop->name;
                }
                return '';
            }
        );
    }
}
