<?php

namespace App\Models;

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

    public function getTranslatedNameAttribute(): string
    {
        return __('role.'.$this->name);
    }

    public static function president(): RoleObject|null
    {
        return self::firstWhere('name', 'president');
    }
}
