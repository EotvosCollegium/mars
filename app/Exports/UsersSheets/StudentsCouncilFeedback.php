<?php

namespace App\Exports\UsersSheets;

use App\Models\SemesterEvaluation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class StudentsCouncilFeedback implements FromCollection, WithTitle, WithMapping, WithHeadings, ShouldAutoSize
{
    protected $evaluations;

    public function __construct(Collection|User $includedUsers)
    {
        $last_semester_id = SemesterEvaluation::query()->orderBy('created_at', 'desc')->first()?->semester_id;
        $this->evaluations = SemesterEvaluation::query()
            ->where('semester_id', $last_semester_id)
            ->whereNotNull('feedback')
            ->whereIn('user_id', $includedUsers->pluck('id'))
            ->get();

    }

    public function collection()
    {
        return $this->evaluations;
    }

    public function title(): string
    {
        return "Választmány visszajelzés";
    }

    public function headings(): array
    {
        return [
            'Név',
            'Visszajelzés'
        ];
    }

    public function map($evaluation): array
    {
        $user = $evaluation->user;

        return [
            $user->name,
            $evaluation->feedback
        ];
    }
}
