<?php

namespace App\Http\Controllers\Dormitory\Printing;

use App\Http\Controllers\Controller;
use App\Models\FreePages;
use App\Utils\TabulatorPaginator;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FreePagesController extends Controller {

    public  function indexFreePages(?string $filter = null) {
        if ($filter === "all") {
            $this->authorize('viewAny', FreePages::class);

            return $this->freePagesPaginator(
                freePages: FreePages::with('user'),
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

        $this->authorize('viewSelf', FreePages::class);

        return $this->freePagesPaginator(
            freePages: user()->freePages(),
            columns: [
                'amount',
                'deadline',
                'modifier',
                'comment',
            ]
        );
    }

    private function freePagesPaginator(Builder $freePages, array $columns) {
        $paginator = TabulatorPaginator::from(
            $freePages->with('modifier')
        )->sortable($columns)->filterable($columns)->paginate();
        return $paginator;
    }

    public function store(Request $request) {
        $data = $request->validate([
            "user_id" => "required|exists:users,id",
            "amount" => "required|integer|min:1",
            "deadline" => "required|date|after:date:now",
            "comment" => "string",
        ]);

        $this->authorize('create', FreePages::class);

        FreePages::create($data + [
            "last_modified_by" => user()->id,
        ]);

        return redirect()->back()->with('message', __('general.successfully_added'));
    }
}