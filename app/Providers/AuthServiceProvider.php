<?php

namespace App\Providers;

use App\Models\Role;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // Model-related policies, registering the contents of $this->policies
        $this->registerPolicies();
        // General policies without models
        $this->registerDocumentPolicies();

        Gate::define('is-collegist', function ($user) {
            return $user->isCollegist();
        });
        Gate::define('is-admin', function ($user) {
            return $user->isAdmin();
        });
    }

    /**
     * Helper function to register document-related policies.
     * @return void
     */
    private function registerDocumentPolicies()
    {
        Gate::define('document.status-certificate.viewAny', function ($user) {
            return $user->hasRole(Role::SECRETARY);
        });
        Gate::define('document.status-certificate', function ($user) {
            return $user->isCollegist(alumni: false);
        });
        Gate::define('document.register-statement', function ($user) {
            return $user->isCollegist(alumni: false)
                || $user->hasRole(Role::TENANT);
        });
        Gate::define('document.import-license', function ($user) {
            return $user->isCollegist()
                || $user->hasRole(Role::TENANT);
        });

        Gate::define('document.any', function ($user) {
            return Gate::any([
                'document.status-certificate.viewAny',
                'document.status-certificate',
                'document.register-statement',
                'document.import-license',
            ]);
        });
    }
}
