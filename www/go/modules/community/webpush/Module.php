<?php
namespace go\modules\community\webpush;

use go\core;
use go\core\model;
use go\modules\community\webpush\model\PushSubscription;
use go\modules\community\webpush\model\Settings;
use go\modules\community\webpush\model\Vapid;

class Module extends core\Module {

	public function getAuthor(): string
	{
		return "Intermesh BV";
	}

	public function getCapabilities(\go\core\model\Module $module) : array {
		$settings = Settings::get();
		if(empty($settings->vapidPublicKey) || empty($settings->vapidPrivateKey)) {
			$newKeys = Vapid::createVapidKeys();
			$settings->vapidPublicKey = $newKeys['pub'];
			$settings->vapidPrivateKey = $newKeys['priv'];
			$settings->save();
		}
		return [
			'urn:ietf:params:jmap:webpush-vapid' => [
				'applicationServerKey'=> $settings->vapidPublicKey // Expose public key to client
			]
		];
	}


	public function pagePush($sId) { // for testing
		$sub = PushSubscription::findById($sId);
		$sub->sendVerificationCode();
	}

	protected function afterInstall(model\Module $model): bool
	{
		// Create VAPID keys once
		$settings = Settings::get();
		if(empty($settings->vapidPublicKey) || empty($settings->vapidPrivateKey)) {
			// todo: update everyones sessionState to publish new public key
			$newKeys = Vapid::createVapidKeys();
			$settings->vapidPublicKey = $newKeys['pub'];
			$settings->vapidPrivateKey = $newKeys['priv'];
			return $settings->save();
		}
		$this->createAlertDispatcherCron();
		return true;
	}

	private function createAlertDispatcherCron() {

		$module = model\Module::findByName("community", "webpush");

		$cron = new model\CronJobSchedule();
		$cron->moduleId = $module->id;
		$cron->name = "AlertDispatcher";
		$cron->expression = "* * * * *";
		$cron->description = "Web push alerts";

		if(!$cron->save()) {
			throw new \Exception("Failed to save cron job: " . var_export($cron->getValidationErrors(), true));
		}
	}

}