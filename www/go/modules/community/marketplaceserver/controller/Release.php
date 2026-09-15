<?php

namespace go\modules\community\marketplaceserver\controller;

use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\util\ArrayObject;
use go\modules\community\marketplaceserver\model;

class Release extends EntityController
{
    /**
     * The class name of the entity this controller is for.
     *
     * @return string
     */
    protected function entityClass(): string
    {
        return model\Release::class;
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
     * Read metadata out of an already-uploaded release ZIP (blob) so the dialog
     * can pre-fill the changelog after upload. The package layout is
     * `{moduleName}/...` (see the client PackageValidator), so the module's
     * CHANGELOG.md lives at `{moduleName}/CHANGELOG.md`. Manager-only.
     *
     * @param array $params ['blobId' => string]
     * @return \ArrayObject ['moduleName' => ?string, 'changelog' => ?string]
     * @throws \go\core\exception\Forbidden
     * @throws InvalidArguments
     * @throws \Exception
     */
    public function readPackageInfo($params)
    {
        $module = \go\core\App::get()->getModule('community', 'marketplaceserver');
        if (!$module || empty($module->getUserRights()->mayManage)) {
            throw new \go\core\exception\Forbidden();
        }

        $blobId = $params['blobId'] ?? null;
        if (empty($blobId)) {
            throw new InvalidArguments('blobId is required');
        }
        $blob = \go\core\fs\Blob::findById($blobId);
        if (!$blob) {
            throw new InvalidArguments('Blob not found');
        }

        $zip = new \ZipArchive();
        if ($zip->open($blob->path()) !== true) {
            throw new \Exception('Could not open the uploaded package (not a valid ZIP?)');
        }

        // Single top-level directory = the module name.
        $rootDir = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = str_replace('\\', '/', (string) $zip->getNameIndex($i));
            $slash = strpos($name, '/');
            if ($slash !== false) {
                $rootDir = substr($name, 0, $slash);
                break;
            }
        }

        $changelog = null;
        if ($rootDir !== null) {
            $data = $zip->getFromName($rootDir . '/CHANGELOG.md');
            if ($data !== false) {
                $changelog = $data;
            }
        }
        // Fallback: any CHANGELOG.md anywhere in the archive.
        if ($changelog === null) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = str_replace('\\', '/', (string) $zip->getNameIndex($i));
                if (basename($name) === 'CHANGELOG.md') {
                    $data = $zip->getFromIndex($i);
                    $changelog = $data === false ? null : $data;
                    break;
                }
            }
        }
        $zip->close();

        // `changelog` is a TEXT column (~64KB) — cap defensively.
        if ($changelog !== null && strlen($changelog) > 60000) {
            $changelog = substr($changelog, 0, 60000);
        }

        return new \ArrayObject(['moduleName' => $rootDir, 'changelog' => $changelog]);
    }
}
