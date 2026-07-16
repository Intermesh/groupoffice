<?php

namespace go\modules\community\marketplace\lib;

use go\core\http\Client;
use go\core\fs\File;
use go\modules\community\marketplace\model\Repository;

/**
 * Thin HTTP client for a single marketplace Repository. Talks to the server's
 * public API (/info, /catalog, /license, /download) with the repository's
 * Bearer token. Requires HTTPS (except for local/private dev hosts) and
 * disables redirect following.
 */
class ApiClient
{
    private Repository $repo;

    public function __construct(Repository $repo)
    {
        $this->repo = $repo;
    }

    private function base(): string
    {
        return rtrim($this->repo->url, '/') . '/api/page.php/community/marketplaceserver';
    }

    private function newClient(): Client
    {
        $c = new Client();
        $c->setOption(CURLOPT_FOLLOWLOCATION, false);       // no redirects (SSRF hygiene)
        $c->setOption(CURLOPT_CONNECTTIMEOUT, 10);
        $c->setOption(CURLOPT_TIMEOUT, 60);
        $token = $this->repo->decryptToken();
        if ($token !== null) {
            $c->setHeader('Authorization', 'Bearer ' . $token);
        }
        return $c;
    }

    private function assertHttps(): void
    {
        $url = (string) $this->repo->url;
        if (stripos($url, 'https://') === 0) {
            return;
        }
        // The API bearer token travels in cleartext over http, so public
        // repositories MUST use TLS. Plain http is permitted only for
        // local/private hosts (dev / LAN), where there is no untrusted wire.
        if (stripos($url, 'http://') === 0 && $this->isLocalHost($url)) {
            return;
        }
        throw new \Exception('Repository URL must use https:// (http is allowed only for local/private hosts)');
    }

    /**
     * True when the URL host is loopback, a private/link-local/reserved IP, or a
     * dev-style local name (single-label host such as a docker container name, or
     * a .local/.test/.internal/.localhost suffix) — cases where plain http carries
     * no untrusted-wire risk. Anything else (a public hostname or public IP
     * literal) is treated as internet-facing and must use TLS.
     */
    private function isLocalHost(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return false;
        }
        $host = strtolower($host);

