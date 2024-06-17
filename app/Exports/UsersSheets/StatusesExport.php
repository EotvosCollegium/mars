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

class StatusesExport implements FromCollection, WithTitle, WithMapping, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $users;
    protected $semesters;

    public function __construct(Collection|User $includedUsers)
    {
        $this->users = $includedUsers;
        $this->semesters = Semester::allUntilCurrent()->sortByDesc('tag');
    }

    public function collection()
    {
        return $this->users;
    }

    public function title(): string
    {
        return "Státuszok";
    }

    public function headings(): array
    {
        $semesters = $this->semesters->map(function ($semester) {
            return $semester->tag;
        })->toArray();

        return array_merge([
            'Név',
            'Neptun kód',
            'Collegiumi felvétel éve',
        ], $semesters);
    }

    public function map($user): array
    {
        $semesters = $this->semesters->map(function ($semester) use ($user) {
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
            '=HYPERLINK("'.route('users.show', ['user' => $user->id]).'", "'.$user->name.'")',
            $user->educationalInformation?->neptun,
            $user->educationalInformation?->year_of_acceptance,
        ];

        return array_merge($data, $semesters);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->freezePane('C2');
            },
        ];
    }
}
