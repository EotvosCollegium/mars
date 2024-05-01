<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Carbon\Carbon;
use Illuminate\Support\Arr;

use App\Models\User;
use App\Models\ReservableItem;
use App\Models\Reservation;

class ReservationSeeder extends Seeder
{
    const NUMBER_OF_WASHING_MACHINES = 2;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // First, create the washing machines.
        $washing_machines = [];
        for ($i = 1; $i <= ReservationSeeder::NUMBER_OF_WASHING_MACHINES; ++$i) {
            $washing_machines[] = ReservableItem::create([
                'name' => "Washing machine no. $i",
                'type' => 'washing_machine',
                'default_reservation_duration' => 60,
                'is_default_compulsory' => true,
                'allowed_starting_minutes' => "0",
                'out_of_order_from' => null,
                'out_of_order_until' => null
            ]);
        }

        // Now the rooms.
        $rooms = ReservableItem::factory()->count(10)->create();

        // The reservations for the washing machines.
        foreach($washing_machines as $washing_machine) {
            // for this day and the next 13 days
            for($day = 0; $day < 14; ++$day) {
                for ($hour = 0; $hour < 24; ++$hour) {
                    if (!rand(0, 2)) { // with 33% chance
                        $washing_machine->reserve(
                            User::all()->random(),
                            null,
                            null,
                            Carbon::today()->add($day, 'day')->add($hour, 'hour'),
                            Carbon::today()->add($day, 'day')->add($hour + 1, 'hour')
                        );
                    }
                }
            }
        }

        $faker = \Faker\Factory::create();

        // The reservations for the rooms.
        // Let's try create five for each day at first;
        // but delete the ones conflicting with older ones.
        foreach($rooms as $room) {
            for($day = 0; $day < 14; ++$day) {
                for($i = 0; $i < 5; ++$i) {
                    $hour = rand(0, 23);
                    $minute = Arr::random([0, 15, 30, 45]);
                    $length = Arr::random([30, 60, 90, 120, 150, 180]); // in minutes
                    $from = Carbon::today()->add($day, 'day')->add($hour, 'hour')->add($minute, 'minute');
                    $until = $from->copy()->add($length, 'minute');

                    // we don't save it yet!
                    $reservation = new Reservation();
                    $reservation->reservable_item_id = $room->id;
                    $reservation->user_id = User::inRandomOrder()->first()->id;
                    $reservation->title = $faker->realText(50);
                    $reservation->note = $faker->realText(150);
                    $reservation->reserved_from = $from;
                    $reservation->reserved_until = $until;
                    $reservation->verified = $faker->boolean(50);

                    $status = $room->statusOfSlot($from, $until);
                    if (ReservableItem::FREE == $status) {
                        $reservation->save();
                    }
                }
            }
        }
    }
}
