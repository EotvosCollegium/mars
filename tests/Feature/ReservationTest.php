<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

use App\Enums\ReservableItemType;
use App\Models\User;
use App\Models\Role;
use App\Models\ReservableItem;
use App\Models\Reservation;

class ReservationTest extends TestCase
{
    /**
     * Creates a user with permissions to request a reservation.
     *
     * @return User
     */
    public static function createCollegist(): User
    {
        $user = User::factory()->create(['verified' => true]);
        $user->setResident();
        return $user;
    }

    /**
     * Creates a user with administrative rights over reservations.
     */
    public static function createSecretary(): User
    {
        $user = User::factory()->create();
        $user->addRole(Role::where('name', Role::SECRETARY)->first());
        return $user;
    }

    /**
     * A case when everything is alright with a reservation
     * and its modification.
     *
     * @return void
     */
    public function test_happy_path(): void
    {

        $user = self::createCollegist();

        $itemInput = [
            'name' => 'újgörög',
            'type' => ReservableItemType::ROOM,
            'out_of_order' => false
        ];

        $anita = self::createSecretary();
        $response = $this->followingRedirects()->actingAs($anita)->post(
            route('reservations.items.store'),
            $itemInput
        );
        $response->assertStatus(200);
        $response->assertSeeText($itemInput['name']);

        $item = ReservableItem::where('name', $itemInput['name'])->first();

        $input = [
            'title' => 'My little film club',
            'reserved_from' => Carbon::now()->addMinutes(2),
            'reserved_until' => Carbon::now()->addMinutes(62),
            'note' => null
        ];
        $response = $this->followingRedirects()->actingAs($user)->post(
            route('reservations.store', $item),
            $input
        );
        $response->assertStatus(200);
        $response->assertSeeText($input['title']);

        $reservation = Reservation::where('title', $input['title'])->first();
        $this->assertEquals(0, $reservation->verified);
    }
}
