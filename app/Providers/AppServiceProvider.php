<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('notification', function ($model) {
            return Blade::compileString("@include('layouts.notification', ['model' => ".$model.'])');
        });


        /**
         * Creates a macro that can be used with any query to filter and load a relation.
         * The relation is loaded only if the given condition is true.
         * @author https://dev.to/othmane_nemli
         */
        Builder::macro(
            'withWhereHas',
            function ($relation, $constraint) {
                return $this
                    ->whereHas($relation, $constraint)
                    ->with($relation, $constraint);
            }
        );
    }
}
