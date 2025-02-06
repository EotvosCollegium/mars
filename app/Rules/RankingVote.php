<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Question;

class RankingVote implements ValidationRule
{
    public function __construct(Question $question)
    {
        $this->question = $question;
    }

    private function isSubset($subset, $superset)
    {
        return count(array_intersect($subset, $superset)) == count($subset);
    }

    private function isArrUni($array)
    {
        return count($array) === count(array_unique($array));
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $converted = json_decode($value);
        if ($converted == null) {
            $fail("The value must be a JSON string.");
            return;
        }
        if (!is_array($converted)) {
            $fail("The value must be an array.");
            return;
        }
        foreach ($converted as $element) {
            if (!is_int($element)) {
                $fail("All elements must be strings.");
                return;
            }
        }
        if (!$this->isArrUni($converted)) {
            $fail("All elements must be unique.");
            return;
        }
        $valid_ids = [];
        foreach($this->question->options as $option){
            $valid_ids[] = $option->id;
        }
        if (!$this->isSubset($converted, $valid_ids)) {
            $fail("All elements must be from a valid selection.");
            return;
        }
    }
}
