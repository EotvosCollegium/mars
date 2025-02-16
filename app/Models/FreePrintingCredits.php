<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model to keep track of the users' free printing credits.
 *
 * Changes are logged in print_account_history table. See FreePrintingCreditsObserver.
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
 * @method static \Database\Factories\FreePrintingCreditsFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePrintingCredits newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FreePrintingCredits newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FreePrintingCredits query()
 * @method static \Illuminate\Database\Eloquent\Builder|FreePrintingCredits whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePrintingCredits whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePrintingCredits whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePrintingCredits whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePrintingCredits whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePrintingCredits whereLastModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePrintingCredits whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FreePrintingCredits whereUserId($value)
 * @mixin \Eloquent
 */
class FreePrintingCredits extends Model
{
    use HasFactory;

    protected $table = 'printing_free_printing_credits';
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

    protected $casts = [
        'deadline' => 'date',
    ];

    /**
     * The user this free printing credits entry belongs to.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The print account this free printing credits entry belongs to.
     * @return BelongsTo
     */
    public function printAccount()
    {
        return $this->belongsTo(PrintAccount::class, 'user_id', 'user_id');
    }

    /**
     * Wether the free printing credits are still available.
     * @return Attribute
     */
    protected function available(): Attribute
    {
        return Attribute::make(
            get: fn () => now()->isBefore($this->deadline)
        );
    }

    /**
     * The user who last modified this free printing credits entry.
     * @return BelongsTo
     */
    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }


    /**
     * Returns the amount of credits that can be subtracted from the free printing credits.
     * If the amount is greater than the available credits, only the available credits are subtracted.
     * @param int $amount
     * @return int
     */
    public function calculateSubtractableCredits(int $amount)
    {
        return min($amount, $this->amount);
    }

    /**
     * Subtracts the given amount of credits from the free printing credits.
     * If the amount is greater than the available credits, only the available credits are subtracted.
     * @param int $amount
     */
    public function subtractCredits(int $amount)
    {
        if ($amount <= 0 || $amount > $this->amount) {
            throw new \InvalidArgumentException("Amount must be greater than 0 and less than or equal to the available credits.");
        }
        $this->update([
            'last_modified_by' => user()->id,
            'amount' => $this->amount - $amount,
        ]);
    }
}
