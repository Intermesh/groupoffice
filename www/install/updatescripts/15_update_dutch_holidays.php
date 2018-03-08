<?php
$stmt = \GO\Base\Model\Holiday::model()->find(\GO\Base\Db\FindParams::newInstance()
	->criteria(\GO\Base\Db\FindCriteria::newInstance()
		->addCondition('region','nl')
		->addCondition('date','2014-01-01','>=')
	)
);

foreach ($stmt as $holidayModel)
	$holidayModel->delete();

\GO\Base\Model\Holiday::model()->generateHolidays('2014', 'nl');