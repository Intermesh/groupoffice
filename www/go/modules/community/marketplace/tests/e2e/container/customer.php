<?php
/**
 * Vendor-side customer administration the scenario needs but the public API
 * deliberately does not expose.
 *
 *   php customer.php reset <email>           delete the account and its rate-limit
 *                                            history, so a rerun starts clean
 *   php customer.php verify <email>          mark the account verified + enabled
 *                                            (what clicking the e-mail link does)
 *   php customer.php entitle <email> <mod>   grant the module's product
 *   php customer.php revoke <email> <mod>    revoke that grant
 *
 * Each command prints one line, so the scenario can assert on it.
 */

use go\core\App;
use go\modules\community\marketplaceserver\model;

// These ship inside the module, and Group-Office serves its own tree with no
// .htaccess — so this file is reachable over HTTP on any install that has the
// module. customer.php can DELETE an account; seed-server.php writes products.
// CLI only, explicitly, rather than relying on the hardcoded autoload path
// happening to be wrong everywhere else.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require '/var/www/html/vendor/autoload.php';
App::get();
go()->setAuthState((new \go\core\auth\TemporaryState())->setUserId(1));

$cmd = $argv[1] ?? '';
$email = $argv[2] ?? '';

$user = \go\core\model\User::find()->where(['email' => $email])->single();

if ($cmd === 'reset') {
    // Harness-only: the scenario must not inherit state from its last run. That
    // bit once — a leftover VERIFIED account made "an unverified account cannot
    // sign in" fail and look like a product bug.
    if ($user) {
        $customer = model\Customer::find()->where(['userId' => $user->id])->single();
        if ($customer) {
            model\Entitlement::delete(['customerId' => $customer->id]);
            model\ApiToken::delete(['customerId' => $customer->id]);
            model\Customer::delete(['id' => $customer->id]);
        }
        \go\core\model\User::delete(['id' => $user->id]);
    }
    // The per-IP registration cap is 5/hour; without this a handful of reruns
    // would start failing at the rate limiter instead of the assertion.
    go()->getDbConnection()->getPDO()->exec('DELETE FROM `marketplaceserver_reg_attempt`');
    echo "reset $email\n";
    exit(0);
}

if (!$user) {
    fwrite(STDERR, "no user with e-mail $email\n");
    exit(1);
}
$customer = model\Customer::find()->where(['userId' => $user->id])->single();
if (!$customer) {
    fwrite(STDERR, "no customer row for $email\n");
    exit(1);
}

switch ($cmd) {
    case 'verify':
        $customer->verifiedAt = new \go\core\util\DateTime();
        if (!$customer->save()) {
            fwrite(STDERR, 'customer: ' . $customer->getValidationErrorsAsString() . "\n");
            exit(1);
        }
        $user->enabled = true;
        if (!$user->save()) {
            fwrite(STDERR, 'user: ' . $user->getValidationErrorsAsString() . "\n");
            exit(1);
        }
        echo "verified $email\n";
        break;

    case 'entitle':
    case 'revoke':
        $moduleName = $argv[3] ?? '';
        $product = model\Product::find()->where(['moduleName' => $moduleName])->single();
        if (!$product) {
            fwrite(STDERR, "no product for module $moduleName\n");
            exit(1);
        }
        $ent = model\Entitlement::find()
            ->where(['customerId' => $customer->id, 'productId' => $product->id])->single();
        if ($cmd === 'entitle') {
            if (!$ent) {
                $ent = new model\Entitlement();
                $ent->customerId = $customer->id;
                $ent->productId = $product->id;
                $ent->source = 'manual';
            }
            $ent->revokedAt = null;
        } else {
            if (!$ent) {
                fwrite(STDERR, "no entitlement to revoke\n");
                exit(1);
            }
            $ent->revokedAt = new \go\core\util\DateTime();
        }
        if (!$ent->save()) {
            fwrite(STDERR, 'entitlement: ' . $ent->getValidationErrorsAsString() . "\n");
            exit(1);
        }
        echo "$cmd $email $moduleName ok\n";
        break;

    default:
        fwrite(STDERR, "unknown command '$cmd'\n");
        exit(1);
}
