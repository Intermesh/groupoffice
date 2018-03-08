<?php
\GO::config()->postfixadmin_autoreply_domain='elite.as';
if(!empty(\GO::config()->postfixadmin_autoreply_domain)){
	$sql = "UPDATE pa_aliases SET goto=REPLACE(goto, concat(',',replace(address,'@','#'),'@".\GO::config()->postfixadmin_autoreply_domain."'),'')";
	echo $sql."\n";

	\GO::getDbConnection()->query($sql);
}



