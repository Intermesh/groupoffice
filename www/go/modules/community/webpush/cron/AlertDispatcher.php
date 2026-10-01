<?php

namespace go\modules\community\webpush\cron;
use go\core\model\Alert;
use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use go\core\model\Search;
use go\core\orm\EntityType;
use go\core\util\DateTime;
use go\modules\community\webpush\model\PushSubscription;
use go\modules\community\webpush\model\Settings;
use go\modules\community\webpush\model\WebPush;

/**
 * docker compose exec -u www-data groupoffice-develop ./www/cli.php core/System/runCron --name='AlertDispatcher' --module='webpush' --package='community' --debug
 */
class AlertDispatcher extends CronJob {

	public function getLabel(){
		return go()->t("Alert Dispatcher", 'webpush', 'community');
	}

	public function getDescription(){
		return go()->t("Dispatch GroupOffice Alerts to web push subscription", 'webpush', 'community');
	}

	private WebPush $webPush;

	public function __construct()
	{
		\go\modules\community\history\Module::$enabled = false;


		$this->webPush = new WebPush();
	}

	public function run(CronJobSchedule $schedule) {

		$now = new DateTime();
		$now->setTimezone(new \DateTimeZone("UTC"));

		// Fetch unsent, non-stale alerts with their subscriptions
//		$alerts = go()->getDbConnection()->query("
//            SELECT
//                a.id,
//                a.entityType,
//                a.entityId,
//                a.recurrenceId,
//                a.staleAt,
//                a.userId,
//                s.id as subscriptionId,
//                s.endpoint,
//                s.p256dh,
//                s.auth
//            FROM core_alert a
//            JOIN core_push_subscription s ON s.userId = a.userId
//            WHERE a.triggerAt <= :now
//              AND a.pushedAt IS NULL
//              AND a.staleAt > :now
//        ");

		$alerts = Alert::find()
			->where('triggerAt', '<=', $now->format('Y-m-d H:i'))
			->andWhere('staleAt', '>', $now->format('Y-m-d H:i')) // it must be stale at some point
			->andWhere('isSent','=',0);
			//->andWhere(['tag'=>'1']); // the first alert of the array

		go()->debug($alerts);

		$toMarkSent = [];
		// find active push subscription.
		foreach ($alerts as $row) {
			$searchRecord  = $row->getModified();
			$subscriptions = PushSubscription::find()->where(['userId'=>$row->userId])->all();
			foreach ($subscriptions as $subscription) {
				$payload = json_encode([
					'title' => $searchRecord->title,
					'body' => $searchRecord->description, // todo: use Format.shortDateTime($row['recurrenceId'] || event.start); if available
					//'url' => $this->getUrl($row),
					'recurrenceId' => $row['recurrenceId'],
				]);

				$this->webPush->queue($subscription, $payload);
			}
			$toMarkSent[] = $row['id'];
		}

		$results = $this->webPush->flush();

		// Handle failed/expired subscriptions
		foreach ($results as $result) {
			if (!$result['success'] && $result['status'] === 410) {
				// TODO: CHANGE update so client knows it is unsubscribed
				// Subscription gone — delete it
				go()->getDbConnection()
					->delete('core_push_subscription', ['endpoint' => $result['endpoint']])
					->execute();
			}
		}

		if ($toMarkSent) {
			go()->getDbConnection()
				->update('core_alert', ['isSent' => 1], ['id' => $toMarkSent])
				->execute();
		}

	}
}