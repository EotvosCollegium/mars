<?php

namespace App\Http\Controllers\Dormitory\Printing;

use App\Http\Controllers\Controller;
use App\Models\FreePrintingCredits;
use App\Utils\TabulatorPaginator;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class FreePrintingCreditsController extends Controller
{
    /**
     * Returns a paginated list of the current user's `FreePrintingCredits`.
     * @return LengthAwarePaginator
     */
    public function index()
    {
        $this->authorize('viewSelf', FreePrintingCredits::class);

        return $this->freePrintingCreditsPaginator(
            freePrintingCredits: user()->freePrintingCredits(),
            columns: [
                'amount',
                'deadline',
                'modifier',
                'comment',
            ]
        );
    }

    /**
     * Returns a paginated list of all `FreePrintingCredits`.
     * @return LengthAwarePaginator
     */
    public function adminIndex()
    {
        $this->authorize('viewAny', FreePrintingCredits::class);

        return $this->freePrintingCreditsPaginator(
            freePrintingCredits: FreePrintingCredits::with('user'),
            columns: [
                'amount',
                'deadline',
                'modifier',
                'comment',
                'user.name',
                'created_at',
            ]
        );
    }


    /**
     * Private helper function to create a paginator for `FreePrintingCredits`.
     */
    private function freePrintingCreditsPaginator(Builder $freePrintingCredits, array $columns)
    {
        $paginator = TabulatorPaginator::from(
            $freePrintingCredits->with('modifier')
        )->sortable($columns)->filterable($columns)->paginate();
        return $paginator;
    }

    /**
     * Adds new free printing credits to a user's account.
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            "user_id" => "required|exists:users,id",
            "free_credits" => "required|integer|min:1",
            "deadline" => "required|date|after:date:now",
            "comment" => "string",
        ]);

        $this->authorize('create', FreePrintingCredits::class);

        FreePrintingCredits::create($data + [
            "amount" => $data["free_credits"],
            "last_modified_by" => user()->id,
        ]);

        return redirect()->back()->with('message', __('general.successfully_added'));
    }
}
