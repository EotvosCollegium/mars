<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MrAndMissOptOut
 *
 * @property int $id
 * @property int $user_id
 * @property int $semester_id
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissOptOut newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissOptOut newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissOptOut query()
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissOptOut whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissOptOut whereSemesterId($value)
 * @mixin \Eloquent
 */
class MrAndMissOptOut extends Model
{
    protected $fillable = ['user_id', 'semester_id'];
}
