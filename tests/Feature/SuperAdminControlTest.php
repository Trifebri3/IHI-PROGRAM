<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperAdminControlTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Peserta', 'guard_name' => 'web']);

        // Create Admin
        $this->admin = User::factory()->create([
            'email' => 'admin@ihi.or.id',
            'password' => bcrypt('password123')
        ]);
        $this->admin->assignRole('Super Admin');
        \App\Models\UserProfile::create(['user_id' => $this->admin->id]);
        \App\Models\Address::create([
            'user_id' => $this->admin->id,
            'negara' => 'Indonesia',
            'provinsi' => 'DKI Jakarta',
            'kabupaten' => 'Jakarta Pusat',
            'kecamatan' => 'Gambir',
            'desa' => 'Gambir',
            'kampung' => 'Gambir'
        ]);

        // Create Target User
        $this->user = User::factory()->create([
            'email' => 'user@gmail.com',
            'password' => bcrypt('userpassword')
        ]);
        $this->user->assignRole('Peserta');
        \App\Models\UserProfile::create(['user_id' => $this->user->id]);
        \App\Models\Address::create([
            'user_id' => $this->user->id,
            'negara' => 'Indonesia',
            'provinsi' => 'DKI Jakarta',
            'kabupaten' => 'Jakarta Pusat',
            'kecamatan' => 'Gambir',
            'desa' => 'Gambir',
            'kampung' => 'Gambir'
        ]);
    }

    /**
     * Test SuperAdmin can initiate user impersonation
     */
    public function test_superadmin_can_impersonate_user(): void
    {
        $this->actingAs($this->admin);

        // Impersonate user
        $response = $this->get(route('superadmin.users.impersonate', $this->user->id));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('impersonator_id', $this->admin->id);

        // Assert we are now authenticated as the target user
        $this->assertEquals($this->user->id, auth()->id());

        // Assert audit log was recorded
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'impersonate_start',
            'target_user_id' => $this->user->id,
        ]);
    }

    /**
     * Test regular users cannot impersonate anyone
     */
    public function test_regular_users_cannot_impersonate(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('superadmin.users.impersonate', $this->admin->id));
        $response->assertStatus(403);
    }

    /**
     * Test impersonator can stop impersonating and return back to original admin session
     */
    public function test_impersonator_can_stop_impersonating(): void
    {
        // Act as target user, but mock session as impersonating
        $this->actingAs($this->user);
        session(['impersonator_id' => $this->admin->id]);

        $response = $this->post(route('impersonate.stop'));

        $response->assertRedirect(route('superadmin.users.index'));
        $this->assertNull(session('impersonator_id'));

        // Assert we are logged back in as the admin
        $this->assertEquals($this->admin->id, auth()->id());

        // Assert stop audit log was recorded
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'impersonate_stop',
            'target_user_id' => $this->user->id,
        ]);
    }

    /**
     * Test SuperAdmin can update a user's password
     */
    public function test_superadmin_can_change_user_password(): void
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('superadmin.users.update', $this->user->id), [
            'name' => 'Updated User Name',
            'email' => 'user@gmail.com',
            'role' => 'Peserta',
            'password' => 'newsecurepassword'
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Verify user password is updated (can log in with new password)
        $this->assertTrue(Hash::check('newsecurepassword', $this->user->fresh()->password));

        // Assert audit log was recorded
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'update_user',
            'target_user_id' => $this->user->id,
        ]);
    }

    /**
     * Test SuperAdmin dashboard loads correctly with statistics
     */
    public function test_superadmin_dashboard_loads_correctly_with_stats(): void
    {
        $this->actingAs($this->admin);

        // Create a program and a registration to populate stats
        $program = \App\Models\Program::create([
            'name' => 'Program Hijau Lestari',
            'slug' => 'program-hijau-lestari',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'published',
            'total_hours' => 20
        ]);

        \App\Models\Registration::create([
            'user_id' => $this->user->id,
            'program_id' => $program->id,
            'status' => 'passed'
        ]);

        // Visit dashboard
        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Program Hijau Lestari');
        $response->assertSee('Grafik Performa Program');
        $response->assertSee('Audit Trail / Log Aktivitas');
    }
}
