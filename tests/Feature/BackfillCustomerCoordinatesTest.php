<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackfillCustomerCoordinatesTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomer(array $attributes): Customer
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'geo-co'],
            ['name' => 'Geo Co', 'email' => 'geo@example.com']
        );
        $branch = Branch::firstOrCreate(['tenant_id' => $tenant->id, 'name' => 'Main']);

        return Customer::create([
            'nickname' => 'Customer',
            'mobile' => '09171234567',
            'address' => 'Somewhere',
            'delivery_fee' => 0,
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            ...$attributes,
        ]);
    }

    public function test_dry_run_does_not_write_coordinates(): void
    {
        $customer = $this->makeCustomer(['notes' => 'Blue gate [geo:13.6462186, 123.2026878]']);

        $this->artisan('customers:backfill-coordinates')->assertSuccessful();

        $this->assertNull($customer->fresh()->latitude);
        $this->assertNull($customer->fresh()->longitude);
    }

    public function test_apply_copies_geo_tag_from_notes_into_coordinates(): void
    {
        $tagged = $this->makeCustomer(['notes' => 'Blue gate [geo:13.6462186, 123.2026878]']);
        $alreadySet = $this->makeCustomer([
            'notes' => '[geo:1.0, 2.0]',
            'latitude' => 14.5,
            'longitude' => 121.0,
        ]);
        $outOfRange = $this->makeCustomer(['notes' => '[geo:95.0, 123.0]']);
        $untagged = $this->makeCustomer(['notes' => 'Call before delivery']);

        $this->artisan('customers:backfill-coordinates', ['--apply' => true])
            ->expectsConfirmation('Update 1 customers now?', 'yes')
            ->assertSuccessful();

        $tagged->refresh();
        $this->assertSame('13.6462186', $tagged->latitude);
        $this->assertSame('123.2026878', $tagged->longitude);
        $this->assertSame('Blue gate [geo:13.6462186, 123.2026878]', $tagged->notes);

        $this->assertSame('14.5000000', $alreadySet->fresh()->latitude);
        $this->assertNull($outOfRange->fresh()->latitude);
        $this->assertNull($untagged->fresh()->latitude);
    }
}
