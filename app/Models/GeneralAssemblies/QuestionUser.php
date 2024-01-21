<?php

namespace App\Models\GeneralAssemblies;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionUser extends Model
{
    use HasFactory;

    protected $table = 'question_user';

    protected $fillable = ['question_id', 'user_id'];
}
