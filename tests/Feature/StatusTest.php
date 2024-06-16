<?php

namespace Tests\Feature;

use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use App\Models\PeriodicEvent;
use App\Models\Role;
use App\Models\Semester;
use App\Models\SemesterStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StatusTest extends TestCase
{
    use RefreshDatabase;
    protected PeriodicEvent $periodicEvent;

    /**
     * Set up the tests
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->periodicEvent = PeriodicEvent::create([
            'event_name' => PeriodicEvent::SEMESTER_EVALUATION_PERIOD,
            'start_date' => now(),
            'end_date' => now()->addDays(1),
            'semester_id' => Semester::current()->id,
        ]);
    }

    /**
     * Remove collegist role from collegists that have not filled their status.
     *
     * @return void
     */
    public function test_set_collegist_to_alumni()
    {
        Mail::fake();

        $user = User::factory()->create(['verified' => true]);
        $user->setCollegist(Role::RESIDENT);
        //no status set for next semester

        $this->periodicEvent->handleEnd();

        $this->assertFalse($user->hasRole(Role::COLLEGIST));
        $this->assertTrue($user->hasRole(Role::ALUMNI));
        $this->assertTrue($user->isCollegist());
    }

    /**
     *
     * @return void
     */
    public function test_set_collegist_to_active()
    {
        Mail::fake();

        $user = User::factory()->create(['verified' => true]);
        $user->setCollegist(Role::RESIDENT);
        $user->setStatusFor(Semester::next(), SemesterStatus::ACTIVE);

        $this->periodicEvent->handleEnd();

        $this->assertTrue($user->isActive(Semester::next()));
        $this->assertFalse($user->hasRole(Role::ALUMNI));
    }
}
