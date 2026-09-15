<?php

namespace go\modules\community\marketplaceserver\model;

use go\core;
use go\core\util\Crypt;
use go\modules\community\marketplaceserver\lib\KeyPair;

class Settings extends core\Settings
{
    /**
     * The module package this marketplace serves (= repository name on clients).
     *
     * @var string
     */
    public $packageName = 'sf';

    /**
     * Comma-separated list of selectable Group-Office version branches for
     * releases, e.g. "6.8,25,26". Admin-editable in System Settings; the Release
     * dialog offers these as the goVersion dropdown.
     *
     * @var string
     */
    public $supportedGoBranches = '6.8,25,26';

    /**
     * The branch list as a trimmed array.
     *
     * @return array<int,string>
     */
    public function getGoBranches(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->supportedGoBranches))));
    }

    /**
     * Whether the public self-registration endpoint accepts new accounts.
     *
     * @var bool
     */
    public $registrationEnabled = true;

    /**
     * Days without a check-in after which a seat-holding instance is considered
     * inactive and its seat freed (so staging/migration/failover naturally release
     * seats). Admin-editable. Kept deliberately short — a stale instance should
     * give its seat back quickly, not sit on it for a month. Clamped to a sane
     * range by {@see getSeatActivityDays()}.
     *
     * @var int
     */
    public $seatActivityDays = 7;

    /**
     * The configured seat-activity window in days, clamped to 1..30 so a bad admin
     * value can neither free every seat instantly (0) nor pin seats indefinitely.
     *
     * @return int
     */
    public function getSeatActivityDays(): int
    {
        $days = (int) $this->seatActivityDays;
        if ($days < 1) {
            return 1;
        }
        return min($days, 30);
    }

    /**
     * Comma-separated proxy IPs allowed to set X-Forwarded-For. Empty by default:
     * the transport IP (REMOTE_ADDR) is used verbatim for rate-limiting so a
     * forged XFF can't mint a fresh IP per request and defeat the per-IP cap. Set
     * this to your reverse proxy's address(es) ONLY when GO sits behind one, so
     * the real client IP is taken from XFF (see {@see \go\modules\community\marketplaceserver\lib\ClientIp}).
     *
     * @var string
     */
    public $trustedProxies = '';

    /**
     * The trusted-proxy list as a trimmed array.
     *
     * @return array<int,string>
     */
    public function getTrustedProxies(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->trustedProxies))));
    }

    /**
     * The static `X-Marketplace-Client` token baked into the stock `community/marketplace`
     * client build. It is NOT a secret (it ships in every client) — it is only an
     * anti-bot gate so the public register/login/resend endpoints reject requests
     * that did not come from a genuine client build. There is intentionally no
     * admin setting to change it: ticking "Allow self-registration" is all an admin
     * needs. Must stay in sync with
     * `\go\modules\community\marketplace\Module::CLIENT_TOKEN`.
     */
    const DEFAULT_CLIENT_TOKEN = 'groupoffice-marketplace-client';

    /**
     * Constant-time check that a presented client token is the built-in one.
     *
     * @param string|null $presented
     * @return bool
     */
    public function acceptsClientToken(?string $presented): bool
    {
        if ($presented === null || $presented === '') {
            return false;
        }
        return hash_equals(self::DEFAULT_CLIENT_TOKEN, $presented);
    }

    /**
     * The active payment gateway id (e.g. 'stripe'), or '' for none. New checkouts
     * use this gateway; empty disables in-app purchasing (Buy falls back to a
     * "contact the vendor" message on the client).
     *
     * @var string
     */
    public $paymentGateway = '';

    /**
     * Encrypted Stripe secret key (sk_...). Protected + no getX() getter so it is
     * never serialized to the browser nor stored in cleartext (same pattern as the
     * RS256 private key). Read server-side via getStripeSecretKey().
     *
     * @var string|null
     */
    protected $stripeSecretKey;

    /**
     * Encrypted Stripe webhook signing secret (whsec_...). Same protection.
     *
     * @var string|null
     */
    protected $stripeWebhookSecret;

    /**
     * @param string|null $value secret key; blank = keep existing (write-only)
     * @return void
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function setStripeSecretKey(?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $this->stripeSecretKey = Crypt::encrypt(trim($value));
    }

    /**
     * @param string|null $value webhook secret; blank = keep existing (write-only)
     * @return void
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function setStripeWebhookSecret(?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $this->stripeWebhookSecret = Crypt::encrypt(trim($value));
    }

    /**
     * Server-side use only — deliberately NOT a getX() API property, so the
     * decrypted secret is never serialized to the browser (mirrors decryptPrivateKey).
     *
     * @return string|null decrypted secret key, or null when unset
     * @throws \Exception
     */
    public function decryptStripeSecretKey(): ?string
    {
        return empty($this->stripeSecretKey) ? null : Crypt::decrypt($this->stripeSecretKey);
    }

    /**
     * Server-side use only — deliberately NOT a getX() API property.
     *
     * @return string|null decrypted webhook signing secret, or null when unset
     * @throws \Exception
     */
    public function decryptStripeWebhookSecret(): ?string
    {
        return empty($this->stripeWebhookSecret) ? null : Crypt::decrypt($this->stripeWebhookSecret);
    }

    /**
     * Safe booleans the settings UI can read without exposing the secrets.
     *
     * @return bool
     */
    public function getStripeSecretConfigured(): bool
    {
        return !empty($this->stripeSecretKey);
    }

    /**
     * @return bool
     */
    public function getStripeWebhookConfigured(): bool
    {
        return !empty($this->stripeWebhookSecret);
    }

    /**
     * RS256 public key (PEM). Safe to expose; served by /info.
     *
     * @var string|null
     */
    public $publicKey;

    /**
     * Encrypted RS256 private key. Protected + no getX() getter so it is never
     * serialized to the browser nor stored in cleartext (same pattern as the
     * marketplace client's password).
     *
     * @var string|null
     */
    protected $privateKey;

    /**
     * @param string|null $value PEM private key; blank = keep existing
     * @return void
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function setPrivateKey(?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $this->privateKey = Crypt::encrypt($value);
    }

    /**
     * Server-side use only — deliberately NOT getPrivateKey() (auto-exposed).
     *
     * @return string|null
     * @throws \Exception
     */
    public function decryptPrivateKey(): ?string
    {
        return empty($this->privateKey) ? null : Crypt::decrypt($this->privateKey);
    }

    /**
     * Generate and persist the signing keypair if not present yet. Called from
     * Module::afterInstall() and lazily from the page API.
     *
     * @return void
     * @throws \Exception
     */
    public function ensureKeyPair(): void
    {
        if (!empty($this->privateKey) && !empty($this->publicKey)) {
            return;
        }
        $pair = KeyPair::generate();
        $this->setPrivateKey($pair['private']);
        $this->publicKey = $pair['public'];
        $this->save();
    }
}
