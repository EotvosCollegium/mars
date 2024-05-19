<?php

namespace Tests\Feature;

use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use App\Models\EducationalInformation;
use App\Models\PersonalInformation;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->assertTrue(app(SemesterEvaluationController::class)->isActive());

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
        $this->assertFalse(app(SemesterEvaluationController::class)->isEvaluationAvailable());

        $response = $this->get('/secretariat/evaluation');
        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Lejárt a határidő a kérdőív kitöltésére. Keresd fel a titkárságot.');

        $response = $this->get('/home');
        $response->assertStatus(200);
        $response->assertSessionMissing('message');
    }

}
