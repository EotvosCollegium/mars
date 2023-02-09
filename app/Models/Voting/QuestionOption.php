<?php

namespace App\Models\Voting;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Voting\Question;
use App\Models\Voting\QuestionUser;
use App\Models\User;

class QuestionOption extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['question_id', 'title', 'votes'];

    /**A query for the question the option belongs to.*/
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
