<?php

namespace App\Models;

use App\Utils\DataCompresser;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property array $program
 * @property string $programs
 * @property string $neptun
 *
 */
class EducationalInformation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'year_of_graduation',
        'high_school',
        'neptun',
        'year_of_acceptance',
        'email',
        'program',
        'alfonso_language',
        'alfonso_desired_level',
        'alfonso_passed_by'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get the program attribute.
     *
     * @return Attribute
     */
    protected function program(): Attribute
    {
        return Attribute::make(
            get: fn ($value): array => DataCompresser::decompressData($value),
            set: fn ($value): string => DataCompresser::compressData($value),
        );
    }

    /**
     * Get the programs attribute.
     *
     * @return Attribute
     */
    protected function programs(): Attribute
    {
        return Attribute::make(
            get: fn (): string => join(', ', $this->program ?? []),
        );
    }
}
