<?php
/**
 * Integration test for the public register/verify/account endpoints.
 * Run in the container.
 */
use go\core\App;
use go\core\auth\TemporaryState;
use go\core\model\User;
use go\modules\community\marketplaceserver\Module;
use go\modules\community\marketplaceserver\model\Settings;
use go\modules\community\marketplaceserver\model\EmailVerification;
use go\modules\community\marketplaceserver\lib\TokenAuth;
use go\modules\community\marketplaceserver\model as S;

require('/var/www/html/vendor/autoload.php');
if (function_exists('apcu_clear_cache')) { apcu_clear_cache(); }
App::get();
\GO()->setAuthState((new TemporaryState())->setUserId(1));
go()->setCache(new \go\core\cache\None());

// Buffer everything so the endpoints' header()/http_response_code() calls work
// under CLI (nothing is flushed to the SAPI until the very end).
ob_start();

$pass = 0; $fail = 0; $failures = [];
function ok($l, $c, $e = '') { global $pass,$fail,$failures; if ($c){echo "  \033[32mPASS\033[0m $l\n";$pass++;} else {echo "  \033[31mFAIL\033[0m $l".($e?" ($e)":"")."\n";$fail++;$failures[]=$l;} }

// Call a page method with a fresh request context; returns [httpCode, decodedJson|rawHtml].
function call(callable $fn, array $post = [], array $server = []): array {
    foreach ($server as $k => $v) { $_SERVER[$k] = $v; }
    $_POST = $post;
    // reset the Request singleton's memoized headers so $_SERVER changes apply
    $req = \go\core\http\Request::get();
    $ref = new \ReflectionObject($req);
    if ($ref->hasProperty('headers')) { $p = $ref->getProperty('headers'); $p->setAccessible(true); $p->setValue($req, null); }
    http_response_code(200);
    ob_start();
    $fn();
    $out = ob_get_clean();
    $json = json_decode($out, true);
    return [http_response_code(), $json === null ? $out : $json];
}

$CLIENT_TOK = Settings::DEFAULT_CLIENT_TOKEN;
$settings = Settings::get();
$origEnabled = $settings->registrationEnabled;
$settings->registrationEnabled = true;
$settings->save();

$mod = Module::get();
$createdUsers = [];
$hdr = ['HTTP_X_MARKETPLACE_CLIENT' => $CLIENT_TOK, 'REMOTE_ADDR' => '203.0.113.7'];

// --- token gate (unit) ---
ok('acceptsClientToken: correct', $settings->acceptsClientToken($CLIENT_TOK));
ok('acceptsClientToken: wrong', !$settings->acceptsClientToken('nope'));
ok('acceptsClientToken: empty', !$settings->acceptsClientToken(''));

// --- branch match (unit) ---
ok('branchMatches: exact', \go\modules\community\marketplaceserver\model\Release::branchMatches('6.8', '6.8'));
ok('branchMatches: client patch version', \go\modules\community\marketplaceserver\model\Release::branchMatches('6.8', '6.8.175'));
ok('branchMatches: year branch patch version', \go\modules\community\marketplaceserver\model\Release::branchMatches('25', '25.0.5'));
ok('branchMatches: client sent branch only', \go\modules\community\marketplaceserver\model\Release::branchMatches('25', '25'));
ok('branchMatches: mismatch', !\go\modules\community\marketplaceserver\model\Release::branchMatches('6.8', '25.0.5'));
ok('branchMatches: prefix not on boundary', !\go\modules\community\marketplaceserver\model\Release::branchMatches('6.8', '6.80.1'));
ok('branchMatches: empty', !\go\modules\community\marketplaceserver\model\Release::branchMatches('6.8', ''));

// --- missing header -> 403 ---
[$code] = call([$mod, 'pageRegister'], ['email' => 'a@x.test', 'password' => 'sup3rsecret'], ['REMOTE_ADDR' => '203.0.113.1', 'HTTP_X_MARKETPLACE_CLIENT' => '']);
ok('register without client token -> 403', $code === 403);

// --- wrong header -> 403 ---
[$code] = call([$mod, 'pageRegister'], ['email' => 'a@x.test', 'password' => 'sup3rsecret'], ['REMOTE_ADDR' => '203.0.113.2', 'HTTP_X_MARKETPLACE_CLIENT' => 'wrong']);
ok('register with wrong client token -> 403', $code === 403);

