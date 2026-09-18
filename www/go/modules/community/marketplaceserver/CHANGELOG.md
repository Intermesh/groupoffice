# Changelog


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
