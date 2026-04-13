<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_access_user_management(): void
    {
        $this->seed([CatalogSeeder::class, RolesAndPermissionsSeeder::class]);

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
    }

    public function test_regular_user_cannot_access_user_management(): void
    {
        $this->seed([CatalogSeeder::class, RolesAndPermissionsSeeder::class]);

        $user = User::factory()->create();
        $user->assignRole('usuario');

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertForbidden();
    }
}
