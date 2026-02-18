<?php

namespace go\modules\community\otp;

use go\core;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\validate\ErrorCode;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\model\User;
use go\modules\community\otp\cron\ClearExpired;

class Module extends core\Module
{
	/**
	 * The development status of this module
	 * @return string
	 */
	public function getStatus(): string
	{
		return self::STATUS_STABLE;
	}

	public static function getCategory(): string
	{
		return go()->t("Authentication", static::getPackage(), static::getName());
	}

	public function getAuthor(): string
	{
		return "Intermesh BV";
	}

	public function autoInstall(): bool
	{
		return true;
	}

	public function defineListeners(): void
	{
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
		User::on(core\jmap\Entity::EVENT_VALIDATE, static::class, 'onUserValidate');
		User::on(User::EVENT_LOGIN, static::class, 'onUserAuthenticated');
	}

	public static function onUserValidate(User $user): void
	{
		/** @phpstan-ignore-next-line */
		if ($user->isModified(['otp']) && !$user->otp) {
			// Prevent validation errors when admin tries to disable OTP for non-admin users
			if (go()->getAuthState()->isAdmin() && go()->getUserId() !== $user->id) {
				return;
			} else {
				// Also prevent validation error if user has no locally stored password, e.g. LDAP
				$upw = go()->getDbConnection()
					->select(['userId'])
					->from('core_auth_password', 'p')
					->where('userId', '=', $user->id)
					->single();
				if (!$upw) {
					return;
				}
			}

			$v = $user->isPasswordVerified();
			if ($v) {
				return;
			} else if ($v === null) {
				$user->setValidationError("currentPassword", ErrorCode::REQUIRED);
			} else {
				$user->setValidationError("currentPassword", ErrorCode::INVALID_INPUT);
			}
		}
	}

	public static function onMap(Mapping $mapping): bool
	{
		$mapping->addHasOne("otp", model\OtpAuthenticator::class, ['id' => 'userId'], false);
		return true;
	}

	/**
	 * A user has just logged in. If they have a temporary OTP secret (e.g. through LDAP), delete it
	 *
	 * @param User $user
	 * @return bool
	 * @throws \Exception
	 */
	public static function onUserAuthenticated(User $user): bool
	{
		$o = $user->otp;
		if ($o && $o->expiresAt) {
			$user->otp = null;
			$user->save();
		}
		return true;
	}

	protected function afterInstall(ModuleModel $model): bool
	{
		if (!OtpAuthenticator::register()) {
			return false;
		}
		ClearExpired::install('*/10 * * * *');

		return parent::afterInstall($model);
	}


	protected function beforeInstall(\go\core\model\Module $model): bool
	{
		// Share module with Internal group
		$model->permissions[Group::ID_INTERNAL] = (new \go\core\model\Permission($model))
			->setRights(['mayRead' => true]);

		return parent::beforeInstall($model);
	}


	public function getSettings()
	{
		return model\Settings::get();
	}


	/**
	 * Get the blob id of the QR code image
	 *
	 * @return void
	 */
	public function downloadQr(): void
	{

		$user = go()->getAuthState()->getUser();

		header("Content-Type: image/png");
		/** @phpstan-ignore-next-line */
		$user->otp->outputQr();


	}

}
