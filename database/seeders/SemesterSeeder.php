<?php

namespace Database\Seeders;

use App\Models\Semester;
use App\Models\User;
use App\Models\SemesterStatus;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $semester = Semester::firstOrCreate([
            'year' => Carbon::now()->year - 2,
            'part' => 1,
        ]);

        while ($semester->year != Carbon::now()->year) {
            // generates semester until this year
            $semester = $semester->succ();
        }
        Semester::current(); // generate current semester if still not exists

        $users = User::collegists();

        $semesters = Semester::all();
        foreach ($users as $user) {
            foreach ($semesters as $semester) {
                $status = array_rand(SemesterStatus::STATUSES);
                SemesterStatus::withoutEvents(function () use ($user, $semester, $status) {
                    $user->setStatusFor($semester, SemesterStatus::STATUSES[$status]);
                });
            }
        }
    }
}
