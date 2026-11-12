<?php

namespace go\core\controller;

use go\core\exception\Forbidden;
use go\core\fs\Blob;
use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\model;

/**
 * @extends EntityController<\go\core\model\EmailTemplate>
 */
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
	 * Create an unsaved e-mail template from an uploaded ZIP file
	 *
	 * @apiParam string blobId Required. ID of the uploaded ZIP file
	 * @apiParam string module Required. Name of the module the template is for
	 * @apiParam string package Required. Package of the module the template is for
	 * @apiParam string subject Subject to set on the template
	 * @apiPermission Access to the module given by "package" and "module". The template's create permission is checked with canCreate(), which uses the permission on that module.
	 * @param array $params
	 * @return false|model\EmailTemplate
	 * @throws InvalidArguments
	 * @throws Forbidden
	 * @throws \Exception
	 */
	public function fromZip(array $params)
	{
		// For now, only newsletts uses this
		if(!isset($params['module']) || !isset($params['package'])) {
			throw new InvalidArguments();
		}
		$modName = $params['module'];
		$package = $params['package'];

		// must have BlobId, Subject will be echoed back for result reference
		$blob = Blob::findById($params['blobId'] ?? '');

		if (!empty($blob)) {
			$module = model\Module::findByName($package, $modName);
			if (!$module) {
				throw new InvalidArguments("Module not found");
			}
			$tpl = model\EmailTemplate::fromBlob($blob);
			$tpl->moduleId = $module->id;
			if(!$this->canCreate($tpl)) {
				throw new Forbidden();
			}
			if (!empty($params['subject'])) {
				$tpl->subject = $params['subject'];
			}
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
