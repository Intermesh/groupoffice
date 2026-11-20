# Code review triage — 2026-09-21

An external review of `marketplaceserver` (27 findings). Every point was checked against
the code. This file holds the **result of that checking**, not the review itself — without
it the next session would have to grind through the same greps, or worse, implement the
findings that were rejected.

Note: when the review ran, the whole module was untracked in git, so `git diff` showed
nothing and a git-based review could not see some files — hence three false "critical"
findings. (The module is tracked now.)

## Done (session 1)

The `storeSet` wrapper is gone. `views/extjs3/Util.js` deleted (and its line in
`scripts.txt`), five calls rewritten against the core API:

- 4x delete (`ProductGrid.js:231`, `ReleaseGrid.js:207`, `CustomerGrid.js:212`,
  `EntitlementGrid.js:303`) -> `go.Db.store(X).destroy(id).catch(…)`
- 1x update (`EntitlementGrid.js:330`, `setRevoked`) -> `set({update}).then(notUpdated).catch(…)`

The handlers cover all three shapes a reject can take:
`r.message || r.description || t("Failed to …")`. Why: `Jmap.js:501` rejects a JMAP method
error as `{type, description}` with **no** `message`; `message` carries only a timeout
(`Jmap.js:519`) and `EntityStore.destroy()` itself. The core docblock above `destroy()`
(`EntityStore.js:765`) suggests `result.error.description` — that misses on all three
paths, so **do not copy it**.

Verified with `node --check`. **Not clicked through in a running instance** — how the error
dialog behaves on a real server rejection is not empirically confirmed.

## Rejected — do not implement

| Finding | Why it is wrong |
|---|---|
| `install.sql`: add `DEFAULT CHARSET=utf8mb4 COLLATE=…` | `go:schema` forbids it explicitly ("server default is authoritative"). Across `community/*` there are 33 bare `ENGINE=InnoDB` against 17 with a charset — the claim "unlike every other module" does not hold |
| 3x "critical": `storeSet` does not exist | It did (`Util.js`); the review could not see it because the module was untracked. The finding pointed the right way for the wrong reason — see Done above |
| `ProductGrid.js:128`: the datecolumn renderer is discarded | False for GO. `go/core/views/extjs3/grid/DateColumn.js:35` replaces `Ext.grid.Column.types.datecolumn` with `go.grid.DateColumn`, whose ctor does `if(!cfg.renderer)` — a custom renderer survives. **But**: that same ctor overwrites `this.width` unconditionally, so `width: dp(130)` really does not work (the review had it backwards) |
| `StripeGateway.php:195` (high) + `:173`: `(string)` cast on an expanded object | The premise is invented — "Charge.payment_intent is expanded by default in webhooks as of API basil 2025-03-31" is not true; webhooks do not expand expandable fields. The `is_string()` guard is harmless, but this is not a high-severity bug |
| `MainPanel.js:29`: `isAvailable` is deprecated | Deprecated in its docblock, yes, but with 330 uses across `go/modules` including business. Cosmetic |
| `ProductDialog.js:223`: "silently wiping the collection" | Overstated. `EntityPanel.js:148` sends `getValues(!!this.currentId)` -> on an edit only dirty fields go out, so untouched chips are never submitted. What is left is only chips that look empty after a failed options load |

## Done — all 10 confirmed findings (session 2)

The order follows the original triage. `php -l` / `node --check` on every file,
`phpunit` 62/62 green, PHPStan level 3 with no new errors (the 3 remaining messages on
`Module.php:479/647/651` are older and unrelated to this diff).

1. `Module.php:146` — `apiCustomer()` reads the header through `requestHeader()` **and
   additionally checks `$_SERVER['REDIRECT_HTTP_AUTHORIZATION']`**. The first fix
   (`requestHeader()` alone) was not enough and the review caught it:
   `Request::getHeaders()` (`go/core/http/Request.php:128`) calls `getNonApacheHeaders()`
   — the only place GO has that `REDIRECT_` fallback — **only when
   `apache_request_headers()` does not exist**. The FPM SAPI has defined that function
   since PHP 7.3 (an alias of `getallheaders()`, which maps only `HTTP_`-prefixed keys),
   so on exactly the Apache + PHP-FPM deployment that needs it, the fallback never runs.
   The integration harness (`tests/integration/endpoints.php`) resets `Request::get()`'s
   memoized headers, so the tests took this change unmodified.
