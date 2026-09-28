# Changelog


## 2026-09-21
- SECURITY: repository rows are manager-gated in queries too — the permission override had no matching `applyAclToQuery`, so `query` handed every repository's id, name and URL to a user who only had the module (the token, signing key and license never leaked: they have no getter)
- The settings panel hides what the server would refuse: a non-admin with the module's manage right saw Add repository, Update all, Edit, Delete, Refresh licenses and Download, and got a 403 from every one of them. It already fetched `permissionLevel` and never used it
- A package whose only entry is a file named like the module is rejected instead of being renamed over the module directory
- A license entry with a non-integer `expiresAt` no longer reads as "still valid" (PHP compares a non-numeric string with an int as strings, so it would have licensed the module forever)
- An IPv6 host keeps its address but loses its port when normalised, so the web refresh, the cron and the runtime gate agree on one hostname
- The package validator requires a lowercase module name, like the server and the download controller already did
- `/register` returns no API token, and neither module's code claims otherwise any more — issuing one only for a new account would reveal that the account did not exist yet
- Three error dialogs said just "Repository URL" or "E-mail"; they now say what to do
- Added the `mayManage` label, so the Permissions dialog no longer shows the raw right name
- New `tests/e2e/`: two disposable instances that run the whole vendor/customer exchange over real HTTP — registration, verification, bearer auth, catalogue, package signature, install, license refresh, revocation and signing-key rotation. Its helper scripts refuse to run over HTTP: they ship inside the module, and Group-Office serves its own tree without an .htaccess
- A downloaded ZIP with an unreadable entry name is rejected instead of passing that entry to the path validator as an empty string
- `moduleRights` treats a missing auth state as "not an admin" rather than calling a method on it
- PHPStan level 8 now passes clean (`phpstan.neon` added); the API client no longer promises response shapes it cannot guarantee — those come from a remote server and every caller already reads them defensively
- Drop the empty `model/Settings.php` and its `getSettings()` override: the client keeps its configuration in `Repository` entities and in the panel's own saved state, and its settings panel has no settings-bound field at all — the class only made the module look configurable
- Fix the `CLIENT_TOKEN` docblock pointing at `Settings::DEFAULT_CLIENT_TOKEN` as if it lived in this module; the constant it must stay in sync with belongs to marketplaceserver

## 2026-09-19
- SECURITY: only admins add repositories, download modules or refresh licenses (module manage right now only reads catalog/account)
- SECURITY: pin the server's package, name and signing key server-side on save; `publicKey`, `licenseJwt`, `keyMismatch`, `package` are no longer writable over JMAP
- SECURITY: never install into core packages (`core`, `community`, `business`, `legacy`) and never overwrite a module this repository did not download
- SECURITY: accept only https checkout URLs and cut the payment tab's opener; escape server-supplied logo URL, tooltips and error texts (XSS)
- Fix paid modules always reported unlicensed: `MarketplaceLicense::has()` looks the repository up by package, not display name; unique key moved from `name` to `package`
- Enforce the license token's 14-day `exp`; normalise hostnames (case, trailing dot, port) and take the host from the configured URL in web, cron and runtime gate alike
- Keep the cached license unless a refreshed one verifies against the pinned key; refresh daily at a random time; one failing repository no longer stops the cron
- Download: resolve the version via the signature first and download exactly that one, per-module lock, 100 MB cap, report a backup that could not be removed
- UI: masks and "Update all" no longer hang on a request timeout; "Update all" cannot run twice; add missing translations

## 2026-07-17
- Rebuild module from a single username/password setting into a full marketplace client: add `marketplace_repository` + `marketplace_repository_module` tables, Repository entity/controller and a System Settings UI (repository list, per-repository module catalog with owned/installed/downloadable states)
- Add offline license enforcement: per-repository RS256-signed license JWT verified against the pinned server key (signature, single-concrete-hostname binding, per-module expiry), `MarketplaceLicense::has()` for paid modules' `isLicensed()`, daily `RefreshLicenses` cron to pick up renewals/revocations
- Add signed package downloads: stream module ZIP from the server, verify its RS256 signature against the pinned key before trusting a byte, validate archive entries (no path traversal, single module root), extract into `go/modules/{package}` with backup + restore-on-failure
- Add customer account registration and login windows talking to the server's public API to obtain a repository token
- Move module from the `sf` to the `community` package; drop the legacy username/password credential settings in migration; add unit tests for `LicenseVerifier` and `PackageValidator`
- SECURITY: encrypt the marketplace password at rest and stop serializing it to the browser. It is now a `protected` property with `setPassword()` (Crypt::encrypt, blank = keep unchanged) and a non-getter `decryptPassword()`; a safe `getPasswordConfigured()` bool is exposed for the UI instead. Public `getX()` methods are auto-exposed as API properties, so the previous public cleartext property was fetchable by the client.
- Add a migration to encrypt the existing plaintext password in `core_setting` (idempotent — skips already-encrypted `{GOCRYPT}` values); the settings-panel field is write-only (blank keeps the stored value) and the "configured?" check reads `passwordConfigured`.
- Fix a stray empty translation key (`"" => ""`) — replaced with `Leave blank to keep unchanged`.