        // Literal IP: allow loopback / private (RFC1918) / link-local / reserved.
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $publicIp = filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
            return $publicIp === false; // failed "no private/reserved" → it IS local
        }

        if ($host === 'localhost' || substr($host, -10) === '.localhost') {
            return true;
        }
        foreach (['.local', '.test', '.internal'] as $suffix) {
            if (substr($host, -strlen($suffix)) === $suffix) {
                return true;
            }
        }
        // Single-label hostname (no dot) — docker/dev container names, e.g. "groupoffice68".
        return strpos($host, '.') === false;
    }

    /**
     * @return array{package:string,name:string,publicKey:string}
     * @throws \Exception
     */
    public function info(): array
    {
        $this->assertHttps();
        $res = $this->newClient()->get($this->base() . '/info');
        return $this->decode($res);
    }

    /**
     * @return array{package:string,products:array<mixed>}
     * @throws \Exception
     */
    public function catalog(): array
    {
        $this->assertHttps();
        // Send our full running GO version; the server branch-matches releases on
        // a dot boundary (Release::branchMatches), robust for "6.8.x" and "25.x".
        $res = $this->newClient()->get($this->base() . '/catalog?goVersion=' . urlencode(go()->getVersion()));
        return $this->decode($res);
    }

    /**
     * @throws \Exception
     */
    public function license(string $hostname): string
    {
        $this->assertHttps();
        $res = $this->newClient()->get($this->base() . '/license?hostname=' . urlencode($hostname));
        $data = $this->decode($res);
        return $data['license'] ?? '';
    }

    /**
     * Stream a module ZIP to $target.
     *
     * NOTE: go\core\http\Client::download() returns only ['name','type'] — it
     * has NO 'status' key (it only checks curl transport errors, throwing a
     * CoreException on those). So there is no HTTP-status to inspect here; a 403
     * JSON error body would be written to $target as-is. The controller's
     * ZipArchive::open() check downstream is the corruption/error-page guard
     * (an error JSON is not a valid ZIP → open() fails → we abort + delete).
     *
     * @throws \Exception
     */
    public function download(string $module, string $version, File $target): void
    {
        $this->assertHttps();
        $url = $this->base() . '/download/' . rawurlencode($module) . '/' . rawurlencode($version)
            . '?goVersion=' . urlencode(go()->getVersion());
        $this->newClient()->download($url, $target);   // throws CoreException on transport failure
    }

    /**
     * Fetch the server's detached RS256/SHA-256 signature (base64) over the exact
     * ZIP bytes that download() streams, so the caller can verify the package
     * against the repository's pinned public key before extracting. Same
     * entitlement gate as download; resolves the same release for (module,
     * version, goVersion).
     *
     * @param string $module
     * @param string $version pinned version, or '' for the latest matching branch
     * @return string base64 signature ('' if the server returned none)
     * @throws \Exception on 403/404/etc (message from the server body)
     */
    public function signature(string $module, string $version): string
    {
        $this->assertHttps();
        $url = $this->base() . '/signature/' . rawurlencode($module) . '/' . rawurlencode($version)
            . '?goVersion=' . urlencode(go()->getVersion());
        $data = $this->decode($this->newClient()->get($url));
        return (string) ($data['signature'] ?? '');
    }

    /**
     * Self-register a customer account on the server. Sends the static
     * X-Marketplace-Client header (no Bearer — there is no token yet). Returns
     * the server response, which on success carries {token, verifyRequired}.
     *
     * @param string $email
     * @param string $name
     * @param string $password
     * @param string|null $company
     * @return array{token?:string,verifyRequired?:bool}
     * @throws \Exception on 403/429/422 (message from the server body)
     */
    public function register(string $email, string $name, string $password, ?string $company = null): array
    {
        $this->assertHttps();
        $c = $this->newClient();
        $c->setHeader('X-Marketplace-Client', \go\modules\community\marketplace\Module::CLIENT_TOKEN);
        $res = $c->post($this->base() . '/register', [
            'email' => $email,
            'name' => $name,
            'password' => $password,
            'companyName' => $company ?? '',
        ]);
        return $this->decode($res);
    }

    /**
     * Password login for an EXISTING account, returning a fresh API token. Sends
     * the static X-Marketplace-Client header (no Bearer — this obtains a token).
     *
     * @param string $email
     * @param string $password
     * @return array{token?:string}
     * @throws \go\modules\community\marketplace\lib\ApiException on 401/403/429 (message + code from the server)
     */
    public function login(string $email, string $password): array
    {
        $this->assertHttps();
        $c = $this->newClient();
        $c->setHeader('X-Marketplace-Client', \go\modules\community\marketplace\Module::CLIENT_TOKEN);
        $res = $c->post($this->base() . '/login', ['email' => $email, 'password' => $password]);
        return $this->decode($res);
    }

    /**
     * Ask the server to re-send the verification e-mail for an account. The
     * server answers uniformly ({ok:true}) whether or not the account exists.
     *
     * @param string $email
     * @return array{ok?:bool}
     * @throws \go\modules\community\marketplace\lib\ApiException
     */
    public function resendVerification(string $email): array
    {
        $this->assertHttps();
        $c = $this->newClient();
        $c->setHeader('X-Marketplace-Client', \go\modules\community\marketplace\Module::CLIENT_TOKEN);
        $res = $c->post($this->base() . '/resend', ['email' => $email]);
        return $this->decode($res);
    }

    /**
     * Start a hosted checkout for a product and return the gateway redirect URL
     * the admin's browser must be sent to. Token-authenticated (Bearer).
     *
     * @param int $productId
     * @return string absolute redirect URL
     * @throws \Exception on 4xx/5xx (message from the server body)
     */
    public function checkout(int $productId): string
    {
        $this->assertHttps();
        $res = $this->newClient()->post($this->base() . '/checkout', ['productId' => $productId]);
        $data = $this->decode($res);
        return (string) ($data['url'] ?? '');
    }

    /**
     * The authenticated customer's own account: companyName + entitlements.
     *
     * @return array{companyName:?string,entitlements:array<mixed>}
     * @throws \Exception
     */
    public function account(): array
    {
        $this->assertHttps();
        $res = $this->newClient()->get($this->base() . '/account');
        return $this->decode($res);
    }

    /**
     * @param array{status:int,body:string} $res
     * @return array<mixed>
     * @throws \Exception
     */
    private function decode(array $res): array
    {
        $status = (int) ($res['status'] ?? 0);
        $body = json_decode($res['body'] ?? '', true);

        if ($status !== 200) {
            // Our own API returns { error, code } — surface that verbatim
            // (already user-facing, e.g. "Invalid login credentials").
            if (is_array($body) && isset($body['error']) && $body['error'] !== '') {
                $code = isset($body['code']) ? (string) $body['code'] : null;
                throw new ApiException((string) $body['error'], $status, $code);
            }
            // Not our API (a wrong URL hitting a raw 404 page, a proxy error, an
            // HTML error page, ...). Give a readable, localised message instead of
            // a bare "HTTP 404".
            if ($status === 404) {
                $msg = go()->t("No marketplace server was found at this URL. Please check the repository address.", 'community', 'marketplace');
            } elseif ($status >= 500) {
                $msg = go()->t("The marketplace server returned an error. Please try again later.", 'community', 'marketplace');
            } else {
                $msg = go()->t("Unexpected response from the marketplace server. Please check the URL.", 'community', 'marketplace') . ' (HTTP ' . $status . ')';
            }
            throw new ApiException($msg, $status, null);
        }
        if (!is_array($body)) {
            // 200 but not our JSON — usually a wrong URL that resolves to some
            // other web page on the same host.
            throw new ApiException(
                go()->t("This URL did not return a valid marketplace response. Please check the address.", 'community', 'marketplace'),
                $status,
                null
            );
        }
        return $body;
    }
}
