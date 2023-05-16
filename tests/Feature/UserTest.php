<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test registration functions.
 *
 * @return void
 */
class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Collegist registration.
     *
     * @return void
     */
    public function test_store_personal_info()
    {
        $user = User::factory()->create(['verified' => false]);
        $user->roles()->attach(Role::collegist()->id);
        $user->application()->create();

        $this->actingAs($user);

        $response = $this->post('/users/'.$user->id.'/personal_information', [
            'email' => 'test@email.com',
            'name' => 'Test User',
            'phone_number' => '123456789',
            'place_of_birth' => 'Budapest',
            'date_of_birth' => '2000-01-01',
            'mothers_name' => 'Mothers name',
            'country' => 'Hungary',
            'county' => 'Pest',
            'zip_code' => '1111',
            'city' => 'Budapest',
            'street_and_number' => 'Test street 1.',
            'relatives_contact_data' => 'Test relative',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');

        $user = User::find($user->id);

        $this->assertEquals('test@email.com', $user->email);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('123456789', $user->personalInformation->phone_number);
        $this->assertEquals('Budapest', $user->personalInformation->place_of_birth);
        $this->assertEquals('2000-01-01', $user->personalInformation->date_of_birth);
        $this->assertEquals('Mothers name', $user->personalInformation->mothers_name);
        $this->assertEquals('Hungary', $user->personalInformation->country);
        $this->assertEquals('Pest', $user->personalInformation->county);
        $this->assertEquals('1111', $user->personalInformation->zip_code);
        $this->assertEquals('Budapest', $user->personalInformation->city);
        $this->assertEquals('Test street 1.', $user->personalInformation->street_and_number);
        $this->assertEquals('Test relative', $user->personalInformation->relatives_contact_data);
    }
}
