<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\ReservableItem;
use App\Models\Reservation;
use App\Models\ReservationGroup;
use App\Models\User;

class ReservationSeeder extends Seeder
{
    // The number of washing machines generated by the seeder.
    private const NUMBER_OF_WASHING_MACHINES = 2;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // first, create the two washing machines
        $washing_machines = [
            ReservableItem::create([
                "name" => "The machine on the left",
                "type" => "washing_machine"
            ]),
            ReservableItem::create([
                "name" => "The machine on the right",
                "type" => "washing_machine"
            ]),
        ];

        // creating the rooms
        $rooms = ReservableItem::factory()->count(10)->create();

        foreach($washing_machines as $machine) {
            // for today and the next 14 days
            for ($day = 0; $day < 14; ++$day) {
                for ($hour = 0; $hour < 24; ++$hour) {
                    if (!rand(0, 2)) {
                        Reservation::create([
                            'reservable_item_id' => $machine->id,
                            'user_id' => User::where('verified', true)->inRandomOrder()->first()->id,
                            'verified' => true,
                            'reserved_from' => Carbon::today()->add($day, 'day')->add($hour, 'hour'),
                            'reserved_until' => Carbon::today()->add($day, 'day')->add($hour + 1, 'hour'),
                        ]);
                    }
                }
            }
        }

        $faker = \Faker\Factory::create('hu_HU');

        foreach($rooms as $room) {
            $reservations = [];

            // first some recurring ones
            for ($i = 0; $i < 10; ++$i) {
                $user = User::where('verified', true)->inRandomOrder()->first();
                $group_from = Carbon::today()->addDays(rand(0, 5))
                    ->addHours(rand(0, 23))
                    ->addMinutes(rand(0, 59));
                $group_until = $group_from->copy()->addMinutes(rand(1, 180));
                $new_group = ReservationGroup::create([
                    'group_item' => $room->id,
                    'user_id' => $user->id,
                    'frequency' => $faker->boolean(90) ? 7 : 1,
                    'group_title' => $faker->realText(10),
                    'group_from' => $group_from,
                    'group_until' => $group_until,
                    'last_day' => $group_from->copy()
                                    ->setHour(0)->setMinute(0)->addWeeks(rand(1, 4)),
                    'verified' => $faker->boolean(70)
                ]);

                try {
                    $new_group->initializeFrom($group_from);
                    $reservations = array_merge($reservations, $new_group->reservations->all());
                } catch (\App\Exceptions\ConflictException $e) {
                    $new_group->delete();
                }
            }

            for ($i = 0; $i < 30; ++$i) {
                $day = rand(0, 13);
                $hour = rand(0, 23);
                $minute = rand(0, 60);
                $duration = rand(1, 180);  // it must not be 0!
                $reserved_from
                    = Carbon::today()->add($day, 'day')
                        ->add($hour, 'hour')
                        ->add($minute, 'minute');
                $reserved_until
                    = Carbon::today()->add($day, 'day')
                        ->add($hour, 'hour')
                        ->add($minute + $duration, 'minute');
                $new_one = Reservation::create([
                    'reservable_item_id' => $room->id,
                    'user_id' => User::where('verified', true)->inRandomOrder()->first()->id,
                    'title' => $faker->realText(10),
                    'verified' => rand(0, 1),
                    'reserved_from'
                        => $reserved_from,
                    'reserved_until'
                        => $reserved_until
                ]);

                $wasDeleted = false;
                foreach($reservations as $earlier) {
                    if ($earlier->conflictsWith($new_one)) {
                        $new_one->delete();
                        $wasDeleted = true;
                        break;
                    }
                }

                if (!$wasDeleted) {
                    $reservations[] = $new_one;
                }
            }
        }
    }
}
