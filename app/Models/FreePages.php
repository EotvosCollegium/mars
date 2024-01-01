<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model to keep track of the users' free pages.
 *
 * Changes are logged in print_account_history table. See FreePagesObserver.
 *
 * @property mixed $user_id
 * @property int $id
 * @property int $amount
 * @property string $deadline
 * @property int $last_modified_by
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PrintAccount $printAccount
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\FreePagesFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePages newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FreePages newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FreePages query()
 * @method static \Illuminate\Database\Eloquent\Builder|FreePages whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePages whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePages whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePages whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePages whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePages whereLastModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePages whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePages whereUserId($value)
 * @mixin \Eloquent
 */
class FreePages extends Model
{
    use HasFactory;

    protected $table = 'printing_free_pages';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'amount',
        'deadline',
        'last_modified_by',
        'comment',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function printAccount()
    {
        return $this->belongsTo('App\Models\PrintAccount', 'user_id', 'user_id');
    }

    public function available()
    {
        return $this->deadline > date('Y-m-d');
    }

    public function lastModifiedBy()
    {
        return User::find($this->last_modified_by);
    }
}
