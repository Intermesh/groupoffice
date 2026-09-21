# Changelog


## 2026-09-21
- Stripe: a LOST dispute (`charge.dispute.closed`, status `lost`) now revokes the entitlement — Stripe never fires `charge.refunded` for a chargeback, so the money was gone while access stayed; logged as its own `chargeback` activity type, not as a refund
- Stripe: pin the API version on outgoing calls; a webhook arriving while no signing secret is configured is logged as a configuration problem instead of being indistinguishable from a forged signature
- Bearer API works behind Apache + FastCGI again: the `Authorization` header is read through `Request`, with an explicit `REDIRECT_HTTP_AUTHORIZATION` fallback (GO's own fallback is unreachable under PHP-FPM, which defines `apache_request_headers()`)
- `/signature` refuses a release whose package file is missing from disk instead of handing out a valid signature over zero bytes; `/download` 404s in the same case
- Rate-limiter table indexed for the queries it actually runs (`(ip, createdAt)`, `(email, createdAt)`) — the per-e-mail check had no index at all
- Settings: the webhook URL shown to the admin respects an install in a subdirectory and lists all five events the server handles; saving no longer caches the Stripe secrets in the browser nor wipes them, and the "Configured" hints refresh after a save
- An entitlement with a date-only expiry now lasts through the END of that day (new `lib/ExpiryDate`): "expires 31 Dec" used to drop the module out of the license JWT from 00:00 on 31 Dec, a full day before the date the dialog shows. A gateway's exact period end is still used verbatim, so a subscription gains no free day
- Resending a verification e-mail reports what actually happened: a failed send is an error instead of "Verification e-mail sent.", and an account that is not awaiting verification says so
- SECURITY: releases and instance logs are manager-only in queries too, not just on get/set — a user with plain read on the module could list their ids and total through `query` (the permission override had no matching `applyAclToQuery`); the catalog's query rule is now spelled out as well
- Product, release, instance-log and activity permissions moved to `internalGetPermissionLevel()`, so `Entity::EVENT_PERMISSION_LEVEL` fires for them and another module can extend these rights like it already can for customers, entitlements and tokens
- `/download` and `/signature` refuse a request that doesn't say which GO version it runs, instead of serving the highest version across all branches — a 6.8 instance could be handed a build made for 26. `/catalog` already offered nothing in that case; the endpoints now agree
- The rate-limiter ledger is pruned by the daily cron. Every `/license`, `/download`, `/signature` and `/checkout` writes a row, while the only pruning ran opportunistically from `/register` and `/login` — which a closed registration never reaches, so the table grew forever
- Enabling a customer account reports a failure to stamp `verifiedAt` instead of discarding the save result and letting the grid show a date the row never got
- A license or package signature is refused with a 500 instead of being built from an empty key when the stored signing key cannot be decrypted — a changed installation crypt key used to yield a signature no client could verify
- PHPStan level 8 now passes clean (`phpstan.neon` added): typed the JMAP controller `$params`, gave the catalog and seat loops their entity types, and dropped `?? ''` fallbacks that could never fire
- Release branches: adding a branch no longer swallows e.g. "6.8" when "16.8.1" exists (substring match)
- Entitlements: picking a customer that later disappears from the reloaded list no longer leaves Add armed for them; activity grid drops a redundant unfiltered load and expands its Item column

## 2026-09-19
- SECURITY: e-mail verification only issues and redeems links for self-registered customer accounts awaiting verification (could re-enable any disabled user, incl. admins)
- SECURITY: managers can enable/disable only customer-group, non-admin accounts; customer rows are created only after the permission check; customer account cannot be re-pointed
- License JWT now expires after 14 days (`exp`), bounding how long revokes, refunds and released seats go unnoticed; doubles as trial window
- Stripe webhook: process each event once (new `marketplaceserver_payment_event` table), answer 500 on failure so Stripe retries, map `async_payment_succeeded` (SEPA)
- Checkout refuses a product the customer already owns; a refunded payment is never re-granted by a late event; a free download no longer lifts a manager's revoke
- Rate limit: per-e-mail cap is 10x the per-IP cap (no more locking customers out), IPv6 counted per /64; seat allocation serialised per customer, hostnames lowercased
- Products: unique, folder-safe module names, no rename once releases exist, no delete while licenses exist (entitlement FK RESTRICT); releases re-validated on product change and branch
- `/verify` link shows a confirm button and verifies on POST (mail scanners no longer verify); product logos served only as raster images with CSP; retired free collections stop granting
- Signing keys read-only through the settings API; self-registration off by default; token owners read-only; fix uninstall leaving the activity table; UI reports failed saves/deletes

## 2026-07-17
- New module: marketplace server — products, releases with uploaded module packages, customers and entitlements, served to `community/marketplace` clients via the public page.php API (`info`, `catalog`, `license`, `download`, `signature`, `productLogo`)
- Issue RS256-signed license JWTs (`LicenseBuilder` + managed `KeyPair`): hostname claim validated to a single concrete host (no `*` redistribution), instance binding via customer seats (`maxInstances`, stale instances free their seat after the inactivity window — recomputed on every `/license` call, tidied by the daily `MarketplaceServerReleaseSeats` cron) or per-entitlement hostname pinning
- Add customer self-service auth endpoints (`register`, `login`, `resend`, `verify`, `account`) with e-mail verification and per-IP + per-e-mail rate limiting (trusted-proxy-aware client IP resolution)
- Add Stripe payments: `checkout`/`checkoutReturn`/`paymentWebhook` endpoints behind a payment-gateway abstraction, webhook signature verification, entitlements granted from payment events
- Add admin UI (Products, Releases, Customers, Entitlements, Activity log grids + settings panel) and unit/integration tests (package validation, Stripe signature/event mapping, registration, endpoint gating, e2e)
