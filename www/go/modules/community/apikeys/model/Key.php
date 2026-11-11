<?php
namespace go\modules\community\apikeys\model;

use go\core\jmap\Entity;
use go\core\model\Token;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;

class Key extends Entity {
	public ?string $id;
	public ?string $name;
	public ?string $accessToken;
	public ?\DateTimeInterface $createdAt;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable('apikeys_key', 'key');						
	}

	/**
	 * The access token is created by the server
	 *
	 * @return string[]
	 */
	public static function readOnlyProps(): array
	{
		return array_merge(parent::readOnlyProps(), ['accessToken']);
	}

	/**
	 * Set when the access token was created by this instance. Only then it's returned to the client.
	 */
	private bool $tokenCreated = false;

	/**
	 * The access token is only returned once, when the key is created. It works like a password, so it's never shown
	 * again.
	 *
	 * @param array|null $properties
	 * @return array
	 */
	public function toArray(?array $properties = null): array
	{
		$arr = parent::toArray($properties);
		if(!$this->tokenCreated && array_key_exists('accessToken', $arr)) {
			$arr['accessToken'] = null;
		}
		return $arr;
	}

	protected function internalValidate()
	{
		// Only administrators may create a key for another user. Otherwise a manager of this module could get a token
		// of an administrator.
		if($this->isNew() && isset($this->userId) && $this->userId != go()->getUserId()) {
			$state = go()->getAuthState();
			if($state && $state->isAuthenticated() && !$state->isAdmin()) {
				$this->setValidationError('userId', ErrorCode::FORBIDDEN, go()->t("Permission denied"));
			}
		}

		parent::internalValidate();
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
			$this->tokenCreated = true;
		}
		
		return parent::internalSave();
	}

	public function historyLog(): bool|array
	{
		$log = parent::historyLog();
		if (isset($log['accessToken'][0])) {
			$log['accessToken'][0] = 'MASKED';
		}
		return $log;
	}

	protected static function internalDelete(Query $query): bool
	{
		// Delete the tokens of all deleted keys
		$q = clone $query;
		$accessTokens = $q->selectSingleValue('accessToken')->all();

		if (!parent::internalDelete($query)) {
			return false;
		}

		if (!empty($accessTokens) && !Token::delete(['accessToken' => $accessTokens])) {
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

