<?php

namespace Tests\Feature;

use App\Mail\InternetFault;
use App\Models\Internet\InternetAccess;
use App\Models\Internet\MacAddress;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Test registration functions.
 *
 * @return void
 */
class AdminInternetControllerTest extends TestCase
{
    /**
     * Test that the user can not access the admin page.
     */
    public function test_no_access_admin_internet_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('internet.admin.index'));

        echo substr(json_encode($response),0,500);

        $response->assertStatus(403);
    }

    /**
     * Test that an admin can access the admin page.
     */
    public function test_access_admin_internet_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('internet.admin.index'));

        $response->assertStatus(200);
    }

    /**
     * Test that an admin can index the internet accesses.
     */
    public function test_index_internet_accesses(): void
    {
        $this->user->internetAccess->update([
            'wifi_username' => 'wifi_username',
        ]);

        $response = $this->actingAs($this->admin)->get(route('internet.internet_accesses.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'user_id' => $this->user->id,
            'wifi_username' => 'wifi_username',
        ]);
    }

    /**
     * Test that a user can not index the internet accesses.
     */
    public function test_can_not_index_internet_accesses(): void
    {
        $response = $this->actingAs($this->user)->get(route('internet.internet_accesses.index'));

        $response->assertStatus(403);
    }


    /**
     * Test that a user can not index the wifi connections.
     */
    public function test_can_not_index_wifi_connections(): void
    {
        $response = $this->actingAs($this->user)->get(route('internet.wifi_connections.index'));

        $response->assertStatus(403);
    }

    /**
     * Test that an admin can index the wifi connections.
     */
    public function test_index_wifi_connections(): void
    {
        $this->user->internetAccess->wifiConnections()->create([
            'ip' => '192.168.0.1',
            'mac_address' => '01:23:45:67:89:AB',
            'note' => 'My note'
        ]);

        $response = $this->actingAs($this->admin)->get(route('internet.wifi_connections.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'mac_address' => '01:23:45:67:89:AB',
            'ip' => '192.168.0.1',
            'note' => 'My note',
        ]);
    }

    /**
     * Test that an admin can extend an internet access.
     */

    public function test_extend_internet_access(): void
    {
        $date = now()->addDays(2)->midDay();
        $response = $this->actingAs($this->admin)->post(route('internet.internet_accesses.extend', $this->user->internetAccess), [
            'has_internet_until' => $date,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('internet_accesses', [
            'user_id' => $this->user->id,
            'has_internet_until' => $date,
        ]);

        $response->assertSee($date->format('Y-m-d'));
    }

    /**
     * Test that an admin can extend an internet access to the default value.
     */
    public function test_extend_internet_access_default(): void
    {
        $date = InternetAccess::getInternetDeadline();
        $response = $this->actingAs($this->admin)->post(route('internet.internet_accesses.extend', $this->user->internetAccess));

        $response->assertStatus(200);

        $this->assertDatabaseHas('internet_accesses', [
            'user_id' => $this->user->id,
            'has_internet_until' => $date,
        ]);

        $response->assertSee($date->format('Y-m-d'));
    }

    /**
     * Test that an admin can revoke an internet access.
     */

    public function test_revoke_internet_access(): void
    {
        $this->user->internetAccess->update([
            'has_internet_until' => now()->addDay(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('internet.internet_accesses.revoke', $this->user->internetAccess));

        $response->assertStatus(204);

        $this->assertDatabaseHas('internet_accesses', [
            'user_id' => $this->user->id,
            'has_internet_until' => null,
        ]);
    }

    /**
     * Test that a user can not update their internet access.
     */
    public function test_cannot_update_internet_access(): void
    {
        //extend
        $response = $this->actingAs($this->user)->post(route('internet.internet_accesses.extend', $this->user->internetAccess));
        $response->assertStatus(403);

        //revoke
        $response = $this->actingAs($this->user)->post(route('internet.internet_accesses.revoke', $this->user->internetAccess));
        $response->assertStatus(403);
    }
}
