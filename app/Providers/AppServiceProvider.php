<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        Model::preventSilentlyDiscardingAttributes(config('app.preventSilantlyDiscardingAttributes'));

        // Throw error when trying to access an attribute that does not exist.
        Model::preventAccessingMissingAttributes(config('app.preventAccessingMissingAttributes'));

        $this->loadGoogleStorageDriver('google');
        $this->loadGoogleStorageDriver('google_admin');
    }

    /**
     * Initialize and load Google Storage driver.
     */
    private function loadGoogleStorageDriver(string $driverName = 'google')
    {
        try {
            Storage::extend($driverName, function ($app, $config) {
                $options = [];

                if (!empty($config['teamDriveId'] ?? null)) {
                    $options['teamDriveId'] = $config['teamDriveId'];
                }

                $client = new \Google\Client();
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->refreshToken($config['refreshToken']);

                $service = new \Google\Service\Drive($client);
                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $config['folder'] ?? '/', $options);
                $driver = new \League\Flysystem\Filesystem($adapter);

                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter);
            });
        } catch(\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
