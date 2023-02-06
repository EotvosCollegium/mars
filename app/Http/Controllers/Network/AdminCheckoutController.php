<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\Checkout;
use App\Models\User;
use App\Utils\CheckoutHandler;
use Illuminate\Support\Collection;

class AdminCheckoutController extends Controller
{
    use CheckoutHandler;

    /**
     * Return the route base for the admin checkout.
     */
    public static function routeBase()
    {
        return 'admin.checkout';
    }

    /**
     * Return the admin checkout.
     */
    public static function checkout(): Checkout
    {
        return Checkout::admin();
    }

    /**
     * Show the admin checkout page.
     */
    public function showCheckout()
    {
        $checkout = Checkout::admin();
        $this->authorize('view', $checkout);

        return view('network.checkout', $this->getData($checkout));
    }
}
