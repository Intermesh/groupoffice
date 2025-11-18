<?php

namespace go\modules\community\otp;

use go\core\model\Token;
use go\core\auth\SecondaryAuthenticator;
use go\core\db\Query;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;

class OtpAuthenticator extends SecondaryAuthenticator
{

	public function authenticate(Token $token, array $data): bool
	{

		if (!isset($data['otp_code'])) {
			$this->setValidationError('otp_code', ErrorCode::REQUIRED);
			return false;
		}

		/** @phpstan-ignore-next-line */
		$otp = $token->getUser()->otp;

		if (!$otp) {
			$this->setValidationError('otp_code', ErrorCode::NOT_FOUND);
			return false;
		}

		$expiresAt = $otp->expiresAt;

		if ($expiresAt && new DateTime($expiresAt) < new DateTime()) {
			$this->setValidationError('otp_code', ErrorCode::NOT_FOUND);
			return false;
		}


		if (!$otp->verifyCode($data['otp_code'])) {
			$this->setValidationError('otp_code', ErrorCode::INVALID_INPUT);
			return false;
		}

		return true;
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
