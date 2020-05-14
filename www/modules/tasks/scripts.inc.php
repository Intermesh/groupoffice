<?php
$show = \GO::config()->get_setting("tasks_filter", \GO::user()->id);

if(!$show)
	$show='active';

//$GO_SCRIPTS_JS .='GO.tasks.defaultTasklist = {id: '.$tasklist['id'].', name: "'.$tasklist['name'].'"};
//$GO_SCRIPTS_JS .='GO.tasks.show="'.$show.'";';
