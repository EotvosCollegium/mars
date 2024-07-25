<?php

namespace App\Exports\UsersSheets;

use App\Models\Semester;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class CollegistsExport implements FromCollection, WithTitle, WithMapping, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $users;
    protected $semester;

    public function __construct(Collection|User $includedUsers)
    {
        $this->users = $includedUsers;
        $this->semester = Semester::current();
    }

    public function collection()
    {
        return $this->users;
    }

    public function title(): string
    {
        return user()->isAdmin() ? "Felhasználók" : "Collegisták";
    }

    public function headings(): array
    {
        return [
            'Név',
            'Neptun-kód',
            'Collegista státusz',
            'Státusz ('.$this->semester->tag.')',
            'E-mail',
            'Egyetemi e-mail',
            'Születési hely',
            'Születési idő',
            'Anyja neve',
            'Telefonszám',
            'Lakhely',
            'Érettségi éve',
            'Középiskola',
            'Collegiumi felvétel éve',
            'Szak',
            'Kar',
            'Műhely',
            'Nyelvvizsgák felvétel előtt',
            'Nyelvvizsgák felvétel után',
            'Alfonsó',
            'Alfonsó teljesítve?',
            'Szobaszám',
        ];
    }

    public function map($user): array
    {

        return [
            '=HYPERLINK("'.route('users.show', ['user' => $user->id]).'", "'.$user->name.'")',
            $user->educationalInformation?->neptun,
            $user->isResident() ? 'Bentlakó' : ($user->isExtern() ? 'Bejáró' : ($user->isAlumni() ? "Alumni" : ($user->isTenant() ? "Vendég" : ""))),
            $user->getStatus($this->semester)?->translatedStatus(),
            $user->email,
            $user->educationalInformation?->email,
            $user->personalInformation?->place_of_birth,
            $user->personalInformation?->date_of_birth,
            $user->personalInformation?->mothers_name,
            $user->personalInformation?->phone_number,
            $user->personalInformation?->getAddress(),
            $user->educationalInformation?->year_of_graduation,
            $user->educationalInformation?->high_school,
            $user->educationalInformation?->year_of_acceptance,
            $user->educationalInformation?->studyLines?->map(function ($studyLine) {
                return $studyLine->getNameWithYear();
            })->implode(" \n"),
            implode(" \n", $user->faculties->pluck('name')->toArray()),
            implode(" \n", $user->workshops->pluck('name')->toArray()),
            $user->educationalInformation?->languageExamsBeforeAcceptance?->map(function ($exam) {
                return implode(", ", [__('role.'.$exam->language), $exam->level, $exam->type, $exam->date->format('Y-m')]);
            })->implode(" \n"),
            $user->educationalInformation?->languageExamsAfterAcceptance?->map(function ($exam) {
                return implode(", ", [__('role.'.$exam->language), $exam->level, $exam->type, $exam->date->format('Y-m')]);
            })->implode(" \n"),
            ($user->educationalInformation?->alfonso_language ?
                __('role.'.$user->educationalInformation->alfonso_language) . " " . $user->educationalInformation->alfonso_desired_level
                : ""),
            ($user->educationalInformation?->alfonsoCompleted() ?? false)
                ? 'Igen'
                : (($user->educationalInformation?->alfonsoCanBeCompleted() ?? true) ? "Folyamatban" : "Nem"),
            $user->room,
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
