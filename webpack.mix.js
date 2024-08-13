const mix = require('laravel-mix');
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.copy([
      'node_modules/@materializecss/materialize/dist/js/materialize.min.js',
      'node_modules/moment/min/moment.min.js',
      'node_modules/jquery/dist/jquery.min.js',
      'node_modules/tabulator-tables/dist/js/tabulator.min.js'
   ], 'public/js/')
   // Compile SASS
   .sass('resources/sass/materialize.scss', 'public/css/', {
        additionalData: '$isDebug: ' + process.env.APP_DEBUG + ';'
    })
    .sass('resources/sass/tabulator/tabulator_materialize.scss', 'public/css/') // copy from node modules after updating tabulator
   // Add common styles here
   .styles([
      //'resources/css/cookieconsent.min.css',
      'resources/css/fonts.css'
   ], 'public/css/app.css')
   // Add page specific files one by one
   .styles('resources/css/welcome_page.css', 'public/css/welcome_page.css')
   .js('resources/js/page_based/localizations.js', 'public/js/page_based/localizations.js');

// For fonts downloaded from Google Fonts
mix.copyDirectory('resources/fonts', 'public/fonts');

if (mix.inProduction()) {
   mix.version(); // For cache bumping
}
