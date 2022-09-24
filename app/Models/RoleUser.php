<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Cache;

/**
 * @property RoleObject $object
 * @property Workshop $workshop
 * @property integer|null $object_id
 * @property integer|null $workshop_id
 */
class RoleUser extends Pivot
{
    protected $fillable = ['workshop_id', 'object_id'];

    protected $with = ['workshop', 'object'];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }
    public function object(): BelongsTo
    {
        return $this->belongsTo(RoleObject::class);
    }

    public function getTranslatedNameAttribute(): string
    {
        return Cache::remember('role_user_'.$this->id.'_translated_name', 86400, function () {
            if ($this->object_id) {
                return $this->object->translatedName;
            }
            if ($this->workshop_id) {
                return $this->workshop->name;
            }
            return '';
        });
    }
}
