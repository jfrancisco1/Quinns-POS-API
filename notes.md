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
