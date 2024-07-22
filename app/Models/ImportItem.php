<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ImportItem
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $serial_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ImportItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ImportItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ImportItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|ImportItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportItem whereSerialNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportItem whereUserId($value)
 * @mixin \Eloquent
 */
class ImportItem extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id', 'name', 'user_id', 'serial_number',
    ];
}
