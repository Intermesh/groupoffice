<?php
/**
 * Marketplace end-to-end integration test (server + client) against a live GO
 * database. Unlike the pure unit tests (tests/*Test.php, no DB), this exercises
 * the real ORM, schema, permission coercion and the full license round-trip.
 *
 * Run inside the app container (repo is mounted at /var/www/html):
 *   docker exec <app> php /var/www/html/go/modules/community/marketplaceserver/tests/integration/e2e.php
 *
 * It installs community/marketplaceserver + community/marketplace if needed, creates test
 * rows, asserts behaviour, and deletes everything it created (even on failure).
 * Safe to re-run.
 */

use go\core\App;
use go\core\jmap\State;
use go\core\auth\TemporaryState;
use go\core\orm\Query;
use go\core\http\Request;

use go\modules\community\marketplaceserver\model as S;
use go\modules\community\marketplaceserver\lib\LicenseBuilder;
use go\modules\community\marketplaceserver\lib\TokenAuth;
use go\modules\community\marketplace\model as C;
use go\modules\community\marketplace\lib\LicenseVerifier;
use go\modules\community\marketplace\lib\MarketplaceLicense;

require('/var/www/html/vendor/autoload.php');

// This instance keeps APCu enabled for the CLI SAPI, and GO's APCu cache flush
// pings the web URL (unreachable from inside the container) which aborts writes.
// A no-op cache reads straight from the DB and never pings — required for a
// headless run. (Has no bearing on the code under test.)
if (function_exists('apcu_clear_cache')) { apcu_clear_cache(); }

App::get()->setAuthState(new State());
\GO()->setAuthState((new TemporaryState())->setUserId(1));
go()->setCache(new \go\core\cache\None());

// ---------------------------------------------------------------------------
// tiny test harness
// ---------------------------------------------------------------------------
$pass = 0;
$fail = 0;
$failures = [];
function check(string $name, bool $cond, string $detail = ''): void
{
    global $pass, $fail, $failures;
    if ($cond) {
        $pass++;
        echo "  \033[32mPASS\033[0m $name\n";
    } else {
        $fail++;
        $failures[] = $name . ($detail ? " — $detail" : '');
        echo "  \033[31mFAIL\033[0m $name" . ($detail ? " — $detail" : '') . "\n";
    }
}

// track created rows for teardown
$created = ['product' => [], 'customer' => [], 'token' => [], 'entitlement' => [], 'repo' => [], 'release' => []];

function cleanup(): void
{
    global $created;
    try { foreach ($created['release'] as $id) { S\Release::delete(['id' => $id]); } } catch (\Throwable $e) {}
    try { foreach ($created['entitlement'] as $id) { S\Entitlement::delete(['id' => $id]); } } catch (\Throwable $e) {}
    try { foreach ($created['token'] as $id) { S\ApiToken::delete(['id' => $id]); } } catch (\Throwable $e) {}
    try { foreach ($created['product'] as $id) { S\Product::delete(['id' => $id]); } } catch (\Throwable $e) {}
    try { foreach ($created['customer'] as $id) { S\Customer::delete(['id' => $id]); } } catch (\Throwable $e) {}
    try { foreach ($created['repo'] as $id) { C\Repository::delete(['id' => $id]); } } catch (\Throwable $e) {}
}

register_shutdown_function('cleanup');