2. `Module.php` — `pageSignature()` will not sign empty content: when the blob is missing
   from disk (or cannot be read) it returns 404 and logs. `pageDownload` got the same
   `exists()` condition, so both endpoints really do have the same contract.
3. `BranchChips.js` — `store.find` -> `findExact`.
4. `SettingsPanel.js` — the webhook URL comes from `GO.settings.config.full_url`
   (subdirectory installs), and the event list is rendered from `stripeEvents()`, i.e. all
   five events `mapEvent()` actually handles.
5. `SettingsPanel.js` — `onSave` no longer caches the secrets into `mod.settings` nor
   overwrites them with an empty string; `stripe*Configured` are updated and
   `afterSecretsSaved()` clears the fields and flips the hint to "Configured".
6. `install.sql` + new migration `202609211200` — `KEY (ip, createdAt)` and
   `KEY (email, createdAt)` on `reg_attempt`; the standalone `createdAt` stays for
   `prune()`. No charset.
7. `ActivityGrid.js` — the render-time load is gone (only the `viewready` -> `setThisWeek`
   path remains), and `deferEmptyText: false` was added so an empty grid does not look
   broken.
8. `ActivityGrid.js` — `autoExpandColumn: 'item'`.
9. `StripeGateway::parseWebhook()` — an unconfigured `whsec` is logged through
   `ErrorHandler::log()` with instructions; the caller still gets the same 400 as for a
   forged signature (the configuration state is never revealed).
10. `createCheckoutSession()` — `Stripe-Version` pinned by the `API_VERSION` constant.

Also picked up from the "low value" list: `EntitlementPanel.js` — `datachanged` on the
left-hand store calls the new `EntitlementGrid.clearCustomer()` when the selected customer
disappears from the list, so Add no longer stays armed for a customer nobody can see.

## Decided and done: chargeback

The user decided that a lost dispute must revoke. `mapEvent()` has a
`charge.dispute.closed` branch with `status === 'lost'` -> `ACCESS_REVOKED` via
`payment_intent`, a sibling of the refund branch. `PaymentEvent` carries a new `reason`
field (`REASON_REFUND` / `REASON_CHARGEBACK`), so `Module.php` records a chargeback as its
own `chargeback` activity type (varchar(32), no migration needed) — refunds and chargebacks
never mix in the audit trail. Covered by four new tests in `StripeMapEventTest`.

**Deployment warning:** for that branch to do anything, `charge.dispute.closed` must be
part of the webhook subscription on the Stripe side. The settings panel now lists it.

## Low value (deliberately deferred)

The `PaymentEvent` constructor (already 7 positional parameters, which is why `reason` is
assigned after construction — like `amount`/`currency`), a shared base class for the four
grids, duplicated `emptyText`, a `customerDisplayName` helper, an ISO regex on the currency
combo. `EntitlementPanel.js` dropped out of this group — it is fixed, see Done.

## New findings from the review (2026-09-21, session 2)

