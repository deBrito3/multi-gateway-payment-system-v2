<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_roles_relationship(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'ADMIN']);
        $user->roles()->attach($role);

        $this->assertTrue($user->roles->contains($role));
    }

    public function test_user_has_role_helper(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'ADMIN']);
        $user->roles()->attach($role);

        $this->assertTrue($user->hasRole('ADMIN'));
        $this->assertFalse($user->hasRole('FINANCE'));
    }

    public function test_user_has_any_role_helper(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'FINANCE']);
        $user->roles()->attach($role);

        $this->assertTrue($user->hasAnyRole(['ADMIN', 'FINANCE']));
        $this->assertFalse($user->hasAnyRole(['ADMIN', 'MANAGER']));
    }

    public function test_user_uses_soft_deletes(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $this->assertSoftDeleted($user);
    }
}
