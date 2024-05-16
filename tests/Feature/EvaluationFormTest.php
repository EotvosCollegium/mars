<?php

namespace Tests\Feature;

use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use App\Models\EducationalInformation;
use App\Models\EventTrigger;
use App\Models\PersonalInformation;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Test registration functions.
 *
 * @return void
 */
class EvaluationFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper function to create a user that has not filled in their status for the next semester.
     */
    private function createUser(): User
    {
        $user = User::factory()->create(['verified' => true]);
        $user->personalInformation()->save(PersonalInformation::factory()->make());
        $user->educationalInformation()->save(EducationalInformation::factory()->make());
        $user->setResident();
        $this->assertNull($user->getStatus(Semester::next()));
        $this->actingAs($user);
        return $user;
    }

    /**
     * Helper function to assert that the form is available.
     */
    private function assertFormAvailable()
    {
        $this->assertTrue(SemesterEvaluationController::isEvaluationAvailable());

        $response = $this->get('/secretariat/evaluation');
        $response->assertStatus(200);

        $response = $this->get('/home');
        $response->assertStatus(200);
        $response->assertSessionHas('message', 'Töltsd ki a szemeszter végi kérdőívet a profilod alatt!');
    }

    /**
     * Helper function to assert that the form is not available.
     */
    private function assertFormDoesNotAvailable()
    {
        $this->assertFalse(SemesterEvaluationController::isEvaluationAvailable());

        $response = $this->get('/secretariat/evaluation');
        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Lejárt a határidő a kérdőív kitöltésére. Keresd fel a titkárságot.');

        $response = $this->get('/home');
        $response->assertStatus(200);
        $response->assertSessionMissing('message');
    }

    /**
     * available < now() && now() < deadline
     */
    public function testFormAccessible()
    {
//        $this->createUser();
//
//        EventTrigger::find(EventTrigger::DEACTIVATE_STATUS_SIGNAL)->update(['date' => now()->addDays(1)]);
//        EventTrigger::find(EventTrigger::SEMESTER_EVALUATION_AVAILABLE)->update(['date' => Semester::next()->getEndDate()->subMonth()]);
//        Config::set('custom.semester_evaluation_deadline', null);
//
//        $this->assertFormAvailable();
    }

    /**
     * if the deadline has not been updated, use the system_deadline
     */
    public function testFormAvailableWithOldDeadline()
    {
//        $this->createUser();
//
//        EventTrigger::find(EventTrigger::DEACTIVATE_STATUS_SIGNAL)->update(['date' => now()->addDays(2)]);
//        EventTrigger::find(EventTrigger::SEMESTER_EVALUATION_AVAILABLE)->update(['date' => Semester::next()->getEndDate()->subMonth()]);
//        Config::set('custom.semester_evaluation_deadline', Semester::previous()->getEndDate()->subDays(1));
//
//        $this->assertFormAvailable();
    }


    /**
     * available > now()
     */
    public function testFormDoesNotAvailableYet()
    {
//        $this->createUser();
//
//        EventTrigger::find(EventTrigger::DEACTIVATE_STATUS_SIGNAL)->update(['date' => now()->addDays(3)]);
//        EventTrigger::find(EventTrigger::SEMESTER_EVALUATION_AVAILABLE)->update(['date' => now()->addDays(1)]);
//        Config::set('custom.semester_evaluation_deadline', now()->addDays(2));
//
//        $this->assertFormDoesNotAvailable();
    }



    /**
     * deadline < now()
     */
    public function testDeadlinePassed()
    {
//        $this->createUser();
//
//        EventTrigger::find(EventTrigger::DEACTIVATE_STATUS_SIGNAL)->update(['date' => now()->addDays(3)]);
//        EventTrigger::find(EventTrigger::SEMESTER_EVALUATION_AVAILABLE)->update(['date' => now()->subDays(2)]);
//        Config::set('custom.semester_evaluation_deadline', now()->subDays(1));
//
//        $this->assertFormDoesNotAvailable();

    }
    /**
     * custom deadline < now()
     */
    public function testCustomDeadlinePassed()
    {
//        $this->createUser();
//
//        EventTrigger::find(EventTrigger::DEACTIVATE_STATUS_SIGNAL)->update(['date' => now()->addDays(3)]);
//        EventTrigger::find(EventTrigger::SEMESTER_EVALUATION_AVAILABLE)->update(['date' => now()->addDays(5)]);
//        Config::set('custom.semester_evaluation_deadline', now()->subDays(1));
//
//        $this->assertFormDoesNotAvailable();

    }

}
