<?php
if(!isset($argv[1]))
{
	die('No config! :: The first argument is empty!');
}

if(!isset($argv[2]))
{
	die('No import file! :: The second argument is empty!');
}

define('CONFIG_FILE', $argv[1]);
require($argv[1]);

require_once($config['root_path']."Group-Office.php");

require_once ($GLOBALS['GO_MODULES']->modules['projects']['class_path']."projects.class.inc.php");

$projects = new projects();

$projects->get_statuses('');
$status = $projects->next_record();

$projects->get_templates();
$template = $projects->next_record();

$projects->get_types();
$type = $projects->next_record();

$fileName = $argv[2];

if(file_exists($fileName))
{
	$file = fopen($fileName,'r');

	while(!feof($file))
	{
		$name = fgets($file);

		if($name != '')
			create_new_project($name);
	}
	fclose($file);
}
else
{
	die('File to read does not exist! :: '.$fileName);
}

function create_new_project($n)
{
	global $projects, $status, $type, $template;
	
	$project = array();
	
	$project['name'] = $n;
	$project['user_id'] = 1;
	$project['parent_project_id'] =	0;
	$project['status_id'] = $status['id'];
	$project['template_id'] =	$template['id'];
	$project['type_id'] = $type['id'];

	$return = true;
	$return = $projects->add_project($project);

	if($return == false)
	{
		echo "!! - Project creation error :: ".$project['name']."";
	}
	else
	{
		echo "     Project creation successful :: ".$project['name']."";
	}
}
?>