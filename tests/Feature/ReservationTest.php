<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\ReservableItem;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Role;
use App\Mail\ReservationVerified;

/**
 * Testing reservation handling
 * through calling controller methods.
 */
class ReservationTest extends TestCase
{

    /**
     * Helper function to create a user having the rights to request reservation.
     */
    private function createCollegist(): User
    {
        $user = User::factory()->create(['verified' => true]);
        $user->setResident();
        return $user;
    }

    /**
     * A case in which everything is alright.
     *
     * @return void
     */
    public function test_happy_path(): void
    {
        $item = ReservableItem::factory()->create();
        // it now has no reservations

        $user = $this->createCollegist();

        $input = [
            'title' => 'My little film club',
            'reserved_from' => '2024-05-17 23:30:00',
            'reserved_until' => '2024-05-18 01:00:00',
            'note' => null
        ];

        $response = $this->actingAs($user)->post(route('reservations.store', $item), $input);

        $response->assertStatus(302);
        $this->assertEquals(1, $item->reservations->count());

        $this->assertDatabaseHas('reservations', array_merge($input, [
            'reservable_item_id' => $item->id,
            'user_id' => $user->id,
            'verified' => false, // this is a room and our user not an admin
        ]));
    }

    /**
     * A case testing control of conflicts
     * (and it must not be a conflict if only the end points touch each other).
     *
     * @return void
     */
    public function test_conflicts(): void
    {
        $item = ReservableItem::factory()->create();
        $user = $this->createCollegist();

        $input1 = [
            'title' => 'My little film club',
            'reserved_from' => '2024-05-17 23:30:00',
            'reserved_until' => '2024-05-18 01:00:00',
            'note' => null
        ];

        $input2 = [
            'title' => 'Veresegyházi asszonykórus',
            'reserved_from' => '2024-05-18 00:15:00',
            'reserved_until' => '2024-05-18 00:17:00',
            'note' => null
        ];

        $response = $this->actingAs($user)->post(route('reservations.store', $item), $input1);

        $response->assertStatus(302);
        $this->assertEquals(1, $item->reservations->count());

        $this->assertDatabaseHas('reservations', array_merge($input1, [
            'reservable_item_id' => $item->id,
            'user_id' => $user->id,
            'verified' => false // this is a room and our user not an admin
        ]));

        $response = $this->actingAs($user)->post(route('reservations.store', $item), $input2);

        $response->assertStatus(409);
        $this->assertEquals(1, $item->reservations->count());
    }

    /**
     * A case testing that we not be able to cause conflicts
     * even when modifying an existing reservation.
     *
     * @return void
     */
    /**
     * A case testing control of conflicts
     * (and it must not be a conflict if only the end points touch each other).
     *
     * @return void
     */
    public function test_conflicts_after_modification(): void
    {
        $item = ReservableItem::factory()->create();
        $user = $this->createCollegist();

        $input1 = [
            'title' => 'My little film club',
            'reserved_from' => '2024-05-17 23:30:00',
            'reserved_until' => '2024-05-18 01:00:00',
            'note' => null
        ];

        $input2 = [
            'title' => 'Veresegyházi asszonykórus',
            'reserved_from' => '2024-05-18 01:00:00',
            'reserved_until' => '2024-05-18 01:05:00',
            'note' => null
        ];

        $this->actingAs($user)->post(route('reservations.store', $item), $input1);
        $this->actingAs($user)->post(route('reservations.store', $item), $input2);

        $input3 = $input2;
        $input3['reserved_from'] = '2024-05-18 00:50:00';
        $input3['reserved_until'] = '2024-05-18 00:55:00';

        $reservation2 = Reservation::where('title', $input2['title'])->first();

        $response = $this->actingAs($user)->post(route('reservations.update', $reservation2), $input3);

        $response->assertStatus(409);
        $this->assertEquals(2, $item->reservations->count());

        // and check whether it remained the same
        $this->assertDatabaseHas('reservations', array_merge($input2, [
            'reservable_item_id' => $item->id,
            'user_id' => $user->id,
            'verified' => false // this is a room and our user not an admin
        ]));
    }

    /**
     * Check whether the system refuses reservations
     * where the end date is earlier than the start date.
     *
     * @return void
     */
    public function test_until_earlier_than_from(): void
    {
        $item = ReservableItem::factory()->create();
        // it now has no reservations

        $user = $this->createCollegist();

        $input = [
            'title' => 'H. G. Wells\' The Time Machine',
            'reserved_from' => '2024-05-17 23:30:00',
            'reserved_until' => '2024-05-17 23:29:00',
            'note' => null
        ];

        $response = $this->actingAs($user)->post(route('reservations.store', $item), $input);

        $response->assertStatus(302);
        $this->assertNull(Reservation::where('title', $input['title'])->first());
    }

