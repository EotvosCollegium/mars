<?php

namespace Tests\Feature;

use App\Mail\Invitation;
use App\Models\Role;
use App\Models\User;
use Google\Service\Dfareporting\Browser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test registration functions.
 *
 * @return void
 */
class InvitationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test invitation sending.
     *
     * @return void
     */
    public function test_send_invitation()
    {
        Mail::fake();

        /** @var User $user */
        $user = User::factory()->create(['verified' => true]);
        $user->roles()->attach(Role::get(Role::SECRETARY)->id);
        $this->actingAs($user);

        $response = $this->post(route('secretariat.invite'), [
            'email' => 'guest@email.com',
            'name' => 'Guest Name'
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas(
            'users',
            ['name' => 'Guest Name', 'email' => 'guest@email.com']
        );
        Mail::assertSent(Invitation::class);
    }

    /**
     * Test accepting the invitation.
     *
     * @return void
     */
    public function test_invitation_link()
    {
        /** @var User $user */
        $user = User::factory()->create(['verified' => true]);
        // do not use `actingAs` as the user is not logged in yet

        $token = $user->generatePasswordResetToken();
        $mailable = new Invitation($user, $token);

        $url = config('app.url').'/password/reset/'. $token . '?email='.$user->email;
        $mailable->assertSeeInHtml($url);

        $response = $this->get($url);
        $response->assertStatus(200);
        $response->assertSee(route('password.update'));
        $response->assertSee($user->email);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertStatus(302);
    }
}
