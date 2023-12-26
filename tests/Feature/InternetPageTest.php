<?php

namespace Tests\Feature;

use App\Models\Internet\MacAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test registration functions.
 *
 * @return void
 */
class InternetPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the user does not see the details on the internet page if it has no internet access.
     */
    public function test_no_access_internet_page(): void
    {
        $user = User::factory()->create(['verified' => true]);
        $user->internetAccess->setWifiCredentials('wifi_username');

        $response = $this->actingAs($user)->get(route('internet.index'));

        $response->assertStatus(200);
        $response->assertSeeText(__('internet.no_internet'));
        $response->assertDontSeeText('wifi_username');
    }

    /**
     * Test that the user sees (only its own) details on the internet page if it has internet access.
     */
    public function test_access_internet_page(): void
    {
        $user = User::factory()->create(['verified' => true]);

        $user->internetAccess->update([
            'has_internet_until' => now()->addDay(),
            'wifi_username' => 'wifi_username',
        ]);
        $user->internetAccess->macAddresses()->create([
            'mac_address' => '01:23:45:67:89:AB',
            'comment' => 'My mac address'
        ]);

        MacAddress::create([
            'user_id' => User::factory()->create()->id,
            'mac_address' => 'AB:CD:EF:01:23:45',
            'comment' => 'I should not see this.'
        ]);

        $response = $this->actingAs($user)->get(route('internet.index'));

        $response->assertStatus(200);

        $response->assertSeeText('01:23:45:67:89:AB');
        $response->assertSeeText('wifi_username');
        $response->assertDontSeeText('AB:CD:EF:01:23:45');
    }

    /**
     * Test that the user can add new mac addresses.
     */
    public function test_add_mac_address(): void
    {
        $user = User::factory()->create(['verified' => true]);

        $response = $this->actingAs($user)->post(route('internet.mac_addresses.store'), [
            'comment' => 'My mac address',
            'mac_address' => '01:23:45:67:89:AB'
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('mac_addresses', [
            'user_id' => $user->id,
            'mac_address' => '01:23:45:67:89:AB',
            'comment' => 'My mac address',
            'state' => MacAddress::REQUESTED
        ]);
    }

    /**
     * Test that the user can delete its own mac address.
     */
    public function test_delete_mac_address(): void
    {
        $user = User::factory()->create(['verified' => true]);

        $mac = MacAddress::factory()->create([
            'user_id' => $user->id,
            'mac_address' => '01:23:45:67:89:AB',
        ]);

        $response = $this->actingAs($user)->delete(route(
            'internet.mac_addresses.destroy',
            ['mac_address' => $mac->id]
        ));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('mac_addresses', [
            'user_id' => $user->id,
            'mac_address' => '01:23:45:67:89:AB',
        ]);
    }

    /**
     * Test that the user can not delete other's mac address.
     */
    public function test_cannot_delete_other_mac_address(): void
    {
        $user = User::factory()->create(['verified' => true]);

        $mac = MacAddress::factory()->create([
            'user_id' => User::factory()->create()->id,
            'mac_address' => '01:23:45:67:89:AB'
        ]);

        $response = $this->actingAs($user)->delete(route(
            'internet.mac_addresses.destroy',
            ['mac_address' => $mac->id]
        ));

        $response->assertStatus(403);
    }
}
