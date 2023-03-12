<?php

namespace Tests\Feature;

use App\Http\Controllers\Secretariat\SemesterController;
use App\Models\Role;
use App\Models\SemesterStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        $user = User::factory()->create(['verified' => true]);
        $user->setCollegist(Role::RESIDENT);

        SemesterController::finalizeStatements();

        $this->assertFalse($user->isCollegist());
    }

    /**
     *
     * @return void
     */
    public function test_set_collegist_to_active()
    {
        $user = User::factory()->create(['verified' => true]);
        $user->setCollegist(Role::RESIDENT);
        $user->setStatus(SemesterStatus::ACTIVE);

        SemesterController::finalizeStatements();

        $this->assertTrue($user->isActive());
    }
}
