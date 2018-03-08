<?php
$holiday = new \GO\Base\Model\Holiday();
for ($year=2014;$year<2031;$year++)
	$holiday->deleteHolidays($year,'nl');
