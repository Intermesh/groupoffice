<?php

namespace go\modules\community\marketplaceserver;

use go\core;
use go\core\model\Group;
use go\modules\community\marketplaceserver\lib;
use go\modules\community\marketplaceserver\model;
use go\modules\community\marketplaceserver\model\Settings;

class Module extends core\Module
{
    /**
     * The locked-down group every self-registered customer is placed in. It is
     * a CRM/support identity only and is deliberately granted NO access to this
     * (marketplaceserver) admin module — customers never touch the server UI,
     * they act through the community/marketplace client over the public API.
     */
    const CUSTOMER_GROUP_NAME = 'Marketplace Customers';

    public function getAuthor(): string
    {
        return 'Michal Charvat <info@michalcharvat.cz>';
    }

    /**
     * Customers are backed by an addressbook Contact (via their GO user's
     * profile contact), so the CRM lives in Group-Office, not re-invented here.
     *
     * @return array<string>
     */
    public function getDependencies(): array
    {
        return ['community/addressbook'];
    }

    /**
     * @return array<string>
     */
    protected function rights(): array
    {
        return ['mayManage'];
    }

    /**
     * Ensure the locked-down "Marketplace Customers" group exists. Idempotent —
     * called from afterInstall (fresh) and an upgrade migration (existing
     * installs). The group is intentionally NOT added to this module's ACL, so
     * its members have zero access to the marketplace admin.
     *
     * @return int the group id
     * @throws \Exception
     */
    public static function ensureCustomerGroup(): int
    {
        $group = Group::find()
            ->where(['name' => self::CUSTOMER_GROUP_NAME, 'isUserGroupFor' => null])
            ->single();
        if ($group) {
            return (int) $group->id;
        }
        $group = new Group();
        $group->name = self::CUSTOMER_GROUP_NAME;
        if (!$group->save()) {
            throw new \Exception('Could not create the marketplace customer group: ' . $group->getValidationErrorsAsString());
        }
        return (int) $group->id;
    }

    /**
     * @return \go\core\Settings|\go\modules\community\marketplaceserver\model\Settings|null
     */
    public function getSettings(): ?\go\core\Settings
    {
        return Settings::get();
    }

    /**
     * Generate the RS256 keypair on install so /info can serve the public key
     * immediately.
     *
     * @param \go\core\model\Module $model
     * @return bool
     */
    protected function afterInstall(\go\core\model\Module $model): bool
    {
        Settings::get()->ensureKeyPair();
        self::ensureCustomerGroup();
        self::ensureReleaseSeatsCron((int) $model->id);
        return parent::afterInstall($model);
    }

    /**
     * Register the daily seat-release cron. Idempotent — called from afterInstall
     * (fresh installs) and an upgrade migration (existing installs), matched by
     * (moduleId, name) so it is never duplicated.
     *
     * @param int $moduleId
     * @return void
     * @throws \Exception
     */
    public static function ensureReleaseSeatsCron(int $moduleId): void
    {
        $name = 'MarketplaceServerReleaseSeats';
        $existing = \go\core\model\CronJobSchedule::find()
            ->where(['moduleId' => $moduleId, 'name' => $name])->single();
        if ($existing) {
            return;
        }
        $cron = new \go\core\model\CronJobSchedule();
        $cron->moduleId = $moduleId;
        $cron->name = $name;
        $cron->expression = '30 4 * * *';    // daily 04:30 (after the client refresh at 04:00)
        $cron->description = go()->t('Release inactive marketplace instance seats', 'community', 'marketplaceserver');
        $cron->enabled = true;
        if (!$cron->save()) {
            throw new \Exception('Failed to save seat-release cron: ' . var_export($cron->getValidationErrors(), true));
        }
    }

    // ------------------------------------------------------------------
    // Public REST API (via /api/page.php/community/marketplaceserver/...)
    // ------------------------------------------------------------------

