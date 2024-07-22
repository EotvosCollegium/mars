<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\PaymentType
 *
 * @property mixed $name
 * @property int $id
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentType query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentType whereName($value)
 * @mixin \Eloquent
 */
class PaymentType extends Model
{
    protected $fillable = ['name'];

    public const INCOME = 'INCOME';
    public const EXPENSE = 'EXPENSE';
    public const KKT = 'KKT';
    public const NETREG = 'NETREG';
    public const PRINT = 'PRINT';
    public const WORKSHOP_EXPENSE = 'WORKSHOP_EXPENSE';
    public const TYPES = [
        self::INCOME,
        self::EXPENSE,
        self::KKT,
        self::NETREG,
        self::PRINT,
        self::WORKSHOP_EXPENSE
    ];

    /**
     * Get the payment types (collection) belonging to a checkout.
     * INCOME and EXPENSE belong to all checkout.
     *
     * Other, special types:
     * ADMIN: NETREG, PRINT;
     * STUDENTS_COUNCIL: KKT, WORKSHOP_EXPENSE
     *
     * Uses cache.
     *
     * @param Checkout $checkout
     * @return Collection of the payment types.
     */
    public static function forCheckout(Checkout $checkout)
    {
        return Cache::remember('paymentTypesFor.' . $checkout, 86400, function () use ($checkout) {
            $payment_types = [self::INCOME, self::EXPENSE];
            if ($checkout->name == Checkout::ADMIN) {
                $payment_types[] = self::NETREG;
                $payment_types[] = self::PRINT;
            } elseif ($checkout->name == Checkout::STUDENTS_COUNCIL) {
                $payment_types[] = self::KKT;
                $payment_types[] = self::WORKSHOP_EXPENSE;
            }

            return self::whereIn('name', $payment_types)->get();
        });
    }

    public static function income(): PaymentType
    {
        return self::getFromCache(self::INCOME);
    }

    public static function expense(): PaymentType
    {
        return self::getFromCache(self::EXPENSE);
    }

    public static function kkt(): PaymentType
    {
        return self::getFromCache(self::KKT);
    }

    public static function netreg(): PaymentType
    {
        return self::getFromCache(self::NETREG);
    }

    public static function print(): PaymentType
    {
        return self::getFromCache(self::PRINT);
    }

    public static function workshopExpense(): PaymentType
    {
        return self::getFromCache(self::WORKSHOP_EXPENSE);
    }

    /**
     * Get the paymentType by name. Uses cache.
     *
     * @param string $type payment type name
     * @return PaymentType
     */
    public static function getFromCache(string $type): PaymentType
    {
        return Cache::remember('paymentType.' . $type, 86400, function () use ($type) {
            return self::where('name', $type)->firstOrFail();
        });
    }

    /**
     * Get the paymentType by name. Uses cache.
     *
     * @param string $name payment type name
     * @return PaymentType
     */
    public static function getByName(string $name): PaymentType
    {
        return self::getFromCache($name);
    }
}
