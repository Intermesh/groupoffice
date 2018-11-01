<?php
// use /files/file/eecalculateDiskUsage for manual execution or recalculation
$users = \GO\Base\Model\User::model()->find();
foreach($users as $user) {
	$user->calculatedDiskUsage()->save();
}
