<?php

namespace App\Http\Controllers\Dormitory\Printing;

use App\Http\Controllers\Controller;
use App\Models\PrintAccountHistory;
use App\Models\PrintJob;
use App\Utils\TabulatorPaginator;

class PrintAccountHistoryController extends Controller {

    public function indexPrintAccountHistory() {
        $this->authorize('viewAny', PrintJob::class);

        $columns = ['user.name', 'balance_change', 'free_page_change', 'deadline_change', 'modifier.name', 'modified_at'];
        return TabulatorPaginator::from(
            PrintAccountHistory::join('users as user', 'user.id', '=', 'user_id')
                ->join('users as modifier', 'modifier.id', '=', 'modified_by')
                ->select('print_account_history.*')
                ->with('user') // TODO: check this
                ->with('modifier')
        )->sortable($columns)
            ->filterable($columns)
            ->paginate();
    }
}
