<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\ReservableItem;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Role;

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
     * Check whether the 'verified' flag gets set and updated correctly.
     * 
     * @return void
     */
    public function test_verified_flag(): void
    {
        $item = ReservableItem::factory()->create();
        $user = $this->createCollegist();

        $input = [
            'title' => 'Please...',
            'reserved_from' => '2024-05-20 23:30:00',
            'reserved_until' => '2024-05-21 01:00:00',
            'note' => null
        ];
        $this->actingAs($user)->post(route('reservations.store', $item), $input);
        $this->assertDatabaseHas('reservations', array_merge($input, [
            'reservable_item_id' => $item->id,
            'user_id' => $user->id,
            'verified' => false // this is a room and our user not an admin
        ]));

        $reservation = Reservation::where('title', $input['title'])->first();

        // TODO: let an admin verify it

        // then: check whether it gets downgraded after modification
        $input['reserved_until'] = '2024-05-21 01:01:00';
        $this->actingAs($user)->post(route('reservations.update', $reservation), $input);
        $this->assertDatabaseHas('reservations', array_merge($input, [
            'reservable_item_id' => $item->id,
            'user_id' => $user->id,
            'verified' => false
        ]));

        // finally, for a washing machine, it has to be true by default
        $item = ReservableItem::factory()->create();
        $item->type = 'washing_machine'; $item->save();
        $input = [
            'title' => null,
            'reserved_from' => '2024-05-20 23:00:00',
            'reserved_until' => '2024-05-21 00:00:00',
            'note' => null
        ];
        $this->actingAs($user)->post(route('reservations.store', $item), $input);

        $this->assertDatabaseHas('reservations', array_merge($input, [
            'reservable_item_id' => $item->id,
            'user_id' => $user->id,
            'verified' => true
        ]));
    }

    /**
     * Check various policies, each with
     * one case for authorized and one for unauthorized access.
     * 
     * @return void
     */
    public function test_policies(): void
    {
        $secretary = User::factory()->create(['verified' => true]);
        $secretary->addRole(Role::SECRETARY);
        $collegist = $this->createCollegist();

        // TODO
    }
}
