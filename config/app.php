<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Urán'),

    'version' => '3.19', // update on release

    'logo_blue_path' => env('APP_ENV', "local") != "production" ? '/img/mars.png' : '/img/uran_blue.png',

    'logo_white_path' => env('APP_ENV', "local") != "production" ? '/img/mars.png' : '/img/uran_white.png',

    'logo_with_bg_path' => env('APP_ENV', "local") != "production" ? '/img/mars.png' : '/img/uran_with_bg.png',

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |
    | Prevent lazy loading for relations.
    | See eager loading: https://laravel.com/docs/9.x/eloquent-relationships#eager-loading
    |
    */
    'preventLazyLoading' => env('PREVENT_LAZY_LOADING', false) && env('APP_DEBUG', false),

    /*
    |
    | Other strictness settings
    |
    */
    'preventSilantlyDiscardingAttributes' => env('PREVENT_SILANTLY_DISCARDING_ATTRIBUTES', false) && env('APP_DEBUG', false),
    'preventAccessingMissingAttributes' => env('PREVENT_ACCESSING_MISSING_ATTRIBUTES', false) && env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Europe/Budapest',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'hu',
    'locales' => [
        'en' => 'A - English',
        'la' => 'L - Latina',
        'fr' => 'F - Français',
        'it' => 'O - Italiano',
        'de' => 'N - Deutsch',
        'sp' => 'S - Español',
        'gr' => 'Ó - Ελληνικά',
        'hu' => 'M - Magyar',
    ],
    'alfonso_languages' => [
        'en' => 'A - English',
        'la' => 'L - Latina',
        'fr' => 'F - Français',
        'it' => 'O - Italiano',
        'de' => 'N - Deutsch',
        'sp' => 'S - Español',
        'gr' => 'Ó - Ελληνικά'
    ],

    'locale_cookie_lifespan' => 9600,


    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
];
