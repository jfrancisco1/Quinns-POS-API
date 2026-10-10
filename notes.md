# Notes

## Payment history backfill (Railway)

Command: `payment-history:backfill`

Dry run:
```bash
php artisan payment-history:backfill
```

Apply (confirm prompt, writes rows):
```bash
php artisan payment-history:backfill --apply
```

Run in Railway's interactive **Console** tab (or `railway run ...`) so the confirm prompt works.

## Customer coordinates backfill (Railway)

Command: `customers:backfill-coordinates`

Moves `[geo:lat, lng]` tags from customer `notes` into the `latitude`/`longitude` columns: fills coordinates for customers that have none (existing coordinates are never overwritten), then removes the tag from `notes`. Safe to re-run.

Dry run:
```bash
php artisan customers:backfill-coordinates
```

Apply (confirm prompt, writes rows):
```bash
php artisan customers:backfill-coordinates --apply
```

Run in Railway's interactive **Console** tab (or `railway run ...`) so the confirm prompt works.