// --- happy path -> 200 + token, user disabled ---
$email = 'e2e-ep-' . getmypid() . '@example.test';
[$code, $body] = call([$mod, 'pageRegister'], ['email' => $email, 'name' => 'EP Test', 'password' => 'sup3rsecret', 'companyName' => 'EP s.r.o.'], $hdr);
ok('register happy path -> 200', $code === 200, "code=$code");
ok('register returns a token', is_array($body) && !empty($body['token']));
ok('register says verifyRequired', is_array($body) && !empty($body['verifyRequired']));
$u = User::find()->where(['email' => $email])->single();
if ($u) { $createdUsers[] = (int) $u->id; }
ok('created user is disabled (locked)', $u && !$u->enabled);
// SECURITY (Finding 1) — the token being INERT until verified is asserted in
// the standalone tests/integration/gate.php (apiCustomer() exit()s on 401,
// which would kill this in-process harness). The account-200 check below only
// passes AFTER the verify step enables the user, which already demonstrates the
// gate in the positive direction.

// --- verify: issue a token, redeem via endpoint ---
if ($u) {
    $plain = EmailVerification::issue((int) $u->id);
    [$vcode, $vhtml] = call([$mod, 'pageVerify'], [], ['REQUEST_METHOD' => 'GET']);
    // note: token comes from $_GET, set it directly
    $_GET['token'] = $plain;
    [$vcode, $vhtml] = call([$mod, 'pageVerify']);
    ok('verify with good token -> 200', $vcode === 200);
    ok('verify enabled the user', User::findById((int) $u->id)->enabled);
    $_GET['token'] = 'bogus';
    [$vcode2] = call([$mod, 'pageVerify']);
    ok('verify with bad token -> 400', $vcode2 === 400);
    unset($_GET['token']);
}

// --- account endpoint: token-auth returns entitlements ---
if ($u) {
    $customer = S\Customer::find()->where(['userId' => (int) $u->id])->single();
    // grab the registration token to authenticate — we have plaintext from body
    $plainApi = $body['token'];
    [$acode, $abody] = call([$mod, 'pageAccount'], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $plainApi]);
    ok('account endpoint authenticates via token -> 200', $acode === 200, "code=$acode");
    ok('account returns entitlements array + companyName', is_array($abody) && array_key_exists('entitlements', $abody) && $abody['companyName'] === 'EP s.r.o.');
}

// --- rate limit: 6th attempt from one IP -> 429 (default max 5/hour) ---
$rlIp = '198.51.100.55';
$last = 200;
for ($i = 1; $i <= 6; $i++) {
    [$last] = call([$mod, 'pageRegister'], ['email' => "rl$i-" . getmypid() . '@example.test', 'password' => 'sup3rsecret'], ['HTTP_X_MARKETPLACE_CLIENT' => $CLIENT_TOK, 'REMOTE_ADDR' => $rlIp]);
    if ($last === 200) {
        $ru = User::find()->where(['email' => "rl$i-" . getmypid() . '@example.test'])->single();
        if ($ru) { $createdUsers[] = (int) $ru->id; }
    }
}
ok('6th attempt from same IP -> 429', $last === 429, "last=$last");

// --- registration disabled -> 403 ---
$settings->registrationEnabled = false; $settings->save();
[$dcode] = call([$mod, 'pageRegister'], ['email' => 'z@x.test', 'password' => 'sup3rsecret'], $hdr);
ok('registration disabled -> 403', $dcode === 403);

// restore + cleanup
$settings->registrationEnabled = $origEnabled;
$settings->save();
foreach ($createdUsers as $uid) {
    try {
        $c = \go\modules\community\addressbook\model\Contact::findForUser($uid);
        User::delete(['id' => $uid]);
        if ($c) { try { \go\modules\community\addressbook\model\Contact::delete(['id' => $c->id]); } catch (\Throwable $e) {} }
    } catch (\Throwable $e) {}
}
try { \go\modules\community\marketplaceserver\lib\RateLimiter::prune(0); } catch (\Throwable $e) {}
echo "  cleanup: removed " . count($createdUsers) . " users\n";

echo "\n  PASS: $pass   FAIL: $fail\n";
if ($fail) echo "  Failures: " . implode('; ', $failures) . "\n";

while (ob_get_level() > 0) { ob_end_flush(); }
