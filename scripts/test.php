<?php
require ('../www/GO.php');
//$folder = 'Folder name with "quotes"';
//$test1 = mb_convert_encoding($folder, "UTF7-IMAP", "UTF-8");
//var_dump($test1);
//
//$imap = new \GO\Base\Mail\Imap();
//$test2 = $imap->utf7_encode($folder);
//var_dump($test2);





go()->setAuthState(new \go\core\auth\TemporaryState(2));
$e = \go\modules\community\calendar\model\CalendarEvent::findById(5006);

echo $e->title ."\n";

echo $e->start->format("c") ."\n";

foreach($e->alerts as $alert) {
	$ca = $alert->buildCoreAlert();

	echo $ca->triggerAt->format('c')."\n";;
}
