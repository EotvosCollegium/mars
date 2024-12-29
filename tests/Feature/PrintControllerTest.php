<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PrintControllerTest extends TestCase
{
    /**
     * Testing a verified user with no printing related permissions.
     */
    public function testUserWithoutPermissions()
    {
        $user = User::factory()->create();
        $user->setVerified();
        $this->actingAs($user);

        $response = $this->get('/print');
        $response->assertStatus(200);
        $response = $this->get('/print/free-pages');
        $response->assertStatus(200);
        $response = $this->get('/print/print-job');
        $response->assertStatus(200);
        $response = $this->get('/print/free-pages/admin');
        $response->assertStatus(403);
        $response = $this->get('/print/print-job/admin');
        $response->assertStatus(403);
        $response = $this->get('/print/admin');
        $response->assertStatus(403);
        $response = $this->get('/print/print-account-history');
        $response->assertStatus(403);

        $response = $this->put('/print-account-update', []);
        $response->assertStatus(302);
        // TODO: #514
        // $response = $this->post('/print/print_jobs/0/cancel', []);
        // $response->assertStatus(403);

        $response = $this->post('/print/print-job', []);
        $response->assertStatus(302);
    }

    /**
     * Testing a verified user with PRINTER permission.
     */
    public function testUserWithPrinterPermissions()
    {
        $user = User::factory()->create();
        $user->setVerified();
        $this->actingAs($user);

        $response = $this->get('/print');
        $response->assertStatus(200);
        $response = $this->get('/print/free-pages');
        $response->assertStatus(200);
        $response = $this->get('/print/print-job');
        $response->assertStatus(200);
        $response = $this->get('/print/free-pages/admin');
        $response->assertStatus(403);
        $response = $this->get('/print/print-job/admin');
        $response->assertStatus(403);
        $response = $this->get('/print/admin');
        $response->assertStatus(403);
        $response = $this->get('/print/print-account-history');
        $response->assertStatus(403);

        $response = $this->put('/print-account-update', []);
        $response->assertStatus(302);
        // TODO: #514
        // $response = $this->post('/print/print_jobs/0/cancel', []);
        // $response->assertStatus(403);

        $response = $this->post('/print/print-job', []);
        $response->assertStatus(302);
    }

    /**
     * Testing a verified user with PRINT_ADMIN permission.
     */
    public function testUserWithPrintAdminPermissions()
    {
        $user = User::factory()->create();
        $user->setVerified();
        $user->roles()->attach(Role::get(Role::SYS_ADMIN)->id);
        $this->actingAs($user);

        $response = $this->get('/print');
        $response->assertStatus(200);
        $response = $this->get('/print/free-pages');
        $response->assertStatus(200);
        $response = $this->get('/print/print-job');
        $response->assertStatus(200);
        $response = $this->get('/print/free-pages/admin');
        $response->assertStatus(200);
        $response = $this->get('/print/print-job/admin');
        $response->assertStatus(200);
        $response = $this->get('/print/admin');
        $response->assertStatus(200);
        $response = $this->get('/print/print-account-history');
        $response->assertStatus(200);

        $response = $this->put('/print-account-update', []);
        $response->assertStatus(302);
        // TODO: #514
        // $response = $this->post('/print/print_jobs/0/cancel', []);
        // $response->assertStatus(403);

        $response = $this->post('/print/print-job', []);
        $response->assertStatus(302);
    }

    public function testBalanceTransfer()
    {
        Mail::fake();

        $sender = User::factory()->create();
        $sender->setVerified();
        $this->actingAs($sender);

        $receiver = User::factory()->create();
        $receiver->setVerified();

        // Setting initial valeus
        $this->assertEquals($sender->printAccount->balance, 0);
        $sender->printAccount->update(['last_modified_by' => $sender->id]);
        $sender->printAccount->increment('balance', 43);
        $this->assertEquals($sender->printAccount->balance, 43);
        $this->assertEquals($receiver->printAccount->balance, 0);

        // Simple transfer test
        $response = $this->transfer($receiver, 10);
        $this->assertCorrectTransfer($response, $sender, $receiver, 33, 10);

        // Transferring values over balance
        $response = $this->transfer($receiver, 123);
        $response = $this->transfer($receiver, 34);
        $this->assertCorrectTransfer($response, $sender, $receiver, 33, 10);

        // Transferring nothing
        $response = $this->transfer($receiver, 0);
        $this->assertCorrectTransfer($response, $sender, $receiver, 33, 10);

        // Transferring negative values
        $response = $this->transfer($receiver, -5);
        $response->assertStatus(400);

        // Transferring all balance
        $response = $this->transfer($receiver, 33);
        $this->assertCorrectTransfer($response, $sender, $receiver, 0, 43);

        // Transferring with empty balance
        $response = $this->transfer($receiver, 1);
        $this->assertCorrectTransfer($response, $sender, $receiver, 0, 43);
    }

    public function testModifyBalance()
    {
        Mail::fake();

        $sender = User::factory()->create();
        $sender->setVerified();
        $sender->roles()->attach(Role::firstWhere('name', Role::SYS_ADMIN)->id);
        $this->actingAs($sender);

        $receiver = User::factory()->create();
        $receiver->setVerified();

        // Asserting initial valeus
        $this->assertEquals($sender->printAccount->balance, 0);
        $this->assertEquals($receiver->printAccount->balance, 0);

        $response = $this->modify($receiver, 10);
        $this->assertCorrectModification($response, $receiver, 10);

        $response = $this->modify($receiver, 123);
        $this->assertCorrectModification($response, $receiver, 133);

        $response = $this->modify($receiver, -23);
        $this->assertCorrectModification($response, $receiver, 110);

        $response = $this->modify($receiver, 1);
        $this->assertCorrectModification($response, $receiver, 111);

        $response = $this->modify($receiver, 0);
        $this->assertCorrectModification($response, $receiver, 111);

        $response = $this->modify($receiver, -112);
        $this->assertCorrectModification($response, $receiver, 111);

        $response = $this->modify($receiver, -111);
        $this->assertCorrectModification($response, $receiver, 0);

        $response = $this->modify($receiver, -1);
        $this->assertCorrectModification($response, $receiver, 0);

        $response = $this->modify($receiver, 0);
        $this->assertCorrectModification($response, $receiver, 0);

        $response = $this->modify($receiver, 12);
        $this->assertCorrectModification($response, $receiver, 12);

        //Sender is not affected
        $this->assertEquals($sender->printAccount->balance, 0);
    }

    // Helpers
    private function transfer($receiver, $balance)
    {
        $response = $this->put('/print-account-update', [
            'user' => user()->id,
            'other_user' => $receiver->id,
            'amount' => $balance,
        ]);

        return $response;
    }
    private function assertCorrectTransfer($response, $sender, $receiver, $senderBalance, $receiverBalance)
    {
        $response->assertStatus(302);
        $sender = User::find($sender->id); // We have to reload the sender here.
        $receiver = User::find($receiver->id); // We have to reload the receiver here.
        $this->assertEquals($senderBalance, $sender->printAccount->balance);
        $this->assertEquals($receiverBalance, $receiver->printAccount->balance);
    }

    private function modify($receiver, $balance)
    {
        $response = $this->put('/print-account-update', [
            'user' => $receiver->id,
            'amount' => $balance,
        ]);
        return $response;
    }
    private function assertCorrectModification($response, $receiver, $receiverBalance)
    {
        $response->assertStatus(302);
        $receiver = User::find($receiver->id); // We have to reload the receiver here.
        $this->assertEquals($receiverBalance, $receiver->printAccount->balance);
    }
}
