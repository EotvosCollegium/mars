<?php

namespace App\Exports;

use App\Models\Semester;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class UsersExport implements FromCollection, WithTitle, WithMapping, WithHeadings, ShouldAutoSize
{
    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function collection()
    {
        return $this->users;
    }

    public function title(): string
    {
        return "Collegisták";
    }

    public function headings(): array
    {
        $semesters = Semester::all()->sortByDesc('tag')->map(function ($semester) {
            return 'Státusz ('.$semester->tag.')';
        })->toArray();

        return array_merge([
            'Név',
            'E-mail',
            'Születési hely',
            'Születési idő',
            'Anyja neve',
            'Telefonszám',
            'Lakhely',
            'Érettségi éve',
            'Középiskola',
            'Neptun kód',
            'Collegiumi felvétel éve',
            'Egyetemi e-mail',
            'Szak',
            'Kar',
            'Műhely',
            'Bentlakó/Bejáró',
            'Alfonsó'
        ], $semesters);
    }

    public function map($user): array
    {
        $semesters = Semester::all()->sortByDesc('tag')->map(function ($semester) use ($user) {
            $status = $user->getStatus($semester);
            if($status) {
                $text = __('user.'.$status->status);
                if($status->comment) {
                    $text .= ' ('.$status->comment.')';
                }
            }
            return $text ?? '';
        })->toArray();

        $data = [
            $user->name,
            $user->email,
            $user->personalInformation?->place_of_birth,
            $user->personalInformation?->date_of_birth,
            $user->personalInformation?->mothers_name,
            $user->personalInformation?->phone_number,
            $user->personalInformation?->getAddress(),
            $user->educationalInformation?->year_of_graduation,
            $user->educationalInformation?->high_school,
            $user->educationalInformation?->neptun,
            $user->educationalInformation?->year_of_acceptance,
            $user->educationalInformation?->email,
            $user->educationalInformation?->studyLines?->map(function ($studyLine) {
                return $studyLine->getNameWithYear();
            })->implode(', '),
            implode(", ", $user->faculties->pluck('name')->toArray()),
            implode(", ", $user->workshops->pluck('name')->toArray()),
            $user->isResident() ? 'Bentlakó' : 'Bejáró',
            $user->educationalInformation?->alfonso_language . " " . $user->educationalInformation?->alfonso_desired_level,
        ];

        return array_merge($data, $semesters);
        ;

    }
}
