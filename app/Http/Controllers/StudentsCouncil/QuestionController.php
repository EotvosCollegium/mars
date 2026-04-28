<?php

namespace App\Http\Controllers\StudentsCouncil;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
use App\Models\AnonymousQuestions\AnswerSheet;
use App\Models\Semester;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Utils\HasPeriodicEvent;
use App\Exports\UsersSheets\AnonymousQuestionsExport;

/**
 * Controls actions related to anonymous questions or general assembly polls.
 */
class QuestionController extends Controller
{
    /**
     * Saves a new question.
     */
    protected function createQuestion(Request $request, Semester|GeneralAssembly|null $parent = null, $opened_at = null, $closed_at = null): Question
    {
        $fn_is_selection_or_ranking = fn () => $request['question_type'] == Question::SELECTION || $request['question_type'] == Question::RANKING;
        $fn_not_selection_or_ranking = fn () => !$fn_is_selection_or_ranking();
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'question_type' => [
                'required',
                Rule::in(Question::QUESTION_TYPES)
            ],
            'max_options' => [Rule::requiredIf($fn_is_selection_or_ranking), Rule::excludeIf($fn_not_selection_or_ranking), 'min:1', 'integer'],
            'options' => [Rule::requiredIf($fn_is_selection_or_ranking), Rule::excludeIf($fn_not_selection_or_ranking), 'min:1', 'array'],
            'options.*' => [Rule::requiredIf($fn_is_selection_or_ranking), Rule::excludeIf($fn_not_selection_or_ranking), 'min:1', 'max:255', 'string'],
        ]);
        $validatedData = $validator->safe()->only(['question_type', 'options']);
        $options = array();
        if ($validatedData['question_type'] == Question::SELECTION || $validatedData['question_type'] == Question::RANKING) {
            $options = array_filter($validatedData['options'], function ($s) {
                return $s != null;
            });
            if (count($options) == 0) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('options', __('voting.at_least_one_option'));
                });
            }
        }
        $validatedData = $validator->validated();

        $question = $parent->questions()->create([
            'title' => $validatedData['title'],
            'max_options' => isset($validatedData['max_options']) ? $validatedData['max_options'] : null,
            'question_type' => $validatedData['question_type'],
            'opened_at' => $opened_at,
            'closed_at' => $closed_at,
        ]);
        if ($validatedData['question_type'] == Question::SELECTION || $validatedData['question_type'] == Question::RANKING) {
            foreach ($options as $option) {
                $question->options()->create([
                    'title' => $option,
                    'votes' => 0
                ]);
            }
        }
        return $question;
    }

    protected function saveVoteForQuestion(Question $question, $validatedData, ?AnswerSheet $answerSheet = null)
    {
        // validation ensures we have answers
        // to all of these questions
        $answer = $validatedData[$question->formKey()];
        if ($question->question_type == Question::TEXT_ANSWER ||
            $question->question_type == Question::RANKING) {
            $question->storeAnswers(user(), $answer, $answerSheet);
        } elseif ($question->question_type == Question::SELECTION) {
            if ($question->isMultipleChoice()) {
                $options = array_map(
                    function (int $id) {return QuestionOption::find($id);},
                    $answer
                );
                $question->storeAnswers(user(), $options, $answerSheet);
            } else {
                $option = QuestionOption::find($answer);
                $question->storeAnswers(user(), $option, $answerSheet);
            }
        } else {
            throw new \Exception("Unknown question type");
        }
    }
}
