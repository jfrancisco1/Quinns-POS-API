<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Item;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CopyBranchItemsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Branch $main;

    private Branch $canaman;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => "Quinn's Laundry",
            'slug' => 'quinns-laundry',
            'email' => 'owner@example.com',
            'plan' => 'pro',
        ]);
        $this->main = $this->tenant->branches()->create(['name' => 'Main Branch']);
        $this->canaman = $this->tenant->branches()->create(['name' => 'Canaman Branch']);
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

    private function makeItem(Branch $branch, string $name, ?Category $category = null): Item
    {
        return Item::create([
            'name' => $name,
            'price' => 100,
            'cost' => 40,
            'is_active' => true,
            'sort_order' => 0,
            'category_id' => $category?->id,
            'tenant_id' => $this->tenant->id,
            'branch_id' => $branch->id,
        ]);
    }

    public function test_admin_copies_items_into_new_branch_keeping_categories(): void
    {
        $category = Category::create(['name' => 'Wash', 'is_active' => true, 'sort_order' => 0, 'tenant_id' => $this->tenant->id]);
        $this->makeItem($this->main, 'Wash & Fold', $category);
        $this->makeItem($this->main, 'Comforter');

        $response = $this->actingAs($this->makeUser('admin'))
            ->postJson("/api/v1/branches/{$this->canaman->id}/copy-items", ['source_branch_id' => $this->main->id]);

        $response->assertOk()
            ->assertJsonPath('data.copied', 2)
            ->assertJsonPath('data.skipped', 0);

        $this->assertSame(2, Item::where('branch_id', $this->canaman->id)->count());
        $this->assertSame(2, Item::where('branch_id', $this->main->id)->count());
        $this->assertSame(
            $category->id,
            Item::where('branch_id', $this->canaman->id)->where('name', 'Wash & Fold')->value('category_id'),
        );
    }

    public function test_repeat_copy_skips_items_already_in_target(): void
    {
        $this->makeItem($this->main, 'Wash & Fold');
        $this->makeItem($this->main, 'Comforter');
        $this->makeItem($this->canaman, 'comforter');

        $this->actingAs($this->makeUser('admin'))
            ->postJson("/api/v1/branches/{$this->canaman->id}/copy-items", ['source_branch_id' => $this->main->id])
            ->assertOk()
            ->assertJsonPath('data.copied', 1)
            ->assertJsonPath('data.skipped', 1);

        $this->assertSame(2, Item::where('branch_id', $this->canaman->id)->count());
    }

    public function test_staff_cannot_copy_items(): void
    {
        $this->actingAs($this->makeUser('staff', $this->canaman))
            ->postJson("/api/v1/branches/{$this->canaman->id}/copy-items", ['source_branch_id' => $this->main->id])
            ->assertForbidden();
    }

    public function test_rejects_same_branch_and_other_tenant_source(): void
    {
        $other = Tenant::create(['name' => 'Other', 'slug' => 'other', 'email' => 'other@example.com', 'plan' => 'pro']);
        $otherBranch = $other->branches()->create(['name' => 'Other Branch']);
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->postJson("/api/v1/branches/{$this->canaman->id}/copy-items", ['source_branch_id' => $this->canaman->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('source_branch_id');

        $this->actingAs($admin)
            ->postJson("/api/v1/branches/{$this->canaman->id}/copy-items", ['source_branch_id' => $otherBranch->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('source_branch_id');
    }
}
