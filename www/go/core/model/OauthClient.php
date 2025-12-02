<?php
namespace go\core\model;

use go\core\orm\Mapping;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class OauthClient extends \go\core\jmap\Entity implements ClientEntityInterface
{
	use EntityTrait, ClientTrait;

	public ?string $id;

	protected ?string $secret;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('core_oauth_client');

	}

	public function setSecret(string|null $secret) {
		$this->secret = isset($string) ?  password_hash($secret, CRYPT_BLOWFISH) : null;
	}

	public function checkSecret(string $secret): bool
	{
		return password_verify($secret, $this->secret);
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function setRedirectUri(string|array $uri): void
	{
		$this->redirectUri = $uri;
	}

	public function getRedirectUri(): array|string
	{
		return $this->redirectUri;
	}

	public function setIsConfidential(bool $value = true): void
	{
		$this->isConfidential = $value;
	}

	public function getIsConfidential(): bool
	{
		return $this->isConfidential();
	}
}