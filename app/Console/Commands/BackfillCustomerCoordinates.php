<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillCustomerCoordinates extends Command
{
    protected $signature = 'customers:backfill-coordinates {--apply : Actually write the coordinates (default is a dry run)}';

    protected $description = 'Copy [geo:lat, lng] tags from customer notes into the latitude/longitude columns for customers that have none';

    private const GEO_PATTERN = '/\[geo:\s*(-?\d{1,2}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)\s*\]/i';

    public function handle(): int
    {
        $candidates = Customer::withTrashed()
            ->where(fn ($query) => $query->whereNull('latitude')->orWhereNull('longitude'))
            ->whereRaw('LOWER(notes) LIKE ?', ['%geo:%'])
            ->get(['id', 'nickname', 'notes']);

        $matched = [];
        $skipped = [];

        foreach ($candidates as $customer) {
            $coordinates = $this->parseCoordinates($customer->notes);

            if ($coordinates === null) {
                $skipped[] = [$customer->id, $customer->nickname, $customer->notes];

                continue;
            }

            $matched[] = ['customer' => $customer, ...$coordinates];
        }

        if ($skipped !== []) {
            $this->warn('Skipped '.count($skipped).' customers with an unparseable or out-of-range geo tag:');
            $this->table(['id', 'nickname', 'notes'], $skipped);
        }

        if ($matched === []) {
            $this->info('No customers to backfill.');

            return self::SUCCESS;
        }

        $this->info('Customers to backfill: '.count($matched));
        $this->table(
            ['id', 'nickname', 'latitude', 'longitude'],
            array_map(fn (array $row) => [$row['customer']->id, $row['customer']->nickname, $row['latitude'], $row['longitude']], $matched)
        );

        if (! $this->option('apply')) {
            $this->warn('Dry run only — nothing written. Re-run with --apply to save the coordinates.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Update '.count($matched).' customers now?')) {
            $this->warn('Aborted.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($matched) {
            foreach ($matched as $row) {
                $row['customer']->update([
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                ]);
            }
        });

        $this->info('Updated '.count($matched).' customers.');

        return self::SUCCESS;
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    private function parseCoordinates(?string $notes): ?array
    {
        if ($notes === null || ! preg_match(self::GEO_PATTERN, $notes, $match)) {
            return null;
        }

        $latitude = (float) $match[1];
        $longitude = (float) $match[2];

        if (abs($latitude) > 90 || abs($longitude) > 180) {
            return null;
        }

        return ['latitude' => $latitude, 'longitude' => $longitude];
    }
}
