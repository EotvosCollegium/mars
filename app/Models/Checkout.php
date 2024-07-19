<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\Checkout
 *
 * @property mixed $name
 * @property mixed $id
 * @property null|User $handler
 * @property int|null $handler_id
 * @property-read Collection|\App\Models\Transaction[] $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout query()
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout whereHandlerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Checkout whereName($value)
 * @mixin \Eloquent
 */
class Checkout extends Model
{
    protected $fillable = ['name', 'handler_id'];

    public $timestamps = false;

    public const STUDENTS_COUNCIL = 'VALASZTMANY';
    public const ADMIN = 'ADMIN';
    public const TYPES = [
        self::STUDENTS_COUNCIL,
        self::ADMIN,
    ];

    /**
     * @return BelongsTo the user who can handle the checkout
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handler_id');
    }

    /**
     * @return HasMany the transactions attached to the checkout
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return Semester[]|Collection the transaction in the checkout grouped by the semesters and payment types.
     * The workshopbalances are loaded and attached also.
     */
    public function transactionsBySemesters(): Collection
    {
        return Semester::orderBy('year', 'desc')
            ->orderBy('part', 'desc')
            ->get()
            ->load([
                'transactions' => function ($query) {
                    $query->where('checkout_id', $this->id);

                    $query->with('type');
                },
                'workshopBalances.workshop',
            ]);
    }

    /**
     * @return int the sum of the transactions
     */
    public function balance(): int
    {
        return $this->transactions->sum('amount');
    }

    /**
     * @return int the sum of the transactions which are not moved to the checkout
     */
    public function balanceInCheckout(): int
    {
        return $this->transactions
            ->where('moved_to_checkout', '<>', null)
            ->sum('amount');
    }

    /**
     * @return Checkout the admin checkout from cache
     */
    public static function admin(): Checkout
    {
        return Cache::remember('checkout.'.self::ADMIN, 86400, function () {
            return self::where('name', self::ADMIN)->firstOrFail();
        });
    }

    /**
     * @return Checkout the student council's checkout from cache
     */
    public static function studentsCouncil(): Checkout
    {
        return Cache::remember('checkout.'.self::STUDENTS_COUNCIL, 86400, function () {
            return self::where('name', self::STUDENTS_COUNCIL)->firstOrFail();
        });
    }

    public function kktSum(Semester $semester): int
    {
        return $this->transactionSum($semester, PaymentType::kkt()->id);
    }

    public function netregSum(Semester $semester): int
    {
        return $this->transactionSum($semester, PaymentType::netreg()->id);
    }

    public function printSum(Semester $semester): int
    {
        return $this->transactionSum($semester, PaymentType::print()->id);
    }

    public function transactionSum(Semester $semester, $typeId): int
    {
        return $this->transactions
            ->where('payment_type_id', $typeId)
            ->where('semester_id', $semester->id)
            ->sum('amount');
    }
}
