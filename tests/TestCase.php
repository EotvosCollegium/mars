<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Mail;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    /**
     * Set up the test.
     * This runs before every test case.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->user = User::factory()->create(['verified' => true]);
        $this->admin = User::factory()->create(['verified' => true]);
        $this->admin->roles()->attach(Role::sysAdmin());

    }
}
