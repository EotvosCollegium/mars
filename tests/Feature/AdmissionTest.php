<?php

namespace Feature;

use App\Http\Controllers\Auth\AdmissionController;
use App\Http\Controllers\Auth\ApplicationController;
use App\Models\Application;
use App\Models\ApplicationWorkshop;
use App\Models\Faculty;
use App\Models\PeriodicEvent;
use App\Models\Role;
use App\Models\Semester;
use App\Models\SemesterStatus;
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
            'end_date' => now()->subDay(),
            'semester_id' => Semester::current()->id
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
     * Test filtering by workshops.
     *
     * @return void
     */
    public function test_filter_by_workshop()
    {
        $user = User::factory()->create(['verified' => true]);
        $user->addRole(Role::get(Role::SECRETARY));
        $this->actingAs($user);

        $aurelion = Workshop::firstWhere('name', Workshop::AURELION)->id;
        $info = Workshop::firstWhere('name', Workshop::INFORMATIKA)->id;

        $this->createApplicant("applicant1", true, []);
        $this->createApplicant("applicant2", true, [$aurelion, $info]);
        $this->createApplicant("applicant3", true, [$info]);


        $this->actingAs($this->admin);
        $response = $this->get(route('admission.applicants.index') . "?workshop=".$aurelion);
        $response->assertDontSee('applicant1');
        $response->assertSee('applicant2');
        $response->assertDontSee('applicant3');
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
        $response = $this->get(route('admission.applicants.index')); // by default, the status filter is 'submitted'
        $response->assertDontSee('applicant1');
        $response->assertDontSee('applicant2');
        $response->assertSee('applicant3');

        $response = $this->get(route('admission.applicants.index') . "?status_filter=everybody");
        $response->assertSee('applicant1');
        $response->assertSee('applicant2');
        $response->assertSee('applicant3');

        $response = $this->get(route('admission.applicants.index') . "?status_filter=unsubmitted");
        $response->assertSee('applicant1');
        $response->assertSee('applicant2');
        $response->assertDontSee('applicant3');
    }

    /**
     * Asserts that the current user cannot access unsubmitted applications.
     * Used as an auxiliary function.
     *
     * @return void
     */
    private function assertDontSeeUnsubmitted()
    {
        foreach(['everybody', 'unsubmitted'] as $filter) {
            $response = $this->get(route('admission.applicants.index') . "?status_filter=$filter");
            $response->assertStatus(403);
            $response->assertSee('You are not authorized to access unsubmitted applications.');
        }
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

        $response = $this->get(route('admission.applicants.index'));

        $response->assertDontSee('applicant1');
        $response->assertSee('applicant2');
        $response->assertSee('applicant3');
        $response->assertDontSee('applicant4');

        $this->assertDontSeeUnsubmitted();
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

        $response = $this->get(route('admission.applicants.index'));

        $response->assertDontSee('applicant1');
        $response->assertSee('applicant2');
        $response->assertSee('applicant3');
        $response->assertDontSee('applicant4');

        $this->assertDontSeeUnsubmitted();
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

        $response = $this->get(route('admission.applicants.index'));

        $response->assertDontSee('applicant1');
        $response->assertSee('applicant2');
        $response->assertSee('applicant3');
        $response->assertSee('applicant4');

        $this->assertDontSeeUnsubmitted();
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

        $response = $this->get(route('admission.applicants.index'));

        $response->assertDontSee('applicant1');
        $response->assertSee('applicant2');
        $response->assertSee('applicant3');
        $response->assertDontSee('applicant4');

        $this->assertDontSeeUnsubmitted();
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

        $response = $this->get(route('admission.applicants.index'));

        $response->assertDontSee('applicant1');
        $response->assertSee('applicant2');
        $response->assertSee('applicant3');
        $response->assertDontSee('applicant4');

        $this->assertDontSeeUnsubmitted();
    }

    /**
     * Test the admin finalization
     *
     * @return void
     */
    public function test_finalize()
    {
        $user = User::factory()->create(['verified' => true]);
        $user->addRole(Role::firstWhere('name', Role::SYS_ADMIN));
        //to test that these roles gets deleted
        $user->addRole(Role::firstWhere('name', Role::APPLICATION_COMMITTEE_MEMBER));
        $user->addRole(Role::firstWhere('name', Role::AGGREGATED_APPLICATION_COMMITTEE_MEMBER));

        $this->actingAs($user);

        $aurelion = Workshop::firstWhere('name', Workshop::AURELION)->id;
        $info = Workshop::firstWhere('name', Workshop::INFORMATIKA)->id;
        $maths = Workshop::firstWhere('name', Workshop::MATEMATIKA)->id;

        //data should be deleted
        $applicant_in_progress = User::factory()->create(['verified' => false]);
        $applicant_in_progress->application()->create(['submitted' => false]);

        //data should be deleted
        $applicant_not_admitted = User::factory()->create(['verified' => false]);
        $applicant_not_admitted->application()->create(['submitted' => true, 'admitted_for_resident_status' => true]); // even if this is true
        $applicant_not_admitted->application->applicationWorkshops()->create([
            'workshop_id' => $aurelion,
            'called_in' => true,
            'admitted' => false
        ]);


        // should be admitted to aurelion as extern
        $applicant_admitted_extern = User::factory()->create(['verified' => false]);
        $applicant_admitted_extern->application()->create(['submitted' => true, 'admitted_for_resident_status' => false]);
        $applicant_admitted_extern->application->applicationWorkshops()->create([
            'workshop_id' => $aurelion,
            'called_in' => true,
            'admitted' => true
        ]);

        // should be admitted to aurelion and maths as resident
        $applicant_admitted_resident = User::factory()->create(['verified' => false]);
        $applicant_admitted_resident->application()->create(['submitted' => true, 'admitted_for_resident_status' => true]);
        $applicant_admitted_resident->application->applicationWorkshops()->create([
                'workshop_id' => $aurelion,
                'called_in' => true,
                'admitted' => true
        ]);
        $applicant_admitted_resident->application->applicationWorkshops()->create([
                'workshop_id' => $info,
                'called_in' => true,
                'admitted' => false
        ]);
        $applicant_admitted_resident->application->applicationWorkshops()->create([
                'workshop_id' => $maths,
                'called_in' => false, // even if this is false
                'admitted' => true
        ]);

        //user data should not be deleted
        $already_collegist = User::factory()->create(['verified' => true]);
        $already_collegist->application()->create(['submitted' => true]);
        $already_collegist->setExtern();

        //Send request
        $response = $this->post(route('admission.finalize'));
        $response->assertStatus(302);
        $response->assertSessionHas('message', __('general.successful_modification'));

        $applicant_admitted_extern->refresh();
        $applicant_admitted_resident->refresh();
        $already_collegist->refresh();

        $this->assertTrue($applicant_admitted_extern->verified == 1);
        $this->assertTrue($applicant_admitted_extern->hasRole([Role::COLLEGIST => Role::EXTERN]));
        $this->assertTrue($applicant_admitted_extern->workshops->contains($aurelion));
        $this->assertEquals(1, $applicant_admitted_extern->workshops->count());
        $this->assertEquals(SemesterStatus::ACTIVE, $applicant_admitted_extern->getStatus()->status);

        $this->assertTrue($applicant_admitted_resident->verified == 1);
        $this->assertTrue($applicant_admitted_resident->hasRole([Role::COLLEGIST => Role::RESIDENT]));
        $this->assertTrue($applicant_admitted_resident->workshops->contains($aurelion));
        $this->assertTrue($applicant_admitted_resident->workshops->contains($maths));
        $this->assertEquals(2, $applicant_admitted_resident->workshops->count());
        $this->assertEquals(SemesterStatus::ACTIVE, $applicant_admitted_resident->getStatus()->status);

        $this->assertTrue($already_collegist->verified == 1);
        $this->assertTrue($already_collegist->hasRole([Role::COLLEGIST => Role::EXTERN]));

        $this->assertNull(User::withoutGlobalScope('verified')->find($applicant_in_progress->id));
        $this->assertNull(User::withoutGlobalScope('verified')->find($applicant_not_admitted->id));

        $this->assertTrue(Application::count() == 0);
        $this->assertTrue(ApplicationWorkshop::count() == 0);

        $user->refresh();
        $this->assertTrue($user->hasRole(Role::get(Role::SYS_ADMIN)));
        $this->assertFalse($user->hasRole(Role::get(Role::APPLICATION_COMMITTEE_MEMBER)));
        $this->assertFalse($user->hasRole(Role::get(Role::AGGREGATED_APPLICATION_COMMITTEE_MEMBER)));
    }
}
