<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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

        // Prevent lazy loading of relations. See eager loading: https://laravel.com/docs/9.x/eloquent-relationships#eager-loading
        Model::preventLazyLoading(config('app.preventLazyLoading'));

        // Throw error when trying to set an attribute that does not set in fillable property of the model.
        Model::preventSilentlyDiscardingAttributes(!$this->app->isProduction());

        // Throw error when trying to access an attribute that does not exist.
        Model::preventAccessingMissingAttributes(!$this->app->isProduction());
    }
}
