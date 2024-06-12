<?php

namespace App\Models\AnonymousQuestions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

use App\Models\AnonymousQuestions\AnswerSheet;
use App\Models\Question;

/**
 * A long written answer given to an anonymous question
 * in the evaluation form.
 */
class LongAnswer extends Model
{
    use HasFactory;

    /**
     * For better anonymity,
     * this model should not be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'answer_sheet_id',
        'text'
    ];

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
