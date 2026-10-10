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

Moves `[geo:lat, lng]` tags from customer `notes` into the `latitude`/`longitude` columns: fills coordinates for customers that have none (existing coordinates are never overwritten), then removes the tag from `notes`. Also removes `[maps:url]` tags from `notes` (links are not resolved to coordinates). Safe to re-run.

Dry run:
```bash
php artisan customers:backfill-coordinates
```

Apply (confirm prompt, writes rows):
```bash
php artisan customers:backfill-coordinates --apply
```

Run in Railway's interactive **Console** tab (or `railway run ...`) so the confirm prompt works.

## Copy items to a new branch (Railway)

Command: `branches:copy-items {source} {target}`

Copies every item from the source branch into the target branch (same tenant only). Items whose name already exists in the target are skipped, so it's safe to re-run. Categories and expense categories are tenant-wide, so they don't need copying. Same logic as `POST /api/v1/branches/{id}/copy-items`.

Find branch IDs:
```bash
php artisan tinker --execute="dump(App\Models\Branch::select('id','tenant_id','name')->get()->toArray());"
```

Dry run:
```bash
php artisan branches:copy-items 1 2
```

Apply (confirm prompt, writes rows):
```bash
php artisan branches:copy-items 1 2 --apply
```

Run in Railway's interactive **Console** tab (or `railway run ...`) so the confirm prompt works.
