<?php

namespace Tests\Unit;

use App\Models\EducationalInformation;
use App\Models\Role;
use App\Models\User;
use App\Models\LanguageExam;
use App\Models\LanguageExamLevel;
use App\Models\Semester;
use App\Models\StudyLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

/**
 * Test alfonos requirements.
 *
 * @return void
 */
class AlfonsoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A date before a given admission date.
     */
    private static function dateBefore(int $year): Carbon
    {
        return Carbon::createFromDate($year, 8, 31);
    }

    /**
     * A date on or after a given admission date.
     */
    private static function dateAfter(int $year): Carbon
    {
        // for some reason, it does not work with 1st September
        return Carbon::createFromDate($year, 9, 2);
    }

    /**
     * @return void
     */
    public function test_entry_without_exams()
    {
        $user = User::factory()->create();
        $educationalInfo = EducationalInformation::factory()->for($user)->create([
            'year_of_acceptance' => Semester::current()->year,
        ]);
        StudyLine::factory()->for($educationalInfo)->create(['type' => 'bachelor', 'start' => Semester::current()->id, 'end' => null]);
        LanguageExam::factory()->for($educationalInfo)->create([
            'date' => self::dateAfter($educationalInfo->year_of_acceptance),
            'language' => 'en',
            'level' => 'B2'
        ]);

        $expected = [
            'en' => LanguageExamLevel::B2,
            'de' => LanguageExamLevel::B2,
            'fr' => LanguageExamLevel::B2,
            'it' => LanguageExamLevel::B2,
            'sp' => LanguageExamLevel::B2,
            'la' => LanguageExamLevel::B2,
            'gr' => LanguageExamLevel::B2
        ];
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
            'year_of_acceptance' => Semester::current()->year,
        ]);
        StudyLine::factory()->for($educationalInfo)->create(['type' => 'bachelor', 'start' => Semester::current()->id, 'end' => null]);
        LanguageExam::factory()->for($educationalInfo)->create(
            [
            'date' => self::dateBefore($educationalInfo->year_of_acceptance), //before
            'level' => 'B2',
            'language' => 'en']
        );
        LanguageExam::factory()->for($educationalInfo)->create(
            [
            'date' => self::dateAfter($educationalInfo->year_of_acceptance), //after
            'level' => 'B2',
            'language' => 'fr']
        );

        $expected = [
            'de' => LanguageExamLevel::B2,
            'fr' => LanguageExamLevel::B2,
            'it' => LanguageExamLevel::B2,
            'sp' => LanguageExamLevel::B2,
            'la' => LanguageExamLevel::B2,
            'gr' => LanguageExamLevel::B2
        ];
        $requirements = $user->educationalInformation->alfonsoRequirements();
        sort($requirements);
        sort($expected);
        $this->assertEquals($expected, $requirements);
        $this->assertTrue($user->educationalInformation->alfonsoCompleted());

        //same with C1
        $user = User::factory()->create();
        $educationalInfo = EducationalInformation::factory()->for($user)->create([
            'year_of_acceptance' => Semester::current()->year,
        ]);
        LanguageExam::factory()->for($educationalInfo)->create([
            'date' => self::dateBefore($educationalInfo->year_of_acceptance), //before
            'level' => 'C1',
            'language' => 'en'
        ]);
        LanguageExam::factory()->for($educationalInfo)->create([
            'date' => self::dateAfter($educationalInfo->year_of_acceptance), //after
            'level' => 'C1',
            'language' => 'fr'
        ]);

        $expected = [
            'de' => LanguageExamLevel::B2,
            'fr' => LanguageExamLevel::B2,
            'it' => LanguageExamLevel::B2,
            'sp' => LanguageExamLevel::B2,
            'la' => LanguageExamLevel::B2,
            'gr' => LanguageExamLevel::B2
        ];
        $requirements = $user->educationalInformation->alfonsoRequirements();
        sort($requirements);
        sort($expected);
        $this->assertEquals($expected, $requirements);
        $this->assertTrue($user->educationalInformation->alfonsoCompleted());

        //have not completed
        $user = User::factory()->create();
        $educationalInfo = EducationalInformation::factory()->for($user)->create([
            'year_of_acceptance' => Semester::current()->year,
        ]);
        StudyLine::factory()->for($educationalInfo)->create(['type' => 'bachelor', 'start' => Semester::current()->id, 'end' => null]);
        LanguageExam::factory()->for($educationalInfo)->create(['date' => self::dateBefore($educationalInfo->year_of_acceptance), 'level' => 'C1', 'language' => 'en']); //before
        LanguageExam::factory()->for($educationalInfo)->create(['date' => self::dateAfter($educationalInfo->year_of_acceptance), 'level' => 'C2', 'language' => 'en']); //after

        $expected = [
            'de' => LanguageExamLevel::B2,
            'fr' => LanguageExamLevel::B2,
            'it' => LanguageExamLevel::B2,
            'sp' => LanguageExamLevel::B2,
            'la' => LanguageExamLevel::B2,
            'gr' => LanguageExamLevel::B2
        ];
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
            'year_of_acceptance' => Semester::current()->year,
        ]);
        StudyLine::factory()->for($educationalInfo)->create(['type' => 'bachelor', 'start' => Semester::current()->id, 'end' => null]);
        LanguageExam::factory()->for($educationalInfo)->create(['date' => self::dateBefore($educationalInfo->year_of_acceptance), 'level' => 'B2', 'language' => 'en']); //before
        LanguageExam::factory()->for($educationalInfo)->create(['date' => self::dateBefore($educationalInfo->year_of_acceptance), 'level' => 'B2', 'language' => 'fr']); //before

        $expected = [
            'en' => LanguageExamLevel::C1,
            'de' => LanguageExamLevel::B2,
            'fr' => LanguageExamLevel::C1,
            'it' => LanguageExamLevel::B2,
            'sp' => LanguageExamLevel::B2,
            'la' => LanguageExamLevel::B2,
            'gr' => LanguageExamLevel::B2
        ];
        $requirements = $user->educationalInformation->alfonsoRequirements();
        sort($requirements);
        sort($expected);
        $this->assertEquals($expected, $requirements);
        LanguageExam::factory()->for($educationalInfo)->create(['date' => self::dateAfter($educationalInfo->year_of_acceptance), 'level' => 'B2', 'language' => 'en']); //after
        $this->assertFalse($user->educationalInformation->alfonsoCompleted());
        LanguageExam::factory()->for($educationalInfo)->create(['date' => self::dateAfter($educationalInfo->year_of_acceptance), 'level' => 'C1', 'language' => 'en']); //after
        $this->assertTrue($user->educationalInformation->alfonsoCompleted());
    }

    /**
     * Test whether seniors and those admitted as masters' students
     * get exempted correctly.
     */
    public function test_exemptions()
    {
        $user = User::factory()->create();
        $educationalInfo = EducationalInformation::factory()->for($user)->create([
            'year_of_acceptance' => Semester::current()->year,
        ]);
        $user->addRole(Role::get(Role::SENIOR));
        StudyLine::factory()->for($educationalInfo)->create(['type' => 'phd', 'start' => Semester::current()->id, 'end' => null]);
        $this->assertTrue($user->educationalInformation->isSenior());
        $this->assertTrue($user->educationalInformation->alfonsoExempted());

        $user = User::factory()->create();
        $educationalInfo = EducationalInformation::factory()->for($user)->create([
            'year_of_acceptance' => Semester::current()->year,
        ]);
        StudyLine::factory()->for($educationalInfo)->create(['type' => 'master', 'start' => Semester::current()->id, 'end' => null]);
        $this->assertFalse($user->educationalInformation->isSenior());
        $this->assertTrue($user->educationalInformation->alfonsoExempted());

        $user = User::factory()->create();
        $educationalInfo = EducationalInformation::factory()->for($user)->create([
            'year_of_acceptance' => Semester::current()->year,
        ]);
        StudyLine::factory()->for($educationalInfo)->create(['type' => 'bachelor', 'start' => Semester::previous()->pred()->id, 'end' => null]);
        $this->assertFalse($user->educationalInformation->isSenior());
        $this->assertFalse($user->educationalInformation->alfonsoExempted());
        $educationalInfo->studyLines()->first()->update([
            'end' => Semester::previous()->pred()->id,
        ]);
        StudyLine::factory()->for($educationalInfo)->create(['type' => 'master', 'start' => Semester::previous()->id, 'end' => null]);
        $this->assertFalse($user->educationalInformation->isSenior());
        $this->assertTrue($user->educationalInformation->alfonsoExempted());
    }
}
