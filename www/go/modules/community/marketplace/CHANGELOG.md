# Changelog


## 2026-07-17
- Rebuild module from a single username/password setting into a full marketplace client: add `marketplace_repository` + `marketplace_repository_module` tables, Repository entity/controller and a System Settings UI (repository list, per-repository module catalog with owned/installed/downloadable states)
- Add offline license enforcement: per-repository RS256-signed license JWT verified against the pinned server key (signature, single-concrete-hostname binding, per-module expiry), `MarketplaceLicense::has()` for paid modules' `isLicensed()`, daily `RefreshLicenses` cron to pick up renewals/revocations
- Add signed package downloads: stream module ZIP from the server, verify its RS256 signature against the pinned key before trusting a byte, validate archive entries (no path traversal, single module root), extract into `go/modules/{package}` with backup + restore-on-failure
- Add customer account registration and login windows talking to the server's public API to obtain a repository token
- Move module from the `sf` to the `community` package; drop the legacy username/password credential settings in migration; add unit tests for `LicenseVerifier` and `PackageValidator`
- SECURITY: encrypt the marketplace password at rest and stop serializing it to the browser. It is now a `protected` property with `setPassword()` (Crypt::encrypt, blank = keep unchanged) and a non-getter `decryptPassword()`; a safe `getPasswordConfigured()` bool is exposed for the UI instead. Public `getX()` methods are auto-exposed as API properties, so the previous public cleartext property was fetchable by the client.
- Add a migration to encrypt the existing plaintext password in `core_setting` (idempotent — skips already-encrypted `{GOCRYPT}` values); the settings-panel field is write-only (blank keeps the stored value) and the "configured?" check reads `passwordConfigured`.
- Fix a stray empty translation key (`"" => ""`) — replaced with `Leave blank to keep unchanged`.
