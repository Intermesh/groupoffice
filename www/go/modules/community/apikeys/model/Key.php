<?php
namespace go\modules\community\apikeys\model;

use go\core\jmap\Entity;
use go\core\model\Token;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\DateTime;

class Key extends Entity {
	public ?int $id;
	public ?string $name;
	public ?string $accessToken;
	public ?DateTime $createdAt;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable('apikeys_key', 'key');						
	}
	
	protected function internalSave(): bool
	{
		
		if($this->isNew()) {
			$token = new Token();
			$token->userId = $this->getUserId();
			$token->expiresAt = null;
			if(!$token->refresh()) {
				$this->setValidationError('accessToken', \go\core\validate\ErrorCode::RELATIONAL, 'Could not save token');
				return false;
			}
			
			$this->accessToken = $token->accessToken;
		}
		
		return parent::internalSave();
	}
	
	protected static function internalDelete(Query $query): bool
	{
		$q = clone $query;
		$q->select('accessToken');

		if(!parent::internalDelete($query)) {
		  return false;
    }

    if(!Token::delete(['accessToken' => $q])) {
      throw new \Exception("Could not delete access token");
    }

    return true;
	}

	private int $userId;

	public function setUserId(int $userId): void
	{
		$this->userId = $userId;
	}

	public function getUserId() :int {
		if(isset($this->userId)) {
			return $this->userId;
		}

		if(isset($this->accessToken)) {
			$token = Token::find(['userId'])->where('accessToken', '=', $this->accessToken)->single();
			$this->userId = $token->userId;
			return $this->userId;
		}

		return go()->getUserId();
	}
}

