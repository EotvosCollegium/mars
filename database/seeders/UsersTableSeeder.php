<?php

namespace Database\Seeders;

use App\Models\Checkout;
use App\Models\Faculty;
use App\Models\FreePages;
use App\Models\RoleObject;
use App\Models\Workshop;
use App\Models\Role;
use App\Models\User;
use App\Models\Internet\MacAddress;
use App\Models\PrintJob;
use App\Models\Internet\WifiConnection;
use App\Models\PrintAccount;
use App\Models\PersonalInformation;
use App\Models\EducationalInformation;
use App\Models\StudyLine;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createSuperUser();
        $this->createStaff();

        //generate collegists

        $collegist = User::create([
            'name' => 'Éliás Próféta',
            'email' => 'collegist@eotvos.elte.hu',
            'password' => bcrypt('asdasdasd'),
            'verified' => true,
        ]);
        $this->createCollegist($collegist);
        $collegist->printAccount()->save(PrintAccount::factory()->make(['user_id' => $collegist->id]));
        $collegist->personalInformation()->save(PersonalInformation::factory()->make(['user_id' => $collegist->id]));

        User::factory(['verified' => true])->count(50)->create()->each(function ($user) {
            $this->createCollegist($user);
        });
        User::factory(['verified' => false])->count(50)->create()->each(function ($user) {
            $this->createApplicant($user);
        });

        //generate tenants

        $tenant = User::create([
            'name' => 'David Tenant',
            'email' => 'tenant@eotvos.elte.hu',
            'password' => bcrypt('asdasdasd'),
            'verified' => true,
        ]);
        $this->createTenant($tenant);
        $tenant->printAccount()->save(PrintAccount::factory()->make(['user_id' => $tenant->id]));
        $tenant->personalInformation()->save(PersonalInformation::factory()->make(['user_id' => $tenant->id]));

        User::factory()->count(5)->create()->each(function ($user) {
            $this->createTenant($user);
        });
    }

    private function createSuperUser()
    {
        $user = User::create([
            'name' => 'Hapák József',
            'email' => config('mail.test_mail'),
            'password' => bcrypt('asdasdasd'),
            'verified' => true,
        ]);
        $user->printAccount()->save(PrintAccount::factory()->make(['user_id' => $user->id]));
        $user->personalInformation()->save(PersonalInformation::factory()->make(['user_id' => $user->id]));
        MacAddress::factory()->count(3)->create(['user_id' => $user->id]);
        FreePages::factory()->count(5)->create(['user_id' => $user->id]);
        PrintJob::factory()->count(5)->create(['user_id' => $user->id]);
        $user->educationalInformation()->save(EducationalInformation::factory()->make(['user_id' => $user->id]));
        for ($x = 0; $x < rand(1, 3); $x++) {
            $user->faculties()->attach(rand(1, count(Faculty::ALL)));
        }
        for ($x = 0; $x < rand(1, 3); $x++) {
            $user->workshops()->attach(rand(1, count(Workshop::ALL)));
        }
        $user->roles()->attach(Role::collegist()->id, ['object_id' => RoleObject::firstWhere('name', Role::RESIDENT)->id]);
        $user->roles()->attach(Role::sysAdmin()->id);
        $wifi_username = $user->internetAccess->setWifiCredentials();
        WifiConnection::factory($user->id % 5)->create(['wifi_username' => $wifi_username]);
        Checkout::query()->update(['handler_id' => $user->id]);
    }

    private function createCollegist($user)
    {
        MacAddress::factory()->count($user->id % 5)->create(['user_id' => $user->id]);
        PrintJob::factory()->count($user->id % 5)->create(['user_id' => $user->id]);
        $user->roles()->attach(
            Role::get(Role::COLLEGIST)->id,
            [
                'object_id' => rand(
                    RoleObject::firstWhere('name', 'resident')->id,
                    RoleObject::firstWhere('name', 'extern')->id
                )
            ]
        );
        $user->educationalInformation()->save(EducationalInformation::factory()->make(['user_id' => $user->id]));
        StudyLine::factory()->count(rand(1, 2))->create(['educational_information_id' => $user->educationalInformation->id]);
        $user->roles()->attach(Role::get(Role::PRINTER)->id);
        $wifi_username = $user->internetAccess->setWifiCredentials();
        WifiConnection::factory($user->id % 5)->create(['wifi_username' => $wifi_username]);
        for ($x = 0; $x < rand(1, 3); $x++) {
            $faculty = rand(1, count(Faculty::ALL));
            if ($user->faculties()->where('faculty_users.faculty_id', $faculty)->count() == 0) {
                $user->faculties()->attach($faculty);
            }
        }
        for ($x = 0; $x < rand(1, 3); $x++) {
            $workshop = rand(1, count(Workshop::ALL));
            if ($user->workshops()->where('workshop_users.workshop_id', $workshop)->count() == 0) {
                $user->workshops()->attach($workshop);
            }
        }
    }

    /**
     * Create application details for the user
     * @param $user
     * @return void
     */
    private function createApplicant($user)
    {
        $submitted = rand(0, 1);
        // submitted applications must not have a null status
        if ($submitted) {
            $appliedForResidentStatus = rand(0, 1);
        } else {
            $appliedForResidentStatus = rand(-1, 1);
            if (-1 == $appliedForResidentStatus) {
                $appliedForResidentStatus = null;
            }
        }
        $user->application()->create([
            'submitted' => $submitted,
            'applied_for_resident_status' => $appliedForResidentStatus
        ]);
        $workshop = Workshop::get()->random(rand(1, 3));
        $user->application->syncAppliedWorkshops($workshop->pluck('id')->toArray());
    }

    private function createTenant($user)
    {
        $user->roles()->attach(Role::get(Role::TENANT)->id);
        $wifi_username = $user->internetAccess->setWifiCredentials();
        WifiConnection::factory($user->id % 5)->create(['wifi_username' => $wifi_username]);
        MacAddress::factory()->count($user->id % 5)->create(['user_id' => $user->id]);
    }

    private function createStaff()
    {
        $user = User::create([
            'name' => 'Albi',
            'email' => 'pikacsur@eotvos.elte.hu',
            'password' => bcrypt('asdasdasd'),
            'verified' => true,
        ]);
        $user->roles()->attach(Role::get(Role::STAFF)->id);
    }
}
