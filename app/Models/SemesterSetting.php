<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\SemesterSettingType;

class SemesterSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'semester_id',
    ];

    /**
     * Get the semester that owns the setting.
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Search for a SemesterSetting by name and return the associated semester.
     * If no SemesterSetting is found, return the first available semester.
     *
     * @param string|SemesterSettingType $name
     * @return Semester | null
     */
    public static function findSemesterByName(string|SemesterSettingType $name)
    {
        $setting = self::where('name', $name)->first();
        if ($setting && $setting->semester) {
            return $setting->semester;
        }

        return null;
    }
}