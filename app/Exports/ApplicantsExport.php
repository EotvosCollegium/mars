<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ApplicantsExport implements FromCollection, WithTitle, WithMapping, WithHeadings, ShouldAutoSize
{
    protected $applications;

    public function __construct($applications)
    {
        $this->applications = $applications;
    }

    public function collection()
    {
        return $this->applications;
    }

    public function title(): string
    {
        return "Felvételizők";
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
            'Egyetemi e-mail',
            'Szak',
            'Kar',
            'Megjelölt műhely',
            'Megjelölt státusz',
            'Alfonsó',
            'Felvételi alatt itt lesz?',
            'Igényel szállást?'
        ];
    }

    public function map($application): array
    {
        $user = $application->user;

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
            $user->educationalInformation?->email,
            $user->educationalInformation?->studyLines?->map(function ($studyLine) {
                return $studyLine->getNameWithYear();
            })->implode(', '),
            implode(",", $user->faculties->pluck('name')->toArray()),
            implode(",", $user->workshops->pluck('name')->toArray()),
            $user->isResident() ? 'Bentlakó' : 'Bejáró',
            $user->educationalInformation?->alfonso_language . " " . $user->educationalInformation?->alfonso_desired_level,
            $application->present ?? "Igen",
            $user->application->accommodation ? "Igen" : "Nem"
        ];
    }
}
