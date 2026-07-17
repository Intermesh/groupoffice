<?php

namespace go\modules\community\marketplace\controller;

use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\util\ArrayObject;
use go\modules\community\marketplace\model;

class Repository extends EntityController
{
    /**
     * The class name of the entity this controller is for.
     *
     * @return string
     */
    protected function entityClass(): string
    {
        return model\Repository::class;
    }

    /**
     * @param $params
     * @return ArrayObject
     * @throws InvalidArguments
     */
    public function query($params)
    {
        return $this->defaultQuery($params);
    }

    /**
     * @param $params
     * @return ArrayObject
     * @throws \Exception
     */
    public function get($params)
    {
        return $this->defaultGet($params);
    }

    /**
     * @param $params
     * @return ArrayObject
     * @throws InvalidArguments
     * @throws StateMismatch
     */
    public function set($params)
    {
        return $this->defaultSet($params);
    }

    /**
     * @param $params
     * @return array|ArrayObject
     * @throws InvalidArguments
     */
    public function changes($params)
    {
        return $this->defaultChanges($params);
    }

    /**
     * Validate a candidate repository: reachable + token valid, and the target
     * package dir is writable. Returns server /info without persisting.
     *
     * @param array $params {url, token}
     * @return \ArrayObject
     * @throws \Exception
     */
    public function validate($params)
    {
        // Controller-level gate (not on an entity): there is no Repository row
        // yet, so this must gate the *user* at the API boundary. Without it a
        // mayRead-only user could abuse validate() as an SSRF / token-probe
        // primitive (outbound HTTPS to an attacker-chosen URL + writability stat).
        $module = \go\core\App::get()->getModule('community', 'marketplace');
        if (!$module || empty($module->getUserRights()->mayManage)) {
            throw new \go\core\exception\Forbidden();
        }

        $probe = new model\Repository();
        $probe->url = (string) ($params['url'] ?? '');
        $probe->setToken((string) ($params['token'] ?? ''));

        $client = new \go\modules\community\marketplace\lib\ApiClient($probe);
        $info = $client->info();                       // throws on bad token / unreachable

        $package = $info['package'] ?? '';
        $writable = $this->packageDirWritable($package);

        return new \ArrayObject([
            'package' => $package,
            'name' => $info['name'] ?? $package,
            'publicKey' => $info['publicKey'] ?? '',
            'writable' => $writable,
            'packageDir' => $this->packageDir($package),
        ]);
    }

    /**
     * Self-register a customer account against a marketplace server (before any
     * repository row exists), returning the freshly issued API token so the
     * dialog can auto-fill it. Same manager-only boundary as validate() — this
     * makes an outbound HTTPS POST with user-chosen data.
     *
     * @param array $params {url, email, name, password, companyName}
     * @return \ArrayObject
     * @throws \Exception
     */
    public function register($params)
    {
        $module = \go\core\App::get()->getModule('community', 'marketplace');
        if (!$module || empty($module->getUserRights()->mayManage)) {
            throw new \go\core\exception\Forbidden();
        }

        $probe = new model\Repository();
        $probe->url = (string) ($params['url'] ?? '');

        $client = new \go\modules\community\marketplace\lib\ApiClient($probe);
        $result = $client->register(
            (string) ($params['email'] ?? ''),
            (string) ($params['name'] ?? ''),
            (string) ($params['password'] ?? ''),
            ($params['companyName'] ?? '') !== '' ? (string) $params['companyName'] : null
        );

        return new \ArrayObject([
            'token' => $result['token'] ?? null,
            'verifyRequired' => !empty($result['verifyRequired']),
        ]);
    }

