# marketplace (SmartFlows Marketplace client)

Client side of the SmartFlows module marketplace. Add one or more **repositories**
(a marketplace server URL + an API token), browse each repository's module
catalog, download owned modules into `go/modules/{package}`, and enforce paid
module licenses at runtime via an offline RS256-signed license JWT that a daily
cron refreshes.

The server side is the `community/marketplaceserver` module.

Managed from **System Settings → Marketplace** (module `mayManage` right required).

## Making a module require a marketplace license

A paid module gates itself by overriding `isLicensed()` in its `Module.php`. GO
core already calls `isLicensed()` from `isInstallable()` and disables the module
when it returns false — the same mechanism used for GO Pro:

```php
public function isLicensed(): bool
{
    return class_exists(\go\modules\community\marketplace\lib\MarketplaceLicense::class)
        && \go\modules\community\marketplace\lib\MarketplaceLicense::has('sf', '<moduleName>');
}
```

`MarketplaceLicense::has($package, $module)` verifies the repository's cached
JWT against its pinned public key **fully offline** (no network call at runtime):
it checks the RS256 signature, the hostname binding (the server validates and
signs a single concrete host — wildcards/`*` are refused, and the verifier will
not treat a bare `*` as a match-all), and the per-module (or `{package}/*`
wildcard) expiry. If the marketplace module isn't
installed, or there's no repository for the package, or no valid license, it
returns `false` → GO blocks install and disables the module. The daily
`RefreshLicenses` cron re-fetches the JWT so renewals/revocations take effect
without re-downloading anything; on a network failure the cached JWT keeps
working until its per-module expiry.

Note: without ionCube the check is patchable by a determined admin — same trust
model as GO without ionCube. Optional future hardening is ionCube-encoding the
packages on the server; it changes nothing in this client.

## How a download works

`System Settings → Marketplace → (select repository) → Download` streams the
module ZIP from the server (entitlement-checked server-side), **verifies the
package's RS256 signature against the repository's pinned public key** (the
server signs the exact ZIP bytes; a package not signed by the pinned key is
rejected before a single byte is trusted — TLS alone is not enough for code that
gets unpacked and executed), validates every archive entry (no path traversal,
single `{module}/` root), backs up any existing
`go/modules/{package}/{module}`, extracts the new version into place (restoring
the backup on failure), and records the downloaded version. It then
points you to **System Settings → Modules** to install the module the standard
GO way (or to run the upgrade for an update). The marketplace never auto-runs a
module install.

## Manual end-to-end smoke test (needs a live server + client instance)

Not run automatically. Requires a `community/marketplaceserver` instance and this
client on a second GO instance.

1. Install/upgrade `community/marketplace` on the client. The migration drops the old
   username/password credential rows, creates `marketplace_repository` +
   `marketplace_repository_module`, and registers the daily `RefreshLicenses` cron.
   Confirm both tables and the cron schedule row exist.
2. System Settings → Marketplace → **Add repository** pointing at the server URL
   with a token generated on the server. Expect: token validated, package name +
   signing key pinned, the `go/modules/{package}` writability checked.
3. The catalog lists the server's products with correct states. Grant an
   entitlement on the server, press **Refresh**, confirm `owned` flips and a
   module becomes downloadable.
4. **Download** a module → verify it lands in `go/modules/{package}/{module}` and
   the toast points to System Settings → Modules; install it there.
5. Add `isLicensed()` (above) to a test module, confirm it reports licensed;
   expire/revoke the entitlement on the server, **Refresh**, confirm it flips to
   unlicensed.
6. Rotate the server signing key (or simulate), **Refresh**, confirm the repo
   flags a key mismatch and licensing keeps working on the cached JWT until you
   re-confirm the new key (re-enter the token and save).

## Tests

Pure unit tests (no DB) — `LicenseVerifier` (offline JWT/hostname/expiry) and
`PackageValidator` (ZIP entry safety):

```bash
cd go/modules/community/marketplace
/opt/homebrew/opt/php@8.3/bin/php ../../../../vendor/bin/phpunit -c phpunit.xml
```
