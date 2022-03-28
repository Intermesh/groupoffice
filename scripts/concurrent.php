<?php
// run in parallel:
// php concurrent.php --sleep & php concurrent.php
require ('../www/GO.php');
go()->getDebugger()->output = true;

go()->setAuthState(new \go\core\auth\TemporaryState(1));

$r = new \go\core\jmap\Router();

$args = \go\core\cli\Router::parseArgs();

if(!empty($args['sleep'])) {
	class lstnr {
		public static function sleep() {
			usleep(5000);
		}
	}

	\go\modules\community\addressbook\model\Contact::on(\go\modules\community\addressbook\model\Contact::EVENT_SAVE, 'lstnr', 'sleep');
}

$state = \go\modules\community\addressbook\model\Contact::getState();
go()->debug($state);

// we want the entity controller to start fresh with the state
\go\modules\community\addressbook\model\Contact::entityType()->clearCache();

$c = new \go\modules\community\addressbook\controller\Contact();
$response = $c->set( [
	'ifInState' => $state,
	'create' => [
		'new' => [
			'name' => 'test',
			'addressBookId' => \go\modules\community\addressbook\model\AddressBook::find()->selectSingleValue('id')->single()
		]
	]
]);

go()->debug($response['newState']);