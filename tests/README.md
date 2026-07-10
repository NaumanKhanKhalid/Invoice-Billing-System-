# Tests

```bash
php artisan test                      # full suite
php artisan test --testsuite=Unit    # fast unit tests
php artisan test --testsuite=Feature # tenancy feature tests
```

## Coverage

- **Unit**: coaching fee math (`effectiveFee`), feature-toggle defaults, currency/kg helpers, OpenTab totals.
- **Feature** (real tenancy — each test boots an actual tenant DB via `Tests\Concerns\InteractsWithTenancy`):
  - POS sale flow (sale + stock decrement + stock-guard block)
  - Open Tab lifecycle (create → add item → recalculate → close)
  - Coaching fee auto-generation + collection (receipt number)
  - Feature toggles (403 when off / plan-gated)
  - Owner-only access (staff blocked from settings)
  - Breeze auth flows (login/logout/password/profile) under tenant subdomains

## Known flake

Running the FULL suite occasionally fails 2–3 Breeze auth tests around the same
suite position with sqlite `database is locked` during back-to-back tenant
creation. The affected classes always pass when run alone
(`php artisan test --filter=PasswordConfirmationTest`). This is a test-infra
sqlite artifact, not an application bug — production runs MySQL. Two
email-verification tests are skipped intentionally: the product provisions
users through the shop owner, there is no email-verification flow.
