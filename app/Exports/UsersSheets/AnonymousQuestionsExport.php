<?php

namespace App\Exports\UsersSheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

use App\Models\AnonymousQuestions\AnswerSheet;
use App\Models\Semester;

/**
 * Exports all answers to anonymous questions of a given semester.
 */
class AnonymousQuestionsExport implements FromCollection, WithMapping, WithHeadings
{
    /**
     * The semester whose questions are queried.
     */
    private Semester $semester;

    public function __construct(Semester $semester)
    {
        $this->semester = $semester;
    }

    /**
     * The collection on which we are going to work.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection(): \Illuminate\Support\Collection
    {
        return $this->semester->answerSheets;
    }

    /**
     * The way we create a row from an element.
     *
     * @param AnswerSheet $answerSheet
     */
    public function map($answerSheet): array
    {
        return $answerSheet->toArray();
    }

    /**
     * The first row of the spreadsheet.
     */
    public function headings(): array
    {
        return array_merge([
            __('general.id'),
            __('general.semester'),
            __('user.year_of_acceptance')
        ], $this->semester->questions()->orderBy('id')->pluck('title')->all());
    }
}
