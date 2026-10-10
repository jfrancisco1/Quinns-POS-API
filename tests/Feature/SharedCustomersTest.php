<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SharedCustomersTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Branch $main;

    private Branch $canaman;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->makeTenant('quinns-laundry');
        $this->main = $this->tenant->branches()->create(['name' => 'Main Branch']);
        $this->canaman = $this->tenant->branches()->create(['name' => 'Canaman Branch']);
    }

    private function makeTenant(string $slug): Tenant
    {
        return Tenant::create([
            'name' => $slug,
            'slug' => $slug,
            'email' => "{$slug}@example.com",
            'plan' => 'pro',
        ]);
    }

    private function makeUser(string $role, ?Branch $branch = null, ?Tenant $tenant = null): User
    {
        return ($tenant ?? $this->tenant)->users()->create([
            'name' => ucfirst($role),
            'username' => $role.uniqid(),
            'password' => Hash::make('password'),
            'role' => $role,
            'branch_id' => $branch?->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);
    }

    private function makeCustomer(Branch $branch, string $nickname, ?string $mobile = null, ?Tenant $tenant = null): Customer
    {
        return Customer::create([
            'nickname' => $nickname,
            'mobile' => $mobile,
            'tenant_id' => ($tenant ?? $this->tenant)->id,
            'branch_id' => $branch->id,
        ]);
    }

    public function test_staff_list_includes_customers_from_every_branch_of_their_tenant(): void
    {
        $this->makeCustomer($this->main, 'Maria', '09171234567');
        $this->makeCustomer($this->canaman, 'Juan', '09181234567');

        $other = $this->makeTenant('other-laundry');
        $this->makeCustomer($other->branches()->create(['name' => 'Other']), 'Outsider', '09191234567', $other);

        $response = $this->actingAs($this->makeUser('staff', $this->canaman))
            ->getJson('/api/v1/customers');

        $response->assertOk()->assertJsonCount(2, 'data');
        $this->assertEqualsCanonicalizing(['Maria', 'Juan'], array_column($response->json('data'), 'nickname'));
    }

    public function test_staff_branch_id_filter_is_ignored(): void
    {
        $this->makeCustomer($this->main, 'Maria');
        $this->makeCustomer($this->canaman, 'Juan');

        $this->actingAs($this->makeUser('staff', $this->canaman))
            ->getJson("/api/v1/customers?branch_id={$this->canaman->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_branch_id_filter_still_applies(): void
    {
        $this->makeCustomer($this->main, 'Maria');
        $this->makeCustomer($this->canaman, 'Juan');

        $this->actingAs($this->makeUser('admin'))
            ->getJson("/api/v1/customers?branch_id={$this->canaman->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nickname', 'Juan')
            ->assertJsonPath('data.0.branch_id', $this->canaman->id);
    }

    public function test_staff_and_delivery_can_view_customer_from_another_branch(): void
    {
        $customer = $this->makeCustomer($this->main, 'Maria');

        foreach (['staff', 'delivery'] as $role) {
            $this->actingAs($this->makeUser($role, $this->canaman))
                ->getJson("/api/v1/customers/{$customer->id}")
                ->assertOk()
                ->assertJsonPath('data.nickname', 'Maria');
        }
    }

    public function test_customer_from_another_tenant_is_not_found(): void
    {
        $other = $this->makeTenant('other-laundry');
        $customer = $this->makeCustomer($other->branches()->create(['name' => 'Other']), 'Outsider', null, $other);

        $this->actingAs($this->makeUser('staff', $this->main))
            ->getJson("/api/v1/customers/{$customer->id}")
            ->assertNotFound();
    }

    public function test_staff_can_update_customer_from_another_branch(): void
    {
        $customer = $this->makeCustomer($this->main, 'Maria');

        $this->actingAs($this->makeUser('staff', $this->canaman))
            ->putJson("/api/v1/customers/{$customer->id}", ['address' => 'Canaman, Camarines Sur'])
            ->assertOk()
            ->assertJsonPath('data.address', 'Canaman, Camarines Sur');

        $this->assertSame($this->main->id, $customer->fresh()->branch_id);
    }

    public function test_staff_cannot_delete_customer_from_another_branch(): void
    {
        $customer = $this->makeCustomer($this->main, 'Maria');

        $this->actingAs($this->makeUser('staff', $this->canaman))
            ->deleteJson("/api/v1/customers/{$customer->id}")
            ->assertForbidden();

        $this->assertNotSoftDeleted($customer);
    }

    public function test_duplicate_mobile_within_tenant_is_rejected_across_branches(): void
    {
        $this->makeCustomer($this->main, 'Maria', '09171234567');

        $this->actingAs($this->makeUser('staff', $this->canaman))
            ->postJson('/api/v1/customers', ['nickname' => 'Maria', 'mobile' => '09171234567'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['mobile' => 'A customer with this mobile number already exists.']);
    }

    public function test_same_mobile_is_allowed_in_another_tenant(): void
    {
        $other = $this->makeTenant('other-laundry');
        $this->makeCustomer($other->branches()->create(['name' => 'Other']), 'Maria', '09171234567', $other);

        $this->actingAs($this->makeUser('staff', $this->main))
            ->postJson('/api/v1/customers', ['nickname' => 'Maria', 'mobile' => '09171234567'])
            ->assertSuccessful();
    }

    public function test_mobile_of_deleted_customer_can_be_reused(): void
    {
        $this->makeCustomer($this->main, 'Maria', '09171234567')->delete();

        $this->actingAs($this->makeUser('staff', $this->main))
            ->postJson('/api/v1/customers', ['nickname' => 'Maria', 'mobile' => '09171234567'])
            ->assertSuccessful();
    }

    public function test_duplicate_nickname_is_allowed(): void
    {
        $this->makeCustomer($this->main, 'Maria', '09171234567');

        $this->actingAs($this->makeUser('staff', $this->main))
            ->postJson('/api/v1/customers', ['nickname' => 'Maria', 'mobile' => '09181234567'])
            ->assertSuccessful();
    }

    public function test_update_keeping_own_mobile_passes_but_taking_another_customers_fails(): void
    {
        $maria = $this->makeCustomer($this->main, 'Maria', '09171234567');
        $this->makeCustomer($this->canaman, 'Juan', '09181234567');
        $staff = $this->makeUser('staff', $this->main);

        $this->actingAs($staff)
            ->putJson("/api/v1/customers/{$maria->id}", ['mobile' => '09171234567', 'notes' => 'VIP'])
            ->assertOk();

        $this->actingAs($staff)
            ->putJson("/api/v1/customers/{$maria->id}", ['mobile' => '09181234567'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['mobile']);
    }
}
