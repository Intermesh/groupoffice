<?php
/**
 * Integration test for EmailVerification. Run in container.
 */
use go\core\App;
use go\core\auth\TemporaryState;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\community\marketplaceserver\lib\Registrar;
use go\modules\community\marketplaceserver\model\EmailVerification;

require('/var/www/html/vendor/autoload.php');
if (function_exists('apcu_clear_cache')) { apcu_clear_cache(); }
App::get();
\GO()->setAuthState((new TemporaryState())->setUserId(1));
go()->setCache(new \go\core\cache\None());

$pass = 0; $fail = 0; $failures = [];
function ok($l, $c, $e = '') { global $pass,$fail,$failures; if ($c){echo "  \033[32mPASS\033[0m $l\n";$pass++;} else {echo "  \033[31mFAIL\033[0m $l".($e?" ($e)":"")."\n";$fail++;$failures[]=$l;} }

$email = 'e2e-verify-' . getmypid() . '@example.test';
$uid = null;
try {
    $res = Registrar::register($email, 'Verify Test', 'sup3rsecret');
    $uid = (int) $res['user']->id;

    ok('user starts DISABLED', !User::findById($uid)->enabled);

    $plain = EmailVerification::issue($uid);
    ok('issue returns a plaintext token', is_string($plain) && strlen($plain) > 20);
    ok('user still disabled before redeem', !User::findById($uid)->enabled);

    ok('redeem with wrong token -> null', EmailVerification::redeem('wrong-token') === null);
    ok('user still disabled after wrong redeem', !User::findById($uid)->enabled);

    $u = EmailVerification::redeem($plain);
    ok('redeem with correct token returns the user', $u && (int)$u->id === $uid);
    ok('user is now ENABLED', User::findById($uid)->enabled);

    ok('redeem again (used) -> null', EmailVerification::redeem($plain) === null);

    // expired token
    $plain2 = EmailVerification::issue($uid);
    $v = EmailVerification::find()->where(['tokenHash' => \go\modules\community\marketplaceserver\lib\TokenAuth::hash($plain2)])->single();
    $past = new DateTime(); $past->sub(new \DateInterval('PT1H'));
    $v->expiresAt = $past; $v->save();
    ok('expired token -> null', EmailVerification::redeem($plain2) === null);

    // issuing invalidates prior unused tokens
    $a = EmailVerification::issue($uid);
    $b = EmailVerification::issue($uid);
    ok('newest token works', EmailVerification::redeem($b) !== null);
    // a was invalidated by issuing b -> but user already enabled; check token row used
    $rowA = EmailVerification::find()->where(['tokenHash' => \go\modules\community\marketplaceserver\lib\TokenAuth::hash($a)])->single();
    ok('older token was invalidated on re-issue', $rowA && $rowA->usedAt !== null);

} catch (\Throwable $e) {
    ok('verify ran without unexpected error', false, get_class($e).': '.$e->getMessage());
}

if ($uid) {
    try {
        $c = \go\modules\community\addressbook\model\Contact::findForUser($uid);
        User::delete(['id' => $uid]);
        if ($c) { try { \go\modules\community\addressbook\model\Contact::delete(['id' => $c->id]); } catch (\Throwable $e) {} }
        echo "  cleanup: deleted user $uid\n";
    } catch (\Throwable $e) { echo "  cleanup err: ".$e->getMessage()."\n"; }
}
echo "\n  PASS: $pass   FAIL: $fail\n";
if ($fail) echo "  Failures: ".implode('; ',$failures)."\n";
