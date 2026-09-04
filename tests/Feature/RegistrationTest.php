<?php

namespace Tests\Feature;

use App\Mail\WelcomeOwnerMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_tenant_and_owner_admin_and_sends_welcome_email(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/register', [
            'business_name' => "Quinn's Laundry",
            'email' => 'owner@quinnslaundry.com',
            'phone' => '09171234567',
            'address' => '123 Main St',
            'owner_name' => 'Juan dela Cruz',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.name', "Quinn's Laundry");
        $response->assertJsonPath('data.plan', 'free');

        $tenant = Tenant::where('email', 'owner@quinnslaundry.com')->firstOrFail();

        $this->assertSame('free', $tenant->plan);
        $this->assertNotNull($tenant->trial_ends_at);
        $this->assertTrue($tenant->trial_ends_at->between(now()->addDays(29), now()->addDays(31)));

        $admin = User::where('tenant_id', $tenant->id)->where('role', 'admin')->firstOrFail();

        $this->assertSame('Juan dela Cruz', $admin->name);
        $this->assertTrue($admin->must_change_password);
        $this->assertStringEndsWith('.admin', $admin->username);

        // Credentials must never appear in the response — only in the email.
        $response->assertJsonMissingPath('data.password');
        $this->assertStringNotContainsString($admin->username, $response->getContent());

        Mail::assertSent(WelcomeOwnerMail::class, function (WelcomeOwnerMail $mail) use ($tenant, $admin) {
            return $mail->hasTo($tenant->email) && $mail->username === $admin->username;
        });
    }

    public function test_registration_rejects_duplicate_business_email(): void
    {
        Mail::fake();

        Tenant::create([
            'name' => 'Existing Tenant',
            'slug' => 'existing-tenant',
            'email' => 'owner@quinnslaundry.com',
            'plan' => 'free',
        ]);

        $response = $this->postJson('/api/v1/register', [
            'business_name' => "Quinn's Laundry",
            'email' => 'owner@quinnslaundry.com',
            'owner_name' => 'Juan dela Cruz',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_registration_generates_unique_slug_on_business_name_collision(): void
    {
        Mail::fake();

        Tenant::create([
            'name' => "Quinn's Laundry",
            'slug' => 'quinns-laundry',
            'email' => 'first@quinnslaundry.com',
            'plan' => 'free',
        ]);

        $response = $this->postJson('/api/v1/register', [
            'business_name' => "Quinn's Laundry",
            'email' => 'second@quinnslaundry.com',
            'owner_name' => 'Maria Santos',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.slug', 'quinns-laundry-2');
    }
}
