<?php

namespace Tests\Feature;

use App\Mail\InternetFault;
use App\Mail\MacNeedsApproval;
use App\Mail\MacStatusChanged;
use App\Models\Internet\MacAddress;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Test registration functions.
 *
 * @return void
 */
class MacAddressControllerTest extends TestCase
{
    /**
     * Test that the user can not index mac addresses.
     */
    public function test_can_not_index_mac_address(): void
    {
        $response = $this->actingAs($this->user)->get(\route('internet.mac_addresses.index'));
        $response->assertStatus(403);
    }

    /**
     * Test that an admin can index mac addresses.
     */
    public function test_index_mac_address(): void
    {
        MacAddress::factory()->create([
            'user_id' => $this->user->id,
            'mac_address' => '01:23:45:67:89:AB',
        ]);

        MacAddress::factory()->create([
            'user_id' => $this->admin->id,
            'mac_address' => 'AB:CD:EF:01:23:45',
        ]);

        $response = $this->actingAs($this->admin)->get(route('internet.mac_addresses.index'));
        $response->assertStatus(200);
        $response->assertSeeText('01:23:45:67:89:AB');
        $response->assertSeeText('AB:CD:EF:01:23:45');
    }

    /**
     * Test that a user can add new mac addresses.
     */
    public function test_store_mac_address(): void
    {
        $response = $this->actingAs($this->user)->post(\route('internet.mac_addresses.store'), [
            'comment' => 'My mac address',
            'mac_address' => '01:23:45:67:89:AB'
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('mac_addresses', [
            'user_id' => $this->user->id,
            'mac_address' => '01:23:45:67:89:AB',
            'comment' => 'My mac address',
            'state' => MacAddress::REQUESTED
        ]);

        Mail::assertQueued(MacNeedsApproval::class);
    }

    /**
     * Test that a user can not edit a mac address.
     */
    public function test_can_not_edit_mac_address(): void
    {
        $mac = MacAddress::factory()->create([
            'user_id' => $this->user->id,
            'mac_address' => '01:23:45:67:89:AB',
            'state' => MacAddress::REQUESTED
        ]);
        $response = $this->actingAs($this->user)->put(route('internet.mac_addresses.update', ['mac_address' => $mac->id]), [
            'state' => MacAddress::APPROVED
        ]);
        $response->assertStatus(403);

        $response = $this->actingAs($this->user)->put(route('internet.mac_addresses.update', ['mac_address' => $mac->id]), [
            'comment' => MacAddress::APPROVED
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test that a user can not edit a mac address.
     */
    public function test_edit_mac_address(): void
    {
        $mac = MacAddress::factory()->create([
            'user_id' => $this->user->id,
            'mac_address' => '01:23:45:67:89:AB',
            'state' => MacAddress::REQUESTED
        ]);
        $response = $this->actingAs($this->admin)->put(route('internet.mac_addresses.update', ['mac_address' => $mac->id]), [
            'state' => MacAddress::APPROVED
        ]);
        $response->assertStatus(200);
        $response->assertJsonFragment(['state' => MacAddress::APPROVED]);
        Mail::assertQueued(MacStatusChanged::class);

        $response = $this->actingAs($this->admin)->put(route('internet.mac_addresses.update', ['mac_address' => $mac->id]), [
            'comment' => 'My new comment'
        ]);
        $response->assertStatus(200);
        $response->assertJsonFragment(['comment' => 'My new comment']);

        $this->assertDatabaseHas('mac_addresses', [
            'user_id' => $this->user->id,
            'mac_address' => '01:23:45:67:89:AB',
            'state' => MacAddress::APPROVED,
            'comment' => 'My new comment'
        ]);

    }


    /**
     * Test that the user can delete its own mac address.
     */
    public function test_delete_mac_address(): void
    {
        $mac = MacAddress::factory()->create([
            'user_id' => $this->user->id,
            'mac_address' => '01:23:45:67:89:AB',
        ]);

        $response = $this->actingAs($this->user)->delete(\route(
            'internet.mac_addresses.destroy',
            ['mac_address' => $mac->id]
        ));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('mac_addresses', [
            'user_id' => $this->user->id,
            'mac_address' => '01:23:45:67:89:AB',
            'deleted_at' => null,
        ]);
    }

    /**
     * Test that the user can not delete other's mac address.
     */
    public function test_cannot_delete_other_mac_address(): void
    {
        $mac = MacAddress::factory()->create([
            'user_id' => User::factory()->create()->id,
            'mac_address' => '01:23:45:67:89:AB'
        ]);

        $response = $this->actingAs($this->user)->delete(\route(
            'internet.mac_addresses.destroy',
            ['mac_address' => $mac->id]
        ));

        $response->assertStatus(403);
    }

    /**
     * Test that the admin can delete other's mac address.
     */
    public function test_delete_other_mac_address(): void
    {
        $mac = MacAddress::factory()->create([
            'user_id' => $this->user->id,
            'mac_address' => '01:23:45:67:89:AB',
        ]);

        $response = $this->actingAs($this->admin)->delete(route(
            'internet.mac_addresses.destroy',
            ['mac_address' => $mac->id]
        ));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('mac_addresses', [
            'user_id' => $this->user->id,
            'mac_address' => '01:23:45:67:89:AB',
            'deleted_at' => null,
        ]);
    }
}