The review over the whole module (`/code-review high`) did not repeat the 10 confirmed
findings and looked for new ones. The finding on `Module.php:146` was checked and **fixed**
(see Done #1). **All 7 findings below were verified against the source and fixed**
(sessions 2 and 3).

| # | Where | What | State |
|---|---|---|---|
| 1 | `model/Entitlement.php:146` | `isActive()` compares `expiresAt >= time()`, but `expiresAt` is edited as a **date** (stored at midnight), so the license dies a day earlier than the date the dialog shows. `Product::isAvailable()` (`model/Product.php:137`) solves the identical case with an explicit `+86400` and a comment about it. | **Verified and FIXED** — new `lib/ExpiryDate` (`cutoff()`/`hasLapsed()`): a midnight value runs through the end of that day (+86400), a value carrying a time of day (a gateway period end) is used verbatim so a subscription never gains a free day. Called from `isActive()`, `grantFree()` and from 4 places in `Module.php` through the new `Entitlement::expiryCutoff()`, so the JWT `exp` and the catalogue show the same number. 4 tests in `ExpiryDateTest`. |
| 2 | `lib/VerificationMailer.php:48` | `send()` catches the exception from `->send()`, logs it and **returns `true`**. `controller/Customer.php:135` builds `success` from that return value, so the UI reports "Verification e-mail sent." even when SMTP failed. | **Verified and FIXED** — `send()` is now `?bool` (true = sent, false = sending failed, null = nothing to send). The public endpoints still ignore the return value (their responses must stay uniform); the admin `resendVerification` throws on `false` and reports `skipped` on `null`; `CustomerGrid.js` no longer claims "sent" for the skipped case. |
| 3 | `lib/RateLimiter.php:91` | `hitKey()` writes a row into `reg_attempt` on every `/license`, `/download`, `/signature` and `/checkout`, but `prune()` is only called opportunistically (`random_int(1,50)===1`) from `pageRegister`/`pageLogin` — and `pageRegister` returns 403 earlier when registration is closed. The table grows without bound. | **Verified and FIXED** — `prune(24)` added to the daily `MarketplaceServerReleaseSeats` cron (already registered through `ensureReleaseSeatsCron()`, so no migration is needed; renaming the class would have required one). Both chores in the cron have their own try/catch so a failure in one does not skip the other. The opportunistic call stays as a safety net for installs with the cron disabled — the comment claiming "no dedicated cron needed" is corrected. |
| 4 | `Module.php:395` | `/catalog` without `goVersion` reports `release: null` for every product, while `authorizeRelease()` in the same situation skips branch filtering and serves the highest version across all branches. An empty catalogue plus a working download for the wrong branch. | **Verified and FIXED** — without `goVersion`, `/download` and `/signature` now return 400. The other direction (falling back to the highest version in the catalogue too) would mean handing a 6.8 instance a build made for 26 and breaking its install. The real client always sends `goVersion` (`marketplace/lib/ApiClient.php:120/155/178`, from `go()->getVersion()`), so this only affects a hand-built request. |
| 5 | `model/Release.php:152`, `model/InstanceLog.php:107` | `getPermissionLevel()` overridden without `applyAclToQuery()`. A user with plain read on the module would get ids and the total through `query`. | **Verified and FIXED** — both got a manager-only `applyAclToQuery()` (copied from the `Activity` pattern), and `Product` an explicit "an authenticated user yes, nobody else". The mechanism is verified in core — see "Why core does not do this for you" below. **Not clicked through**: the module is not installed on the dev instance (`core_module` only has the `marketplace` client), so a test as `demo_alena`/`demo_cizi` could not be run without installing the module into the dev database. |
| 6 | `model/Product.php:203` (+ `Release`, `InstanceLog`, `Activity`) | The public `getPermissionLevel()` overridden instead of `internalGetPermissionLevel()`, so `Entity::EVENT_PERMISSION_LEVEL` never fires. | **Verified and FIXED** — all four renamed to `protected internalGetPermissionLevel()`. Nothing in the module called them from outside (grep), so this was purely mechanical. Admin rights are unchanged: `Module::getUserRights()` returns `adminRights()` (everything true) for an admin, so `mayManage` still holds. |
| 7 | `controller/Customer.php:99` | The return value of `$customer->save()` when stamping `verifiedAt` is discarded; the response reports success and the grid optimistically fills in `verifiedAt` even when the save failed. | **Verified and FIXED** — `if (!$customer->save()) throw` with the validation errors, the same as `$user->save()` a few lines above. |

Note: the review ran against the working tree and reported the module as untracked with an
empty `git diff` — which is why it read the whole module (~10.7k lines) instead of a diff.

### Why core does not do this for you (`applyAclToQuery`)

Core **calls** that method on every `query` — `EntityController::getQueryQuery()` ->
`applyPermissionLevelToQueryQuery()` (`EntityController.php:175`) adds
`permissionLevel => LEVEL_READ` when the client did not send one, and that filter
(`Entity.php:789`) calls `static::applyAclToQuery()`. So the call always happens.

What happens inside it depends on the base class:

- `AclOwnerEntity` / `AclItemEntity` — core has its own implementation (a join through
  `core_acl`), so the ACL really is applied and you need write nothing.
- `go\core\jmap\Entity` (which is **all 8 entities of this module**) — the base
  implementation in `go/core/orm/Entity.php:645` is `return $query;`, a no-op. There is no
  ACL table, so core has nothing to filter on.

Hence the pairing requirement in the docblock above `getPermissionLevel()`
(`Entity.php:598`): `getPermissionLevel()` guards `get`/`set` of individual records,
`applyAclToQuery()` guards `query`. Override only the first and you have a locked door and
an open window — `query` runs through the no-op and returns ids and the total.
`Customer`/`Entitlement`/`ApiToken`/`Activity` had it right from the start in this module;
`Release`/`InstanceLog` did not.
