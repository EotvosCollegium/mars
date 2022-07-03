<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $id
 * @property string $name
 * @property integer $role_id
 */
class RoleObject extends Model
{
    protected $fillable = [
        'role_id', 'name',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

}
