<?php
namespace go\modules\community\apikeys\model;

use go\core\orm\Query;

class Key extends \go\core\jmap\Entity {
	public $id;
	public $name;	
	public $accessToken;
	public $createdAt;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('apikeys_key', 'key');						
	}
	
	protected function internalSave() {
		
		if($this->isNew()) {
			$token = new \go\core\auth\model\Token();
			$token->userId = 1; //TODO make configurable
			$token->expiresAt = null;
			if(!$token->refresh()) {
				$this->setValidationError('accessToken', \go\core\validate\ErrorCode::RELATIONAL, 'Could not save token');
				return false;
			}
			
			$this->accessToken = $token->accessToken;
		}
		
		return parent::internalSave();
	}
	
	protected static function internalDelete(Query $query) {
		$q = clone $query;
		$q->select('accessToken');

		if(!Token::delete(['accessToken' => $q])) {
			throw new \Exception("Could not delete access token");
		}

		return parent::internalDelete($query);
	}
}

