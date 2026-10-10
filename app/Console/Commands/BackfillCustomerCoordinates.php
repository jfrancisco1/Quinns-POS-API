<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillCustomerCoordinates extends Command
{
    protected $signature = 'customers:backfill-coordinates {--apply : Actually write the changes (default is a dry run)}';

    protected $description = 'Move [geo:lat, lng] tags from customer notes into the latitude/longitude columns and remove them from notes';

    private const GEO_TAG = '\[geo:\s*(-?\d{1,2}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)\s*\]';

    public function handle(): int
    {
        $candidates = Customer::withTrashed()
            ->whereRaw('LOWER(notes) LIKE ?', ['%geo:%'])
            ->get(['id', 'nickname', 'notes', 'latitude', 'longitude']);

        $changes = [];
        $skipped = [];

        foreach ($candidates as $customer) {
            $hasCoordinates = $customer->latitude !== null && $customer->longitude !== null;
            $coordinates = $hasCoordinates ? [] : $this->parseCoordinates($customer->notes);

            if ($coordinates === null) {
                $skipped[] = [$customer->id, $customer->nickname, $customer->notes];

                continue;
            }

            $notes = $this->stripGeoTags($customer->notes);

            if ($coordinates === [] && $notes === $customer->notes) {
                continue;
            }

            $changes[] = ['customer' => $customer, 'attributes' => [...$coordinates, 'notes' => $notes]];
        }

        if ($skipped !== []) {
            $this->warn('Skipped '.count($skipped).' customers with no coordinates and an unparseable or out-of-range geo tag:');
            $this->table(['id', 'nickname', 'notes'], $skipped);
        }

        if ($changes === []) {
            $this->info('No customers to update.');

            return self::SUCCESS;
        }

        $this->info('Customers to update: '.count($changes));
        $this->table(
            ['id', 'nickname', 'latitude', 'longitude', 'notes before', 'notes after'],
            array_map(fn (array $change) => [
                $change['customer']->id,
                $change['customer']->nickname,
                $change['attributes']['latitude'] ?? $change['customer']->latitude,
                $change['attributes']['longitude'] ?? $change['customer']->longitude,
                $change['customer']->notes,
                $change['attributes']['notes'],
            ], $changes)
        );

        if (! $this->option('apply')) {
            $this->warn('Dry run only — nothing written. Re-run with --apply to save the changes.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Update '.count($changes).' customers now?')) {
            $this->warn('Aborted.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($changes) {
            foreach ($changes as $change) {
                $change['customer']->update($change['attributes']);
            }
        });

        $this->info('Updated '.count($changes).' customers.');

        return self::SUCCESS;
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    private function parseCoordinates(?string $notes): ?array
    {
        if ($notes === null || ! preg_match('/'.self::GEO_TAG.'/i', $notes, $match)) {
            return null;
        }

        $latitude = (float) $match[1];
        $longitude = (float) $match[2];

        if (abs($latitude) > 90 || abs($longitude) > 180) {
            return null;
        }

        return ['latitude' => $latitude, 'longitude' => $longitude];
    }

    private function stripGeoTags(string $notes): string
    {
        return trim(preg_replace(['/\s*'.self::GEO_TAG.'/i', '/[ \t]{2,}/'], ['', ' '], $notes));
    }
}
