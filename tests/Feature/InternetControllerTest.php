<?php

namespace Tests\Feature;

use App\Mail\InternetFault;
use App\Models\Internet\MacAddress;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Test registration functions.
 *
 * @return void
 */
class InternetControllerTest extends TestCase
{
    /**
     * Test that the user does not see the details on the internet page if it has no internet access.
     */
    public function test_no_access_internet_page(): void
    {
        $this->user->internetAccess->setWifiCredentials('wifi_username');

        $response = $this->actingAs($this->user)->get(route('internet.index'));

        $response->assertStatus(200);
        $response->assertSeeText(__('internet.no_internet'));
        $response->assertDontSeeText('wifi_username');
    }

    /**
     * Test that the user sees (only its own) details on the internet page if it has internet access.
     */
    public function test_access_internet_page(): void
    {
        $this->user->internetAccess->update([
            'has_internet_until' => now()->addDay(),
            'wifi_username' => 'wifi_username',
        ]);
        $this->user->internetAccess->macAddresses()->create([
            'mac_address' => '01:23:45:67:89:AB',
            'comment' => 'My mac address'
        ]);

        MacAddress::create([
            'user_id' => User::factory()->create()->id,
            'mac_address' => 'AB:CD:EF:01:23:45',
            'comment' => 'I should not see this.'
        ]);

        $response = $this->actingAs($this->user)->get(route('internet.index'));

        $response->assertStatus(200);

        $response->assertSeeText('01:23:45:67:89:AB');
        $response->assertSeeText('wifi_username');
        $response->assertDontSeeText('AB:CD:EF:01:23:45');
    }


    /**
     * Test that the user can send the internet fault form with null values as possible.
     * @throws \JsonException
     */
    public function test_send_internet_fault_form(): void
    {
        $response = $this->actingAs($this->user)->post(route(
            'internet.report_fault',
            [
                'report' => 'This is a report',
                'when' => 'Since yesterday',
                'user_os' => 'Linux',
            ]
        ));

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('message', __('mail.email_sent'));

        $response = $this->actingAs($this->user)->post(route(
            'internet.report_fault',
            [
                'report' => 'This is a report',
                'error_message' => 'Error message',
                'when' => 'Since yesterday',
                'tries' => 'Tried this and that',
                'user_os' => 'Linux',
                'room' => '203',
                'availability' => 'I am available',
                'can_enter_room' => 'on',
            ]
        ));

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('message', __('mail.email_sent'));

        Mail::assertQueued(InternetFault::class, 2);
    }

}
