<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

use App\Enums\ReservableItemType;
use App\Models\User;
use App\Models\Role;
use App\Models\Reservations\ReservableItem;
use App\Models\Reservations\Reservation;
use App\Models\Reservations\ReservationGroup;
use App\Models\Workshop;
use App\Mail\Reservations\ReportReservableItemFault;
use App\Mail\Reservations\ReservationAffected;
use App\Mail\Reservations\ReservationDeleted;
use App\Mail\Reservations\ReservationRequested;
use App\Mail\Reservations\ReservationVerified;
use App\Http\Controllers\Dormitory\Reservations\ReservationController;

class ReservationTest extends TestCase
{
    /**
     * We would like to erase the database after each test case
     * so that they don't interfere with each other.
     */
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /**
     * Creates an ordinary collegist who can reserve washing machines
     * but not rooms.
     *
     * @return User
     */
    private static function createCollegist(): User
    {
        $user = User::factory()->create(['verified' => true]);
        $user->setResident();
        return $user;
    }

    /**
     * Creates a workshop leader who can request reservations for rooms.
     */
    private static function createWorkshopLeader(): User
    {
        $user = User::factory()->create(['verified' => true]);
        $user->addRole(
            Role::firstWhere('name', Role::WORKSHOP_LEADER),
            Workshop::firstWhere('name', Workshop::AURELION)
        );
        return $user;
    }

    /**
     * Creates a user with administrative rights over reservations.
     */
    private static function createSecretary(): User
    {
        $user = User::factory()->create(['verified' => true]);
        $user->addRole(Role::where('name', Role::SECRETARY)->first());
        return $user;
    }

    /**
     * Creates a verified user without either a collegist or a tenant role.
     */
    private static function createUserWithoutRole(): User
    {
        return User::create([
            'name' => 'No One',
            'email' => 'no.one@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('asdasdasd'), // password
            'verified' => 1
        ]);
    }