    /**
     * Password login for an EXISTING account, returning a fresh API token to
     * auto-fill. Same manager-only boundary + outbound-POST shape as register().
     * On an unverified account the server answers with code 'verifyRequired' —
     * surfaced as {verifyRequired:true} so the dialog can offer "resend" rather
     * than a dead-end error. Other failures carry the server's generic message.
     *
     * @param array $params {url, email, password}
     * @return \ArrayObject
     * @throws \Exception
     */
    public function login($params)
    {
        $module = \go\core\App::get()->getModule('community', 'marketplace');
        if (!$module || empty($module->getUserRights()->mayManage)) {
            throw new \go\core\exception\Forbidden();
        }

        $probe = new model\Repository();
        $probe->url = (string) ($params['url'] ?? '');
        $client = new \go\modules\community\marketplace\lib\ApiClient($probe);

        try {
            $result = $client->login(
                (string) ($params['email'] ?? ''),
                (string) ($params['password'] ?? '')
            );
        } catch (\go\modules\community\marketplace\lib\ApiException $e) {
            if ($e->errorCode === 'verifyRequired') {
                return new \ArrayObject(['verifyRequired' => true, 'message' => $e->getMessage()]);
            }
            throw new \Exception($e->getMessage());
        }

        return new \ArrayObject(['token' => $result['token'] ?? null]);
    }

    /**
     * Ask the server to re-send the verification e-mail. Uniform outcome (the
     * server never reveals whether the account exists), so this always reports
     * success for any HTTP-level response; only a transport failure propagates.
     *
     * @param array $params {url, email}
     * @return \ArrayObject
     * @throws \Exception
     */
    public function resendVerification($params)
    {
        $module = \go\core\App::get()->getModule('community', 'marketplace');
        if (!$module || empty($module->getUserRights()->mayManage)) {
            throw new \go\core\exception\Forbidden();
        }

        $probe = new model\Repository();
        $probe->url = (string) ($params['url'] ?? '');
        $client = new \go\modules\community\marketplace\lib\ApiClient($probe);

        try {
            $client->resendVerification((string) ($params['email'] ?? ''));
        } catch (\go\modules\community\marketplace\lib\ApiException $e) {
            // Keep the outcome uniform — never surface whether the account exists.
        }

        return new \ArrayObject(['ok' => true]);
    }

    /**
     * The authenticated customer's own account (companyName + entitlements) for
     * a repository — the client-side "My account" view, fed by the server's
     * token-authenticated /account endpoint.
     *
     * @param array $params {repositoryId}
     * @return \ArrayObject
     * @throws \Exception
     */
    public function account($params)
    {
        $repo = model\Repository::findById((string) $params['repositoryId']);
        if (!$repo || !$repo->getPermissionLevel()) {
            throw new \go\core\exception\Forbidden();
        }
        $data = (new \go\modules\community\marketplace\lib\ApiClient($repo))->account();
        return new \ArrayObject([
            'companyName' => $data['companyName'] ?? null,
            'entitlements' => $data['entitlements'] ?? [],
        ]);
    }

    /**
     * Start a hosted checkout for a product on a repository and return the gateway
     * redirect URL for the browser to open. Manager-gated via the repository's own
     * permission level (same as catalog/download).
     *
     * @param array $params {repositoryId, productId}
     * @return \ArrayObject {url}
     * @throws \Exception
     */
    public function checkout($params)
    {
        $repo = model\Repository::findById((string) $params['repositoryId']);
        if (!$repo || !$repo->getPermissionLevel()) {
            throw new \go\core\exception\Forbidden();
        }
        $productId = (int) ($params['productId'] ?? 0);
        if ($productId <= 0) {
            throw new \Exception('productId is required');
        }
        $url = (new \go\modules\community\marketplace\lib\ApiClient($repo))->checkout($productId);
        return new \ArrayObject(['url' => $url]);
    }

