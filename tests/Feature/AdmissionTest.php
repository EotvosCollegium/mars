<?php

namespace Feature;

use App\Http\Controllers\Auth\AdmissionController;
use App\Http\Controllers\Auth\ApplicationController;
use App\Models\Application;
use App\Models\Faculty;
use App\Models\PeriodicEvent;
use App\Models\Role;
use App\Models\Semester;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test registration functions.
 *
 * @return void
 */
class AdmissionTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Set up the test.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        //open application period
        PeriodicEvent::create([
            'event_model' => ApplicationController::class,
            'start_date' => now()->subWeeks(2),
            'end_date' => now()->addWeeks(2),
        ]);

    }

    /**
     * Create an application with given parameters.
     *
     * @return void
     */
    private function createApplicant(string $name, bool $submitted, $workshops): User
    {
        /* @var User $user */
        $user = User::factory()->create(['name' => $name, 'verified' => false]);
        $user->application()->create(['submitted' => $submitted]);
        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'submitted' => $submitted
        ]);
        $user->application->syncAppliedWorkshops($workshops);

        return $user;

    }

    /**
     * Test viewing all applications as secretary.
     *
     * @return void
     */
    public function test_view_applications_as_secretary()
    {
        $user = User::factory()->create(['verified' => true]);
        $user->addRole(Role::get(Role::SECRETARY));
        $this->actingAs($user);

        $aurelion = Workshop::firstWhere('name', Workshop::AURELION)->id;
        $info = Workshop::firstWhere('name', Workshop::INFORMATIKA)->id;

        $this->createApplicant("applicant1", false, []);
        $this->createApplicant("applicant2", false, [$aurelion, $info]);
        $this->createApplicant("applicant3", true, [$aurelion, $info]);


        $this->actingAs($this->admin);
        $response = $this->get(route('admission.applicants.index') . "?show_not_submitted=true");
        $response->assertSee('applicant1');
        $response->assertSee('applicant2');
        $response = $this->get(route('admission.applicants.index'));
        $response->assertSee('applicant3');
    }

    /**
     * Test viewing all finalised applications in a workshop as workshop leader.
     *
     * @return void
     */
    public function test_view_applications_as_workshop_leader()
    {
        $user = User::factory()->create(['verified' => true]);
        $this->actingAs($user);

        $aurelion = Workshop::firstWhere('name', Workshop::AURELION);
        $info = Workshop::firstWhere('name', Workshop::INFORMATIKA);
        $angol = Workshop::firstWhere('name', Workshop::ANGOL);

        $user->addRole(Role::get(Role::WORKSHOP_LEADER), $aurelion);
        $user->addRole(Role::get(Role::WORKSHOP_LEADER), $info);

        $this->createApplicant("applicant1", false, [$info->id]);
        $this->createApplicant("applicant2", true, [$aurelion->id, $info->id]);
        $this->createApplicant("applicant3", true, [$info->id, $angol->id]);
        $this->createApplicant("applicant4", true, [$angol->id]);

        $response = $this->get(route('admission.applicants.index') . "?show_not_submitted=true");

        $response->assertDontSee('applicant1');
        $response->assertSee('applicant2');
        $response->assertSee('applicant3');
        $response->assertDontSee('applicant4');
    }

    /**
     * Test viewing all finalised applications in a workshop as workshop admin.
     *
     * @return void
     */
    public function test_view_applications_as_workshop_admin()
    {
        $user = User::factory()->create(['verified' => true]);
        $this->actingAs($user);

        $aurelion = Workshop::firstWhere('name', Workshop::AURELION);
        $info = Workshop::firstWhere('name', Workshop::INFORMATIKA);
        $angol = Workshop::firstWhere('name', Workshop::ANGOL);

        $user->addRole(Role::get(Role::WORKSHOP_ADMINISTRATOR), $aurelion);
        $user->addRole(Role::get(Role::WORKSHOP_ADMINISTRATOR), $info);

        $this->createApplicant("applicant1", false, [$info->id]);
        $this->createApplicant("applicant2", true, [$aurelion->id, $info->id]);
        $this->createApplicant("applicant3", true, [$info->id, $angol->id]);
        $this->createApplicant("applicant4", true, [$angol->id]);

        $response = $this->get(route('admission.applicants.index') . "?show_not_submitted=true");

        $response->assertDontSee('applicant1');
        $response->assertSee('applicant2');
        $response->assertSee('applicant3');
        $response->assertDontSee('applicant4');
    }

    /**
     * Test viewing all finalised applications as aggregated committe member.
     *
     * @return void
     */
    public function test_view_applications_as_aggregated_committee_member()
    {
        $user = User::factory()->create(['verified' => true]);
        $this->actingAs($user);

        $aurelion = Workshop::firstWhere('name', Workshop::AURELION);
        $info = Workshop::firstWhere('name', Workshop::INFORMATIKA);
        $angol = Workshop::firstWhere('name', Workshop::ANGOL);

        $user->addRole(Role::get(Role::AGGREGATED_APPLICATION_COMMITTEE_MEMBER));

        $this->createApplicant("applicant1", false, [$info->id]);
        $this->createApplicant("applicant2", true, [$aurelion->id, $info->id]);
        $this->createApplicant("applicant3", true, [$info->id, $angol->id]);
        $this->createApplicant("applicant4", true, [$angol->id]);

        $response = $this->get(route('admission.applicants.index') . "?show_not_submitted=true");

        $response->assertDontSee('applicant1');
        $response->assertSee('applicant2');
        $response->assertSee('applicant3');
        $response->assertSee('applicant4');
    }

    /**
     * Test viewing all finalised applications in a workshop as workshop committe member.
     *
     * @return void
     */
    public function test_view_applications_as_workshop_committee_member()
    {
        $user = User::factory()->create(['verified' => true]);
        $this->actingAs($user);

        $aurelion = Workshop::firstWhere('name', Workshop::AURELION);
        $info = Workshop::firstWhere('name', Workshop::INFORMATIKA);
        $angol = Workshop::firstWhere('name', Workshop::ANGOL);

        $user->addRole(Role::get(Role::APPLICATION_COMMITTEE_MEMBER), $aurelion);
        $user->addRole(Role::get(Role::APPLICATION_COMMITTEE_MEMBER), $info);

        $this->createApplicant("applicant1", false, [$info->id]);
        $this->createApplicant("applicant2", true, [$aurelion->id, $info->id]);
        $this->createApplicant("applicant3", true, [$info->id, $angol->id]);
        $this->createApplicant("applicant4", true, [$angol->id]);

        $response = $this->get(route('admission.applicants.index') . "?show_not_submitted=true");

        $response->assertDontSee('applicant1');
        $response->assertSee('applicant2');
        $response->assertSee('applicant3');
        $response->assertDontSee('applicant4');
    }


    /**
     * Test viewing all finalised applications with multiple workshop roles.
     *
     * @return void
     */
    public function test_view_applications_with_mixed_roles()
    {
        $user = User::factory()->create(['verified' => true]);
        $this->actingAs($user);

        $aurelion = Workshop::firstWhere('name', Workshop::AURELION);
        $info = Workshop::firstWhere('name', Workshop::INFORMATIKA);
        $angol = Workshop::firstWhere('name', Workshop::ANGOL);

        $user->addRole(Role::get(Role::APPLICATION_COMMITTEE_MEMBER), $aurelion);
        $user->addRole(Role::get(Role::WORKSHOP_ADMINISTRATOR), $info);

        $this->createApplicant("applicant1", false, [$info->id]);
        $this->createApplicant("applicant2", true, [$aurelion->id, $info->id]);
        $this->createApplicant("applicant3", true, [$info->id, $angol->id]);
        $this->createApplicant("applicant4", true, [$angol->id]);

        $response = $this->get(route('admission.applicants.index') . "?show_not_submitted=true");

        $response->assertDontSee('applicant1');
        $response->assertSee('applicant2');
        $response->assertSee('applicant3');
        $response->assertDontSee('applicant4');
    }



    //    /**
    //     * Test the admin finalization
    //     *
    //     * @return void
    //     */
    //    public function test_cannot_finalize()
    //    {
    //        $user = User::factory()->create();
    //        $user->addRole(Role::firstWhere('name', Role::SYS_ADMIN));
    //        $this->actingAs($user);
    //
    //        $applicant_in_progress = User::factory()->create(['verified' => false]);
    //        $applicant_in_progress->application->update(['submitted' => false]);
    //
    //        $applicant_submitted = User::factory()->create(['verified' => false]);
    //        $applicant_submitted->application->update(['submitted' => true]);
    ////
    ////        $applicant_called_in = User::factory()->create(['verified' => false]);
    ////        $applicant_called_in->application->update(['status' => Application::STATUS_CALLED_IN]);
    ////
    ////        $applicant_accepted = User::factory()->create(['verified' => false]);
    ////        $applicant_accepted->application->update(['status' => Application::STATUS_ACCEPTED]);
    ////
    ////        $applicant_banished = User::factory()->create(['verified' => false]);
    ////        $applicant_banished->application->update(['status' => Application::STATUS_BANISHED]);
    //
    //        $response = $this->post('/application/finalize');
    //        $response->assertStatus(302);
    //        $response->assertSessionHas('error', 'Még vannak feldolgozatlan jelentkezések!');
    //    }

    //    /**
    //     * Test the admin finalization
    //     *
    //     * @return void
    //     */
    //    public function test_finalize()
    //    {
    //        $user = User::factory()->create(['verified' => true]);
    //        $user->addRole(Role::firstWhere('name', Role::SYS_ADMIN));
    //        $user->addRole(Role::firstWhere('name', Role::APPLICATION_COMMITTEE_MEMBER));
    //        $user->addRole(Role::firstWhere('name', Role::AGGREGATED_APPLICATION_COMMITTEE_MEMBER));
    //        Config::set('custom.application_deadline', now()->subWeeks(3));
    //        $this->actingAs($user);
    //
    //        Application::query()->delete();
    //        $applicant_in_progress = User::factory()->create(['verified' => false]);
    //        $applicant_in_progress->application->update(['status' => Application::STATUS_IN_PROGRESS]);
    //
    //        $applicant_accepted = User::factory()->create(['verified' => false]);
    //        $applicant_accepted->application->update(['status' => Application::STATUS_ACCEPTED]);
    //
    //        $applicant_banished = User::factory()->create(['verified' => false]);
    //        $applicant_banished->application->update(['status' => Application::STATUS_BANISHED]);
    //
    //
    //        $response = $this->post('/application/finalize');
    //        $response->assertStatus(302);
    //        $response->assertSessionHas('message', 'Sikeresen jóváhagyta az elfogadott jelentkezőket');
    //
    //        $applicant_accepted->refresh();
    //        $this->assertTrue($applicant_accepted->verified == 1);
    //        $this->assertNull(User::find($applicant_banished->id));
    //        $this->assertNull(User::find($applicant_in_progress->id));
    //
    //        $this->assertTrue(Application::count() == 0);
    //
    //        $user->refresh();
    //        $this->assertTrue($user->hasRole(Role::firstWhere('name', Role::SYS_ADMIN)));
    //        $this->assertFalse($user->hasRole(Role::firstWhere('name', Role::APPLICATION_COMMITTEE_MEMBER)));
    //        $this->assertFalse($user->hasRole(Role::firstWhere('name', Role::AGGREGATED_APPLICATION_COMMITTEE_MEMBER)));
    //    }

}
