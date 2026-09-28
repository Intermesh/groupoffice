# marketplace ⇄ marketplaceserver — end-to-end harness

Two throwaway Group-Office instances on one compose project:

| container | module | port |
|---|---|---|
| `mpserver` | `community/marketplaceserver` (the vendor) | 8081 |
| `mpclient` | `community/marketplace` (the customer) | 8082 |
| `mpdb` | MariaDB holding both databases | 33081 |

```bash
./run.sh all      # build, wipe, install, publish a product, run the scenario
./run.sh test     # just the scenario against the running pair (repeatable)
./run.sh logs     # both apache error logs
./run.sh down     # remove the containers, the volume and the data dirs
```

## Why two instances

The unit tests cover the pure rules — token hashing, hostname binding, entry
validation, the expiry rule. What they cannot cover is the pair *as a pair*:
bearer auth across a real network hop, the catalogue's branch matching against
the client's actual GO version, the detached package signature verifying against
the pinned key, and the license JWT being accepted by an instance whose hostname
the vendor signed. `scenario.py` drives all of that through the customer's
public JMAP API as an administrator — the same calls the System Settings panel
makes.

## What it proves (33 checks)

1. Self-registration, and that it hands out **no** token (issuing one only on the
   "new account" path would tell a caller the account did not exist yet).
2. An unverified account cannot sign in; a wrong password is refused.
3. After verification, `/login` issues the API token, and saving the repository
   pins the vendor's package, name and signing key. The token is never echoed
   back — only `tokenConfigured`.
4. The catalogue lists the product with the release matching this instance's
   branch, and reports it as not owned.
5. Downloading without an entitlement is refused **with the vendor's own reason**,
   and nothing is written to `go/modules`.
6. With an entitlement: the license refresh stores a JWT bound to this host, the
   offline runtime gate then licenses the module, and the download lands the
   files on disk with the concrete version recorded.
7. A revocation on the vendor reaches the customer on the next refresh.
8. The pinned signing key holds: a vendor that regenerated its key pair is
   refused and the repository is flagged, never silently re-pinned.

## Safety

- Both apps serve the live `www/` checkout, so an edit is served without a
  rebuild. The one write that must not touch the checkout — the customer's
  module download — is bind-mounted over a scratch directory.
- `guard()` refuses to run against anything but this compose project, checked
  four ways: container names, the compose project label, and the database each
  app's config actually points at.
- `./run.sh test` clears its own leftovers first, on both sides. That is not
  cosmetic: a verified customer left over from an earlier run made "an
  unverified account cannot sign in" fail and look like a product bug.
