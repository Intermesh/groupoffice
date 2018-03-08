<?php
require('Group-Office.php');

$db = new db();
$db->set_parameters('localhost', 'mysql', 'root', '');

$dbs=array();
$db->query("SHOW databases");
while($record = $db->next_record())
{
	$dbs[]=$record['Database'];
}

$db->query("select user.User,user.Host,db.Db from user inner join db On db.User=user.User");
$drop_users=array();
while($db->next_record())
{
	if(!in_array(stripslashes($db->f('Db')), $dbs))
	{
		if($db->f('User')!='' && $db->f('User')!='root' && $db->f('User')!='debian-sys-maint' && !in_array($db->f('User'), $drop_users))
		{
			$user = "'".$db->f('User')."'@'".$db->f('Host')."'";
			$drop_users[]=$user;		
		}
	}
}

foreach($drop_users as $user)
	echo "DROP USER $user;\n";
?>