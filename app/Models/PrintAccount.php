<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model to keep track of the users' print balance.
 *
 * Changes are logged in print_account_history table. See PrintAccountObserver.
 *
 * @property mixed $user_id
 * @property int $balance
 * @property int|null $last_modified_by
 * @property string|null $modified_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FreePages[] $freePages
 * @property-read int|null $free_pages_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\PrintAccountFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccount whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccount whereLastModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccount whereModifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrintAccount whereUserId($value)
 * @mixin \Eloquent
 */
class PrintAccount extends Model
{
    use HasFactory;

    protected $table = 'print_accounts';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    public $timestamps = false;

    public static $COST;

    protected $fillable = [
        'user_id',
        'balance',
        'last_modified_by',
        'modified_at',
    ];

    /**
     * The model's default values for attributes.
     */
    protected $attributes = [
        'balance' => 0,
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function freePages()
    {
        return $this->hasMany('App\Models\FreePages', 'user_id', 'user_id');
    }

    public function hasEnoughMoney($balance)
    {
        return $this->balance >= abs($balance);
    }

    public static function getCost($pages, $is_two_sided, $number_of_copies)
    {
        if (!$is_two_sided) {
            return $pages * self::$COST['one_sided'] * $number_of_copies;
        }

        $orphan_ending = $pages % 2;
        $one_copy_cost = floor($pages / 2) * self::$COST['two_sided']
            + $orphan_ending * self::$COST['one_sided'];

        return $one_copy_cost * $number_of_copies;
    }
}

PrintAccount::$COST = config('print.cost');
