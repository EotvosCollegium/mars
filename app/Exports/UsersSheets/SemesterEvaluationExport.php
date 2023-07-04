<?php

namespace App\Exports\UsersSheets;

use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\Role;
use App\Models\SemesterEvaluation;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class SemesterEvaluationExport implements FromCollection, WithTitle, WithMapping, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $evaluations;
    protected $semester;
    protected $show_feedback = false;

    public function __construct()
    {
        $this->semester = SemesterEvaluation::query()->orderBy('created_at', 'desc')->first()->semester;
        $users = User::query()->canView()->get(['id'])->pluck('id');
        $this->evaluations = SemesterEvaluation::query()
            ->where('semester_id', $this->semester->id)
            ->whereIn('user_id', $users)
            ->with('user')
            ->get()
            ->sortBy(fn ($evaluation) => $evaluation->user->name);
    }

    public function collection()
    {
        return $this->evaluations;
    }

    public function title(): string
    {
        return str_replace("/", "-", $this->semester->tag) . " értékelés";
    }

    public function headings(): array
    {
        return [
            'Név',
            'Neptun kód',
            'Szak',
            'Műhely',
            'Collegista státusz',
            'Lemondott bentlakó helyéről',
            'Státusz (jelenlegi)',
            'Státusz (következő)',
            'Kérvényt ír',
            'Alfonsó tanult nyelv',
            'Alfonsót teljesített?',
            'Alfonsó megjegyzés',
            'Átlag (jelenlegi)',
            'Átlag (előző)',
            'Kurzusok',
            'Közgyűlés (utolsó 2)',
            'Közgyűlés megjegyzés',
            'Tisztségek',
            'Közösségi tevékenység',
            'Szakmai eredmények',
            'Kutatómunka',
            'Publikációk',
            'Konferenciarészvétel',
            'Ösztöndíjak, elismerések',
            'Oktatási tevékenység',
            'Közéleti tevékenység',
            'Eredmények publikálásához hozzájárult?',
        ];
    }

    public function map($evaluation): array
    {
        $user = $evaluation->user;

        return [
            $user->name,
            $user->educationalInformation?->neptun,
            implode(" \n", $user->faculties->pluck('name')->toArray()),
            implode(" \n", $user->workshops->pluck('name')->toArray()),
            $user->isResident() ? 'Bentlakó' : ($user->isExtern() ? 'Bejáró' : ($user->isAlumni() ? "Alumni" : ($user->isTenant() ? "Vendég" : ""))),
            $evaluation->resign_residency ? 'Igen' : '',
            $user->getStatus($evaluation->semester)?->translatedStatus(),
            $user->getStatus($evaluation->semester->succ())?->translatedStatus(),
            $evaluation->will_write_request ? "Igen" : '',
            ($user->educationalInformation?->alfonso_language ? __('role.'.$user->educationalInformation?->alfonso_language) . " " . $user->educationalInformation?->alfonso_desired_level : ""),
            ($user->educationalInformation?->alfonsoCompleted() ?? false)
                ? 'Igen'
                : (($user->educationalInformation?->alfonsoCanBeCompleted() ?? true) ? "Folyamatban" : "Nem"),
            $evaluation->alfonso_note,
            $evaluation->current_avg,
            $evaluation->last_avg,
            implode(" \n", array_map(fn ($course) => $course['code'] . " " . $course['name'] . ' - ' . $course['grade'] ?? "N/A", $evaluation->courses)),
            GeneralAssembly::all()->sortByDesc('closed_at')->take(2)->map(function ($generalAssembly) use ($user) {
                return $generalAssembly->isAttended($user) ? "Részt vett" : "Nem vett részt";
            })->implode(" \n"),
            $evaluation->general_assembly_note,
            $user->roles()->whereIn('name', Role::STUDENT_POSTION_ROLES)->get()->map(function ($role) {
                if($role->has_objects || $role->has_workshops) {
                    return $role->translatedName . " (" .$role->pivot->translatedName. ")";
                } else {
                    return $role->translatedName;
                }
            })->implode(" \n"),
            $user->communityServiceRequests()->where('semester_id', $evaluation->semester->id)->get()->map(function ($communityService) {
                return $communityService->description;
            })->implode(" \n"),
            implode(" \n", $evaluation->professional_results),
            implode(" \n", $evaluation->research),
            implode(" \n", $evaluation->publications),
            implode(" \n", $evaluation->conferences),
            implode(" \n", $evaluation->scholarships),
            implode(" \n", $evaluation->educational_activity),
            implode(" \n", $evaluation->public_life_activities),
            $evaluation->can_be_shared ? "Igen" : "Nem"
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $event->sheet->getDelegate()->freezePane('C2');
            },
        ];
    }
}
