<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MrAndMissVote
 *
 * @property int $id
 * @property int $voter
 * @property int $category
 * @property int|null $votee_id
 * @property string|null $votee_name
 * @property int $semester
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissVote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissVote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissVote query()
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissVote whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissVote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissVote whereSemester($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissVote whereVoteeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissVote whereVoteeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MrAndMissVote whereVoter($value)
 * @mixin \Eloquent
 */
class MrAndMissVote extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'voter', 'category', 'votee_id', 'votee_name', 'semester',
    ];
}
