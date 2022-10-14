<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $id
 * @property string $name
 * @property integer $role_id
 * @property string $translatedName
 */
class RoleObject extends Model
{
    protected $fillable = [
        'role_id', 'name',
    ];

    protected $with = ['role'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the translated_name attribute.
     *
     * @return Attribute
     */
    public function translatedName(): Attribute
    {
        return Attribute::make(
            get: fn () => __('role.'.$this->name)
        );
    }

    public static function president(): RoleObject|null
    {
        return self::firstWhere('name', 'president');
    }
}
