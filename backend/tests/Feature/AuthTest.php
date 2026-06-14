<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    /** @test */
    public function user_can_login_with_valid_credentials(): void
    {
        $role = Role::where('name', 'siswa')->first();
        $user = User::factory()->create([
            'role_id'  => $role->id,
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
                 ->assertJsonStructure(['token', 'user', 'message'])
                 ->assertJsonPath('user.email', 'test@example.com');
    }

    /** @test */
    public function login_fails_with_wrong_password(): void
    {
        $role = Role::where('name', 'siswa')->first();
        User::factory()->create([
            'role_id'  => $role->id,
            'email'    => 'test@example.com',
            'password' => bcrypt('correct_password'),
            'is_active' => true,
        ]);

        $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'wrong_password',
        ])->assertUnauthorized();
    }

    /** @test */
    public function inactive_user_cannot_login(): void
    {
        $role = Role::where('name', 'siswa')->first();
        User::factory()->create([
            'role_id'   => $role->id,
            'email'     => 'inactive@example.com',
            'password'  => bcrypt('password123'),
            'is_active' => false,
        ]);

        $this->postJson('/api/auth/login', [
            'email'    => 'inactive@example.com',
            'password' => 'password123',
        ])->assertForbidden();
    }

    /** @test */
    public function authenticated_user_can_logout(): void
    {
        $role = Role::where('name', 'siswa')->first();
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);

        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson('/api/auth/logout')
             ->assertOk()
             ->assertJsonPath('message', 'Logout berhasil.');
    }

    /** @test */
    public function authenticated_user_can_get_profile(): void
    {
        $role = Role::where('name', 'siswa')->first();
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);

        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson('/api/user')
             ->assertOk()
             ->assertJsonStructure(['user' => ['id', 'name', 'email', 'role']]);
    }

    /** @test */
    public function unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }
}
