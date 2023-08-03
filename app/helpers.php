<?php

use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

if (! function_exists('user')) {

    /**
     * Returns the currently authenticated (logged in) user.
     * This is used to convert Authenticatable class to User class.
     * If there may not necessarily a user, use Auth::user() instead.
     * @return \App\Models\User
     * @throws \Illuminate\Auth\AuthenticationException
     */
    function user(): User
    {
        $user = Auth::user();
        if($user) {
            return $user;
        } else if(App::environment() == 'testing') {
            return User::factory()->create(['verified' => true]);
        } else {
            throw new \Illuminate\Auth\AuthenticationException("The session does not have an authenticated user");
        }
    }
}
