<?php

namespace App\Exports;

use App\Exports\UsersSheets\CollegistsExport;
use App\Exports\UsersSheets\SemesterEvaluationExport;
use App\Exports\UsersSheets\StatusesExport;
use App\Exports\UsersSheets\StudentsCouncilFeedback;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Style\Style;

class UsersExport implements WithMultipleSheets, WithDefaultStyles
{
    private Builder|User $includedUsersQuery;

    public function __construct(Builder|User $includedUsersQuery)
    {
        $this->includedUsersQuery = $includedUsersQuery;
    }

    public function sheets(): array
    {
        $sheets = [
            new CollegistsExport($this->includedUsersQuery->clone()),
            new StatusesExport($this->includedUsersQuery->clone()),
        ];

        if(user()->can('viewSemesterEvaluation', User::class)) {
            $sheets[] = new SemesterEvaluationExport($this->includedUsersQuery->clone());
            if(user()->hasRole(Role::STUDENT_COUNCIL)) {
                $sheets[] = new StudentsCouncilFeedback($this->includedUsersQuery->clone());
            }
        }

        return $sheets;
    }

    public function defaultStyles(Style $defaultStyle)
    {
        return $defaultStyle->getAlignment()->setWrapText(true);
    }

}
