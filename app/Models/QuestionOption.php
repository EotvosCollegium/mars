<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Question;

/**
 * App\Models\QuestionOption
 *
 * @property int $id
 * @property int $question_id
 * @property string $title
 * @property int $votes
 * @property-read Question|null $question
 * @method static \Database\Factories\QuestionOptionFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionOption query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionOption whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionOption whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionOption whereVotes($value)
 * @mixin \Eloquent
 */
class QuestionOption extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['question_id', 'title', 'votes'];

    /**
     * @return BelongsTo The question the option belongs to.
    */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
