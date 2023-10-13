<?php

namespace go\core\controller;

use go\core\fs\Blob;
use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\model;

class EmailTemplate extends EntityController {

	protected function entityClass(): string
	{
		return model\EmailTemplate::class;
	}

	public function query(array $params)
	{
		return $this->defaultQuery($params);
	}

	public function get(array $params)
	{
		return $this->defaultGet($params);
	}

	/**
	 * @param array $params
	 * @return false|model\EmailTemplate
	 * @throws InvalidArguments
	 */
	public function fromZip(array $params)
	{
		// must have BlobId, Subject will be echoed back for result reference
		$blob = Blob::findById($params['blobId']);
		// For now, only newsletts uses this
		if(!isset($params['module']) || !isset($params['package'])) {
			throw new InvalidArguments();
		}
		$modName = $params['module'];
		$package = $params['package'];

		if (!empty($blob)) {
			$tpl = model\EmailTemplate::fromBlob($blob);
			if (!empty($params['subject'])) {
				$tpl->subject = $params['subject'];
			}
			$module = model\Module::findByName($package, $modName);
			$tpl->moduleId = $module->id;
			return $tpl;
		}
		return false;
	}

	public function set($params) {
		return $this->defaultSet($params);
	}

	public function changes($params) {
		return $this->defaultChanges($params);
	}
}
