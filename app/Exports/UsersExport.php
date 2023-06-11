<?php

namespace App\Exports;

use App\Exports\UsersSheets\CollegistsExport;
use App\Exports\UsersSheets\SemesterEvaluationExport;
use App\Exports\UsersSheets\StatusesExport;
use App\Exports\UsersSheets\StudentsCouncilFeedback;
use App\Models\Role;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Style\Style;

class UsersExport implements WithMultipleSheets, WithDefaultStyles
{
    public function sheets(): array
    {
        $sheets = [new CollegistsExport(), new StatusesExport()];

        if(user()->can('viewSemesterEvaluation', User::class)) {
            $sheets[] = new SemesterEvaluationExport();
            if(user()->hasRole(Role::STUDENT_COUNCIL)) {
                $sheets[] = new StudentsCouncilFeedback(true);
            }
        }

        return $sheets;
    }

    public function defaultStyles(Style $defaultStyle)
    {
        return $defaultStyle->getAlignment()->setWrapText(true);
    }

}
