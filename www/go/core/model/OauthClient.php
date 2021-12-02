<?php
namespace go\core\model;

use go\core\orm\Mapping;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class OauthClient extends \go\core\jmap\Entity implements ClientEntityInterface
{
	use EntityTrait, ClientTrait;

	public $id;

	protected $secret;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('core_oauth_client');

	}

	public function setSecret($secret) {
		$this->secret = password_hash($secret, CRYPT_BLOWFISH);
	}

	public function checkSecret($secret) {
		return password_verify($secret, $this->secret);
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setRedirectUri($uri)
	{
		$this->redirectUri = $uri;
	}

	public function getRedirectUri()
	{
		return $this->redirectUri;
	}

	public function setIsConfidential($value = true)
	{
		$this->isConfidential = $value;
	}

	public function getIsConfidential() {
		return $this->isConfidential();
	}
}