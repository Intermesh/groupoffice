<?php
require ('../www/GO.php');

//GO::session()->login("admin", "admim");
$user = \go\core\model\User::findById(1);
//\go()->setAuthState((new \go\core\auth\TemporaryState())->setUserId($user->id));
sleep(5);
//\go\modules\community\addressbook\model\Contact::find();
go()->getDatabase()->clearCache();

//\GO\Projects2\Model\Project::model()->find()->fetch();
echo "disconnect\n";
\GO::unsetDbConnection();
sleep(20);
exit();
