<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feature;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class FeatureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize("viewAny", Feature::class);

        return view('configuration.feature.app', [
            'features' => Feature::all()
        ]);
    }

    /**
     * Clear cache and recreate if necessary
     */
    private function refreshCache(){
        Cache::forget('features');

        Artisan::call("event:clear");
        Artisan::call("route:clear");
        Artisan::call("view:clear");
        Artisan::call("config:clear");
        Artisan::call("cache:clear");

        if(Feature::isFeatureEnabled("caching")){
            Artisan::call("event:cache");
            Artisan::call("route:cache");
            Artisan::call("view:cache");
            Artisan::call("config:cache");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Feature  $feature
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Feature $feature)
    {
        $this->authorize("update", $feature);

        if(request('enabled')){
            $feature->enabled = true;
        } else {
            $feature->enabled = false;
        }

        $feature->update();

        $this->refreshCache();

        return redirect()->back()->with('message', __('general.successful_modification'));
    }
}
