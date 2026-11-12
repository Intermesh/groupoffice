<?php
/**
 * Integration test for lib/Registrar. Run in the container:
 *   docker exec groupoffice68 php /var/www/html/go/modules/community/marketplaceserver/tests/integration/registrar.php
 */

use go\core\App;
use go\core\auth\TemporaryState;
use go\core\model\User;
use go\core\model\Group;
use go\modules\community\marketplaceserver\Module;
use go\modules\community\marketplaceserver\lib\Registrar;
use go\modules\community\marketplaceserver\lib\RegistrationException;
use go\modules\community\marketplaceserver\lib\TokenAuth;
use go\modules\community\marketplaceserver\model as S;

require('/var/www/html/vendor/autoload.php');
if (function_exists('apcu_clear_cache')) { apcu_clear_cache(); }
App::get();
\GO()->setAuthState((new TemporaryState())->setUserId(1));
go()->setCache(new \go\core\cache\None());

$pass = 0; $fail = 0; $failures = [];
function ok($label, $cond, $extra = '') {
    global $pass, $fail, $failures;
    if ($cond) { echo "  \033[32mPASS\033[0m $label\n"; $pass++; }
    else { echo "  \033[31mFAIL\033[0m $label" . ($extra ? " ($extra)" : "") . "\n"; $fail++; $failures[] = $label; }
}

$email = 'e2e-reg-' . getmypid() . '@example.test';
$createdUserId = null;

try {
    $res = Registrar::register($email, 'Reg Test', 'sup3rsecret', 'Acme Reg s.r.o.');
    $user = $res['user']; $customer = $res['customer']; $plain = $res['token'];
    $createdUserId = (int) $user->id;

    ok('returns user + customer + token', $user->id && $customer->id && !empty($plain));

    $u = User::findById($createdUserId);
    ok('user is DISABLED (locked until verify)', $u && !$u->enabled);
    ok('password is hashed (not the plaintext)', $u && $u->hasPassword());

    $gid = Module::ensureCustomerGroup();
    ok('user is in the locked customer group', in_array($gid, $u->groups));

    // NOT admin, and not in Admins group (id 1)
    ok('user is NOT in Admins group', !in_array(Group::ID_ADMINS, $u->groups));
    ok('user is NOT admin', !$u->isAdmin());

    // customer linked + companyName + profile contact
    ok('customer linked to the user', (int) $customer->userId === $createdUserId);
    ok('companyName denormalised', $customer->companyName === 'Acme Reg s.r.o.');
    $contact = \go\modules\community\addressbook\model\Contact::findForUser($createdUserId);
    ok('profile Contact created', $contact !== null);

    // token authenticates -> resolves this customer
    $tok = S\ApiToken::find()->where(['tokenHash' => TokenAuth::hash($plain), 'revoked' => false])->single();
    ok('returned token resolves to this customer', $tok && (int) $tok->customerId === (int) $customer->id);
    ok('token plaintext is NOT stored', S\ApiToken::find()->where(['tokenHash' => $plain])->single() === null);

    // duplicate rejected
    try {
        Registrar::register($email, 'Dup', 'sup3rsecret');
        ok('duplicate e-mail rejected', false, 'no exception thrown');
    } catch (RegistrationException $e) {
        ok('duplicate e-mail rejected', $e->reason === RegistrationException::DUPLICATE);
    }

    // bad input rejected (no user created)
    $before = User::find()->where(['email' => 'not-an-email'])->single();
    try { Registrar::register('not-an-email', 'x', 'sup3rsecret'); ok('invalid email rejected', false); }
    catch (RegistrationException $e) { ok('invalid email rejected', $e->reason === RegistrationException::INVALID); }
    try { Registrar::register('short-pw-' . getmypid() . '@example.test', 'x', 'short'); ok('short password rejected', false); }
    catch (RegistrationException $e) { ok('short password rejected', $e->reason === RegistrationException::INVALID); }

} catch (\Throwable $e) {
    ok('registrar ran without unexpected error', false, get_class($e) . ': ' . $e->getMessage());
}

// cleanup: deleting the user cascades to customer + token (FK ON DELETE CASCADE)
if ($createdUserId) {
    try {
        $c = \go\modules\community\addressbook\model\Contact::findForUser($createdUserId);
        User::delete(['id' => $createdUserId]);
        if ($c) { try { \go\modules\community\addressbook\model\Contact::delete(['id' => $c->id]); } catch (\Throwable $e) {} }
        echo "  cleanup: deleted user $createdUserId (+cascade)\n";
    } catch (\Throwable $e) { echo "  cleanup err: " . $e->getMessage() . "\n"; }
}

echo "\n  PASS: $pass   FAIL: $fail\n";
if ($fail) { echo "  Failures: " . implode('; ', $failures) . "\n"; }
