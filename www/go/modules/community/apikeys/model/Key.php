<?php
namespace go\modules\community\apikeys\model;

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
			if(!$token->refresh()) {
				$this->setValidationError('accessToken', \go\core\validate\ErrorCode::RELATIONAL, 'Could not save token');
				return false;
			}
			
			$this->accessToken = $token->accessToken;
		}
		
		return parent::internalSave();
	}
	
	protected function internalDelete() {
		$token = \go\core\auth\model\Token::find()->where(['accessToken' => $this->accessToken])->single();
		if($token) {
			$token->delete();
		}
		return parent::internalDelete();
	}
}

