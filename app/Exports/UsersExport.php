<?php

namespace App\Exports;

use App\Exports\UsersSheets\CollegistsExport;
use App\Exports\UsersSheets\SemesterEvaluationExport;
use App\Exports\UsersSheets\StatusesExport;
use App\Exports\UsersSheets\StudentsCouncilFeedback;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Style\Style;

class UsersExport implements WithMultipleSheets, WithDefaultStyles
{
    private Collection|User $includedUsers;

    public function __construct(Collection|User $includedUsers)
    {
        $this->includedUsers = $includedUsers->sortBy('name');
    }

    public function sheets(): array
    {
        $sheets = [
            new CollegistsExport($this->includedUsers),
            new StatusesExport($this->includedUsers),
        ];

        if(user()->can('viewSemesterEvaluation', User::class)) {
            $sheets[] = new SemesterEvaluationExport($this->includedUsers);
            if(user()->hasRole(Role::STUDENT_COUNCIL)) {
                $sheets[] = new StudentsCouncilFeedback($this->includedUsers);
            }
        }

        return $sheets;
    }

    public function defaultStyles(Style $defaultStyle)
    {
        return $defaultStyle->getAlignment()->setWrapText(true);
    }

}
