<?php

namespace App\Models;

use App\Enums\FileType;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\File
 *
 * @property int $id
 * @property int|null $application_id
 * @property int|null $user_id
 * @property int|null $transaction_id
 * @property FileType|null $type
 * @property string|null $description
 * @property string $path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|File query()
 * @method static \Illuminate\Database\Eloquent\Builder|File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class File extends Model
{
    protected $table = 'files';

    protected $fillable = [
        'application_id',
        'user_id',
        'transaction_id',
        'type',
        'description',
        'path',
    ];

    protected $casts = [
        'type' => FileType::class,
    ];
}
