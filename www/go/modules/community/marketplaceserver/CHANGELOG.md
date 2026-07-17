# Changelog


## 2026-07-17
- New module: marketplace server — products, releases with uploaded module packages, customers and entitlements, served to `community/marketplace` clients via the public page.php API (`info`, `catalog`, `license`, `download`, `signature`, `productLogo`)
- Issue RS256-signed license JWTs (`LicenseBuilder` + managed `KeyPair`): hostname claim validated to a single concrete host (no `*` redistribution), instance binding via customer seats (`maxInstances`, stale instances free their seat after the inactivity window — recomputed on every `/license` call, tidied by the daily `MarketplaceServerReleaseSeats` cron) or per-entitlement hostname pinning
- Add customer self-service auth endpoints (`register`, `login`, `resend`, `verify`, `account`) with e-mail verification and per-IP + per-e-mail rate limiting (trusted-proxy-aware client IP resolution)
- Add Stripe payments: `checkout`/`checkoutReturn`/`paymentWebhook` endpoints behind a payment-gateway abstraction, webhook signature verification, entitlements granted from payment events
- Add admin UI (Products, Releases, Customers, Entitlements, Activity log grids + settings panel) and unit/integration tests (package validation, Stripe signature/event mapping, registration, endpoint gating, e2e)
