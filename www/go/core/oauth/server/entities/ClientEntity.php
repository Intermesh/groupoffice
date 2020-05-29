<?php
namespace go\core\oauth\server\entities;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ClientEntity extends \go\core\jmap\Entity implements ClientEntityInterface
{
	use EntityTrait, ClientTrait;

	protected $secret;

	protected static function defineMapping() {
		return parent::defineMapping()
			->addTable('core_oauth_client');

	}

	public function setSecret($secret) {
		$this->secret = password_hash($secret);
	}

	public function checkSecret($secret) {
		return \password_verify($secret, $this->secret);
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setRedirectUri($uri)
	{
		$this->redirectUri = $uri;
	}

	public function setConfidential()
	{
		$this->isConfidential = true;
	}
}