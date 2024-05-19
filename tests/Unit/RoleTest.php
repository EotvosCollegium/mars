<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RoleTest extends TestCase
{
    /**
     * Add collegist role.
     * @return void
     */
    public function test_set_collegist()
    {
        Mail::fake();

        $user = User::factory()->create();

        $user->setExtern();

        $this->assertTrue($user->isExtern());
        $this->assertTrue($user->hasRole(Role::get(Role::COLLEGIST)));
        $this->assertTrue($user->hasRole([Role::COLLEGIST => Role::EXTERN]));

        $user->setCollegist(Role::RESIDENT);

        $this->assertTrue($user->isResident());
        $this->assertTrue($user->hasRole(Role::get(Role::COLLEGIST)));
        $this->assertTrue($user->hasRole([Role::COLLEGIST => Role::RESIDENT]));


    }

    /**
     * Add and check base role example.
     * @return void
     */
    public function test_add_base_role()
    {
        Mail::fake();

        $user = User::factory()->create();

        $user->addRole(Role::get(Role::TENANT));

        $this->assertTrue($user->hasRole(Role::get(Role::TENANT)));
        $this->assertTrue($user->hasRole(Role::TENANT));
        $this->assertTrue($user->hasRole([Role::TENANT, Role::DIRECTOR]));
    }

    /**
     * Add and check a role with object.
     * @return void
     */
    public function test_roles_with_object()
    {
        Mail::fake();

        $user = User::factory()->create();

        $role = Role::get(Role::STUDENT_COUNCIL);
        $user->addRole($role, $role->getObject(Role::PRESIDENT));

        $this->assertTrue($user->hasRole(Role::STUDENT_COUNCIL));
        $this->assertTrue($user->hasRole([Role::STUDENT_COUNCIL => Role::PRESIDENT]));
        $this->assertTrue($user->hasRole([Role::DIRECTOR, Role::STUDENT_COUNCIL]));
        $this->assertTrue($user->hasRole([Role::DIRECTOR, Role::STUDENT_COUNCIL => [Role::PRESIDENT, Role::ECONOMIC_VICE_PRESIDENT]]));
    }

    /**
     * Adding a role base and an object that belongs to a different role base.
     * @return void
     */
    public function test_invalid_objects()
    {
        Mail::fake();

        $user = User::factory()->create();
        $role1 = Role::get(Role::STUDENT_COUNCIL);
        $role2 = Role::get(Role::COLLEGIST);

        $this->assertFalse($user->addRole($role1, $role2->getObject(Role::EXTERN)));
        $this->assertFalse($user->hasRole([Role::STUDENT_COUNCIL, Role::COLLEGIST]));
    }

    /**
     * Adding a role base without an expected object.
     * @return void
     */
    public function test_missing_object()
    {
        $user = User::factory()->create();

        $this->assertFalse($user->addRole(Role::get(Role::STUDENT_COUNCIL)));
        $this->assertFalse($user->hasRole([Role::STUDENT_COUNCIL]));
    }

    /**
     * Chaining of {@see HasRoles::scopeWithRole()} results in them being combined with logical AND.
     */
    public function test_scopeWithRole_chained(): void
    {
        $userRoleA = User::factory()->create();
        $userRoleA->addRole(Role::get(Role::TENANT));
        $userRoleB = User::factory()->create();
        $userRoleB->addRole(Role::get(Role::DIRECTOR));
        $userRolesAB = User::factory()->create();
        $userRolesAB->addRole(Role::get(Role::TENANT));
        $userRolesAB->addRole(Role::get(Role::DIRECTOR));

        $result = User::withRole(Role::get(Role::TENANT))->withRole(Role::get(Role::DIRECTOR))->get();
        $this->assertCount(1, $result);
        $this->assertEquals($userRolesAB->id, $result->first()->id);

        $result = User::withRole(Role::TENANT)->withRole(Role::DIRECTOR)->get();
        $this->assertCount(1, $result);
        $this->assertEquals($userRolesAB->id, $result->first()->id);

        $result = User::withRole(Role::get(Role::TENANT)->id)->withRole(Role::get(Role::DIRECTOR)->id)->get();
        $this->assertCount(1, $result);
        $this->assertEquals($userRolesAB->id, $result->first()->id);
    }

    /**
     * {@see HasRoles::scopeWithAllRoles()} combines the roles with logical AND.
     */
    public function test_scopeWithAllRoles(): void
    {
        $userRoleA = User::factory()->create();
        $userRoleA->addRole(Role::get(Role::TENANT));
        $userRoleB = User::factory()->create();
        $userRoleB->addRole(Role::get(Role::DIRECTOR));
        $userRolesAB = User::factory()->create();
        $userRolesAB->addRole(Role::get(Role::TENANT));
        $userRolesAB->addRole(Role::get(Role::DIRECTOR));

        $result = User::withAllRoles([Role::TENANT, Role::DIRECTOR])->get();
        $this->assertCount(1, $result);
        $this->assertEquals($userRolesAB->id, $result->first()->id);

        $result = User::withAllRoles([Role::get(Role::TENANT), Role::get(Role::DIRECTOR)])->get();
        $this->assertCount(1, $result);
        $this->assertEquals($userRolesAB->id, $result->first()->id);

        $result = User::withAllRoles([Role::TENANT, Role::DIRECTOR])->get();
        $this->assertCount(1, $result);
        $this->assertEquals($userRolesAB->id, $result->first()->id);

        $result = User::withAllRoles([Role::get(Role::TENANT)->id, Role::get(Role::DIRECTOR)->id])->get();
        $this->assertCount(1, $result);
        $this->assertEquals($userRolesAB->id, $result->first()->id);
    }
}
