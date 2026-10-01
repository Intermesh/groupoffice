<?php

namespace go\modules\community\otp;

use go\core\ErrorHandler;
use go\core\http\Request;
use go\core\model\Token;
use go\core\model\User;
use go\core\auth\SecondaryAuthenticator;
use go\core\db\Query;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;

class OtpAuthenticator extends SecondaryAuthenticator
{
	/**
	 * How many wrong codes we allow within LOCKOUT_SECONDS before locking out
	 * further attempts for this user/IP combination.
	 */
	private const MAX_ATTEMPTS = 5;

	/**
	 * The lockout/counting window in seconds. Resets on a correct code.
	 */
	private const LOCKOUT_SECONDS = 300;

	public function authenticate(Token $token, array $data): bool
	{

		if (!isset($data['otp_code'])) {
			$this->setValidationError('otp_code', ErrorCode::REQUIRED);
			return false;
		}

		/** @phpstan-ignore-next-line */
		$otp = $token->getUser()->otp;
		$user = $token->getUser();

		$cacheKey = $this->getAttemptsCacheKey($user);
		$attempts = (int)(go()->getCache()->get($cacheKey) ?? 0);

		if ($attempts >= self::MAX_ATTEMPTS) {
			$this->setValidationError('otp_code', ErrorCode::INVALID_INPUT);
			ErrorHandler::log("Token authentication blocked (too many attempts) for user ". $user->username . " from IP: '" . Request::get()->getRemoteIpAddress() . "'");
			return false;
		}

		if (!$otp) {
			$this->setValidationError('otp_code', ErrorCode::NOT_FOUND);
			ErrorHandler::log("Token authentication failed for user ". $user->username . " from IP: '" . Request::get()->getRemoteIpAddress() . "'");
			return false;
		}

		$expiresAt = $otp->expiresAt;

		if ($expiresAt && new DateTime($expiresAt) < new DateTime()) {
			$this->setValidationError('otp_code', ErrorCode::NOT_FOUND);
			ErrorHandler::log("Token authentication failed for user ". $user->username . " from IP: '" . Request::get()->getRemoteIpAddress() . "'");
			return false;
		}


		if (!$otp->verifyCode($data['otp_code'])) {
			go()->getCache()->set($cacheKey, $attempts + 1, true, self::LOCKOUT_SECONDS);
			$this->setValidationError('otp_code', ErrorCode::INVALID_INPUT);
			ErrorHandler::log("Token authentication failed for user ". $user->username . " from IP: '" . Request::get()->getRemoteIpAddress() . "'");
			return false;
		}

		go()->getCache()->delete($cacheKey);

		return true;
	}

	private function getAttemptsCacheKey(User $user): string
	{
		return 'otp-attempts-' . $user->id . '-' . Request::get()->getRemoteIpAddress();
	}

	/**
	 * Check whether user has an available OTP secret.
	 *
	 * An OTP authenticator is available when
	 * 1. A user has an OTP secret
	 * 2. this secret is not expired
	 *
	 * @param string $username
	 * @return bool
	 * @throws \Exception
	 */
	public static function isAvailableFor(string $username): bool
	{
		$otp = (new Query)
			->select(['id', 'expiresAt'])
			->from('otp_secret', 's')
			->join('core_user', 'u', 'u.id = s.userId')
			->where(['username' => $username, 'verified' => true])->single();

		if (!$otp) {
			return false;
		}
		$expiresAt = $otp['expiresAt'];
		if ($expiresAt && new DateTime($expiresAt) < new DateTime()) {
			return false;
		}
		return true;
	}
}