try {
    // -----------------------------------------------------------------------
    echo "\n== Install ==\n";
    // Use findByName(...,null) — the same authoritative check install() uses —
    // rather than isInstalled() (which relies on a module cache that is stale in
    // a fresh CLI process).
    $installed = function (string $m): bool {
        return \go\core\model\Module::findByName('community', $m, null) !== null;
    };
    // addressbook is a hard dependency (Customer -> profile Contact).
    if (\go\core\model\Module::findByName('community', 'addressbook', null) === null) {
        \go\modules\community\addressbook\Module::get()->install();
        echo "  installed community/addressbook\n";
    } else {
        echo "  community/addressbook already installed\n";
    }
    if (!$installed('marketplaceserver')) {
        \go\modules\community\marketplaceserver\Module::get()->install();
        echo "  installed community/marketplaceserver\n";
    } else {
        echo "  community/marketplaceserver already installed\n";
    }
    if (!$installed('marketplace')) {
        \go\modules\community\marketplace\Module::get()->install();
        echo "  installed community/marketplace\n";
    } else {
        echo "  community/marketplace already installed\n";
    }
    check('both modules installed', $installed('marketplaceserver') && $installed('marketplace'));

    $host = Request::get()->getHost();
    echo "  host = $host\n";

    // -----------------------------------------------------------------------
    echo "\n== Product NOT NULL coercion (the sortOrder/currency bug) ==\n";
    $p = new S\Product();
    $p->title = 'ITEST null sortOrder';
    $p->type = S\Product::TYPE_MODULE;
    $p->moduleName = 'chat';
    $p->sortOrder = null;      // an empty numberfield submits null
    $p->currency = '';         // empty combo
    $ok = $p->save();
    if ($p->id) { $created['product'][] = $p->id; }
    check('product with null sortOrder + empty currency saves', $ok, $ok ? '' : json_encode($p->getValidationErrors()));
    $reloaded = S\Product::findById((string)$p->id);
    check('sortOrder coerced to 0', $reloaded && (int)$reloaded->sortOrder === 0, 'got ' . ($reloaded->sortOrder ?? 'null'));
    check('currency coerced to EUR', $reloaded && $reloaded->currency === 'EUR', 'got ' . ($reloaded->currency ?? 'null'));

    // -----------------------------------------------------------------------
    echo "\n== Collection product modules[] round-trip ==\n";
    $col = new S\Product();
    $col->title = 'ITEST collection';
    $col->type = S\Product::TYPE_COLLECTION;
    $col->modules = ['chat', 'tours'];
    $col->save();
    if ($col->id) { $created['product'][] = $col->id; }
    $colReload = S\Product::findById((string)$col->id);
    $mods = $colReload ? (array)$colReload->modules : [];
    sort($mods);
    check('collection modules persist and round-trip', $mods === ['chat', 'tours'], json_encode($mods));

    // -----------------------------------------------------------------------
    echo "\n== Customer provisioning + CRM Contact link ==\n";
    $c1 = S\Customer::findOrCreateForUser(1);
    if ($c1->id) { $created['customer'][] = $c1->id; }
    $c2 = S\Customer::findOrCreateForUser(1);
    check('findOrCreateForUser is idempotent (same row)', $c1->id && $c1->id === $c2->id, "$c1->id vs $c2->id");

    // Customer -> profile Contact (CRM source of truth). Ensure the test user
    // has a profile (addressbook creates it on user save; older users predating
    // the addressbook install may lack one).
    if (\go\modules\community\addressbook\model\Contact::findForUser(1) === null) {
        \go\core\model\User::findById(1)->save();
    }
    $profile = \go\modules\community\addressbook\model\Contact::findForUser(1);
    $viaCustomer = $c1->contact();
    check('Customer::contact() resolves the user profile Contact',
        $profile && $viaCustomer && $viaCustomer->id === $profile->id,
        'profile ' . ($profile->id ?? 'null') . ' vs contact() ' . ($viaCustomer->id ?? 'null'));

    // Denormalised companyName round-trips.
    $c1->companyName = 'Acme e2e s.r.o.';
    check('companyName saves', $c1->save());
    $c1r = S\Customer::findById((string) $c1->id);
    check('companyName reloads', $c1r && $c1r->companyName === 'Acme e2e s.r.o.');

    // Locked-down customer group exists and has NO access to marketplaceserver.
    $gid = \go\modules\community\marketplaceserver\Module::ensureCustomerGroup();
    $grp = \go\core\model\Group::findById((string) $gid);
    check('Marketplace Customers group exists', $grp && $grp->name === 'Marketplace Customers');
    // Module access is granted via core_permission(moduleId, groupId, rights).
    // The customer group must have NO permission row on this module.
    $perms = (int) go()->getDbConnection()->query(
        "SELECT COUNT(*) c FROM core_permission p JOIN core_module m ON m.id = p.moduleId " .
        "WHERE m.name = 'marketplaceserver' AND m.package = 'community' AND p.groupId = " . (int) $gid
    )->fetch()['c'];
    check('customer group has ZERO access to marketplaceserver module', $perms === 0);

    // -----------------------------------------------------------------------
    echo "\n== API token: hash stored, plaintext never persisted, auth lookup ==\n";
    $plain = TokenAuth::generateToken();
    $tok = new S\ApiToken();
    $tok->customerId = $c1->id;
    $tok->name = 'ITEST token';
    $tok->assignTokenHash(TokenAuth::hash($plain));
    $tok->save();
    if ($tok->id) { $created['token'][] = $tok->id; }
    check('token matches prefix format', (bool)preg_match('/^marketplaceserver_[0-9a-f]{40}$/', $plain));
    $arr = $tok->toArray();
    check('tokenHash NOT serialized to client (toArray)', !array_key_exists('tokenHash', $arr) && !array_key_exists('token', $arr));
    $found = S\ApiToken::find()->where(['tokenHash' => TokenAuth::hash($plain), 'revoked' => false])->single();
    check('auth lookup by hash finds the token', $found && $found->id === $tok->id);
    $notFound = S\ApiToken::find()->where(['tokenHash' => TokenAuth::hash('marketplaceserver_wrong'), 'revoked' => false])->single();
    check('wrong token hash does not match', $notFound === null);

    // admin issue-token: manager issues a token for a customer
    $issued = (new \go\modules\community\marketplaceserver\controller\ApiToken())->issue(['customerId' => $c1->id, 'name' => 'e2e-issued']);
    $issuedArr = $issued->getArrayCopy();
    if (!empty($issuedArr['id'])) { $created['token'][] = $issuedArr['id']; }
    $issuedFound = S\ApiToken::find()->where(['tokenHash' => TokenAuth::hash($issuedArr['token']), 'revoked' => false])->single();
    check('admin issue() mints a working token for the customer',
        !empty($issuedArr['token']) && $issuedFound && (int) $issuedFound->customerId === (int) $c1->id);

    // -----------------------------------------------------------------------
    echo "\n== Entitlement -> license resolution (module / collection / subscription) ==\n";
    // module entitlement
    $prodMod = $reloaded; // the module=chat product from above
    $entMod = new S\Entitlement();
    $entMod->customerId = $c1->id;
    $entMod->productId = $prodMod->id;
    $entMod->expiresAt = null;
    $entMod->source = 'manual';
    $entMod->save();
    if ($entMod->id) { $created['entitlement'][] = $entMod->id; }

    // build the LicenseBuilder input the way the page API does
    $rows = [];
    foreach (S\Entitlement::find()->where(['customerId' => $c1->id]) as $e) {
        $prod = S\Product::findById((string)$e->productId);
        if (!$prod || !$prod->active) { continue; }
        $m = [];
        if ($prod->type === S\Product::TYPE_MODULE && $prod->moduleName) { $m = [$prod->moduleName]; }
        elseif ($prod->type === S\Product::TYPE_COLLECTION) { $m = (array)$prod->modules; }
        $rows[] = ['type' => $prod->type, 'modules' => $m, 'expiresAt' => $e->expiresAt ? $e->expiresAt->getTimestamp() : null];
    }
    $licenses = LicenseBuilder::resolveLicenses('sf', $rows);
    check('module entitlement yields sf/chat', array_key_exists('sf/chat', $licenses), json_encode(array_keys($licenses)));

    // -----------------------------------------------------------------------
    echo "\n== Full license round-trip: server signs -> client verifies ==\n";
    $settings = S\Settings::get();
    $settings->ensureKeyPair();
    check('server has a keypair', !empty($settings->publicKey) && !empty($settings->decryptPrivateKey()));

    $jwt = LicenseBuilder::build('https://itest.local', (int)$c1->id, $host, 'sf', $licenses, $settings->decryptPrivateKey());
    $v = new LicenseVerifier($jwt, $settings->publicKey, $host);
    check('client verifies sf/chat licensed', $v->has('sf', 'chat'));
    check('client rejects sf/other (not entitled)', !$v->has('sf', 'other'));
    check('client rejects wrong package', !$v->has('amd', 'chat'));

    $vWrongHost = new LicenseVerifier($jwt, $settings->publicKey, 'attacker.example.com');
    check('client rejects wrong hostname', !$vWrongHost->has('sf', 'chat'));

    $vTampered = new LicenseVerifier($jwt, \go\modules\community\marketplaceserver\lib\KeyPair::generate()['public'], $host);
    check('client rejects tampered signature (wrong key)', !$vTampered->has('sf', 'chat'));

    // expired entitlement dropped
    $expiredLic = LicenseBuilder::resolveLicenses('sf', [['type' => 'module', 'modules' => ['chat'], 'expiresAt' => time() - 10]]);
    check('expired entitlement dropped from licenses', !array_key_exists('sf/chat', $expiredLic));

    // subscription wildcard
    $subLic = LicenseBuilder::resolveLicenses('sf', [['type' => 'subscription', 'modules' => [], 'expiresAt' => time() + 3600]]);
    $subJwt = LicenseBuilder::build('https://itest.local', 1, $host, 'sf', $subLic, $settings->decryptPrivateKey());
    $subV = new LicenseVerifier($subJwt, $settings->publicKey, $host);
    check('subscription wildcard licenses any module', $subV->has('sf', 'anything') && $subV->has('sf', 'chat'));

    // -----------------------------------------------------------------------
    echo "\n== Client Repository: encrypted token + MarketplaceLicense.has() from DB ==\n";
    $repo = new C\Repository();
    $repo->name = 'sf';
    $repo->url = 'https://itest.local';
    $repo->setToken('marketplaceserver_secretplaintext');
    $repo->publicKey = $settings->publicKey;
    $repo->licenseJwt = $jwt;
    $repo->save();
    if ($repo->id) { $created['repo'][] = $repo->id; }
    check('repository saves', (bool)$repo->id, json_encode($repo->getValidationErrors()));
    $repoArr = $repo->toArray();
    check('token NOT serialized to client', !array_key_exists('token', $repoArr));
    check('decryptToken round-trips', $repo->decryptToken() === 'marketplaceserver_secretplaintext');
    check('tokenConfigured true', $repo->getTokenConfigured() === true);

    // MarketplaceLicense reads the Repository row + verifies offline
    check('MarketplaceLicense::has(sf,chat) TRUE via DB repo', MarketplaceLicense::has('sf', 'chat'));
    check('MarketplaceLicense::has(sf,other) FALSE', !MarketplaceLicense::has('sf', 'other'));

    // -----------------------------------------------------------------------
    echo "\n== Downloaded-version tracking (marketplace_repository_module) ==\n";
    $rm = new C\RepositoryModule($repo);
    $rm->moduleName = 'chat';
    $rm->version = '1.0.0';
    $rm->downloadedAt = new \go\core\util\DateTime();
    $repo->downloadedModules = [$rm];
    $repo->save();
    $repoReload = C\Repository::findById((string)$repo->id);
    $tracked = [];
    foreach (($repoReload->downloadedModules ?? []) as $dm) { $tracked[$dm->moduleName] = $dm->version; }
    check('downloaded version tracked', ($tracked['chat'] ?? null) === '1.0.0', json_encode($tracked));

    // -----------------------------------------------------------------------
    echo "\n== Release bound to a module product + GO branch ==\n";
    // blobId is a NOT NULL FK to core_blob; reuse any existing blob (creating a
    // real one needs file storage — not what this test is about).
    $blobId = go()->getDbConnection()->query("SELECT id FROM core_blob LIMIT 1")->fetch()['id'] ?? null;
    check('a core_blob exists to attach (test prerequisite)', $blobId !== null);
    $rel = new S\Release();
    $rel->productId = $prodMod->id;         // the module=chat product
    $rel->goVersion = '6.8';
    $rel->version = '1.0.0';
    $rel->blobId = $blobId;
    $relOk = $rel->save();
    if ($rel->id) { $created['release'][] = $rel->id; }
    check('release saves against a module product', $relOk, $relOk ? '' : json_encode($rel->getValidationErrors()));
    $relReload = S\Release::findById((string) $rel->id);
    check('moduleName auto-derived from product', $relReload && $relReload->moduleName === $prodMod->moduleName,
        'got ' . ($relReload->moduleName ?? 'null') . ' expected ' . $prodMod->moduleName);

    // a release may not be attached to a collection/subscription product
    $badRel = new S\Release();
    $badRel->productId = $col->id;          // the collection product from earlier
    $badRel->goVersion = '6.8';
    $badRel->version = '9.9.9';
    $badRel->blobId = $blobId;
    check('release rejected on a non-module product', $badRel->save() === false);

    // a module product must carry a moduleName (every release derives its NOT
    // NULL moduleName from it) — otherwise release creation would fail later
    // with a confusing "moduleName is required" on the Release itself.
    $noName = new S\Product();
    $noName->type = S\Product::TYPE_MODULE;
    $noName->title = 'e2e no-name module';
    $noNameOk = $noName->save();
    if ($noName->id) { $created['product'][] = $noName->id; }
    check('module product without moduleName is rejected', $noNameOk === false
        && isset($noName->getValidationErrors()['moduleName']));

    // branch filtering: a second release for another branch coexists
    $rel25 = new S\Release();
    $rel25->productId = $prodMod->id;
    $rel25->goVersion = '25';
    $rel25->version = '2.0.0';
    $rel25->blobId = $blobId;
    $rel25->save();
    if ($rel25->id) { $created['release'][] = $rel25->id; }
    $for68 = S\Release::find()->where(['productId' => $prodMod->id, 'goVersion' => '6.8'])->all();
    $for25 = S\Release::find()->where(['productId' => $prodMod->id, 'goVersion' => '25'])->all();
    check('branch filter returns the 6.8 release only', count($for68) === 1 && $for68[0]->version === '1.0.0');
    check('branch filter returns the 25 release only', count($for25) === 1 && $for25[0]->version === '2.0.0');

    // -----------------------------------------------------------------------
    echo "\n== JMAP change tracking (powers grid auto-refresh after create) ==\n";
    // The client grid re-queries when the entity store fires a 'changes' event,
    // which is driven server-side by rows logged to core_change on save +
    // flushed by EntityType::push() at request end. Verify a create is logged.
    $etId = (int) go()->getDbConnection()
        ->query("SELECT id FROM core_entity WHERE clientName='MarketplaceServerProduct'")->fetch()['id'];
    $beforeChange = (int) go()->getDbConnection()
        ->query("SELECT COALESCE(MAX(id),0) m FROM core_change")->fetch()['m'];
    $pc = new S\Product();
    $pc->title = 'ITEST change-tracking';
    $pc->type = S\Product::TYPE_MODULE;
    $pc->moduleName = 'chgtrack';
    $pc->save();
    if ($pc->id) { $created['product'][] = $pc->id; }
    \go\core\orm\EntityType::push();   // flush queued changes (JMAP does this at request end)
    $logged = (int) go()->getDbConnection()
        ->query("SELECT COUNT(*) c FROM core_change WHERE id > $beforeChange AND entityTypeId = $etId")
        ->fetch()['c'];
    check('creating a Product logs a core_change row (client sync sees it)', $logged >= 1, "logged=$logged");

} catch (\Throwable $e) {
    $fail++;
    $failures[] = 'EXCEPTION: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine();
    echo "  \033[31mEXCEPTION\033[0m " . $e->getMessage() . "\n  " . $e->getFile() . ':' . $e->getLine() . "\n";
}

echo "\n== Result ==\n";
echo "  PASS: $pass   FAIL: $fail\n";
if ($fail > 0) {
    echo "\n  Failures:\n";
    foreach ($failures as $f) { echo "   - $f\n"; }
}
echo "\n";
exit($fail > 0 ? 1 : 0);
