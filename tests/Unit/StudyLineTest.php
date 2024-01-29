<?php

namespace Tests\Unit;

use App\Models\StudyLine;
use App\Models\Semester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test alfonos requirements.
 *
 * @return void
 */
class StudyLineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_sorting_by_level()
    {
        StudyLine::factory()->create(['type' => 'phd']);
        StudyLine::factory()->create(['type' => 'bachelor']);
        StudyLine::factory()->create(['type' => 'master']);
        StudyLine::factory()->create(['type' => 'ot']);
        StudyLine::factory()->create(['type' => 'other']);
        StudyLine::factory()->create(['type' => 'master']);
        StudyLine::factory()->create(['type' => 'phd']);
        StudyLine::factory()->create(['type' => 'bachelor']);

        $sorted = StudyLine::orderByLevel()->pluck('type')->toArray();
        // check first values
        $this->assertEquals(array_slice($sorted, 0, 2), ["phd", "phd"]);
        // check last values
        $this->assertEquals(array_slice($sorted, 6, 2), ["bachelor", "bachelor"]);
    }

    /**
     * @return void
     */
    public function test_currently_enrolled_scope()
    {
        StudyLine::factory()->create(['end' => Semester::current()->id]);
        StudyLine::factory()->create(['end' => Semester::previous()->id]);
        StudyLine::factory()->create(['end' => Semester::next()->id]);
        StudyLine::factory()->create(['end' => null]);

        $filtered = StudyLine::currentlyEnrolled()->pluck('end')->toArray();

        $this->assertFalse(in_array(Semester::previous()->id, $filtered));
        $this->assertEquals(count($filtered), 3);
    }


}
