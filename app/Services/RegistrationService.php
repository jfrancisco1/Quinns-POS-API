<?php

namespace App\Services;

use App\Mail\WelcomeOwnerMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class RegistrationService
{
    public function __construct(
        private readonly TenantService $tenantService
    ) {
    }

    public function register(array $data): Tenant
    {
        $slug = $this->generateUniqueSlug($data['business_name']);
        $username = "{$slug}.admin";
        $temporaryPassword = Str::password(10);

        $tenant = DB::transaction(function () use ($data, $slug, $username, $temporaryPassword) {
            $tenant = $this->tenantService->create([
                'name'  => $data['business_name'],
                'slug'  => $slug,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'plan'  => 'free',
            ]);

            $tenant->users()->create([
                'name'     => $data['owner_name'],
                'username' => $username,
                'password' => Hash::make($temporaryPassword),
                'role'     => 'admin',
                'must_change_password' => true,
            ]);

            return $tenant;
        });

        $this->sendWelcomeEmail($tenant, $username, $temporaryPassword);

        return $tenant;
    }

    private function generateUniqueSlug(string $businessName): string
    {
        $base = Str::slug($businessName);
        $slug = $base;
        $suffix = 2;

        while (Tenant::where('slug', $slug)->exists() || User::where('username', "{$slug}.admin")->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function sendWelcomeEmail(Tenant $tenant, string $username, string $temporaryPassword): void
    {
        try {
            Mail::to($tenant->email)->send(new WelcomeOwnerMail(
                businessName: $tenant->name,
                username: $username,
                temporaryPassword: $temporaryPassword,
                loginUrl: config('app.url'),
            ));
        } catch (Throwable $e) {
            // Never let a mail (or logging) failure turn a successful
            // registration into a failed HTTP response.
            try {
                Log::error('Failed to send welcome email for new tenant registration.', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);
            } catch (Throwable) {
                // Logging itself is unavailable — nothing more we can safely do here.
            }
        }
    }
}