    /**
     * @param array $params {repositoryId}
     * @return \ArrayObject
     * @throws \Exception
     */
    public function catalog($params)
    {
        $repo = model\Repository::findById((string) $params['repositoryId']);
        if (!$repo || !$repo->getPermissionLevel()) {
            throw new \go\core\exception\Forbidden();
        }

        $client = new \go\modules\community\marketplace\lib\ApiClient($repo);
        $catalog = $client->catalog();

        // Self-heal the package for repositories added before it was tracked, so
        // downloads work without a manual re-save (the catalog response carries
        // the server's package).
        $catalogPackage = (string) ($catalog['package'] ?? '');
        if (empty($repo->package) && $catalogPackage !== '') {
            $repo->package = $catalogPackage;
            $repo->save();
        }

        $downloaded = [];
        foreach ($repo->downloadedModules as $dm) {
            $downloaded[$dm->moduleName] = $dm->version;
        }

        // Which of the catalogue's modules already exist in THIS Group-Office
        // (core_module row) — so the client can offer an "Install" action for
        // downloaded-but-not-yet-installed modules and hide it once installed.
        $package = $catalog['package'] ?? $repo->package ?? $repo->name;
        $installed = [];
        foreach (($catalog['products'] ?? []) as $p) {
            $mn = (string) ($p['moduleName'] ?? '');
            if ($mn !== '' && !isset($installed[$mn])) {
                $installed[$mn] = (bool) \go\core\model\Module::find()
                    ->where(['name' => $mn, 'package' => $package])->single();
            }
        }

        return new \ArrayObject([
            'package' => $package,
            'products' => $catalog['products'] ?? [],
            'downloaded' => $downloaded,                 // moduleName => downloaded version
            'installed' => $installed,                   // moduleName => is installed in this GO
            'goVersion' => go()->getMajorVersion(),      // this instance's current branch (for display)
            'branches' => $catalog['branches'] ?? [],    // all branches the server publishes for
        ]);
    }

    /**
     * The declared right names of an installed module, so the catalogue can show
     * the standard group-permissions dialog after installing it (same as System
     * Settings → Modules). Admin-only, like Module/install itself.
     *
     * @param array $params {name, package}
     * @return \ArrayObject {moduleId, rights}
     * @throws \Exception
     */
    public function moduleRights($params)
    {
        if (!go()->getAuthState()->isAdmin()) {
            throw new \go\core\exception\Forbidden();
        }
        $name = (string) ($params['name'] ?? '');
        $package = (string) ($params['package'] ?? '');
        $model = \go\core\model\Module::find()->where(['name' => $name, 'package' => $package])->single();
        if (!$model) {
            throw new \go\core\exception\NotFound();
        }
        return new \ArrayObject([
            'moduleId' => (int) $model->id,
            'rights' => array_keys($model->module()->getRights()),
        ]);
    }

