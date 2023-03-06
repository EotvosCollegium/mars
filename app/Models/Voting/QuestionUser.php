<?php

namespace App\Models\Voting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionUser extends Model
{
    use HasFactory;

    protected $table = 'question_user';

    protected $fillable = ['question_id', 'user_id'];
}
