<?php

use GO\Base\Db\FindParams;
use GO\Calendar\Model\Event;
require('GO.php');

$calendarId = 53380;

GO::session()->runAsRoot();

$findParams = new FindParams();
//$findParams->joinRelation('calendar');
$findParams->getCriteria()->addCondition('rrule', '', '!=');
$findParams->getCriteria()->addCondition('calendar_id', $calendarId);
$findParams->debugSql()->ignoreAcl();

$events = Event::model()->find($findParams);

foreach($events as $event) {

        echo $event->id."++++ \n\n";

        $vobject = $event->toVObject();

      echo $vobject->serialize();
				$vobject->isInTimeRange(new DateTime(), new DateTime());

}
