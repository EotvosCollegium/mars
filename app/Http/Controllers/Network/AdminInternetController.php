<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\Internet\InternetAccess;
use App\Models\Internet\WifiConnection;
use App\Utils\TabulatorPaginator;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdminInternetController extends Controller
{

    /**
     * Show the admin internet access page.
     *
     * @throws AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('handleAny', InternetAccess::class);

        return view('network.admin.app', [
            'activation_date' => InternetAccess::getInternetDeadline(),
            'users' => InternetAccess::query()->with('user')->get()
        ]);
    }

    /**
     * Return paginated internet access data.
     *
     * @return LengthAwarePaginator
     * @throws AuthorizationException
     */
    public function indexInternetAccesses(): LengthAwarePaginator
    {
        $this->authorize('handleAny', InternetAccess::class);

        return TabulatorPaginator::from(InternetAccess::query()->with('user'))
            ->sortable(['has_internet_until', 'user.name'])
            ->filterable(['has_internet_until', 'user.name'])
            ->paginate();
    }

    /**
     * Get paginated wifi connections data.
     * @param Request $request
     * @return LengthAwarePaginator
     * @throws AuthorizationException
     */
    public function indexWifi(Request $request): LengthAwarePaginator
    {
        $this->authorize('viewAny', WifiConnection::class);

        return TabulatorPaginator::from(
            WifiConnection::query()
                ->groupBy(['wifi_username', 'mac_address', 'ip', 'lease_start', 'lease_end', 'note'])
                ->select(['wifi_username', 'mac_address', 'ip', 'lease_start', 'lease_end', 'note',
                    DB::raw('COUNT(*) as radius_connections')])
        )
            ->sortable(['wifi_username', 'mac_address', 'ip', 'lease_start'])
            ->filterable(['wifi_username', 'mac_address', 'ip', 'lease_start'])
            ->paginate();
    }


    /**
     * @throws AuthorizationException
     */
    public function extend(InternetAccess $internetAccess, Carbon $newDate = null): ?Carbon
    {
        $this->authorize('extend', $internetAccess);

        $newDate = $newDate ?? InternetAccess::getInternetDeadline();
        $internetAccess->update(['has_internet_until' => $newDate]);
        return $newDate;
    }

}
