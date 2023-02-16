<?php

namespace go\core\controller;

use go\core\fs\Blob;
use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\model;

class EmailTemplate extends EntityController {

	protected function entityClass(): string {
		return model\EmailTemplate::class;
	}

	public function query($params) {
		return $this->defaultQuery($params);
	}

	public function get($params) {
		return $this->defaultGet($params);
	}

	public function fromZip($params) {
		// must have BlobId, Subject will be echoed back for result reference

		$blob = Blob::findById($params['blobId']);
		if(!empty($blob)) {
			$tpl = model\EmailTemplate::fromBlob($blob);
			if(!empty($params['subject']));
				$tpl->subject = $params['subject'];
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