    /**
     * @param array $params {repositoryId, module, version}
     * @return \ArrayObject
     * @throws \Exception
     */
    public function download($params)
    {
        $repo = model\Repository::findById((string) $params['repositoryId']);
        if (!$repo || !$repo->getPermissionLevel()) {
            throw new \go\core\exception\Forbidden();
        }
        $module = (string) $params['module'];
        $version = (string) ($params['version'] ?? '');

        // The install target is the SERVER's package (e.g. "sf"), captured from
        // /info at validate time — NOT the human-readable repository name.
        $package = (string) $repo->package;
        if ($package === '') {
            // Backfill from the server's /info for repositories added before the
            // package was tracked, instead of forcing a manual re-save.
            try {
                $info = (new \go\modules\community\marketplace\lib\ApiClient($repo))->info();
                $package = (string) ($info['package'] ?? '');
                if ($package !== '') {
                    $repo->package = $package;
                    $repo->save();
                }
            } catch (\Throwable $e) {
                // fall through to the guard below with an empty package
            }
        }
        if ($package === '') {
            throw new \Exception('Could not determine this repository\'s package from the server. Re-enter the API token and save the repository, then try again.');
        }
        if (!preg_match('/^[a-z0-9_]+$/i', $module) || !preg_match('/^[a-z0-9_]+$/i', $package)) {
            throw new \Exception('Unsafe module/package name');
        }
        $packageDir = go()->getEnvironment()->getInstallPath() . '/go/modules/' . $package;
        if (!is_dir($packageDir) && !@mkdir($packageDir, 0775, true)) {
            throw new \Exception('Package directory not writable: ' . $packageDir);
        }
        if (!is_writable($packageDir)) {
            throw new \Exception('Package directory not writable: ' . $packageDir);
        }

        // 1. download to a temp zip. try/finally guarantees the temp ZIP and
        // the scratch extract dir are removed on EVERY exit (there is no
        // framework tmp GC here — the tmp GC cron is disabled), including when
        // ApiClient::download() throws mid-transfer leaving a partial file.
        $tmpZip = \go\core\fs\File::tempFile('zip');
        $extractDir = null;
        try {
            (new \go\modules\community\marketplace\lib\ApiClient($repo))->download($module, $version, $tmpZip);

            // http\Client::download() doesn't inspect HTTP status (see ApiClient::download),
            // so a 403/404 JSON error body is written verbatim into $tmpZip. Detect a
            // non-ZIP body and re-throw the server's real message ("No entitlement for
            // this module", "Release not found", …) so the user learns the actual reason
            // instead of the opaque "not a valid ZIP".
            $this->assertDownloadedZip($tmpZip);

            // 2. verify the package signature against the repository's PINNED public
            // key before trusting a single byte. The module is PHP that gets
            // unpacked into go/modules and executed, so TLS + entitlement checks
            // are not enough: a spoofed/compromised server (or broken TLS) must not
            // be able to deliver arbitrary code. Only a package signed by the key
            // that matches the pinned public key is extracted.
            if (empty($repo->publicKey)) {
                throw new \Exception('Cannot verify this package: the repository has no pinned signing key. Open the repository and save its API token again to re-pin it, then retry.');
            }
            $signature = (new \go\modules\community\marketplace\lib\ApiClient($repo))->signature($module, $version);
            $bytes = (string) file_get_contents($tmpZip->getPath());
            if (!\go\modules\community\marketplace\lib\PackageSigner::verify($bytes, $signature, (string) $repo->publicKey)) {
                throw new \Exception('Package signature verification failed — this download was not signed by the repository\'s trusted key and was rejected. Your modules were not modified.');
            }

            // 3. validate entries before extracting
            $zip = new \ZipArchive();
            if ($zip->open($tmpZip->getPath()) !== true) {
                throw new \Exception('Downloaded file is not a valid ZIP');
            }
            $names = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $names[] = $zip->getNameIndex($i);
            }
            $err = \go\modules\community\marketplace\lib\PackageValidator::validateEntries($names, $module);
            if ($err !== null) {
                $zip->close();
                throw new \Exception($err);
            }

            // 4. extract to a temp dir, then swap into place with backup.
            // The temp dir lives INSIDE the package dir (same filesystem) so the
            // final rename() is a same-device move — a temp dir on /tmp is often
            // a different mount (e.g. in Docker), where rename() falls back to a
            // copy() that cannot handle directories ("first argument to copy()
            // cannot be a directory").
            $moduleDir = $packageDir . '/' . $module;
            $extractDir = $packageDir . '/.marketplace_tmp_' . $module . '_' . uniqid();
            if (!$zip->extractTo($extractDir)) {
                $zip->close();
                throw new \Exception('Extraction failed');
            }
            $zip->close();

            $backup = null;
            if (is_dir($moduleDir)) {
                $backup = $moduleDir . '.bak-' . time();
                if (!rename($moduleDir, $backup)) {
                    throw new \Exception('Could not back up existing module');
                }
            }
            if (!rename($extractDir . '/' . $module, $moduleDir)) {
                // final swap failed. Try to restore the backup; if THAT also
                // fails the module dir is now missing — surface a distinct,
                // actionable error naming the surviving backup for manual
                // recovery (and do NOT let finally remove it).
                if ($backup && !rename($backup, $moduleDir)) {
                    throw new \Exception('CRITICAL: module "' . $module . '" directory is missing and automatic restore failed. Recover it manually from: ' . $backup);
                }
                throw new \Exception('Could not move new module into place' . ($backup ? ' (previous version restored)' : ''));
            }
            if ($backup) {
                $this->rrmdir($backup);
            }

            // 5. record downloaded version
            $this->recordDownloaded($repo, $module, $version);

            return new \ArrayObject(['success' => true, 'module' => $module, 'version' => $version]);
        } finally {
            $tmpZip->delete();                       // no-op if already gone
            if ($extractDir !== null) {
                $this->rrmdir($extractDir);          // no-op if already removed
            }
        }
    }

    /**
     * Guard: the downloaded body must be a real ZIP. Because http\Client::download()
     * streams whatever the server returns (it doesn't inspect HTTP status), an error
     * response is a JSON body, not a ZIP. Detect that and re-throw the server's own
     * message so the reason ("No entitlement for this module", "Release not found", …)
     * reaches the UI instead of a generic "not a valid ZIP".
     *
     * @param \go\core\fs\File $file
     * @return void
     * @throws \Exception
     */
    private function assertDownloadedZip(\go\core\fs\File $file): void
    {
        $path = $file->getPath();
        $fh = @fopen($path, 'rb');
        if ($fh === false) {
            return; // nothing to check; downstream ZipArchive::open() will report
        }
        $head = fread($fh, 2);
        fclose($fh);
        if ($head === 'PK') {
            return; // local-file-header magic — a real ZIP
        }
        // Not a ZIP: the server sent an error body instead of the package.
        $body = (string) file_get_contents($path, false, null, 0, 8192);
        $data = json_decode($body, true);
        if (is_array($data) && !empty($data['error'])) {
            // Structured JSON error (403 "No entitlement", 404 "Release not found", …).
            throw new \Exception((string) $data['error']);
        }
        if (preg_match('~<h1[^>]*>(.*?)</h1>~is', $body, $m)) {
            // Uncaught server error rendered as an HTML page — surface its headline
            // so the real cause is visible rather than an opaque "not a valid ZIP".
            throw new \Exception('The marketplace server returned an error: ' . trim(strip_tags($m[1])));
        }
        throw new \Exception('The marketplace server did not return a valid package.');
    }

    /**
     * @param array $params {repositoryId}
     * @return \ArrayObject
     * @throws \Exception
     */
    public function refresh($params)
    {
        $repo = model\Repository::findById((string) $params['repositoryId']);
        if (!$repo || !$repo->getPermissionLevel()) {
            throw new \go\core\exception\Forbidden();
        }

        $client = new \go\modules\community\marketplace\lib\ApiClient($repo);

        // detect signing-key rotation
        $info = $client->info();
        if (!empty($repo->publicKey) && ($info['publicKey'] ?? '') !== $repo->publicKey) {
            $repo->keyMismatch = true;
            $repo->lastError = 'Signing key changed';
            $repo->save();
            throw new \Exception("This marketplace server's security key changed (for example the server was reinstalled). To reconnect, open this repository and save its API token again.");
        }

        $host = \go\core\http\Request::get()->getHost();
        $repo->licenseJwt = $client->license($host);
        $repo->lastSyncAt = new \go\core\util\DateTime();
        $repo->lastError = null;
        $repo->keyMismatch = false;
        $repo->save();

        return new \ArrayObject(['success' => true]);
    }

    /**
     * @param string $package
     * @return string
     */
    private function packageDir(string $package): string
    {
        return go()->getEnvironment()->getInstallPath() . '/go/modules/' . $package;
    }

    /**
     * @param string $package
     * @return bool
     */
    private function packageDirWritable(string $package): bool
    {
        if (!preg_match('/^[a-z0-9_]+$/i', $package)) {
            return false;
        }
        $dir = $this->packageDir($package);
        if (!is_dir($dir)) {
            // creatable if the parent (go/modules) is writable
            return is_writable(dirname($dir));
        }
        return is_writable($dir);
    }

    /**
     * Recursively remove a directory tree.
     *
     * @param string $dir
     * @return void
     */
    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        if ($items === false) {
            return;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (is_dir($path) && !is_link($path)) {
                $this->rrmdir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    /**
     * Upsert the downloaded-version record for a module on this repository.
     * `RepositoryModule` is a `go\core\orm\Property` (no standalone save), so
     * it must be mutated/added on `$repo->downloadedModules` and persisted via
     * `$repo->save()` — the ORM's `saveRelatedArray()` diffs by primary key and
     * inserts/updates the surviving elements.
     *
     * @param model\Repository $repo
     * @param string $module
     * @param string $version
     * @return void
     * @throws \Exception
     */
    private function recordDownloaded(model\Repository $repo, string $module, string $version): void
    {
        $entry = null;
        foreach ($repo->downloadedModules as $dm) {
            if ($dm->moduleName === $module) {
                $entry = $dm;
                break;
            }
        }
        if (!$entry) {
            $entry = new model\RepositoryModule($repo);
            $entry->moduleName = $module;
            $repo->downloadedModules[] = $entry;
        }
        $entry->version = $version;
        $entry->downloadedAt = new \go\core\util\DateTime();

        $repo->save();
    }
}
