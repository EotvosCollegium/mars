<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Feature extends Model
{


    protected $fillable = [
        'name',
        'enabled',
    ];

    /**
     * Get the Feature object with a given name
     */
    public static function getFeature(string $feature_name){
        $features = Cache::rememberForever('features', function () {
            return DB::table('features')->get();
        });
        return $features->where('name', '=', $feature_name)->first();
    }

    /**
     * Check if a given feature is enabled
     */
    public static function isFeatureEnabled(string $feature_name){
        $feature = Feature::getFeature($feature_name);
        if($feature){
            return $feature->enabled;
        }
        return false;
    }
}
