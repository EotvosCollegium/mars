<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\RegisterController;
use App\Mail\Invitation;
use App\Models\Application;
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
        $user_data = User::factory()->make()->only(['name', 'email']);
        app(RegisterController::class)->create(array_merge(
            [
                'password' => 'secret',
                'password_confirmation' => 'secret',
                'user_type' => 'collegist'],
            $user_data,
        ));

        $this->assertDatabaseHas(
            'users',
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
        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'submitted' => false
        ]);

    }


    /**
     * Test Tenant registration.
     *
     * @return void
     */
    public function test_tenant_collegist()
    {
        Mail::fake();

        $user_data = User::factory()->make()->only(['name', 'email']);
        $personal_info_data = PersonalInformation::factory()->make()->only(['phone_number', 'tenant_until']);
        app(RegisterController::class)->create(array_merge(
            [
                'password' => 'secret',
                'password_confirmation' => 'secret',
                'user_type' => 'tenant'],
            $user_data,
            $personal_info_data
        ));

        $this->assertDatabaseHas(
            'users',
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
            'user_id' => $user->id
        ]);
        $this->assertDatabaseHas('print_accounts', [
            'user_id' => $user->id,
            'balance' => 0
        ]);
        $this->assertTrue($user->hasRole(Role::TENANT));
    }

}
