<?php

namespace App\Livewire;

use App\Exports\UsersExport;
use App\Models\User;
use App\Utils\UserFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ListUsers extends Component
{
    public $roles = [];
    public $workshops = [];
    public $statuses = [];

    public $year_of_acceptance = null;
    public $filter_name = '';

    /**
     * @return User|Builder the query with all filters (the properties of this class) applied
     */
    private function createFilteredQuery(): User|Builder
    {
        $filter = new UserFilter(User::canView());
        $filter->roleIdsAll($this->roles)
            ->workshopIdsAll($this->workshops)
            ->statusesAny($this->statuses);
        if (isset($this->year_of_acceptance) && $this->year_of_acceptance !== '') {
            $filter->yearOfAcceptance($this->year_of_acceptance);
        }
        if (isset($this->filter_name)) {
            $filter->nameLike($this->filter_name);
        }
        return $filter->getQuery();
    }

    /**
     * Return the `users` property.
     */
    public function getUsersProperty()
    {
        return $this->createFilteredQuery()
            ->with(['roles', 'workshops', 'educationalInformation', 'semesterStatuses'])
            ->orderBy('name')->get();
    }

    /**
     * Add a role to the list of roles.
     *
     * @param int $role_id
     */
    public function addRole($role_id)
    {
        $this->roles[] = $role_id;
    }

    /**
     * Delete a role from the list of roles.
     *
     * @param int $role_id
     */
    public function deleteRole($role_id)
    {
        $this->roles = \array_diff($this->roles, [$role_id]);
    }

    /**
     * Add a status to filter on.
     *
     * @param string $status
     */
    public function addStatus($status)
    {
        $this->statuses[] = $status;
    }

    /**
     * Delete a status from the list of statuses to filter on.
     *
     * @param string $status
     */
    public function deleteStatus($status)
    {
        $this->statuses = \array_diff($this->statuses, [$status]);
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
     * Export listed users to excel
     */
    public function export()
    {
        return Excel::download(new UsersExport($this->createFilteredQuery()), 'uran_export.xlsx');
    }

    /**
     * Render the component.
     *
     * @return View
     */
    public function render()
    {
        return view('secretariat.user.list_users_component');
    }
}
