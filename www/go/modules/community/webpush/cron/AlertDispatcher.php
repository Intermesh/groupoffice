<?php

namespace go\modules\community\webpush\cron;

use go\core\db\Criteria;
use go\core\db\Query;
use go\core\model\Alert;
use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use go\core\util\DateTime;
use go\modules\community\webpush\model\PushSubscription;
use go\modules\community\webpush\model\WebPush;

/**
 * docker compose exec -u www-data groupoffice-develop ./www/cli.php core/System/runCron --name='AlertDispatcher' --module='webpush' --package='community' --debug
 */
class AlertDispatcher extends CronJob
{

	public function getLabel()
	{
		return go()->t("Alert Dispatcher", 'webpush', 'community');
	}

	public function getDescription()
	{
		return go()->t("Dispatch GroupOffice Alerts to web push subscription", 'webpush', 'community');
	}

	private WebPush $webPush;

	public function __construct()
	{
		\go\modules\community\history\Module::$enabled = false;


		$this->webPush = new WebPush();
	}

	public function run(CronJobSchedule $schedule)
	{
		$now = new DateTime();
		$now->setTimezone(new \DateTimeZone("UTC"));
		// Don't push old alerts, like after the module was switched on
		$from = (clone $now)->modify('-1 day');

		$alerts = Alert::find()
			->where('triggerAt', '<=', $now->format('Y-m-d H:i:s'))
			->andWhere('triggerAt', '>', $from->format('Y-m-d H:i:s'))
			->andWhere((new Criteria())
				->where('staleAt', 'IS', null)
				->orWhere('staleAt', '>', $now->format('Y-m-d H:i:s')))
			->andWhere('id', 'NOT IN', (new Query())->select('alertId')->from('webpush_pushed_alert'))
			->all();

		$pushed = [];
		$baseUrl = go()->getSettings()->URL;
		foreach ($alerts as $alert) {
			$pushed[] = $alert->id;

			$search = $alert->getSearch();
			if (!$search) {
				continue;
			}

			foreach (PushSubscription::findActiveForUser($alert->userId) as $subscription) {
				go()->debug("Dispatching: " . $alert->id . ' '.$search->name);
				$this->webPush->queue($subscription, json_encode([
					'id' => $alert->id,
					'tag' => 'alert-' . $alert->id,
					'title' => $search->name,
					'body' => $search->description,
					'url' => $baseUrl
				]));
			}
		}

		$results = $this->webPush->flush();

		// The browser removed the subscription
		foreach ($results as $result) {
			if (!$result['success'] && in_array($result['status'], [404, 410])) {
				go()->getDbConnection()
					->delete('core_push_subscription', ['url' => $result['endpoint']])
					->execute();
			}
		}

		foreach ($pushed as $alertId) {
			go()->getDbConnection()->insertIgnore('webpush_pushed_alert', ['alertId' => $alertId])->execute();
		}
	}
}