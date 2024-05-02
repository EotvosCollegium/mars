<?php

namespace App\Livewire;

use App\Models\Semester;
use App\Models\User;
use App\Models\Transaction;
use App\Models\PaymentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class KktNetreg extends Component
{
    public $workshops = [];

    /**
     * Return all the users who have not paid their community tax in this semester.
     */
    public function getUnpaidUsersProperty()
    {
        $query = User::hasToPayKKTNetreg()->canView();

        $query->where(function (Builder $query) {
            foreach ($this->workshops as $workshop) {
                $query->whereHas('workshops', function (Builder $query) use ($workshop) {
                    $query->where('id', $workshop);
                });
            }
        });

        return $query
            ->with(['workshops'])
            ->orderBy('name')->get();
    }


    /**
     * Return all the completed payments in this semester.
     */
    public function getPaymentsProperty()
    {
        $query = Transaction::whereIn('payment_type_id', [PaymentType::kkt()->id, PaymentType::netreg()->id])->where('semester_id', Semester::current()->id);

        $query->where(function (Builder $query) {
            foreach ($this->workshops as $workshop) {
                $query->whereHas('payer', function (Builder $query) use ($workshop) {
                    $query->whereHas('workshops', function (Builder $query) use ($workshop) {
                        $query->where('id', $workshop);
                    });
                });
            }
        });

        return $query
            ->with(['payer'])
            ->get();
    }

    /**
     * Add a workshop to filter on.
     *
     * @param int $workshop_id
     */
    public function addWorkshop($workshop_id)
    {
        $this->workshops[] = $workshop_id;
    }

    /**
     * Delete a workshop from the list of workshops to filter on.
     *
     * @param int $workshop_id
     */
    public function deleteWorkshop($workshop_id)
    {
        $this->workshops = \array_diff($this->workshops, [$workshop_id]);
    }

    /**
     * Render the component.
     *
     * @return View
     */
    public function render()
    {
        return view('student-council.economic-committee.kktnetreg_component');
    }
}
