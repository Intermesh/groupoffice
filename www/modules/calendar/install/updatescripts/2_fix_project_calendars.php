<?php
//require_once('../../../../GO.php');
//\GO::session()->runAsRoot();

if(\GO::modules()->isInstalled('projects')){
	$fp = \GO\Base\Db\FindParams::newInstance()->ignoreAcl();

	$joinCriteria = \GO\Base\Db\FindCriteria::newInstance()->addRawCondition('t.acl_id', 'p.acl_id');

	$fp->join('pm_types', $joinCriteria,'p');

	$stmt = \GO\Calendar\Model\Calendar::model()->find($fp);

	foreach($stmt as $calendar){

		echo "Fixing ".$calendar->name."\n";
		$oldAcl = $calendar->acl;

		$newAcl = $calendar->setNewAcl();
		$calendar->save();

		$oldAcl->copyPermissions($newAcl);
	}
}
