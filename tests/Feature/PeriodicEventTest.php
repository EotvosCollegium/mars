<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\ApplicationController;
use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use App\Models\PeriodicEvents\PeriodicEvent;
use App\Models\Semester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodicEventTest extends TestCase
{
    use RefreshDatabase;

    /**
     * start < now < end
     */
    public function test_get_periodic_event(): void
    {
        PeriodicEvent::create([
            'event_model' => ApplicationController::class,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $event = ApplicationController::periodicEvent();

        $this->assertNotNull($event);
        $this->assertTrue($event->isActive());
        $this->assertFalse($event->isExtended());
        $this->assertEquals($event->end_date, $event->real_end_date);


        $this->assertNull(SemesterEvaluationController::periodicEvent());
    }

    /**
     * start < end < now
     */
    public function test_get_periodic_event_passed(): void
    {
        PeriodicEvent::create([
            'event_model' => ApplicationController::class,
            'start_date' => now()->subDays(2),
            'end_date' => now()->subDay(),
        ]);

        $event = ApplicationController::periodicEvent();
        $this->assertNull($event);

    }

    /**
     * start < end < now < show_until
     */
    public function test_get_periodic_event_passed_showed(): void
    {
        PeriodicEvent::create([
            'event_model' => ApplicationController::class,
            'start_date' => now()->subDays(2),
            'end_date' => now()->subDay(),
            'show_until' => now()->addDay()
        ]);

        $event = ApplicationController::periodicEvent();

        $this->assertNotNull($event);
        $this->assertFalse($event->isActive());
        $this->assertFalse($event->isExtended());
        $this->assertEquals($event->end_date, $event->real_end_date);

    }

    /**
     * start < end < now < extended_end
     */
    public function test_get_periodic_event_passed_extended(): void
    {
        PeriodicEvent::create([
            'event_model' => ApplicationController::class,
            'start_date' => now()->subDays(2),
            'end_date' => now()->subDay(),
            'extended_end_date' => now()->addDay()
        ]);

        $event = ApplicationController::periodicEvent();

        $this->assertNotNull($event);
        $this->assertTrue($event->isActive());
        $this->assertTrue($event->isExtended());
        $this->assertEquals($event->extended_end_date, $event->real_end_date);
    }

    /**
     * start < end < extended_end < now
     */
    public function test_get_periodic_event_passed_extended_passed(): void
    {
        PeriodicEvent::create([
            'event_model' => ApplicationController::class,
            'start_date' => now()->subDays(3),
            'end_date' => now()->subDays(2),
            'extended_end_date' => now()->subDay()
        ]);

        $this->assertNull(ApplicationController::periodicEvent());
    }

    /**
     * start < end < extended_end < now < show_until
     */
    public function test_get_periodic_event_passed_extended_passed_showed(): void
    {
        PeriodicEvent::create([
            'event_model' => ApplicationController::class,
            'start_date' => now()->subDays(3),
            'end_date' => now()->subDays(2),
            'extended_end_date' => now()->subDay(),
            'show_until' => now()->addDay()
        ]);

        $event = ApplicationController::periodicEvent();

        $this->assertNotNull($event);
        $this->assertFalse($event->isActive());
        $this->assertTrue($event->isExtended());
        $this->assertEquals($event->extended_end_date, $event->real_end_date);
    }

    /**
     * now < start < end
     */
    public function test_get_periodic_event_future(): void
    {
        PeriodicEvent::create([
            'event_model' => ApplicationController::class,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDay(),
        ]);

        $event = ApplicationController::periodicEvent();

        $this->assertNotNull($event);
        $this->assertFalse($event->isActive());
    }

    /**
     * get latest event based on start date
     */
    public function test_get_latest_event(): void
    {
        PeriodicEvent::create([
            'event_model' => ApplicationController::class,
            'start_date' => now()->subDays(3),
            'end_date' => now()->addDays(2),
        ]);
        PeriodicEvent::create([
            'event_model' => ApplicationController::class,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        PeriodicEvent::create([
            'event_model' => SemesterEvaluationController::class,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDay(),
        ]);

        $event = ApplicationController::periodicEvent();

        $this->assertEquals(now()->subDay()->format('Y-m-d'), $event->start_date->format('Y-m-d'));
        $this->assertEquals(now()->addDay()->format('Y-m-d'), $event->end_date->format('Y-m-d'));
    }

    /**
     * test event creation, if current event exists it is overridden
     */
    public function test_creeta_event(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('applications.event'), [
            'semester_id' => Semester::current()->id,
            'start_date' => now()->subDays(3),
            'end_date' => now()->addDays(2),
            'show_until' => now()->addDay(),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $response = $this->post(route('applications.event'), [
            'semester_id' => Semester::next()->id,
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(3),
            'show_until' => now()->addDay(),
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseCount('periodic_events', 1);
    }
}
