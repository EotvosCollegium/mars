<?php

namespace Tests\Unit;

use App\Models\EducationalInformation;
use App\Models\User;
use App\Models\LanguageExam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test alfonos requirements.
 *
 * @return void
 */
class AlfonsoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_entry_without_exams()
    {
        $user = User::factory()->create();
        $educationalInfo = EducationalInformation::factory()->for($user)->create([
            'year_of_acceptance' => 2020,
        ]);
        LanguageExam::factory()->for($educationalInfo)->create(['date' => '2021-01-01', 'level' => 'B2']);

        $expected = ['en' => 'B2', 'de' => 'B2', 'fr' => 'B2', 'it' => 'B2', 'sp' => 'B2', 'la' => 'B2', 'gr' => 'B2'];
        $requirements = $user->educationalInformation->alfonsoRequirements();
        sort($requirements);
        sort($expected);
        $this->assertEquals($expected, $requirements);
        $this->assertTrue($user->educationalInformation->alfonsoCompleted());
    }

        /**
     * @return void
     */
    public function test_entry_with_one_exam()
    {
        $user = User::factory()->create();
        $educationalInfo = EducationalInformation::factory()->for($user)->create([
            'year_of_acceptance' => 2020,
        ]);
        LanguageExam::factory()->for($educationalInfo)->create(['date' => '2020-08-30', 'level' => 'B2', 'language' => 'en']); //before
        LanguageExam::factory()->for($educationalInfo)->create(['date' => '2020-09-02', 'level' => 'B2', 'language' => 'fr']); //after

        $expected = ['de' => 'B2', 'fr' => 'B2', 'it' => 'B2', 'sp' => 'B2', 'la' => 'B2', 'gr' => 'B2'];
        $requirements = $user->educationalInformation->alfonsoRequirements();
        sort($requirements);
        sort($expected);
        $this->assertEquals($expected, $requirements);
        $this->assertTrue($user->educationalInformation->alfonsoCompleted());

        //same with C1
        $user = User::factory()->create();
        $educationalInfo = EducationalInformation::factory()->for($user)->create([
            'year_of_acceptance' => 2020,
        ]);
        LanguageExam::factory()->for($educationalInfo)->create(['date' => '2020-08-30', 'level' => 'C1', 'language' => 'en']); //before
        LanguageExam::factory()->for($educationalInfo)->create(['date' => '2020-09-02', 'level' => 'C1', 'language' => 'fr']); //after

        $expected = ['de' => 'B2', 'fr' => 'B2', 'it' => 'B2', 'sp' => 'B2', 'la' => 'B2', 'gr' => 'B2'];
        $requirements = $user->educationalInformation->alfonsoRequirements();
        sort($requirements);
        sort($expected);
        $this->assertEquals($expected, $requirements);
        $this->assertTrue($user->educationalInformation->alfonsoCompleted());

        //have not completed
        $user = User::factory()->create();
        $educationalInfo = EducationalInformation::factory()->for($user)->create([
            'year_of_acceptance' => 2020,
        ]);
        LanguageExam::factory()->for($educationalInfo)->create(['date' => '2020-08-30', 'level' => 'C1', 'language' => 'en']); //before
        LanguageExam::factory()->for($educationalInfo)->create(['date' => '2020-09-02', 'level' => 'C2', 'language' => 'en']); //after

        $expected = ['de' => 'B2', 'fr' => 'B2', 'it' => 'B2', 'sp' => 'B2', 'la' => 'B2', 'gr' => 'B2'];
        $requirements = $user->educationalInformation->alfonsoRequirements();
        sort($requirements);
        sort($expected);
        $this->assertEquals($expected, $requirements);
        $this->assertFalse($user->educationalInformation->alfonsoCompleted());
    }

        /**
     * @return void
     */
    public function test_entry_with_multiple_exams()
    {
        $user = User::factory()->create();
        $educationalInfo = EducationalInformation::factory()->for($user)->create([
            'year_of_acceptance' => 2020,
        ]);
        LanguageExam::factory()->for($educationalInfo)->create(['date' => '2020-08-30', 'level' => 'B2', 'language' => 'en']); //before
        LanguageExam::factory()->for($educationalInfo)->create(['date' => '2020-08-30', 'level' => 'B2', 'language' => 'fr']); //before

        $expected = ['en' => 'C1', 'de' => 'B2', 'fr' => 'C1', 'it' => 'B2', 'sp' => 'B2', 'la' => 'B2', 'gr' => 'B2'];
        $requirements = $user->educationalInformation->alfonsoRequirements();
        sort($requirements);
        sort($expected);
        $this->assertEquals($expected, $requirements);
        LanguageExam::factory()->for($educationalInfo)->create(['date' => '2020-09-02', 'level' => 'B2', 'language' => 'en']); //after
        $this->assertFalse($user->educationalInformation->alfonsoCompleted());
        LanguageExam::factory()->for($educationalInfo)->create(['date' => '2020-09-02', 'level' => 'C1', 'language' => 'en']); //after
        $this->assertTrue($user->educationalInformation->alfonsoCompleted());
    }
}
