<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MrAndMissCategory
 *
 * @property int $id
 * @property string $title
 * @property int $mr
 * @property int|null $created_by
 * @property int $hidden
 * @property int $public
 * @property int $custom
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissCategory whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissCategory whereCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissCategory whereHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissCategory whereMr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissCategory wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissCategory whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MrAndMissCategory extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'title', 'mr', 'created_by', 'hidden', 'public', 'custom',
    ];
}
