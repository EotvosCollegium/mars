<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\Checkout;
use App\Utils\CheckoutHandler;

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
    public static function checkout() : Checkout
    {
        return Checkout::admin();
    }

    public function showCheckout($redirected = false)
    {
        $checkout = Checkout::admin();
        $this->authorize('view', $checkout);

        $view = view('network.checkout', $this->getData($checkout));

        if ($redirected) {
            return $view->with('message', __('general.successfully_added'));
        }

        return $view;
    }
}
