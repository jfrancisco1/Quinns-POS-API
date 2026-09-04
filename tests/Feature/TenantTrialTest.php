<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantTrialTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(Tenant $tenant, string $username = 'admin'): User
    {
        return $tenant->users()->create([
            'name' => 'Admin User',
            'username' => $username,
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'must_change_password' => false,
        ]);
    }

    public function test_login_is_rejected_for_expired_free_trial_tenant(): void
    {
        $tenant = Tenant::create([
            'name' => 'Expired Co',
            'slug' => 'expired-co',
            'email' => 'expired@example.com',
            'plan' => 'free',
            'trial_ends_at' => now()->subDay(),
        ]);
        $this->makeAdmin($tenant);

        $response = $this->postJson('/api/v1/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertStatus(403);
    }

    public function test_login_succeeds_for_tenant_within_trial(): void
    {
        $tenant = Tenant::create([
            'name' => 'Active Co',
            'slug' => 'active-co',
            'email' => 'active@example.com',
            'plan' => 'free',
            'trial_ends_at' => now()->addDays(10),
        ]);
        $this->makeAdmin($tenant);

        $response = $this->postJson('/api/v1/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    }

    public function test_login_succeeds_for_paid_plan_regardless_of_trial_date(): void
    {
        $tenant = Tenant::create([
            'name' => 'Paid Co',
            'slug' => 'paid-co',
            'email' => 'paid@example.com',
            'plan' => 'pro',
            'trial_ends_at' => null,
        ]);
        $this->makeAdmin($tenant);

        $response = $this->postJson('/api/v1/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    }

    public function test_authenticated_request_is_rejected_once_token_already_issued_and_trial_expires(): void
    {
        $tenant = Tenant::create([
            'name' => 'Soon Expired Co',
            'slug' => 'soon-expired-co',
            'email' => 'soon@example.com',
            'plan' => 'free',
            'trial_ends_at' => now()->addMinute(),
        ]);
        $admin = $this->makeAdmin($tenant);

        // Trial expires after the token is issued.
        $tenant->update(['trial_ends_at' => now()->subMinute()]);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/me');

        $response->assertStatus(403);
    }

    public function test_superadmin_is_unaffected_by_tenant_trial_checks(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => true,
            'must_change_password' => false,
            'tenant_id' => null,
        ]);

        $response = $this->actingAs($superadmin, 'sanctum')->getJson('/api/v1/me');

        $response->assertStatus(200);
    }
}