    /**
     * Check various policies, each with
     * one case for authorized and one for unauthorized access.
     * Also checks whether the 'verified' flag is set correctly.
     *
     * @return void
     */
    public function test_policies(): void
    {
        $secretary = User::factory()->create(['verified' => true]);
        $secretary->addRole(Role::firstWhere('name', Role::SECRETARY));
        $collegist = $this->createCollegist();

        $itemInput = [
            'name' => 'újgörög',
            'type' => 'room',
            'default_reservation_duration' => 15,
            'is_default_compulsory' => false,
            'allowed_starting_minutes' => '0,15,30,45'
        ];

        $response = $this->actingAs($collegist)->post(route('reservations.items.store'), $itemInput);
        $response->assertStatus(403);
        $this->assertEquals(0, ReservableItem::all()->count());

        $response = $this->actingAs($secretary)->post(route('reservations.items.store'), $itemInput);
        $response->assertStatus(302);
        $this->assertDatabaseHas('reservable_items', $itemInput);

        $item = ReservableItem::where('name', $itemInput['name'])->first();

        $reservationInput = [
            'title' => 'Konferencia',
            'reserved_from' => '2024-05-18 01:00:00',
            'reserved_until' => '2024-05-18 01:05:00',
            'note' => null
        ];

        $response = $this->actingAs($secretary)->post(route('reservations.store', $item), $reservationInput);
        // it has to be verified immediately
        $response->assertStatus(302);
        $this->assertDatabaseHas('reservations', array_merge($reservationInput, [
            'reservable_item_id' => $item->id,
            'user_id' => $secretary->id,
            'verified' => true
        ]));

        $reservationInput = [
            'title' => 'Polka',
            'reserved_from' => '2024-05-18 01:05:00',
            'reserved_until' => '2024-05-18 02:35:00',
            'note' => null
        ];
        $response = $this->actingAs($collegist)->post(route('reservations.store', $item), $reservationInput);
        // this must not be verified immediately
        $response->assertStatus(302);
        $this->assertDatabaseHas('reservations', array_merge($reservationInput, [
            'reservable_item_id' => $item->id,
            'user_id' => $collegist->id,
            'verified' => false
        ]));

        $reservation = Reservation::where('title', $reservationInput['title'])->first();

        // try to verify it
        $response = $this->actingAs($collegist)->post(route('reservations.verify', $reservation));
        $response->assertStatus(403);
        $this->assertDatabaseHas('reservations', array_merge($reservationInput, [
            'reservable_item_id' => $item->id,
            'user_id' => $collegist->id,
            'verified' => false
        ]));
        $response = $this->actingAs($secretary)->post(route('reservations.verify', $reservation));
        $response->assertStatus(302);
        Mail::assertQueued(ReservationVerified::class);
        $this->assertDatabaseHas('reservations', array_merge($reservationInput, [
            'reservable_item_id' => $item->id,
            'user_id' => $collegist->id,
            'verified' => true
        ]));

        // when the secretariat modifies it:
        $reservationInput['reserved_from'] = '2024-05-18 01:10:00';
        $response = $this->actingAs($secretary)->post(route('reservations.update', $reservation), $reservationInput);
        $response->assertStatus(302);
        $this->assertDatabaseHas('reservations', array_merge($reservationInput, [
            'reservable_item_id' => $item->id,
            'user_id' => $collegist->id,
            'verified' => true
        ]));
        // when a collegist modifies it:
        $reservationInput['reserved_from'] = '2024-05-18 01:05:00';
        $response = $this->actingAs($collegist)->post(route('reservations.update', $reservation), $reservationInput);
        $response->assertStatus(302);
        $this->assertDatabaseHas('reservations', array_merge($reservationInput, [
            'reservable_item_id' => $item->id,
            'user_id' => $collegist->id,
            'verified' => false // it gets downgraded
        ]));

        // another collegist must not be able to modify it
        $collegist2 = $this->createCollegist();
        $reservationInput2 = $reservationInput;
        $reservationInput2['reserved_from'] = '2024-05-18 01:07:00';
        $response = $this->actingAs($collegist2)->post(route('reservations.update', $reservation), $reservationInput2);
        $response->assertStatus(403);
        $this->assertDatabaseHas('reservations', array_merge($reservationInput, [ // with the previous properties
            'reservable_item_id' => $item->id,
            'user_id' => $collegist->id,
            'verified' => false
        ]));
        // and they also must not be able to delete it
        $response = $this->actingAs($collegist2)->post(route('reservations.delete', $reservation));
        $response->assertStatus(403);
        $this->assertDatabaseHas('reservations', array_merge($reservationInput, [ // with the previous properties
            'reservable_item_id' => $item->id,
            'user_id' => $collegist->id,
            'verified' => false
        ]));
        // but their owner must
        $response = $this->actingAs($collegist)->post(route('reservations.delete', $reservation));
        $response->assertStatus(302);
        $this->assertDatabaseMissing('reservations', $reservationInput);

        // and deleting items:
        $response = $this->actingAs($collegist)->delete(route('reservations.items.destroy', $item));
        $response->assertStatus(403);
        $this->assertDatabaseHas('reservable_items', $itemInput);
        $response = $this->actingAs($secretary)->delete(route('reservations.items.destroy', $item));
        $response->assertStatus(302);
        $this->assertDatabaseMissing('reservable_items', $itemInput);
    }
}
