<?php

namespace App\Http\Controllers\Dormitory\Printing;

use App\Http\Controllers\Controller;
use App\Models\PrintAccountHistory;
use App\Models\PrintJob;
use App\Utils\TabulatorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use InvalidArgumentException;

class PrintAccountHistoryController extends Controller
{
    /**
     * Returns a paginated list of `PrintAccountHistory` entries.
     * @return LengthAwarePaginator
     * @throws AuthorizationException
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    public function index()
    {
        $this->authorize('viewAny', PrintJob::class);

        $columns = ['user.name', 'balance_change', 'free_page_change', 'deadline_change', 'modifier.name', 'modified_at'];
        return TabulatorPaginator::from(
            PrintAccountHistory::with(['user', 'modifier'])->select('print_account_history.*')
        )->sortable($columns)
            ->filterable($columns)
            ->paginate();
    }
}
