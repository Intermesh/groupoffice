<?php
/**
 * Standalone check for the "token inert until verified" gate. apiCustomer()
 * calls exit() on 401, so this runs in its own process; the assertion + cleanup
 * happen in a shutdown hook.
 */
use go\core\App;
use go\core\auth\TemporaryState;
use go\core\model\User;
use go\modules\community\marketplaceserver\Module;
use go\modules\community\marketplaceserver\lib\Registrar;

require('/var/www/html/vendor/autoload.php');
if (function_exists('apcu_clear_cache')) { apcu_clear_cache(); }
App::get();
\GO()->setAuthState((new TemporaryState())->setUserId(1));
go()->setCache(new \go\core\cache\None());

ob_start();  // let jsonOut()'s header()/http_response_code() work under CLI

$email = 'e2e-gate-' . getmypid() . '@example.test';
$res = Registrar::register($email, 'Gate', 'sup3rsecret');
$uid = (int) $res['user']->id;
$token = $res['token'];

register_shutdown_function(function () use ($uid) {
    $code = http_response_code();
    $body = ob_get_length() !== false ? ob_get_clean() : '';
    // cleanup (exit() skipped normal teardown)
    try {
        $c = \go\modules\community\addressbook\model\Contact::findForUser($uid);
        User::delete(['id' => $uid]);
        if ($c) { try { \go\modules\community\addressbook\model\Contact::delete(['id' => $c->id]); } catch (\Throwable $e) {} }
    } catch (\Throwable $e) {}
    $ok = ($code === 401) && (strpos($body, 'Invalid or missing API token') !== false);
    fwrite(STDOUT, ($ok ? "  PASS" : "  FAIL") . " unverified token -> 401 (code=$code)\n");
    fwrite(STDOUT, "\n  PASS: " . ($ok ? 1 : 0) . "   FAIL: " . ($ok ? 0 : 1) . "\n");
});

if (User::findById($uid)->enabled) {
    ob_end_clean();
    fwrite(STDOUT, "  FAIL user should be disabled\n\n  PASS: 0   FAIL: 1\n");
    exit(0);
}

$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
http_response_code(200);
Module::get()->pageAccount();   // -> apiCustomer() -> 401 + exit()