    /**
     * Authenticate the Bearer token; on failure emits 401 JSON and exits.
     * Touches lastUsedAt.
     *
     * @return \go\modules\community\marketplaceserver\model\Customer
     * @throws \Exception
     */
    private function apiCustomer(): model\Customer
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if ($header === null && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $header = $headers['Authorization'] ?? ($headers['authorization'] ?? null);
        }
        $plain = lib\TokenAuth::parseBearer($header);
        if ($plain !== null) {
            $token = model\ApiToken::find()
                ->where(['tokenHash' => lib\TokenAuth::hash($plain), 'revoked' => false])
                ->single();
            if ($token) {
                $customer = model\Customer::findById((string) $token->customerId);
                if ($customer) {
                    // Security: the token is issued at registration but the whole
                    // customer API must stay inert until the account's e-mail is
                    // verified (the user is enabled). Without this the "disabled
                    // until verified" rule would only gate server login — a path
                    // customers never take — and a token minted for an unverified
                    // (possibly squatted) e-mail would work immediately. Also
                    // instantly cuts off any account an admin later disables.
                    $user = \go\core\model\User::findById((int) $customer->userId, ['enabled']);
                    if ($user && $user->enabled) {
                        $token->lastUsedAt = new \go\core\util\DateTime();
                        $token->save();
                        return $customer;
                    }
                    // The token is genuine (only the registrant ever received it),
                    // so it's safe — and far more useful — to say WHY it's inert
                    // instead of a blanket "invalid token". Distinguish "never
                    // verified" (actionable: verify your e-mail) from "verified but
                    // an admin disabled it" (actionable: contact support).
                    if ($customer->verifiedAt === null) {
                        $this->jsonOut([
                            'code' => 'verifyRequired',
                            'error' => 'Your account is not verified yet. Please open the verification link we e-mailed you (or request a new one).',
                        ], 403);
                    } else {
                        $this->jsonOut([
                            'code' => 'disabled',
                            'error' => 'This account has been disabled. Please contact support.',
                        ], 403);
                    }
                    exit;
                }
            }
        }
        $this->jsonOut(['code' => 'invalidToken', 'error' => 'Invalid or missing API token'], 401);
        exit;
    }

    /**
     * @param mixed $data
     * @param int $status
     * @return void
     */
    private function jsonOut($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json;charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Per-IP + per-email rate-limit check for the public auth endpoints. Returns
     * true when the caller is over the limit.
     *
     * @param string $ip
     * @param string $email
     * @return bool
     */
    private function rateLimited(string $ip, string $email): bool
    {
        try {
            // Normalise the e-mail so case/whitespace variants (and the same
            // address reached via username) share ONE bucket — the account is
            // looked up case-insensitively, so the per-email cap must be too.
            return !lib\RateLimiter::hit($ip, mb_strtolower(trim($email)));
        } catch (\Throwable $e) {
            // Never leak a ledger/DB error to an unauthenticated caller (the
            // generic page.php handler would echo it). Fail open — the per-IP cap
            // and the client-token gate still apply.
            \go\core\ErrorHandler::logException($e);
            return false;
        }
    }

    /**
     * Cap the (authenticated) download + signature endpoints per customer. Each
     * call streams a whole package or reads its bytes and RSA-signs them (CPU +
     * IO), so an authenticated but abusive client must not be able to hammer them
     * unbounded. `/download` and `/signature` share one bucket (a normal download
     * makes one of each) generously sized so a legitimate multi-module refresh is
     * never blocked. Fail OPEN on a ledger error — never break a paid customer's
     * download over a housekeeping table.
     *
     * @param \go\modules\community\marketplaceserver\model\Customer $customer
     * @return void
     */
    private function throttleDownload(model\Customer $customer): void
    {
        try {
            if (!lib\RateLimiter::hitKey('dl:' . $customer->id, 300, 60)) {
                $this->jsonOut(['error' => 'Too many download requests. Please try again later.'], 429);
                exit;
            }
        } catch (\Throwable $e) {
            \go\core\ErrorHandler::logException($e);
        }
    }

    /**
     * Active entitlements of a customer mapped to LicenseBuilder input shape.
     *
     * @param \go\modules\community\marketplaceserver\model\Customer $customer
     * @return array<array{type: string, modules: array<string>, expiresAt: int|null}>
     * @throws \Exception
     */
    private function entitlementRows(model\Customer $customer): array
    {
        $rows = [];
        $entitlements = model\Entitlement::find()->where(['customerId' => $customer->id]);
        foreach ($entitlements as $e) {
            if ($e->isRevoked()) {
                continue;   // kill switch: a revoked grant licenses nothing
            }
            $product = model\Product::findById((string) $e->productId);
            if (!$product || !$product->active) {
                continue;
            }
            $modules = [];
            if ($product->type === model\Product::TYPE_MODULE && $product->moduleName) {
                $modules = [$product->moduleName];
            } elseif ($product->type === model\Product::TYPE_COLLECTION) {
                $modules = $product->modules;
            }
            $rows[] = [
                'type' => $product->type,
                'modules' => $modules,
                'expiresAt' => $e->expiresAt ? $e->expiresAt->getTimestamp() : null,
            ];
        }
        return $rows;
    }

    /**
     * The effective license expiry for an OWNED product, read from the resolved
     * `licenses` map (unix ts | null = perpetual). For a module: the key's expiry.
     * For a collection: the EARLIEST member expiry (it stops being covered when
     * its first member lapses); null when every member is perpetual.
     *
     * @param \go\modules\community\marketplaceserver\model\Product $p
     * @param string $package
     * @param array<string, array{expiresAt: int|null}> $licenses
     * @return int|null
     */
    private function effectiveExpiry(model\Product $p, string $package, array $licenses)
    {
        if ($p->type === model\Product::TYPE_MODULE) {
            $key = $package . '/' . $p->moduleName;
            return isset($licenses[$key]) ? $licenses[$key]['expiresAt'] : null;
        }
        if ($p->type === model\Product::TYPE_COLLECTION && $p->modules) {
            $min = null;
            foreach ($p->modules as $m) {
                $key = $package . '/' . $m;
                $e = isset($licenses[$key]) ? $licenses[$key]['expiresAt'] : null;
                if ($e !== null) {
                    $min = ($min === null) ? $e : min($min, $e);
                }
            }
            return $min;
        }
        return null;
    }

    /**
     * The first active FREE collection (price null/0) whose member modules
     * include the given module, or null. A free collection makes its members
     * downloadable regardless of the members' own price.
     *
     * @param string $moduleName
     * @return \go\modules\community\marketplaceserver\model\Product|null
     * @throws \Exception
     */
    private function freeCollectionContaining(string $moduleName): ?model\Product
    {
        $collections = model\Product::find()->where([
            'type' => model\Product::TYPE_COLLECTION,
            'active' => true,
        ]);
        foreach ($collections as $col) {
            $free = $col->price === null || (float) $col->price == 0.0;
            if ($free && in_array($moduleName, $col->modules ?? [], true)) {
                return $col;
            }
        }
        return null;
    }

    /**
     * GET /info — package name + public key. Requires a valid token.
     *
     * @return void
     * @throws \Exception
     */
    public function pageInfo(): void
    {
        $this->apiCustomer();
        $settings = model\Settings::get();
        $settings->ensureKeyPair();
        $this->jsonOut([
            'package' => $settings->packageName,
            'name' => go()->getSettings()->title,
            'publicKey' => $settings->publicKey,
        ]);
    }

    /**
     * GET /catalog — products with owned/purchasable state for this customer.
     *
     * @return void
     * @throws \Exception
     */
    public function pageCatalog(): void
    {
        $customer = $this->apiCustomer();
        $settings = model\Settings::get();
        $package = $settings->packageName;

        $licenses = lib\LicenseBuilder::resolveLicenses($package, $this->entitlementRows($customer));

        // Modules are branch-specific. The client sends its running GO version
        // (e.g. "6.8.175"); we match releases by branch on a dot boundary (see
        // Release::branchMatches) so both "6.8" and year-based schemes work.
        $goVersion = trim((string) ($_GET['goVersion'] ?? ''));

        $latest = [];          // moduleName => latest release matching the client's branch
        $availability = [];    // moduleName => [branch => latest version] across ALL branches
        foreach (model\Release::find()->where(['active' => true]) as $r) {
            // Cross-branch availability: the highest version per (module, branch).
            // Lets the client show "is there a build for 6.8 / 25 / 26?" so an
            // operator can see, before upgrading GO, whether every module they
            // use already has a build for the target branch.
            $curAv = $availability[$r->moduleName][$r->goVersion] ?? null;
            if ($curAv === null || version_compare($r->version, $curAv) > 0) {
                $availability[$r->moduleName][$r->goVersion] = $r->version;
            }
            // The release to offer for download NOW: latest matching this client's
            // own branch.
            if ($goVersion !== '' && model\Release::branchMatches($r->goVersion, $goVersion)) {
                $cur = $latest[$r->moduleName] ?? null;
                if ($cur === null || version_compare($r->version, $cur['version']) > 0) {
                    $latest[$r->moduleName] = [
                        'version' => $r->version,
                        'goVersion' => $r->goVersion,
                        'changelog' => $r->changelog,
                    ];
                }
            }
        }

        // Absolute base URL to THIS server, so the client browser (on another
        // instance) can load product logos directly via <img src>.
        $base = rtrim((string) (go()->getSettings()->URL ?? ''), '/');

        // License expiry per PRODUCT the customer actually holds an entitlement for
        // (INCLUDING expired ones — LicenseBuilder drops expired grants, but the
        // client still wants to show "license expired on X / expires on X"). Keyed
        // by productId so the indicator reflects the product's OWN grant — a
        // collection only shows expiry if the customer really owns that collection,
        // not because its member modules were bought individually. productId =>
        // unix ts (null = perpetual).
        $now = time();
        $entExpiryByProduct = [];
        foreach (model\Entitlement::find()->where(['customerId' => $customer->id]) as $e) {
            if ($e->isRevoked()) {
                continue;   // a revoked grant is neither owned nor an expiry nudge
            }
            $exp = $e->expiresAt ? $e->expiresAt->getTimestamp() : null;
            if (!array_key_exists($e->productId, $entExpiryByProduct)) {
                $entExpiryByProduct[$e->productId] = $exp;
            } elseif ($entExpiryByProduct[$e->productId] !== null && ($exp === null || $exp > $entExpiryByProduct[$e->productId])) {
                $entExpiryByProduct[$e->productId] = $exp; // keep the most generous (latest / perpetual)
            }
        }

        $products = [];
        foreach (model\Product::find()->where(['active' => true])->orderBy(['sortOrder' => 'ASC']) as $p) {
            $owned = ($p->type === model\Product::TYPE_MODULE && array_key_exists($package . '/' . $p->moduleName, $licenses))
                || ($p->type === model\Product::TYPE_COLLECTION && $p->modules
                    && !array_diff(array_map(fn($m) => $package . '/' . $m, $p->modules), array_keys($licenses)));
            // Retired product (past its availability window): drop it from the
            // catalog for new customers, but keep showing it to existing owners so
            // they can still re-download / update.
            if (!$p->isAvailable() && !$owned) {
                continue;
            }
            // A product with no price is free: downloadable by any registered
            // customer without an entitlement (see pageDownload).
            $free = $p->price === null || (float) $p->price == 0.0;
            // License indicator. Prefer the EFFECTIVE license (the resolved
            // `licenses` map, which merges every grant — direct or via a
            // collection — and drops expired ones) so a module covered by a
            // (perpetual) collection isn't shown "expired" just because its own
            // individual grant lapsed. Only when nothing actively covers the
            // product do we fall back to its own lapsed entitlement to flag
            // "expired" as a renewal nudge.
            $licenseExpiresAt = null;
            $licenseExpired = false;
            if ($owned) {
                $licenseExpiresAt = $this->effectiveExpiry($p, $package, $licenses);
            } else {
                $ownExp = $entExpiryByProduct[$p->id] ?? false;
                if ($ownExp !== false && $ownExp !== null && $ownExp < $now) {
                    $licenseExpired = true;
                    $licenseExpiresAt = $ownExp;
                }
            }
            $products[] = [
                'id' => $p->id,
                'type' => $p->type,
                'moduleName' => $p->moduleName,
                'modules' => $p->modules,
                'title' => $p->title,
                'description' => $p->description,
                'price' => $p->price,
                'currency' => $p->currency,
                'free' => $free,
                'owned' => $owned,
                'licenseExpiresAt' => $licenseExpiresAt,
                'licenseExpired' => $licenseExpired,
                'availableUntil' => $p->availableUntil ? $p->availableUntil->getTimestamp() : null,
                'logoUrl' => (!empty($p->logoBlobId) && $base !== '')
                    ? ($base . '/api/page.php/community/marketplaceserver/productLogo/' . $p->id)
                    : null,
                'release' => $p->moduleName ? ($latest[$p->moduleName] ?? null) : null,
                // branch => latest version, for the cross-branch availability UI.
                // Only module products carry releases; collections/subscriptions
                // get an empty map (their members appear as their own products).
                'availability' => ($p->moduleName && isset($availability[$p->moduleName]))
                    ? (object) $availability[$p->moduleName]
                    : (object) [],
            ];
        }

        $this->jsonOut([
            'package' => $package,
            'products' => $products,
            // Every branch this marketplace publishes for, so the client can show
            // a stable set of availability columns even for branches with no build.
            'branches' => $settings->getGoBranches(),
        ]);
    }

    /**
     * GET /productLogo/{id} — stream a product's logo image so the client catalog
     * card can show it via <img src>. PUBLIC (no token): a logo is a marketing
     * asset and the browser fetches it without the Bearer header. Reveals only
     * the logo of an active-or-not product by id — nothing sensitive.
     *
     * @param string $id
     * @return void
     * @throws \Exception
     */
    public function pageProductLogo(string $id = ''): void
    {
        $product = model\Product::findById((int) $id);
        if (!$product || empty($product->logoBlobId)) {
            http_response_code(404);
            return;
        }
        $blob = \go\core\fs\Blob::findById($product->logoBlobId);
        if (!$blob) {
            http_response_code(404);
            return;
        }
        $blob->output(true); // inline image
    }

    /**
     * GET /license?hostname=... — signed license JWT. Logs the instance.
     *
     * @return void
     * @throws \Exception
     */
    public function pageLicense(): void
    {
        $customer = $this->apiCustomer();

        // Throttle license (re-)issuance per customer: each call RSA-signs a JWT
        // (CPU) and writes InstanceLog. The daily cron + occasional manual refresh
        // stay well under this; a runaway/abusive client is capped. Fail open on a
        // ledger error — never break licensing over a housekeeping table.
        try {
            if (!lib\RateLimiter::hitKey('lic:' . $customer->id, 120, 60)) {
                $this->jsonOut(['error' => 'Too many license requests. Please try again later.'], 429);
                return;
            }
        } catch (\Throwable $e) {
            \go\core\ErrorHandler::logException($e);
        }

        // Bind the license to a single concrete host. Reject wildcards / lists /
        // empty: the client sends its own running host, and a value like "*" would
        // yield a JWT the verifier treats as valid on EVERY host — one paid
        // entitlement redistributable to unlimited instances.
        $hostname = trim((string) ($_GET['hostname'] ?? ''));
        if (!lib\HostnameValidator::isValid($hostname)) {
            $this->jsonOut(['error' => 'A single valid hostname is required'], 400);
            return;
        }

        $settings = model\Settings::get();
        $settings->ensureKeyPair();

        // Apply per-entitlement instance binding for THIS host: seat-mode grants
        // obey the customer's seat pool, hostname-mode grants their own pin.
        [$rows, $seatGranted] = $this->bindingRowsForHost($customer, $hostname);
        $licenses = lib\LicenseBuilder::resolveLicenses($settings->packageName, $rows);
        $jwt = lib\LicenseBuilder::build(
            go()->getSettings()->URL ?? '',
            (int) $customer->id,
            $hostname,
            $settings->packageName,
            $licenses,
            $settings->decryptPrivateKey()
        );

        $log = model\InstanceLog::find()->where(['customerId' => $customer->id, 'hostname' => $hostname])->single();
        if (!$log) {
            $log = new model\InstanceLog();
            $log->customerId = $customer->id;
            $log->hostname = $hostname;
        }
        $log->lastSeenAt = new \go\core\util\DateTime();
        // Mark whether this host holds a seat, so it counts toward the seat pool
        // (a host with only hostname-mode licenses is logged but consumes none).
        $log->consumesSeat = $seatGranted;
        $log->save();

        $this->jsonOut(['license' => $jwt]);
    }

    /**
     * Default days without a check-in after which a seat is considered free again,
     * so staging/migration/failover naturally release seats. Admin-configurable
     * via {@see model\Settings::$seatActivityDays}; this is only the fallback.
     */
    const SEAT_ACTIVITY_DAYS = 7;

    /**
     * Build the LicenseBuilder entitlement rows for $host with per-entitlement
     * instance binding applied, and report whether the host was granted a seat.
     *
     * Seat-mode grants share the customer's seat pool (Customer::maxInstances,
     * 0 = unlimited); a host over the limit gets those modules withheld. Hostname-
     * mode grants are licensed only on their pinned host (pinned on first use) and
     * never touch the seat pool.
     *
     * @param \go\modules\community\marketplaceserver\model\Customer $customer
     * @param string $host the validated requesting hostname
     * @return array{0: array<array{type:string,modules:array<string>,expiresAt:int|null,permitted:bool}>, 1: bool}
     * @throws \Exception
     */
    private function bindingRowsForHost(model\Customer $customer, string $host): array
    {
        // Seat picture: distinct OTHER hosts currently holding a seat within the
        // activity window, and whether THIS host already holds one.
        $activityDays = model\Settings::get()->getSeatActivityDays();
        $cutoff = (new \DateTime())->sub(new \DateInterval('P' . $activityDays . 'D'))->getTimestamp();
        $activeOtherSeats = 0;
        $hostHoldsSeat = false;
        foreach (model\InstanceLog::find()->where(['customerId' => $customer->id]) as $log) {
            if (empty($log->consumesSeat) || $log->lastSeenAt === null
                || $log->lastSeenAt->getTimestamp() < $cutoff) {
                continue;
            }
            if ($log->hostname === $host) {
                $hostHoldsSeat = true;
            } else {
                $activeOtherSeats++;      // InstanceLog is unique per (customer, host) → already distinct
            }
        }
        $seatAllowed = lib\SeatPolicy::allows($activeOtherSeats, $hostHoldsSeat, (int) $customer->maxInstances);

        $rows = [];
        $seatGranted = false;
        foreach (model\Entitlement::find()->where(['customerId' => $customer->id]) as $e) {
            if ($e->isRevoked()) {
                continue;   // kill switch: a revoked grant licenses nothing
            }
            $product = model\Product::findById((string) $e->productId);
            if (!$product || !$product->active) {
                continue;
            }
            $modules = [];
            if ($product->type === model\Product::TYPE_MODULE && $product->moduleName) {
                $modules = [$product->moduleName];
            } elseif ($product->type === model\Product::TYPE_COLLECTION) {
                $modules = $product->modules ?? [];
            }
            if (empty($modules)) {
                continue;
            }

            if ($e->bindingMode === model\Entitlement::BINDING_HOSTNAME) {
                $bound = ($e->boundHostname === null || $e->boundHostname === '')
                    ? model\Entitlement::pinHostname((int) $e->id, $host)   // trust on first use
                    : $e->boundHostname;
                $permitted = ($bound === $host);
            } else {
                $permitted = $seatAllowed;
                if ($permitted) {
                    $seatGranted = true;
                }
            }

            $rows[] = [
                'type' => $product->type,
                'modules' => $modules,
                'expiresAt' => $e->expiresAt ? $e->expiresAt->getTimestamp() : null,
                'permitted' => $permitted,
            ];
        }

        return [$rows, $seatGranted];
    }

    /**
     * GET /download/{module}/{version?} — ZIP stream, entitlement-gated.
     *
     * @param string $moduleName
     * @param string $version
     * @return void
     * @throws \Exception
     */
    public function pageDownload(string $moduleName = '', string $version = ''): void
    {
        $customer = $this->apiCustomer();
        $this->throttleDownload($customer);
        if ($moduleName === '') {
            $this->jsonOut(['error' => 'module name is required'], 400);
            return;
        }

        [$release, $grantProductId] = $this->authorizeRelease($customer, $moduleName, $version);

        // A free acquisition is still a licensed grant — record it so it shows in
        // the customer's "My account" (and lets it count toward collection
        // ownership). Idempotent; skipped when already licensed.
        if ($grantProductId) {
            model\Entitlement::grantFree($customer->id, $grantProductId);
        }

        $blob = \go\core\fs\Blob::findById($release->blobId);
        if (!$blob) {
            $this->jsonOut(['error' => 'Package blob missing'], 404);
            return;
        }

        // Audit the download BEFORE streaming (output() ends the request).
        model\Activity::record(model\Activity::TYPE_DOWNLOAD, [
            'customerId' => (int) $customer->id,
            'moduleName' => $moduleName,
            'version' => $release->version,
            'ip' => $this->clientIp(),
        ]);

        // Blob::output() hardcodes Content-Type/Content-Disposition from the blob's
        // own type/name; we need a custom filename ("{module}-{version}.zip"), so
        // stream via File::output() directly with our own headers. This still gets
        // range-request support, chunked streaming and cache headers for free.
        $blob->getFile()->output(true, true, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $moduleName . '-' . $release->version . '.zip"',
        ]);
    }

    /**
     * GET /signature/{module}/{version?} — base64 RS256/SHA-256 signature over the
     * exact ZIP bytes /download serves, so the client can verify the package
     * against its pinned public key BEFORE extracting (a compromised/spoofed
     * server can't deliver arbitrary PHP to be unpacked and executed). Same
     * entitlement gate + branch resolution as /download.
     *
     * @param string $moduleName
     * @param string $version
     * @return void
     * @throws \Exception
     */
    public function pageSignature(string $moduleName = '', string $version = ''): void
    {
        $customer = $this->apiCustomer();
        $this->throttleDownload($customer);
        if ($moduleName === '') {
            $this->jsonOut(['error' => 'module name is required'], 400);
            return;
        }

        [$release, ] = $this->authorizeRelease($customer, $moduleName, $version);

        $blob = \go\core\fs\Blob::findById($release->blobId);
        if (!$blob) {
            $this->jsonOut(['error' => 'Package blob missing'], 404);
            return;
        }

        $settings = model\Settings::get();
        $settings->ensureKeyPair();
        $bytes = (string) file_get_contents($blob->getFile()->getPath());

        $this->jsonOut([
            'signature' => lib\PackageSigner::sign($bytes, $settings->decryptPrivateKey()),
            'algorithm' => lib\PackageSigner::ALGORITHM,
            'version' => $release->version,
        ]);
    }

    /**
     * Authorise a download of $moduleName (optionally pinned to $version) for the
     * customer and resolve the concrete Release to serve — the shared gate for
     * both /download and /signature so they can never diverge. On any failure this
     * emits the JSON error and exits (same contract as apiCustomer()).
     *
     * A module is downloadable when:
     *   1. it's free itself (module with no price, still within its window), or
     *   2. it's licensed — a module/collection entitlement covering it, or
     *   3. it belongs to a FREE collection (bundle is free → members downloadable).
     *
     * @param \go\modules\community\marketplaceserver\model\Customer $customer
     * @param string $moduleName
     * @param string $version pinned version, or '' for the latest matching branch
     * @return array{0: \go\modules\community\marketplaceserver\model\Release, 1: int|null}
     *   [release to serve, product id whose FREE grant to record | null if licensed]
     * @throws \Exception
     */
    private function authorizeRelease(model\Customer $customer, string $moduleName, string $version): array
    {
        // Server-side name safety, mirroring the client's own check: moduleName
        // comes straight off the URL path and later lands in a Content-Disposition
        // header and DB lookups. Reject anything that isn't a plain module folder
        // name before it is used anywhere.
        if (!preg_match('/^[a-z0-9_]+$/i', $moduleName)) {
            $this->jsonOut(['error' => 'Invalid module name'], 400);
            exit;
        }

        $package = model\Settings::get()->packageName;

        $product = model\Product::find()
            ->where(['moduleName' => $moduleName, 'active' => true])->single();
        // A free product is freely acquirable only while still available (within
        // its window). Past that, a NEW customer can't grab it; an existing owner
        // still passes via the licensed path below.
        $isFree = $product
            && $product->isAvailable()
            && ($product->price === null || (float) $product->price == 0.0);

        $allowed = $isFree;
        // Product whose free acquisition to record (null when already licensed).
        $grantProductId = ($isFree && $product) ? $product->id : null;

        if (!$allowed) {
            $licenses = lib\LicenseBuilder::resolveLicenses($package, $this->entitlementRows($customer));
            if (array_key_exists($package . '/' . $moduleName, $licenses)) {
                $allowed = true;
            } else {
                $freeCollection = $this->freeCollectionContaining($moduleName);
                if ($freeCollection) {
                    $allowed = true;
                    $grantProductId = $freeCollection->id;
                }
            }
        }

        if (!$allowed) {
            $this->jsonOut(['error' => 'No entitlement for this module'], 403);
            exit;
        }

        $query = model\Release::find()->where(['moduleName' => $moduleName, 'active' => true]);
        if ($version !== '') {
            $query->andWhere(['version' => $version]);
        }
        // Serve only a release matching the client's GO branch (dot-boundary match
        // — see Release::branchMatches). The client sends its own running version.
        $goVersion = trim((string) ($_GET['goVersion'] ?? ''));
        $release = null;
        foreach ($query as $r) {
            if ($goVersion !== '' && !model\Release::branchMatches($r->goVersion, $goVersion)) {
                continue;
            }
            if ($release === null || version_compare($r->version, $release->version) > 0) {
                $release = $r;
            }
        }
        if (!$release) {
            $this->jsonOut(['error' => 'Release not found'], 404);
            exit;
        }

        return [$release, $grantProductId];
    }

    /**
     * POST /register — public self-registration. Gated by a static
     * `X-Marketplace-Client` header (shipped in the client build) + a per-IP /
     * per-e-mail rate limit. Creates a LOCKED account (disabled until verified,
     * in the zero-access customer group), issues a verification e-mail, and
     * returns an API token so the client can start immediately.
     *
     * @return void
     * @throws \Exception
     */
    public function pageRegister(): void
    {
        $settings = model\Settings::get();
        if (empty($settings->registrationEnabled)) {
            $this->jsonOut(['error' => 'Registration is closed'], 403);
            return;
        }
        // Static client-build token gate (weak by design: raises the bar vs bots,
        // is not authentication — real protection is verify + rate-limit + the
        // locked account).
        if (!$settings->acceptsClientToken($this->requestHeader('X-Marketplace-Client'))) {
            $this->jsonOut(['error' => 'Forbidden'], 403);
            return;
        }

        // SECURITY: rate-limit on the transport IP. X-Forwarded-For is
        // attacker-controlled, so it is honoured ONLY when REMOTE_ADDR is a
        // configured trusted proxy (Settings::trustedProxies) — otherwise a caller
        // could forge a fresh IP per request and defeat the per-IP cap. See ClientIp.
        $ip = $this->clientIp();
        $email = trim((string) ($_POST['email'] ?? ''));
        $name = trim((string) ($_POST['name'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $company = trim((string) ($_POST['companyName'] ?? ''));

        if ($this->rateLimited($ip, $email)) {
            $this->jsonOut(['error' => 'Too many attempts. Please try again later.'], 429);
            return;
        }
        // Opportunistic housekeeping so the attempt ledger can't grow unbounded
        // (no dedicated cron needed for a low-volume endpoint).
        if (random_int(1, 50) === 1) {
            try { lib\RateLimiter::prune(24); } catch (\Throwable $e) { /* best effort */ }
        }

        try {
            $res = lib\Registrar::register($email, $name, $password, $company !== '' ? $company : null);
        } catch (lib\RegistrationException $e) {
            if ($e->reason === lib\RegistrationException::DUPLICATE) {
                // Non-enumerating message: re-issue a verification mail to the
                // existing *unverified* account and report the same
                // "check your e-mail" shape, so registration never reveals whether
                // an account already exists.
                $existing = \go\core\model\User::find()
                    ->where(['email' => $email])->orWhere(['username' => $email])->single();
                if ($existing && !$existing->enabled) {
                    try { lib\VerificationMailer::send($existing); }
                    catch (\Throwable $x) { \go\core\ErrorHandler::logException($x); }
                }
                $this->jsonOut(['verifyRequired' => true], 200);
                return;
            }
            $this->jsonOut(['error' => $e->getMessage()], 422);
            return;
        } catch (\Throwable $e) {
            // Never reflect internal error detail to an unauthenticated caller.
            \go\core\ErrorHandler::logException($e);
            $this->jsonOut(['error' => 'Registration failed. Please try again later.'], 500);
            return;
        }

        model\Activity::record(model\Activity::TYPE_REGISTER, [
            'customerId' => isset($res['customer']) ? (int) $res['customer']->id : null,
            'ip' => $ip,
        ]);

        // User is created; a mail/token hiccup here must not 500 the whole call
        // (the account exists and verification can be re-requested).
        try {
            lib\VerificationMailer::send($res['user']);
        } catch (\Throwable $e) {
            \go\core\ErrorHandler::logException($e);
        }

        // Uniform response — NO token. Returning the freshly-minted token here
        // (while the duplicate path returns none) would leak account existence: a
        // caller could tell "new" from "already registered" by the token's
        // presence. The token is inert until verified anyway; the client tells the
        // user to verify their e-mail and then sign in (login issues a token).
        $this->jsonOut(['verifyRequired' => true]);
    }

    /**
     * POST /login — password login for an EXISTING customer, returning a fresh
     * API token (the old token is only a hash server-side, so it can't be
     * returned). Same gates as register: X-Marketplace-Client + rate limit.
     * Failures are generic (no e-mail-vs-password enumeration).
     *
     * @return void
     * @throws \Exception
     */
    public function pageLogin(): void
    {
        $settings = model\Settings::get();
        if (!$settings->acceptsClientToken($this->requestHeader('X-Marketplace-Client'))) {
            $this->jsonOut(['error' => 'Forbidden'], 403);
            return;
        }

        $ip = $this->clientIp();
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($this->rateLimited($ip, $email)) {
            $this->jsonOut(['error' => 'Too many attempts. Please try again later.'], 429);
            return;
        }
        if (random_int(1, 50) === 1) {
            try { lib\RateLimiter::prune(24); } catch (\Throwable $e) { /* best effort */ }
        }

        try {
            $res = lib\Authenticator::login($email, $password);
        } catch (lib\AuthException $e) {
            if ($e->reason === lib\AuthException::NOT_VERIFIED) {
                $this->jsonOut(['code' => 'verifyRequired', 'error' => $e->getMessage()], 403);
                return;
            }
            if ($e->reason === lib\AuthException::DISABLED) {
                $this->jsonOut(['code' => 'disabled', 'error' => $e->getMessage()], 403);
                return;
            }
            // INVALID → generic, never says whether the e-mail or password was wrong.
            $this->jsonOut(['error' => 'Invalid login credentials'], 401);
            return;
        } catch (\Throwable $e) {
            \go\core\ErrorHandler::logException($e);
            $this->jsonOut(['error' => 'Login failed. Please try again later.'], 500);
            return;
        }

        $this->jsonOut(['token' => $res['token']]);
    }

    /**
     * POST /resend — re-send the verification e-mail for an unverified account.
     * Uniform response regardless of whether the account exists / its state, so
     * it can't probe which e-mails are registered. Gated + rate-limited.
     *
     * @return void
     * @throws \Exception
     */
    public function pageResend(): void
    {
        $settings = model\Settings::get();
        if (!$settings->acceptsClientToken($this->requestHeader('X-Marketplace-Client'))) {
            $this->jsonOut(['error' => 'Forbidden'], 403);
            return;
        }

        $ip = $this->clientIp();
        $email = trim((string) ($_POST['email'] ?? ''));

        if ($this->rateLimited($ip, $email)) {
            $this->jsonOut(['error' => 'Too many attempts. Please try again later.'], 429);
            return;
        }

        try {
            $user = \go\core\model\User::find()
                ->where(['email' => $email])->orWhere(['username' => $email])->single();
            if ($user && !$user->enabled) {
                // Customer is ACL-scoped, so read verifiedAt under a system state
                // (an unauthenticated find would return nothing). Only (re)send
                // for accounts that were NEVER verified — a disabled-after-
                // verification account is an admin action, not a pending verify.
                $prev = go()->getAuthState();
                go()->setAuthState((new \go\core\auth\TemporaryState())->setUserId(1));
                try {
                    $customer = model\Customer::find()->where(['userId' => (int) $user->id])->single();
                } finally {
                    go()->setAuthState($prev ?? new \go\core\auth\TemporaryState());
                }
                if (!$customer || $customer->verifiedAt === null) {
                    lib\VerificationMailer::send($user);
                }
            }
        } catch (\Throwable $e) {
            \go\core\ErrorHandler::logException($e);
        }

        $this->jsonOut(['ok' => true]);
    }

    /**
     * GET /verify?token=... — activate an account from the e-mail link.
     *
     * @return void
     * @throws \Exception
     */
    public function pageVerify(): void
    {
        $user = model\EmailVerification::redeem((string) ($_GET['token'] ?? ''));
        if ($user) {
            // Customer is ACL-scoped; this endpoint is unauthenticated, so read it
            // under a system state (as pageResend does) to attribute the log entry.
            $prev = go()->getAuthState();
            go()->setAuthState((new \go\core\auth\TemporaryState())->setUserId(1));
            try {
                $customer = model\Customer::find()->where(['userId' => (int) $user->id])->single();
            } finally {
                go()->setAuthState($prev ?? new \go\core\auth\TemporaryState());
            }
            model\Activity::record(model\Activity::TYPE_VERIFY, [
                'customerId' => $customer ? (int) $customer->id : null,
            ]);
        }
        header('Content-Type: text/html;charset=utf-8');
        if ($user) {
            echo '<!doctype html><meta charset="utf-8"><title>Account verified</title>'
                . '<body style="font-family:sans-serif;max-width:32rem;margin:4rem auto;text-align:center">'
                . '<h1>Account verified ✓</h1><p>Your marketplace account is active. '
                . 'You can now sign in from the marketplace client.</p></body>';
        } else {
            http_response_code(400);
            echo '<!doctype html><meta charset="utf-8"><title>Invalid link</title>'
                . '<body style="font-family:sans-serif;max-width:32rem;margin:4rem auto;text-align:center">'
                . '<h1>Invalid or expired link</h1><p>Please request a new verification e-mail.</p></body>';
        }
    }

    /**
     * GET /account — the authenticated customer's own entitlements + profile,
     * so the client can render "My account" without any server login.
     *
     * @return void
     * @throws \Exception
     */
    public function pageAccount(): void
    {
        $customer = $this->apiCustomer();
        $now = time();
        $rows = [];
        foreach (model\Entitlement::find()->where(['customerId' => $customer->id]) as $e) {
            $product = model\Product::findById((string) $e->productId);
            if (!$product) {
                continue;
            }
            $exp = $e->expiresAt ? $e->expiresAt->getTimestamp() : null;
            $rows[] = [
                'product' => $product->title,
                'type' => $product->type,
                'expiresAt' => $exp,
                'expired' => $exp !== null && $exp < $now,
                'revoked' => $e->isRevoked(),
            ];
        }
        $this->jsonOut([
            'companyName' => $customer->companyName,
            'entitlements' => $rows,
        ]);
    }

    /**
     * POST /checkout — start a hosted payment for a product and return the redirect
     * URL. Token-authenticated (the buyer's instance), rate-limited. The active
     * gateway embeds the customer + product ids in the session so /paymentWebhook
     * can attribute the payment; nothing is granted here (only the webhook grants).
     *
     * @return void
     * @throws \Exception
     */
    public function pageCheckout(): void
    {
        $customer = $this->apiCustomer();

        try {
            if (!lib\RateLimiter::hitKey('checkout:' . $customer->id, 30, 60)) {
                $this->jsonOut(['error' => 'Too many checkout attempts. Please try again later.'], 429);
                return;
            }
        } catch (\Throwable $e) {
            \go\core\ErrorHandler::logException($e);
        }

        $productId = (int) ($_POST['productId'] ?? $_GET['productId'] ?? 0);
        if ($productId <= 0) {
            $this->jsonOut(['error' => 'productId is required'], 400);
            return;
        }
        $product = model\Product::findById((string) $productId);
        if (!$product || !$product->active || !$product->isAvailable()) {
            $this->jsonOut(['error' => 'Product not available'], 404);
            return;
        }
        // A free product is acquired via /download, not bought.
        if ($product->price === null || (float) $product->price == 0.0) {
            $this->jsonOut(['error' => 'This product is free'], 400);
            return;
        }

        $settings = model\Settings::get();
        $gateway = lib\payment\PaymentGateways::active($settings);
        if (!$gateway) {
            $this->jsonOut(['error' => 'Online payment is not available. Please contact the vendor.'], 503);
            return;
        }

        $base = rtrim((string) (go()->getSettings()->URL ?? ''), '/')
            . '/api/page.php/community/marketplaceserver/checkoutReturn';
        try {
            $url = $gateway->createCheckoutSession(
                (int) $customer->id,
                $product,
                $base . '?status=success',
                $base . '?status=cancel'
            );
        } catch (\Throwable $e) {
            \go\core\ErrorHandler::logException($e);
            $this->jsonOut(['error' => 'Could not start checkout. Please try again later.'], 502);
            return;
        }

        $this->jsonOut(['url' => $url]);
    }

    /**
     * GET /checkoutReturn — the plain page the gateway redirects the buyer's
     * browser back to after checkout. Public, no side effects (the entitlement is
     * granted by the signature-verified webhook, never here).
     *
     * @return void
     */
    public function pageCheckoutReturn(): void
    {
        $ok = (($_GET['status'] ?? '') === 'success');
        header('Content-Type: text/html;charset=utf-8');
        $title = $ok ? 'Payment complete' : 'Checkout canceled';
        $body = $ok
            ? '<h1>Thank you ✓</h1><p>Your payment was received. Return to your Group-Office '
                . 'marketplace and press Refresh to download your module.</p>'
            : '<h1>Checkout canceled</h1><p>No payment was taken. You can return to your '
                . 'Group-Office marketplace and try again.</p>';
        echo '<!doctype html><meta charset="utf-8"><title>' . $title . '</title>'
            . '<body style="font-family:sans-serif;max-width:32rem;margin:4rem auto;text-align:center">'
            . $body . '</body>';
    }

    /**
     * POST /paymentWebhook/{gateway} — the gateway's server-to-server callback.
     * UNAUTHENTICATED but signature-verified inside the gateway driver: a payload
     * whose signature does not verify is refused (400) and grants nothing. On a
     * verified purchase the entitlement is granted; on a subscription end it is
     * revoked. Always 200 on a verified payload (even if already processed) so the
     * gateway stops retrying.
     *
     * @param string $gatewayId
     * @return void
     * @throws \Exception
     */
    public function pagePaymentWebhook(string $gatewayId = ''): void
    {
        $settings = model\Settings::get();
        $gateway = $gatewayId !== '' ? lib\payment\PaymentGateways::byId($settings, $gatewayId) : null;
        if (!$gateway) {
            $this->jsonOut(['error' => 'Unknown gateway'], 404);
            return;
        }

        $payload = (string) file_get_contents('php://input');
        $signature = $this->requestHeader($gateway->signatureHeaderName());

        $event = $gateway->parseWebhook($payload, $signature);
        if ($event === null) {
            // Signature did not verify — do NOT reveal why, grant nothing.
            $this->jsonOut(['error' => 'Invalid signature'], 400);
            return;
        }

        try {
            switch ($event->type) {
                case lib\payment\PaymentEvent::PURCHASE_COMPLETED:
                case lib\payment\PaymentEvent::SUBSCRIPTION_RENEWED:
                    if ($event->customerId && $event->productId) {
                        model\Entitlement::grantPaid(
                            $event->customerId,
                            $event->productId,
                            $event->expiresAt,
                            $event->subscriptionId,
                            $event->paymentRef
                        );
                        model\Activity::record(model\Activity::TYPE_PURCHASE, [
                            'customerId' => $event->customerId,
                            'productId' => $event->productId,
                            'amount' => $event->amount,
                            'currency' => $event->currency,
                            'ref' => $event->paymentRef ?: $event->subscriptionId,
                        ]);
                    }
                    break;
                case lib\payment\PaymentEvent::ACCESS_REVOKED:
                    // A subscription end revokes by subscription id; a full refund
                    // of a one-off purchase revokes by its payment-intent id.
                    if ($event->paymentRef) {
                        $enrich = model\Entitlement::find()->where(['stripePaymentIntentId' => $event->paymentRef])->single();
                        model\Entitlement::revokeByPaymentRef($event->paymentRef);
                        model\Activity::record(model\Activity::TYPE_REFUND, [
                            'customerId' => $enrich ? (int) $enrich->customerId : null,
                            'productId' => $enrich ? (int) $enrich->productId : null,
                            'amount' => $event->amount,
                            'currency' => $event->currency,
                            'ref' => $event->paymentRef,
                        ]);
                    }
                    if ($event->subscriptionId) {
                        $enrich = model\Entitlement::find()->where(['stripeSubscriptionId' => $event->subscriptionId])->single();
                        model\Entitlement::revokeBySubscription($event->subscriptionId);
                        model\Activity::record(model\Activity::TYPE_SUBSCRIPTION_CANCELED, [
                            'customerId' => $enrich ? (int) $enrich->customerId : null,
                            'productId' => $enrich ? (int) $enrich->productId : null,
                            'ref' => $event->subscriptionId,
                        ]);
                    }
                    break;
                // IGNORED → acknowledged, no action.
            }
        } catch (\Throwable $e) {
            // Log but still 200: the payment is real and verified; a transient DB
            // error must not make the gateway retry forever against a poisoned row.
            // (Reconciliation is handled out of band.)
            \go\core\ErrorHandler::logException($e);
        }

        $this->jsonOut(['received' => true]);
    }

    /**
     * Read a request header case-insensitively (works under both Apache and
     * the $_SERVER fallback).
     *
     * @param string $name
     * @return string|null
     */
    private function requestHeader(string $name): ?string
    {
        $v = \go\core\http\Request::get()->getHeader($name);
        return ($v === false || $v === null || $v === '') ? null : (string) $v;
    }

    /**
     * The client IP to rate-limit on. Uses REMOTE_ADDR verbatim unless it is a
     * configured trusted proxy, in which case the real client is taken from
     * X-Forwarded-For (see {@see \go\modules\community\marketplaceserver\lib\ClientIp}).
     *
     * @return string
     */
    private function clientIp(): string
    {
        return lib\ClientIp::resolve(
            (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'),
            $this->requestHeader('X-Forwarded-For'),
            model\Settings::get()->getTrustedProxies()
        );
    }

}
