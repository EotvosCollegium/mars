<?php

namespace App\Models\Voting;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Voting\Question;

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
