<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\QuestionUser
 *
 * @property int $question_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionUser whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionUser whereUserId($value)
 * @mixin \Eloquent
 */
class QuestionUser extends Model
{
    use HasFactory;

    protected $table = 'question_user';

    protected $fillable = ['question_id', 'user_id'];
}
