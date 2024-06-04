<?php

namespace App\Models\AnonymousQuestions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

use App\Models\AnonymousQuestions\AnswerSheet;
use App\Models\GeneralAssemblies\Question;

/**
 * A long written answer given to an anonymous question
 * in the evaluation form.
 */
class LongAnswer extends Model
{
    use HasFactory;

    /**
     * The answer sheet (anonymous user)
     * containing the answer.
     */
    public function answerSheet(): BelongsTo
    {
        return $this->belongsTo(AnswerSheet::class);
    }

    /**
     * The question answered.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
