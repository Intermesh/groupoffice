<?php
namespace go\core\model;

use go\core\orm\Mapping;
use go\core\util\Password;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class OauthClient extends \go\core\jmap\Entity implements ClientEntityInterface
{
	use EntityTrait, ClientTrait;

	public ?string $id;

	protected ?string $secret;

	/**
	 * How the client registered itself: "dynamic" (RFC 7591) or "metadata" (a client ID metadata document). Null for a
	 * client that an administrator registered.
	 */
	protected ?string $registration = null;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('core_oauth_client');

	}

	public function setSecret(string|null $secret): void
	{
		$this->secret = isset($secret) ? Password::hash($secret) : null;
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

	/**
	 * @param string|null $registration "dynamic", "metadata", or null for a client that an administrator registered
	 */
	public function setRegistration(?string $registration): void
	{
		$this->registration = $registration;
	}

	public function getRegistration(): ?string
	{
		return $this->registration;
	}

	/**
	 * Whether the client registered itself, like an AI assistant. The user must then approve it.
	 */
	public function isSelfRegistered(): bool
	{
		return $this->registration !== null;
	}
}