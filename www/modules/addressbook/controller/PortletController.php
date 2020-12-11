<?php


namespace GO\Addressbook\Controller;

use GO\Base\Controller\AbstractMultiSelectModelController;
use go\core\orm\Query;
use go\modules\community\addressbook\model\Date as ContactDate;

final class PortletController extends AbstractMultiSelectModelController
{
	public function modelName()
	{
		return 'GO\Addressbook\Model\Addressbook';
	}

	public function linkModelName()
	{
		return 'GO\Addressbook\Model\AddressbookPortletBirthday';
	}

	public function linkModelField()
	{
		return 'addressBookId';
	}


	public function actionBirthdays($params)
	{
		$today = mktime(0, 0, 0);
		$next_month = \GO\Base\Util\Date::date_add(mktime(0, 0, 0), 30);

		$start = date('Y-m-d', $today);
		$end = date('Y-m-d', $next_month);

		// TODO: Refactor into new FW
		$settings = \GO\Addressbook\Model\AddressbookPortletBirthday::model()->findByAttribute('userId', \GO::user()->id);

		$abooks = array_map(function ($value) {
			return $value->addressBookId;
		}, $settings->fetchAll());

		$q = (new Query())
			->select('t.id,t.firstName,t.middleName,t.lastName,t.photoBlobId, d.date as birthday, a.name as addressbook, ' .
				 "IF (STR_TO_DATE(CONCAT(YEAR('$start'),'/',MONTH(d.date),'/',DAY(d.date)),'%Y/%c/%e') >= '$start', " .
				"STR_TO_DATE(CONCAT(YEAR('$start'),'/',MONTH(d.date),'/',DAY(d.date)),'%Y/%c/%e') , " .
				"STR_TO_DATE(CONCAT(YEAR('$start')+1,'/',MONTH(d.date),'/',DAY(d.date)),'%Y/%c/%e')) " .
				"AS upcoming "
			)->from('addressbook_contact')
			->join('addressbook_date','d','t.id = d.contactId')
			->join('addressbook_addressbook','a','t.addressBookId = a.id')
			->where('d.type', '=', ContactDate::TYPE_BIRTHDAY);

		if(count($abooks)) {
			$q->andWhere(['addressBookId' => $abooks]);
		}

		$q->having("upcoming BETWEEN '$start' AND '$end'");
		if(isset($params['sort'])) {
			$q->orderBy([$params['sort'] => $params['dir']]);
		} else {
			$q->orderBy(['upcoming' => 'ASC']);
		}
		$results = [];
		foreach($q->all() as $r) {
			$r['age'] = $this->getAge($r);
			$r['name'] = $r['firstName'] . (strlen($r['middleName']) ? ' ' . $r['middleName'] : '') . ' ' . $r['lastName'];
			$results[] = $r;
		}

		$store = new \GO\Base\Data\ArrayStore(false, $results);
		return $store->getData();
	}


	/**
	 * Calculate age
	 *
	 * @param array $r A full record
	 * @return int age in years
	 */
	private function getAge($r)
	{
		$date = new \DateTime($r['birthday']);
		$diff = $date->diff(new \DateTime());
		return $diff->y;
	}
}