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

Copies `[geo:lat, lng]` tags from customer `notes` into the `latitude`/`longitude` columns for customers that have none. Notes are left unchanged; existing coordinates are never overwritten.

Dry run:
```bash
php artisan customers:backfill-coordinates
```

Apply (confirm prompt, writes rows):
```bash
php artisan customers:backfill-coordinates --apply
```

Run in Railway's interactive **Console** tab (or `railway run ...`) so the confirm prompt works.
