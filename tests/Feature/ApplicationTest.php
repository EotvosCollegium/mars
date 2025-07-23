<?php

namespace Tests\Feature;

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
use Tests\Unit\AlfonsoTest;

/**
 * Test registration functions.
 *
 * @return void
 */
class ApplicationTest extends TestCase
{
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
     * Create a new applicant.
     */
    private function createApplicant(): User
    {
        $user = User::factory()->create(['verified' => false]);
        $user->application()->create(['submitted' => false]);
        $this->actingAs($user);

        return $user;
    }

    /**
     * Test redirecting to application form.
     *
     * @return void
     */
    public function test_redirect_to_application_form()
    {
        $this->createApplicant();

        $response = $this->get('/');
        $response->assertStatus(302);
        $response->assertRedirect('/home');

        $response = $this->get('/home');
        $response->assertStatus(302);
        $response->assertRedirect('/application');

        $response = $this->get('/print');
        $response->assertStatus(302);
        $response->assertRedirect('/application');

        $response = $this->get('/application');
        $response->assertStatus(200);
    }

    /**
     * Test filling out the questions page.
     *
     * @return void
     * @throws \JsonException
     */
    public function test_store_questions()
    {
        $user = $this->createApplicant();

        $this->get('/application'); // for `redirect->back()`...
        $response = $this->post('/application', [
            'page' => 'questions',
            'status' => 'resident',
            'graduation_average' => '4'
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/application');

        $response->assertSessionHas('message', __('general.successful_modification'));


        $user = User::findOrFail($user->id);

        $this->assertFalse($user->isResident());

        $response = $this->post('/application', [
            'page' => 'questions',
            "graduation_average" => "3",
            "semester_average" => [
                "3.3", "3.5", "3231"
            ],
            "status" => "resident",
            "question_1" => [
                "question 1_1",
                "question 1_2"
            ],
            "question_2" => "question 2",
            "question_3" => "question 3",
            "question_4" => "question 4",
            "present" => "on",
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/application');
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('message', __('general.successful_modification'));

        $this->assertTrue($user->application->applied_for_resident_status);
        $this->assertEquals('3', $user->application->graduation_average);
        $this->assertEquals(["3.3", "3.5", "3231"], $user->application->semester_average);
        $this->assertEquals(["question 1_1", "question 1_2"], $user->application->question_1);
        $this->assertEquals("question 2", $user->application->question_2);
        $this->assertEquals("question 3", $user->application->question_3);
        $this->assertEquals("question 4", $user->application->question_4);
        $this->assertTrue(true, $user->application->present);
    }


    /**
     * Test uploading a file.
     *
     * @return void
     */
    public function test_store_file()
    {
        Storage::fake('uploads');

        $user = $this->createApplicant();

        $response = $this->get('/application');
        $response = $this->post('/application', [
            'page' => 'files',
            'name' => 'file name',
            'file' => UploadedFile::fake()->create('file.pdf', 100)
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/application');
        $response->assertSessionHas('message', __('general.successful_modification'));

        $user = User::find($user->id);

        $files = $user->application->files;
        $this->assertEquals(1, $files->count());
        $this->assertEquals('file name', $files[0]->name);

        $response = $this->post('/application', [
            'page' => 'files.delete',
            'id' => $files[0]->id
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/application');
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('message', __('general.successful_modification'));

        $user = User::find($user->id);
        $files = $user->application->files;
        $this->assertEquals(0, $files->count());
    }

    /**
     * Test the full process with minimal required information.
     *
     * @return void
     */
    public function test_submit()
    {
        Storage::fake('uploads');
        Storage::fake('avatars');

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'example@test.com',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
            'user_type' => 'collegist'
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $user = User::firstWhere('email', 'example@test.com');
        $this->assertNotNull($user);
        $this->assertFalse($user->verified == 1);

        $this->assertFalse($user->isResident());
        $this->assertFalse($user->isExtern());

        //personal data
        $this->assertContains("Személyes adat: ".strtolower(__('user.place_of_birth')), $user->application->missingData());
        $this->assertContains("Személyes adat: ".strtolower(__('user.date_of_birth')), $user->application->missingData());
        $this->assertContains("Személyes adat: ".strtolower(__('user.mothers_name')), $user->application->missingData());
        $this->assertContains("Személyes adat: ".strtolower(__('user.phone_number')), $user->application->missingData());
        $this->assertContains("Személyes adat: ".strtolower(__('user.country')), $user->application->missingData());
        $this->assertContains("Személyes adat: ".strtolower(__('user.county')), $user->application->missingData());
        $this->assertContains("Személyes adat: ".strtolower(__('user.zip_code')), $user->application->missingData());
        $this->assertContains("Személyes adat: ".strtolower(__('user.city')), $user->application->missingData());
        $this->assertContains("Személyes adat: ".strtolower(__('user.street_and_number')), $user->application->missingData());
        $response = $this->post('/users/' . $user->id . '/personal_information', [
            'email' => 'example@test.com',
            'name' => 'John Doe',
            'phone_number' => '123456789',
            'place_of_birth' => 'Budapest',
            'date_of_birth' => '2000-01-01',
            'mothers_name' => 'Mothers name',
            'country' => 'Hungary',
            'county' => 'Pest',
            'zip_code' => '1111',
            'city' => 'Budapest',
            'street_and_number' => 'Test street 1.',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $user->load('application');
        $this->assertNotContains("Személyes adat: ".strtolower(__('user.place_of_birth')), $user->application->missingData());
        $this->assertNotContains("Személyes adat: ".strtolower(__('user.date_of_birth')), $user->application->missingData());
        $this->assertNotContains("Személyes adat: ".strtolower(__('user.mothers_name')), $user->application->missingData());
        $this->assertNotContains("Személyes adat: ".strtolower(__('user.phone_number')), $user->application->missingData());
        $this->assertNotContains("Személyes adat: ".strtolower(__('user.country')), $user->application->missingData());
        $this->assertNotContains("Személyes adat: ".strtolower(__('user.county')), $user->application->missingData());
        $this->assertNotContains("Személyes adat: ".strtolower(__('user.zip_code')), $user->application->missingData());
        $this->assertNotContains("Személyes adat: ".strtolower(__('user.city')), $user->application->missingData());
        $this->assertNotContains("Személyes adat: ".strtolower(__('user.street_and_number')), $user->application->missingData());

        //educational data
        $this->assertContains("Tanulmányi adat: ".strtolower(__('user.high_school')), $user->application->missingData());
        $this->assertContains("Tanulmányi adat: ".strtolower(__('user.year_of_graduation')), $user->application->missingData());
        $this->assertContains("Tanulmányi adat: ".strtolower(__('user.year_of_acceptance')), $user->application->missingData());
        $this->assertContains("Tanulmányi adat: ".strtolower(__('user.neptun')), $user->application->missingData());
        $this->assertContains("Tanulmányi adat: megjelölt szak", $user->application->missingData());
        $response = $this->post('/users/' . $user->id . '/educational_information', [
            'year_of_graduation' => '2018',
            'year_of_acceptance' => date('Y'),
            'high_school' => 'Test high school',
            'neptun' => 'NEPTUN',
            'study_lines' => [[
                "name" => "Test study line",
                "type" => "bachelor",
                "start" => AlfonsoTest::autumnSemester()->id,
                "training_code" => "123456"]],
            'email' => 'study@email.com',
            'faculty' => [Faculty::first()->id],
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $user->load(['application', 'workshops', 'faculties']);
        $this->assertNotContains("Tanulmányi adat: ".strtolower(__('user.high_school')), $user->application->missingData());
        $this->assertNotContains("Tanulmányi adat: ".strtolower(__('user.year_of_graduation')), $user->application->missingData());
        $this->assertNotContains("Tanulmányi adat: ".strtolower(__('user.year_of_acceptance')), $user->application->missingData());
        $this->assertNotContains("Tanulmányi adat: ".strtolower(__('user.neptun')), $user->application->missingData());
        $this->assertNotContains("Tanulmányi adat: megjelölt szak", $user->application->missingData());
        $this->assertNotContains("Tanulmányi adat: valamely tanult szak adata: ".strtolower(__('user.study_line')), $user->application->missingData());
        $this->assertNotContains("Tanulmányi adat: valamely tanult szak adata: ".strtolower(__('user.study_line_level')), $user->application->missingData());
        $this->assertNotContains("Tanulmányi adat: valamely tanult szak adata: ".strtolower(__('user.study_line_training_code')), $user->application->missingData());
        $this->assertNotContains("Tanulmányi adat: valamely tanult szak adata: ".strtolower(__('user.study_line_start')), $user->application->missingData());
        //alfonso
        $this->assertContains('Tanulmányi adat: megjelölt ALFONSÓ nyelv', $user->application->missingData());
        $this->assertContains('Tanulmányi adat: elérni kívánt ALFONSÓ szint', $user->application->missingData());
        $response = $this->post('/users/' . $user->id . '/alfonso', [
            'alfonso_language' => 'en',
            'alfonso_desired_level' => 'C1',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $user->load('application');
        $this->assertNotContains('Tanulmányi adat: megjelölt ALFONSÓ nyelv', $user->application->missingData());
        $this->assertNotContains('Tanulmányi adat: elérni kívánt ALFONSÓ szint', $user->application->missingData());

        //profile picture
        //this is not a required field due to privacy reasons
        $response = $this->post('/users/' . $user->id . '/profile_picture', [
            'picture' => UploadedFile::fake()->image('image.png', 100)
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $user->load('application');

        //files
        $this->assertContains('Legalább két feltöltött fájl', $user->application->missingData());
        $response = $this->post('/application', [
            'page' => 'files',
            'name' => 'file name',
            'file' => UploadedFile::fake()->create('file.pdf', 100)
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $user->load('application');
        $this->assertContains('Legalább két feltöltött fájl', $user->application->missingData());
        $response = $this->post('/application', [
            'page' => 'files',
            'name' => 'file name 2',
            'file' => UploadedFile::fake()->create('file2.pdf', 100)
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $user->load('application');
        $this->assertNotContains('Legalább két feltöltött fájl', $user->application->missingData());

        //questions
        $this->assertContains('Szakmai és motivációs kérdések: érettségi átlaga', $user->application->missingData());
        $response = $this->post('/application', [
            'page' => 'questions',
            'status' => 'extern',
            'graduation_average' => '4'
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $user->load('application');
        $this->assertNotContains('Szakmai és motivációs kérdések: érettségi átlaga', $user->application->missingData());

        $this->assertContains('Szakmai és motivációs kérdések: megpályázni kívánt műhely', $user->application->missingData());
        $this->assertContains('Szakmai és motivációs kérdések: "Honnan hallott a Collegiumról?" kérdés', $user->application->missingData());
        $this->assertContains('Szakmai és motivációs kérdések: "Miért kíván a Collegium tagja lenni?" kérdés', $user->application->missingData());
        $this->assertContains('Szakmai és motivációs kérdések: "Tervez-e tovább tanulni a diplomája megszerzése után? Milyen tervei vannak az egyetem után?" kérdés', $user->application->missingData());
        $response = $this->post('/application', [
            'page' => 'questions',
            'status' => 'extern',
            'graduation_average' => '4',
            'workshop' => [
                Workshop::first()->id
            ],
            'question_1' => ['answer 1'],
            'question_2' => 'answer 2',
            'question_3' => 'answer 3',
            'publication_consent' => '1',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $user->load('application');
        $this->assertNotContains('Szakmai és motivációs kérdések: megpályázni kívánt műhely', $user->application->missingData());
        $this->assertNotContains('Szakmai és motivációs kérdések: "Honnan hallott a Collegiumról?" kérdés', $user->application->missingData());
        $this->assertNotContains('Szakmai és motivációs kérdések: "Miért kíván a Collegium tagja lenni?" kérdés', $user->application->missingData());
        $this->assertNotContains('Szakmai és motivációs kérdések: "Tervez-e tovább tanulni a diplomája megszerzése után? Milyen tervei vannak az egyetem után?" kérdés', $user->application->missingData());

        $user->load(['workshops', 'faculties', 'educationalInformation.studyLines']);

        $this->assertEquals([], $user->application->missingData());

        $response = $this->post('/application', [
            'page' => 'submit'
        ]);
        $response->assertStatus(302);
        $user->load('application');
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('message', 'Sikeresen véglegesítette a jelentkezését!');
        $this->assertTrue($user->application->submitted);
        $this->assertNotNull($user->internetAccess);
        $this->assertEquals($user->internetAccess->wifi_username, $user->educationalInformation->neptun);
        $this->assertTrue($user->internetAccess->has_internet_until > now());
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
