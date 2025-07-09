<?php

namespace App\Models;

use App\Utils\PrinterHelper;
use App\Models\PrinterConfiguration;
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FreePrintingCredits[] $freePrintingCredits
 * @property-read int|null $free_printing_credits_count
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

    public function freePrintingCredits()
    {
        return $this->hasMany(FreePrintingCredits::class, 'user_id', 'user_id');
    }

    /**
     * The free credits which are currently available. Sorts the free credits by their deadline.
     * @return Collection|FreePrintingCredits[]
     */
    public function availableFreePrintingCredits()
    {
        return $this->freePrintingCredits()->where('deadline', '>', now())->orderBy('deadline')->get();
    }

    /**
     * Returns wether the user has enough free credits to print a document.
     * @param int $pages
     * @param int $copies
     * @param PrinterConfiguration $configuration
     * @return bool
     */
    public function hasEnoughFreePrintingCredits(int $pages, int $copies, PrinterConfiguration $configuration)
    {
        return $this->availableFreePrintingCredits()->sum('amount') >=
            $configuration->getPrice($pages, $copies);
    }

    /**
     * Returns wether the user has enough balance to print a document.
     * @param int $pages
     * @param int $copies
     * @param PrinterConfiguration $configuration
     * @return bool
     */
    public function hasEnoughBalance(int $pages, int $copies, PrinterConfiguration $configuration)
    {
        return $this->balance >= $configuration->getPrice($pages, $copies);
    }

    /**
     * Returns wether the user has enough balance or free credits to print a document.
     * @param bool $useFreePrintingCredits
     * @param int $pages
     * @param int $copies
     * @param PrinterConfiguration $configuration
     * @return bool
     */
    public function hasEnoughBalanceOrFreePrintingCredits(bool $useFreePrintingCredits, int $pages, int $copies, PrinterConfiguration $configuration)
    {
        return $useFreePrintingCredits ? $this->hasEnoughFreePrintingCredits($pages, $copies, $configuration) : $this->hasEnoughBalance($pages, $copies, $configuration);
    }

    /**
     * Updates the print account history and the print account balance.
     * Important note: This function should only be called within a transaction. Otherwise, the history may not be consistent.
     * @param bool $useFreePrintingCredits
     * @param int $cost
     */
    public function updateHistory(bool $useFreePrintingCredits, int $cost)
    {
        // Update the print account history
        $this->last_modified_by = user()->id;

        if ($useFreePrintingCredits) {
            $freePrintingCreditsToSubtract = $cost;
            $availableFreePrintingCredits = $this->availableFreePrintingCredits()->where('amount', '>', 0);

            // Subtract the credits from the free credits pool, as many free credits as necessary
            /** @var FreePrintingCredits $freePrintingCredits */
            foreach ($availableFreePrintingCredits as $freePrintingCredits) {
                $subtractableCredits = $freePrintingCredits->calculateSubtractableCredits($freePrintingCreditsToSubtract);
                $freePrintingCredits->subtractCredits($subtractableCredits);
                $freePrintingCreditsToSubtract -= $subtractableCredits;

                if ($freePrintingCreditsToSubtract <= 0) { // < should not be necessary, but better safe than sorry
                    break;
                }
            }
            // Set value in the session so that free printing credits checkbox stays checked
            session()->put('use_free_printing_credits', true);
        } else {
            $this->balance -= $cost;

            // Remove value regarding the free printing credits checkbox from the session
            session()->remove('use_free_printing_credits');
        }

        $this->save();
    }


}
