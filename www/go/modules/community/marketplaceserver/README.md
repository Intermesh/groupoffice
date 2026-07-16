# marketplaceserver

SmartFlows marketplace server — serves the module catalog, licenses and package
downloads to `community/marketplace` clients running on remote Group-Office instances.

API base URL shape: `/api/page.php/community/marketplaceserver/...`

Public endpoints: `info`, `catalog`, `license`, `download/{module}/{version}`,
`signature/{module}/{version}` (RS256 signature over the ZIP the client verifies
before extracting), `register`, `login`, `resend`, `verify`, `account`,
`productLogo/{id}`.

Security notes:
- **License hostname binding** is validated server-side (`HostnameValidator`):
  the signed `hostname` claim must be a single concrete host, so one entitlement
  can't be bound to `*` and redistributed to every instance.
- **Instance binding** governs how many instances a grant covers, two
  independent mechanisms:
  - *Seats* (on the customer, `Customer.maxInstances`, 0 = unlimited): how many
    concurrent active instances may hold seat-mode licenses. A stale instance
    (no check-in for `SEAT_ACTIVITY_DAYS`, default 30) frees its seat, so
    staging/migration/failover don't need admin intervention.
  - *Hostname* (on the entitlement, `Entitlement.bindingMode = 'hostname'`): the
    grant's modules are licensed only on the single host it is pinned to (pinned
    on first `/license` use; a manager clears `boundHostname` to re-pin). Does
    not draw from the seat pool.

  Existing grants migrate to seat-mode and every customer to 1 seat, so
  licensing is enforced immediately after upgrade.
- **Rate-limited auth endpoints** cap per IP + per e-mail. Behind a reverse
  proxy, set *Settings → Security → Trusted proxy IPs* so the real client IP is
  read from `X-Forwarded-For`; otherwise the direct connection IP is used (a
  forged `X-Forwarded-For` is never trusted).
