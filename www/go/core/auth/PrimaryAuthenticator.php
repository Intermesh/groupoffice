<?php

namespace go\core\auth;

use go\core\model\User;
use go\core\validate\ErrorCode;

abstract class PrimaryAuthenticator extends BaseAuthenticator
{

	protected int $errorCode = ErrorCode::INVALID_INPUT;
	protected string $errorMessage = 'Bad username or password';

	/**
	 * Get a user object from this authenticator
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return boolean|User
	 */
	public function authenticate(string $username, string $password): bool|User
	{
		return false;
	}

	/**
	 * By default, the user / authenticator combination should be cached for a certain amount of time.
	 *
	 * @return bool
	 */
	public function needsCache(): bool
	{
		return true;
	}


	/**
	 * Set a custom error code
	 *
	 * @param int $errorCode
	 * @return void
	 */
	public function setErrorCode(int $errorCode): void
	{
		$this->errorCode = $errorCode;
	}

	/**
	 * Set a custom error message
	 *
	 * @param string $errorMessage
	 * @return void
	 */
	public function setErrorMessage(string $errorMessage): void
	{
		$this->errorMessage = $errorMessage;
	}

	public function getErrorCode(): int
	{
		return $this->errorCode;
	}

	public function getErrorMessage(): string
	{
		return $this->errorMessage;
	}
}

