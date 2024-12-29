<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Note: the elements of this class should no be changed manually.
// Observers for updating entries are set up.
/**
 * App\Models\PrintAccountHistory
 *
 * @property int $id
 * @property int $user_id
 * @property int $balance_change
 * @property int $free_page_change
 * @property string|null $deadline_change
 * @property int $modified_by
 * @property string $modified_at
 * @property-read \App\Models\User|null $modifier
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccountHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccountHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccountHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccountHistory whereBalanceChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccountHistory whereDeadlineChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccountHistory whereFreePageChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccountHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccountHistory whereModifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccountHistory whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccountHistory whereUserId($value)
 * @mixin \Eloquent
 */
class PrintAccountHistory extends Model
{
    protected $table = 'print_account_history';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'balance_change',
        'free_page_change',
        'modified_by',
        'modified_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
