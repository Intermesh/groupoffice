<?php
namespace go\modules\community\pwned;

use go\core;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\model\User;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\modules\community\otp\model;
use go\modules\community\otp\OtpAuthenticator;
use go\modules\community\pwned\model\Pwned;
use go\modules\community\pwned\model\Settings;

class Module extends core\Module {
	/**
	 * The development status of this module
	 * @return string
	 */
	public function getStatus() : string{
		return self::STATUS_STABLE;
	}

	public function getAuthor(): string
	{
		return "Intermesh BV";
	}

	public function autoInstall(): bool
	{
		return true;
	}
	
	public function defineListeners() {
		User::on(User::EVENT_VALIDATE, static::class, 'onUserValidate');
		User::on(User::EVENT_PASSWORD_VERIFIED, static::class, 'onUserPasswordVerified');
	}

	public static function onUserValidate(User $user) {
		$plain = $user->plainPassword(); // dV5(
		if(isset($plain)) {

			if(Settings::get()->enableForGroupId != Group::ID_EVERYONE && !$user->isInGroup(Settings::get()->enableForGroupId)) {
				return;
			}

			$pwnd = new Pwned();
			if($pwnd->hasBeenPwned($plain)) {
				$user->setValidationError("password", core\validate\ErrorCode::INVALID_INPUT, "The new password is invalid because it has been breached.");
			}
		}
	}

	public static function onUserPasswordVerified(User $user, string $password) {

		if($user->forcePasswordChange) {
			return;
		}

		if(Settings::get()->enableForGroupId != Group::ID_EVERYONE && !$user->isInGroup(Settings::get()->enableForGroupId)) {
			return;
		}

		$pwnd = new Pwned();
		if($pwnd->hasBeenPwned($password)) {
			$user->forcePasswordChange = true;

			//user is readonly
			$writable = User::findById($user->id);
			$writable->forcePasswordChange = true;
			$writable->save();
		}

	}

	public function getSettings()
	{
		return Settings::get();
	}

}
