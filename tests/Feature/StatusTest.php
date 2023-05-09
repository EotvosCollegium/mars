<?php

namespace Tests\Feature;

use App\Http\Controllers\Secretariat\SemesterController;
use App\Models\Role;
use App\Models\Semester;
use App\Models\SemesterStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StatusTest extends TestCase
{
    use RefreshDatabase;

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

        SemesterController::finalizeStatements();

        $this->assertFalse($user->isCollegist());
        $this->assertTrue($user->hasRole(Role::ALUMNI));
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
        $user->setStatus(SemesterStatus::ACTIVE);

        SemesterController::finalizeStatements();

        $this->assertTrue($user->isActive());
        $this->assertTrue($user->isActive(Semester::current()));
        $this->assertFalse($user->hasRole(Role::ALUMNI));
    }
}
