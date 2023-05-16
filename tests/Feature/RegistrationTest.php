<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\RegisterController;
use App\Models\ApplicationForm;
use App\Models\PersonalInformation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Test registration functions.
 *
 * @return void
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Collegist registration.
     *
     * @return void
     */
    public function test_register_collegist()
    {
        $controller = new RegisterController();
        $user_data = User::factory()->make()->only(['name', 'email']);
        $controller->create(array_merge(
            [
            'password' => 'secret',
            'password_confirmation' => 'secret',
            'user_type' => 'collegist'],
            $user_data,
        ));

        $this->assertDatabaseHas('users',
            $user_data +
            ['verified' => 'false']
        );
        $user = User::where('email', $user_data['email'])->firstOrFail();
        $this->assertTrue(Hash::check('secret', $user->password));
        $this->assertDatabaseHas('internet_accesses', [
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('print_accounts', [
            'user_id' => $user->id,
            'balance' => 0
        ]);
        $this->assertDatabaseHas('application_forms', [
            'user_id' => $user->id,
            'status' => ApplicationForm::STATUS_IN_PROGRESS
        ]);

        $this->assertTrue($user->hasRole(Role::PRINTER));
        $this->assertTrue($user->hasRole(Role::INTERNET_USER));
        $this->assertTrue($user->hasRole(Role::COLLEGIST));
    }


    /**
     * Test Tenant registration.
     *
     * @return void
     */
    public function test_tenant_collegist()
    {
        Mail::fake();

        $controller = new RegisterController();

        $user_data = User::factory()->make()->only(['name', 'email']);
        $personal_info_data = PersonalInformation::factory()->make()->only(['phone_number', 'tenant_until']);
        $controller->create(array_merge(
            [
            'password' => 'secret',
            'password_confirmation' => 'secret',
            'user_type' => 'tenant'],
            $user_data,
            $personal_info_data
        ));

        $this->assertDatabaseHas('users',
            $user_data +
            ['verified' => 'false']
        );
        $user = User::where('email', $user_data['email'])->firstOrFail();
        $this->assertTrue(Hash::check('secret', $user->password));
        $this->assertDatabaseHas('personal_information', array_merge(
            ['user_id' => $user->id],
            $personal_info_data
        ));
        $this->assertDatabaseHas('internet_accesses', [
            'user_id' => $user->id,
            'wifi_username' => 'wifiuser'.$user->id
        ]);
        $this->assertDatabaseHas('print_accounts', [
            'user_id' => $user->id,
            'balance' => 0
        ]);

        $this->assertTrue($user->hasRole(Role::PRINTER));
        $this->assertTrue($user->hasRole(Role::INTERNET_USER));
        $this->assertTrue($user->hasRole(Role::TENANT));
    }
}
