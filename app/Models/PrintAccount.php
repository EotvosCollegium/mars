<?php

namespace App\Models;

use App\Utils\PrinterHelper;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

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
        return $this->belongsTo(User::class);
    }

    public function freePages()
    {
        return $this->hasMany(FreePages::class, 'user_id', 'user_id');
    }

    /**
     * The free pages which are currently available. Sorts the free pages by their deadline.
     * @return Collection
     */
    public function availableFreePages()
    {
        return $this->freePages()->where('deadline', '>', now())->orderBy('deadline')->get();
    }

    /**
     * Returns wether the user has enough free pages to print a document.
     * A free page is enough to print either a one sided or a two sided page.
     * @param int $pages
     * @param int $copies
     * @param bool $twoSided
     * @return bool
     */
    public function hasEnoughFreePages(int $pages, int $copies, bool $twoSided)
    {
        return $this->availableFreePages()->sum('amount') >=
            PrinterHelper::getFreePagesNeeded($pages, $copies, $twoSided);
    }

    /**
     * Returns wether the user has enough balance to print a document.
     * @param int $pages
     * @param int $copies
     * @param bool $twoSided
     * @return bool
     */
    public function hasEnoughBalance(int $pages, int $copies, bool $twoSided)
    {
        return $this->balance >= PrinterHelper::getBalanceNeeded($pages, $copies, $twoSided);
    }

    /**
     * Returns wether the user has enough balance or free pages to print a document.
     * @param bool $useFreePages
     * @param int $pages
     * @param int $copies
     * @param bool $twoSided
     * @return bool
     */
    public function hasEnoughBalanceOrFreePages(bool $useFreePages, int $pages, int $copies, bool $twoSided)
    {
        return $useFreePages ? $this->hasEnoughFreePages($pages, $copies, $twoSided) : $this->hasEnoughBalance($pages, $copies, $twoSided);
    }

    /**
     * Updates the print account history and the print account balance.
     * Important note: This function should only be called within a transaction. Otherwise, the history may not be consistent.
     * @param bool $useFreePages
     * @param int $cost
     */
    public function updateHistory(bool $useFreePages, int $cost)
    {
        // Update the print account history
        $this->last_modified_by = user()->id;

        if ($useFreePages) {
            $freePagesToSubtract = $cost;
            $availableFreePages = $this->availableFreePages()->where('amount', '>', 0);

            // Subtract the pages from the free pages pool, as many free pages as necessary
            /** @var FreePages */
            foreach ($availableFreePages as $freePages) {
                $subtractablePages = $freePages->calculateSubtractablePages($freePagesToSubtract);
                $freePages->subtractPages($subtractablePages);
                $freePagesToSubtract -= $subtractablePages;

                if ($freePagesToSubtract <= 0) { // < should not be necessary, but better safe than sorry
                    break;
                }
            }
            // Set value in the session so that free page checkbox stays checked
            session()->put('use_free_pages', true);
        } else {
            $this->balance -= $cost;

            // Remove value regarding the free page checkbox from the session
            session()->remove('use_free_pages');
        }

        $this->save();
    }


}
