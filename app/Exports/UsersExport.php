<?php

namespace App\Exports;

use App\Models\Semester;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;
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
        return [
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
            'Státusz ('.Semester::current()->tag.')',
            'Státusz ('.Semester::previous()->tag.')',
        ];
    }

    public function map($user): array
    {
        return [
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
            $user->educationalInformation?->programs,
            implode(",", $user->faculties->pluck('name')->toArray()),
            implode(",", $user->workshops->pluck('name')->toArray()),
            __('user.'.$user->getStatus()),
            __('user.'.$user->getStatusIn(Semester::previous()->id))
        ];
    }
}
