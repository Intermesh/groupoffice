<?php

namespace go\modules\community\otp\model;

use DateTime;
use DateTimeInterface;
use Exception;
use go\core\exception\Forbidden;
use go\core\fs\Blob;
use go\core\model\Acl;
use go\core\model\User;
use go\core\orm\Mapping;
use go\core\fs\File;
use go\core\orm\Property;
use go\core\util\QRcode;
use go\core\validate\ErrorCode;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'util' . DIRECTORY_SEPARATOR . 'QRcode.php';

/**
 * @property User $owner
 */
class OtpAuthenticator extends Property
{
	public int $userId;
	protected ?string $secret;
	public DateTimeInterface|null $createdAt = null;
	public DateTimeInterface|null $expiresAt = null;
	protected int $codeLength = 6;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable("otp_secret");
	}

	public function getSecret()
	{
		return null;
	}


	public function setSecret(string $secret): void
	{
		$this->secret = $secret;
	}

	protected function internalValidate()
	{
		// Temporary secrets need not be validated against currently verified password
		if (!empty($this->expiresAt)) {
			parent::internalValidate();
			return;
		}

		if ($this->isModified("secret")) {
			if ((!go()->getAuthState() || !go()->getAuthState()->isAdmin()) && !$this->owner->isPasswordVerified()) {
				$this->owner->setValidationError("currentPassword", ErrorCode::INVALID_INPUT);
				throw new Forbidden();
			}
		}

		parent::internalValidate();
	}

	public function verifyCode($code) : bool {
		$validator = new OtpValidator();
		return $validator->verify($code, $this->secret);
	}

	/**
	 * Set the code length, should be >=6.
	 *
	 * @param int $length
	 *
	 * @return OtpAuthenticator
	 */
	public function setCodeLength(int $length): static
	{
		$this->codeLength = $length;

		return $this;
	}

}
