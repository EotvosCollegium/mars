<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // so that we do not send emails during seeding
        config(['mail.driver' => 'log']);

        $this->call(UsersTableSeeder::class);
        $this->call(SemesterSeeder::class);
        $this->call(RouterSeeder::class);
        $this->call(TransactionSeeder::class);
        $this->call(EpistolaSeeder::class);
        $this->call(GeneralAssemblySeeder::class);
        $this->call(ReservationSeeder::class);
        $this->call(AnonymousQuestionSeeder::class);

        config(['mail.driver' => env('MAIL_DRIVER')]);
    }
}
