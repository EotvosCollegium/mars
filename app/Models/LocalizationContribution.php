<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LocalizationContribution
 *
 * @property mixed $language
 * @property int $id
 * @property string $key
 * @property string $value
 * @property int|null $contributor_id
 * @property int $approved
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $contributor
 * @method static \Illuminate\Database\Eloquent\Builder|LocalizationContribution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LocalizationContribution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LocalizationContribution query()
 * @method static \Illuminate\Database\Eloquent\Builder|LocalizationContribution whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LocalizationContribution whereContributorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LocalizationContribution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LocalizationContribution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LocalizationContribution whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LocalizationContribution whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LocalizationContribution whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LocalizationContribution whereValue($value)
 * @mixin \Eloquent
 */
class LocalizationContribution extends Model
{
    protected $table = 'localization_contributions';

    protected $fillable = [
        'language', 'key', 'value', 'contributor_id', 'approved',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'approved' => false,
    ];

    public function contributor()
    {
        return $this->belongsTo('App\Models\User', 'contributor_id');
    }
}
