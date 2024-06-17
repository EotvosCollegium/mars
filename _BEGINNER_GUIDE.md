## Beginner guide
Note: update the laravel docs references to the currently used version (see "laravel/framework" in `composer.json`)

The basic things you will need as a beginner:
- Controllers: `app/Http/Controllers` - the main functionalities of the backend
- Database: 
    - [Migrations](https://laravel.com/docs/8.x/migrations): `database/migrations` - the database structure can be modified with migrations
    - [Seeders](https://laravel.com/docs/8.x/seeding): `database/seeders`, `database/factories` - the database can be seeded with dummy data which are set in factories
    - Read the [documentation of queries](https://laravel.com/docs/8.x/queries) to understand how to insert/update/delete data in the database. Laravel also has [particular functions for inserting and updating models](https://laravel.com/docs/8.x/eloquent#inserting-and-updating-models). The queries will mostly return [Collections](https://laravel.com/docs/8.x/collections), which are similar to arrays but with custom functions. 
- [Models](https://laravel.com/docs/8.x/eloquent): `app/Models` - the database is mapped to php classes using Models (ORM - Object Relational Mapping). Models are the main way to interact with the database. To create Models, use `php artisan make:model ModelName -a` to generate the model and a corresponding controller, factory, etc. Also take a look at [Relationships](https://laravel.com/docs/8.x/eloquent-relationships) to define relations between Models.
- Routes: `routes/web` - to map the requests from the browser to controller functions
- Frontend: `resources/views` - the webpage is generated from these blade files. To return a webpage, use `return view('path.to.view', ['additional_data' => $data, ...]);`. Blade files are html codes with [additional php functionalities](https://laravel.com/docs/8.x/blade#blade-directives): loops, variables, etc. Writing `{{ something }}` is equivalent to php's print: `echo something;`. When writing forms, add `@csrf` to the form for security reasons (without it, the request will not work). Blade files can also be nested, included, etc (eg. `@include('path.to.file'))`). Our front-end framework is [Materialize](https://materializecss.github.io/materialize/).
- Language files: `resources/lang` - to translate the webpage. Use `__('filename.key')` in the backend and `@lang('filename.key')` in blades. To add variables: `__('filename.key', ['variable' => 'value'])`, prefix the variable name with `:` in the language files. We translate to English and Hungarian, other languages are handled by the localization contribution model.
- Validation: It is recommended to validate every user input: for example, in the controller: `$request->validate(['title' => 'required|string|max:255']);`. [Available validation rules](https://laravel.com/docs/8.x/validation#available-validation-rules).
- Debugging: log files: `storage/logs/laravel.log` - use `Log::info('something happened', ['additional_data' => $data])` to log (also: error, warning, etc.). Alternatively, in the controllers, you can type `return response()->json(...);` to print something in the browser. In blades, type `{{ var_dump($data) }}` to display some data. It is worth to take a look at the [query debugging options](https://laravel.com/docs/8.x/queries#debugging) also.

You can use `php artisan db` command to enter into the database directly or `php artisan tinker` to get a runtime laravel evaluation tool.

## Official documentations
- [Laravel](https://laravel.com/)
- [Materialize](https://materializecss.com/)