    /**
     * A case when everything is alright with
     * storing an item,
     * storing a reservation,
     * and editing the latter.
     *
     * @return void
     */
    public function test_happy_path(): void
    {
        $user = self::createWorkshopLeader();

        $itemInput = [
            'name' => 'újgörög',
            'type' => ReservableItemType::ROOM->value,
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

        $now = CarbonImmutable::now();

        $input = [
            'title' => 'My little film club',
            'reserved_from' => $now->addMinutes(2),
            'reserved_until' => $now->addMinutes(62),
            'note' => null
        ];
        $response = $this->followingRedirects()->actingAs($user)->post(
            route('reservations.store', $item),
            $input
        );
        $this->assertTrue(Reservation::where('title', $input['title'])->exists());
        $response->assertSeeText($input['title']);
        Mail::assertQueued(ReservationRequested::class);

        $reservation = Reservation::where('title', $input['title'])->first();
        $this->assertEquals(0, $reservation->verified);

        // let's verify it
        $response = $this->actingAs($anita)->post(
            route('reservations.verify', $reservation)
        );
        $reservation->refresh();
        $this->assertEquals(1, $reservation->verified);
        Mail::assertQueued(ReservationVerified::class);

        $input = [
            'title' => 'A',
            'reserved_from' => $now->addMinutes(2),
            'reserved_until' => $now->addMinutes(63),
            'note' => null
        ];
        $response = $this->followingRedirects()->actingAs($user)->post(
            route('reservations.update', $reservation),
            $input
        );
        $reservation->refresh();
        $this->assertEquals("{$now->addMinutes(63)}", $reservation->reserved_until);
        $response->assertSeeText($input['title']);
        $this->assertEquals(0, $reservation->verified);
        Mail::assertQueued(ReservationRequested::class);

        $response = $this->actingAs($user)->post(
            route('reservations.delete', $reservation)
        );
        $this->assertEquals(0, Reservation::count());
    }

    /**
     * A case where we try to store reservations
     * with illegal data.
     */
    public function test_illegal_reservations(): void
    {
        $user = self::createWorkshopLeader();
        $room = ReservableItem::factory()->create([
            'type' => ReservableItemType::ROOM->value,
            'out_of_order' => false
        ]);

        $now = CarbonImmutable::now();

        // with reserved_until in the past
        $input = [
            'title' => 'My little film club',
            'reserved_from' => $now->subMinutes(10),
            'reserved_until' => $now->subMinutes(1),
            'note' => null
        ];
        $response = $this->followingRedirects()->actingAs($user)->post(
            route('reservations.store', $room),
            $input
        );
        $this->assertTrue(Reservation::where('title', $input['title'])->doesntExist());
        $response->assertDontSeeText($input['title']);

        // with reserved_until before reserved_from
        $input = [
            'title' => 'My little film club',
            'reserved_from' => $now->addMinutes(10),
            'reserved_until' => $now->addMinutes(9),
            'note' => null
        ];
        $response = $this->followingRedirects()->actingAs($user)->post(
            route('reservations.store', $room),
            $input
        );
        $this->assertTrue(Reservation::where('title', $input['title'])->doesntExist());
        $response->assertDontSeeText($input['title']);

        // with reserved_until equal to reserved_from
        $input['reserved_from'] = $input['reserved_until'];
        $response = $this->followingRedirects()->actingAs($user)->post(
            route('reservations.store', $room),
            $input
        );
        $this->assertTrue(Reservation::where('title', $input['title'])->doesntExist());
        $response->assertDontSeeText($input['title']);

        // and when the item is out of order:
        $anita = self::createSecretary();
        $response = $this->followingRedirects()->actingAs($anita)->post(
            route('reservations.items.toggle_out_of_order', $room)
        );
        $room->refresh();
        $this->assertEquals(1, $room->out_of_order);
        $input = [
            'title' => 'My little film club',
            'reserved_from' => $now->addMinutes(2),
            'reserved_until' => $now->addMinutes(62),
            'note' => null
        ];
        $response = $this->followingRedirects()->actingAs($user)->post(
            route('reservations.store', $room),
            $input
        );
        $this->assertTrue(Reservation::where('title', $input['title'])->doesntExist());
        $response->assertDontSeeText($input['title']);
    }

    /**
     * Tests whether single conflicts are handled appropriately.
     */
    public function test_conflicts(): void
    {
        $user = self::createWorkshopLeader();
        $room = ReservableItem::factory()->create([
            'type' => ReservableItemType::ROOM->value,
            'out_of_order' => false
        ]);

        // this has to be the same for every case;
        // that is why we save it to a variable
        $now = CarbonImmutable::now(); // important: it must be immutable!

        $input = [
            'title' => 'My little film club',
            'reserved_from' => $now->addHours(2),
            'reserved_until' => $now->addHours(5),
            'note' => null
        ];
        $response = $this->followingRedirects()->actingAs($user)->post(
            route('reservations.store', $room),
            $input
        );
        $reservation = Reservation::where('title', $input['title'])->first();
        $this->assertNotNull($reservation);
        $response->assertSeeText($input['title']);
        $this->assertEquals(0, $reservation->verified);

        $input = [
            'title' => 'Usurpers\' Conference',
            'note' => null
        ];
        // these will correspond to different possible positions of two intervals relative to each other
        $intervals = [[1, 3], [1,5], [3, 6], [2, 5], [2, 6], [3, 4], [1, 6]];
        foreach ($intervals as $interval) {
            $input['reserved_from'] = $now->addHours($interval[0]);
            $input['reserved_until'] = $now->addHours($interval[1]);
            $response = $this->followingRedirects()->actingAs($user)->post(
                route('reservations.store', $room),
                $input
            );
            $this->assertTrue(Reservation::where('title', $input['title'])->doesntExist());
            $response->assertDontSeeText($input['title']);
        }

        // and these only touch the already given event;
        // these should be allowed
        $intervals = [[1, 2], [5, 6]];
        foreach ($intervals as $interval) {
            $input['title'] = "{$interval[0]}–{$interval[1]}"; // these must have different titles
            $input['reserved_from'] = $now->addHour($interval[0]);
            $input['reserved_until'] = $now->addHour($interval[1]);
            $response = $this->followingRedirects()->actingAs($user)->post(
                route('reservations.store', $room),
                $input
            );
            $this->assertTrue(Reservation::where('title', $input['title'])->exists());
            $response->assertSeeText($input['title']);
        }
    }

    /**
     * A case in which we try to edit a reservation in the past.
     */
    public function test_editing_past_reservation(): void
    {
        $user = self::createWorkshopLeader();
        $room = ReservableItem::factory()->create([
            'type' => ReservableItemType::ROOM->value,
            'out_of_order' => false
        ]);

        $now = CarbonImmutable::now();
        $reservation = $room->reservations()->create([
            'user_id' => $user->id,
            'title' => 'A',
            'reserved_from' => $now->subHour(2),
            'reserved_until' => $now->subHour(1),
            'note' => null,
            'verified' => rand(0, 1)
        ]);

        // we even try to move it into the future
        $input = [
            'title' => 'B',
            'reserved_from' => $now->addHour(1),
            'reserved_until' => $now->addHour(2),
            'note' => null
        ];
        $response = $this->followingRedirects()->actingAs($user)->post(
            route('reservations.update', $reservation),
            $input
        );
        $reservation->refresh();
        // it should not have changed
        $this->assertEquals($now->subHour(2), $reservation->reserved_from);
        $this->assertEquals($now->subHour(1), $reservation->reserved_until);
        $this->assertEquals('A', $reservation->title);
    }

    /**
     * A case for testing creating and editing reservation groups
     * where everything is fine.
     */
    public function test_happy_path_for_groups(): void
    {
        $user = self::createWorkshopLeader();

        $itemInput = [
            'name' => 'újgörög',
            'type' => ReservableItemType::ROOM->value,
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

        $now = CarbonImmutable::now();

        $input = [
            'title' => 'My little film club',
            'reserved_from' => $now->addMinutes(2),
            'reserved_until' => $now->addMinutes(62),
            'note' => null,
            'recurring' => 'on',
            'frequency' => 2,
            'last_day' => $now->addMinutes(2)->addDays(4)->setHour(0)->setMinute(0)
        ];
        $response = $this->followingRedirects()->actingAs($user)->post(
            route('reservations.store', $item),
            $input
        );
        $response->assertSeeText($input['title']);
        $this->assertTrue(ReservationGroup::where('group_title', $input['title'])->exists());
        $group = ReservationGroup::where('group_title', $input['title'])->first();
        $this->assertEquals(3, $group->reservations()->count());
        $this->assertEquals(0, $group->verified);
        Mail::assertQueued(ReservationRequested::class);

        $frequency = $input['frequency']; // we are going to need this later




        // editing all reservations
        $input = [
            'title' => 'My great film club',
            'reserved_from' => $now->addMinutes(3),
            'reserved_until' => $now->addMinutes(63),
            'note' => null,
            'last_day' => $now->addMinutes(2)->addDays(6)->setHour(0)->setMinute(0),
            'for_what' => ReservationController::EDIT_ALL
        ];

        $response = $this->followingRedirects()->actingAs($user)->post(
            // we'll deliberately take the last one
            route('reservations.update', $group->reservations()->orderBy('reserved_from', 'desc')->first()),
            $input
        );

        $group->refresh();
        $this->assertEquals($input['title'], $group->group_title);
        $reservations = $group->reservations()->orderBy('reserved_from')->get()->all();
        $this->assertEquals(0, $group->verified);
        $response->assertSeeText($input['title']);
        Mail::assertQueued(ReservationRequested::class);

        $i = 0;
        foreach ($reservations as $reservation) {
            $this->assertEquals($input['title'], $reservation->title);
            $this->assertEquals($input['note'], $reservation->note);
            $this->assertEquals("{$input['reserved_from']->addDays($i * $frequency)}", $reservation->reserved_from);
            $this->assertEquals("{$input['reserved_until']->addDays($i * $frequency)}", $reservation->reserved_until);
            ++$i;
        }
        $this->assertEquals(4, $i); // the number of reservations




        // let's verify the group
        $response = $this->followingRedirects()->actingAs($anita)->post(
            route('reservations.verify_all', $reservations[2])
        );
        $group->refresh();
        $this->assertEquals(1, $group->verified);
        $this->assertTrue($group->reservations()->where('verified', false)->doesntExist());
        Mail::assertQueued(ReservationVerified::class);




        // now editing only the third one
        $input = [
            'title' => 'Anything but a film club',
            'reserved_from' => $now->addDays(4)->addMinutes(4),
            'reserved_until' => $now->addDays(4)->addMinutes(64),
            'note' => null,
            'last_day' => $now->addDays(6)->addMinutes(4)->setHour(0)->setMinute(0),
            'for_what' => ReservationController::EDIT_THIS_ONLY
        ];
        $response = $this->followingRedirects()->actingAs($user)->post(
            route('reservations.update', $reservations[2]),
            $input
        );

        $group->refresh();
        $reservations = $group->reservations()->orderBy('reserved_from')->get()->all();
        $this->assertEquals('My great film club', $group->group_title);
        $this->assertEquals(1, $group->verified);
        $response->assertSeeText($input['title']);

        // now the third one must still be there,
        // but with a different title and different starting dates,
        // and it also must be unverified
        $this->assertEquals(4, count($reservations));
        foreach ([0, 1, 3] as $i) {
            $reservation = $reservations[$i];
            $this->assertEquals($reservations[0]->title, $reservation->title);
            $this->assertEquals($reservations[0]->note, $reservation->note);
            $this->assertEquals("{$now->addMinutes(3)->addDays($i * $frequency)}", $reservation->reserved_from);
            $this->assertEquals("{$now->addMinutes(63)->addDays($i * $frequency)}", $reservation->reserved_until);
            $this->assertEquals(1, $reservation->verified);
        }
        $reservation = $reservations[2];
        $this->assertEquals($input['title'], $reservation->title);
        $this->assertEquals($input['note'], $reservation->note);
        $this->assertEquals("{$input['reserved_from']}", $reservation->reserved_from);
        $this->assertEquals("{$input['reserved_until']}", $reservation->reserved_until);
        $this->assertEquals(0, $reservation->verified);
        Mail::assertQueued(ReservationRequested::class);




        // and finally editing the second one and all of them after it
        // NOTE: the first one does not get detached! (Google Calendar makes them a separate group)
        $firstReservation = $reservations[0]; // we will need this later
        $input = [
            'title' => 'Film club reloaded',
            'reserved_from' => $now->addDays(2)->addMinutes(5),
            'reserved_until' => $now->addDays(2)->addMinutes(65),
            'note' => null,
            'last_day' => $now->addDays(6)->addMinutes(5)->setHour(0)->setMinute(0),
            'for_what' => ReservationController::EDIT_ALL_AFTER
        ];
        $response = $this->followingRedirects()->actingAs($user)->post(
            route('reservations.update', $reservations[1]),
            $input
        );

        $group->refresh();
        $this->assertEquals($input['title'], $group->group_title);
        $response->assertSeeText($input['title']);
        Mail::assertQueued(ReservationRequested::class);

        // now the first one should not even have remained in the group,
        // and it must be the only one to have remained verified
        $firstReservation->refresh();
        $this->assertNull($firstReservation->group_id);
        $this->assertEquals(1, $firstReservation->verified);

        // and for the remainder (even the third one):
        $i = 0;
        foreach ($group->reservations()->orderBy('reserved_from')->get() as $reservation) {
            $this->assertEquals($input['title'], $reservation->title);
            $this->assertEquals($input['note'], $reservation->note);
            $this->assertEquals("{$input['reserved_from']->addDays($i * $frequency)}", $reservation->reserved_from);
            $this->assertEquals("{$input['reserved_until']->addDays($i * $frequency)}", $reservation->reserved_until);
            $this->assertEquals(0, $reservation->verified);
            ++$i;
        }
        $this->assertEquals(3, $i); // the number of them




        // and finally, deletion
        $response = $this->actingAs($anita)->post(
            route('reservations.delete_all', $reservations[1])
        );
        $this->assertTrue(Reservation::where('id', $group->id)->doesntExist());
        $this->assertEquals(1, Reservation::count()); // only the first one should have remained
        Mail::assertQueued(ReservationDeleted::class);
    }

    /**
     * Tests what happens when a later reservation of a group
     * conlicts with an existing reservation.
     */
    public function test_group_conflicts(): void
    {
        $user = self::createWorkshopLeader();
        $room = ReservableItem::factory()->create([
            'type' => ReservableItemType::ROOM->value,
            'out_of_order' => false
        ]);

        $now = CarbonImmutable::now(); // important: it must be immutable!

        $input = [
            'title' => 'My little film club',
            'reserved_from' => $now->addDays(7)->addHour(2),
            'reserved_until' => $now->addDays(7)->addHour(5),
            'note' => null
        ];
        $response = $this->actingAs($user)->post(
            route('reservations.store', $room),
            $input
        );
        $reservation = Reservation::where('title', $input['title'])->first();
        $this->assertNotNull($reservation);
        $this->assertEquals(0, $reservation->verified);



        // and now the conflicting group

        $input = [
            'title' => 'Very important class',
            'reserved_from' => $now->addHour(3),
            'reserved_until' => $now->addHour(4),
            'note' => null,
            'recurring' => 'on',
            'frequency' => 7,
            // important if those 3 hours stretch through midnight
            'last_day' => $now->addHour(3)->addDays(7)->setHour(0)->setMinute(0)
        ];
        $response = $this->actingAs($user)->post(
            route('reservations.store', $room),
            $input
        );
        $this->assertTrue(Reservation::where('title', $input['title'])->doesntExist());
    }

    /**
     * A simple happy path for washing machines.
     */
    public function test_washing_machine_happy_path(): void
    {
        $user = self::createCollegist();
        $machine = ReservableItem::factory()->create([
            'type' => ReservableItemType::WASHING_MACHINE->value,
            'out_of_order' => false
        ]);

        $now = CarbonImmutable::now();

        $input = [
            'reserved_from' => $now->addHour()->setMinute(0)->setSecond(0),
            'reserved_until' => $now->addHours(2)->setMinute(0)->setSecond(0),
            'note' => 'I\'ll be there soon'
        ];
        $response = $this->actingAs($user)->post(
            route('reservations.store', $machine),
            $input
        );
        $reservation = $machine->reservations()->first();
        $this->assertNotNull($reservation);
        $this->assertEquals($input['note'], $reservation->note);
        // it has to be automatically verified
        $this->assertEquals(1, $reservation->verified);
        // and no mail should be sent
        Mail::assertNotQueued(ReservationRequested::class);

        $response = $this->actingAs($user)->post(
            route('reservations.delete', $reservation)
        );
        $this->assertEquals(0, Reservation::count());
    }

    /**
     * Tests whether washing machines really only accept
     * one-hour slots beginning with an integer hour.
     */
    public function test_washing_machine_slots(): void
    {
        $user = self::createCollegist();
        $machine = ReservableItem::factory()->create([
            'type' => ReservableItemType::WASHING_MACHINE->value,
            'out_of_order' => false
        ]);

        $now = CarbonImmutable::now();

        foreach ([59, 1] as $startMinute) {
            foreach ([59, 1] as $endMinute) {
                $input = [
                    'reserved_from'
                        => $now->addHour()->setMinute($startMinute)->setSecond(0),
                    'reserved_until'
                        => $now->addHours(2)->setMinute($endMinute)->setSecond(0)
                ];
                $response = $this->actingAs($user)->post(
                    route('reservations.store', $machine),
                    $input
                );
                $this->assertEquals(0, Reservation::count());
            }
        }

        // a 2-hour long slot
        $input = [
            'reserved_from' => $now->addHour()->setMinute(0),
            'reserved_until' => $now->addHours(3)->setMinute(0)
        ];
        $response = $this->actingAs($user)->post(
            route('reservations.store', $machine),
            $input
        );
        $this->assertEquals(0, Reservation::count());
    }

    /**
     * Tests whether a user can have more than 6 active reservations.
     */
    public function test_washing_machine_maximum(): void
    {
        $user = self::createCollegist();
        $machine1 = ReservableItem::factory()->create([
            'name' => 'machine1',
            'type' => ReservableItemType::WASHING_MACHINE->value,
            'out_of_order' => false
        ]);
        $machine2 = ReservableItem::factory()->create([
            'name' => 'machine2',
            'type' => ReservableItemType::WASHING_MACHINE->value,
            'out_of_order' => false
        ]);

        $now = CarbonImmutable::now();
        // to achieve one-hour slots:
        $nowHour = $now->addHours(1)->setMinute(0)->setSecond(0);

        // this should not count as it is in the past
        Reservation::create([
            'reservable_item_id' => $machine1->id,
            'user_id' => $user->id,
            'reserved_from' => $nowHour->subHours(3),
            'reserved_until' => $nowHour->subHours(2),
            'verified' => 1
        ]);

        foreach([$machine1, $machine2] as $machine) {
            for ($i = 0; $i < 3; ++$i) {
                $input = [
                    'reserved_from' => $nowHour->addHour($i),
                    'reserved_until' => $nowHour->addHours($i + 1),
                    'note' => "active_reservation_{$machine->name}_$i"
                ];
                $response = $this->actingAs($user)->post(
                    route('reservations.store', $machine),
                    $input
                );
                $this->assertTrue(Reservation::where('note', $input['note'])->exists());
            }
        }

        // and now let's try to add a seventh one
        foreach([$machine1, $machine2] as $machine) {
            $input = [
                'reserved_from' => $nowHour->addHours(3),
                'reserved_until' => $nowHour->addHours(4),
                'note' => "active_reservation_{$machine->name}_4"
            ];
            $response = $this->actingAs($user)->post(
                route('reservations.store', $machine),
                $input
            );
            $this->assertTrue(Reservation::where('note', $input['note'])->doesntExist());
        }
    }

    /**
     * Tests various unauthorized requests.
     */
    public function test_unauthorized_requests(): void
    {
        $workshopLeader = self::createWorkshopLeader();
        $collegist = self::createCollegist();

        $itemInput = [
            'name' => 'újgörög',
            'type' => ReservableItemType::ROOM->value,
            'out_of_order' => false
        ];

        $response = $this->actingAs($workshopLeader)->post(
            route('reservations.items.store'),
            $itemInput
        );
        $response->assertStatus(403);
        $this->assertEquals(0, ReservableItem::count());

        // let's create one to work with
        $room = ReservableItem::factory()->create([
            'type' => ReservableItemType::ROOM->value,
            'out_of_order' => false
        ]);

        $now = CarbonImmutable::now();

        $input = [
            'title' => 'My little film club',
            'reserved_from' => $now->addMinutes(2),
            'reserved_until' => $now->addMinutes(62),
            'note' => null
        ];
        // as an ordinary collegist
        $response = $this->actingAs($collegist)->post(
            route('reservations.store', $room),
            $input
        );
        $response->assertStatus(403);
        $this->assertEquals(0, Reservation::count());

        // updating the reservation of someone else
        $reservation = Reservation::create([
            'reservable_item_id' => $room->id,
            'user_id' => $workshopLeader->id,
            'title' => 'Very important lesson',
            'reserved_from' => $now->addMinutes(32),
            'reserved_until' => $now->addMinutes(92),
            'verified' => 0
        ]);
        $workshopLeader2 = self::createWorkshopLeader();
        $input = [
            'title' => 'My little film club',
            'reserved_from' => $now->addMinutes(10),
            'reserved_until' => $now->addMinutes(62),
            'note' => null
        ];
        $response = $this->actingAs($workshopLeader2)->post(
            route('reservations.update', $reservation),
            $input
        );
        $response->assertStatus(403);
        $this->assertTrue(Reservation::where('title', $input['title'])->doesntExist());

        // unauthorized toggling of the out_of_order flag
        $response = $this->actingAs($workshopLeader)->post(
            route('reservations.items.toggle_out_of_order', $room)
        );
        $response->assertStatus(403);
        $room->refresh();
        $this->assertEquals(0, $room->out_of_order);

        // unauthorized verification
        $response = $this->actingAs($workshopLeader)->post(
            route('reservations.verify', $reservation)
        );
        $response->assertStatus(403);
        $reservation->refresh();
        $this->assertEquals(0, $reservation->verified);

        // unauthorized deletion
        $response = $this->actingAs($workshopLeader2)->post(
            route('reservations.delete', $reservation)
        );
        $response->assertStatus(403);
        $this->assertTrue(Reservation::where('title', $reservation->title)->exists());

        // and for a washing machine:
        $noOne = self::createUserWithoutRole();
        $machine = ReservableItem::factory()->create([
            'name' => 'machine1',
            'type' => ReservableItemType::WASHING_MACHINE->value,
            'out_of_order' => false
        ]);
        $input = [
            'reserved_from' => $now->addHour()->setMinute(0)->setSecond(0),
            'reserved_until' => $now->addHours(2)->setMinute(0)->setSecond(0),
            'note' => 'I\'ll be there soon'
        ];
        $response = $this->actingAs($noOne)->post(
            route('reservations.store', $machine),
            $input
        );
        $response->assertStatus(403);
    }

    /**
     * Testing fault reporting and status toggling functionality.
     */
    public function test_fault_report(): void
    {
        $anita = self::createSecretary();
        $collegist = self::createCollegist();
        $now = CarbonImmutable::now();

        foreach([ReservableItemType::WASHING_MACHINE, ReservableItemType::ROOM] as $type) {
            foreach([1, 0] as $outOfOrder) {
                $item = ReservableItem::factory()->create([
                    'name' => 'item',
                    'type' => $type->value,
                    'out_of_order' => $outOfOrder
                ]);
                // an existing reservation for testing notifications
                Reservation::create([
                    'reservable_item_id' => $item->id,
                    'user_id' => $collegist->id,
                    'reserved_from' => $now->addHours(2),
                    'reserved_until' => $now->addHours(3),
                    'verified' => 1
                ]);

                $response = $this->actingAs($collegist)->followingRedirects()->post(
                    route('reservations.items.report_fault', $item),
                    ['message' => null]
                );
                $response->assertStatus(200);
                $item->refresh();
                $this->assertEquals($outOfOrder, $item->out_of_order); // it must have remained
                Mail::assertQueued(ReportReservableItemFault::class);

                $response = $this->actingAs($anita)->followingRedirects()->post(
                    route('reservations.items.toggle_out_of_order', $item)
                );
                $response->assertStatus(200);
                $item->refresh();
                $negateOutOfOrder = $outOfOrder ? 0 : 1;
                $this->assertEquals($negateOutOfOrder, $item->out_of_order); // it must have changed
                Mail::assertQueued(ReservationAffected::class);
            }
        }
    }
}
