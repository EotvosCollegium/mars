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

mix.js('node_modules/@materializecss/materialize/dist/js/materialize.min.js', 'public/js/materialize.js') // We use our custom materialize JS
   .js('resources/js/cookieconsent-initialize.js', 'public/js/')
   // We have to copy already minimized JS
   .copy('resources/js/cookieconsent.min.js', 'public/js/') // TODO: see #223
   .copy([
      'node_modules/moment/min/moment.min.js',
      'node_modules/jquery/dist/jquery.min.js',
      'node_modules/tabulator-tables/dist/js/tabulator.min.js',
      'node_modules/sortablejs/Sortable.min.js',
      'node_modules/selectize/dist/js/standalone/selectize.min.js'
   ], 'public/js/')
   // Compile SASS
   .sass('resources/sass/materialize.scss', 'public/css/', {
        additionalData: '$isDebug: ' + process.env.APP_DEBUG + ';'
    })
   // Add common styles here
   .styles([
      'node_modules/tabulator-tables/dist/css/materialize/tabulator_materialize.min.css',
      'node_modules/selectize/dist/css/selectize.css',
      'resources/css/cookieconsent.min.css',
      'resources/css/selectize_materialize.css',
   ], 'public/css/app.css')
   // Add page specific files one by one
   .styles('resources/css/welcome_page.css', 'public/css/welcome_page.css')
   .js('resources/js/page_based/localizations.js', 'public/js/page_based/localizations.js')
   .js('resources/js/hide_pictures.js', 'public/js/hide_pictures.js');

if (mix.inProduction()) {
   mix.version(); // For cache bumping
}
