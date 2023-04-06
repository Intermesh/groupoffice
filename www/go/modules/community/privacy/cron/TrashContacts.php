<?php

namespace go\modules\community\privacy\cron;

use DateInterval;
use Exception;
use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use go\core\orm\Query;
use go\core\util\DateTime;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\privacy\Module;

final class TrashContacts extends CronJob
{
	/**
	 * docker-compose exec  -e XDEBUG_SESSION-1 -u www-data groupoffice ./www/cli.php core/System/runCron --name=TrashContacts --module=privacy --package=community
	 *
	 * @throws Exception
	 */
	public function run(CronJobSchedule $schedule)
	{
		$ids = [];
		$settings = Module::get()->getSettings();

		$monABs = explode(",", $settings->monitorAddressBooks);
		$trashABId = $settings->trashAddressBook;

		// Retrieve IDs of contacts in monitored address books that are to be moved
		$dt = new DateTime();
		$dt->sub(new DateInterval("P" . $settings->trashAfterXDays . 'D'));

		$res = Contact::find(['id'])->where('createdAt', '<', $dt)
			->andWhere('addressBookId', 'IN', $monABs)
			->all();
		foreach ($res as $re) {
			$ids[] = $re->id;
		}

		// Retrieve IDs of  contacts with a deleteAt field
		$dt = new DateTime("now");
		$r2 = Contact::find(['id'])
			->join('community_privacy_contact', 'cpc', 'cpc.contactId =c.id')
			->where('cpc.deleteAt', '<=', $dt)
			->andWhere('c.addressBookId', '!=', $trashABId);

		foreach ($r2->all() as $re) {
			$ids[] = $re->id;
		}

		// Update by Contact by the selected IDs
		if (count($ids)) {
			$stmt = go()->getDbConnection()->update(
				Contact::getMapping()->getPrimaryTable()->getName(),
				['addressBookId' => $trashABId],
				(new Query())
					->where('id', 'IN', $ids)
			);
			$stmt->execute();
			Contact::entityType()->changes($ids);
		}
	}
}