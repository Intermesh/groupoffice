<?php
// run in parallel:
// php deadlock.php & php deadlock.php
require ('../www/GO.php');
go()->getDebugger()->output = true;
go()->getDebugger()->enable(true);

$start = go()->getDebugger()->getMicroTime();
//go()->getDbConnection()->beginTransaction();

for($i = 0; $i < 100; $i++) {

	echo $i ."\n";
 try {
	 $contact = new \go\modules\community\addressbook\model\Contact();
	 $contact->addressBookId = \go\modules\community\addressbook\model\AddressBook::find()->selectSingleValue('id')->single();
	 $contact->name = 'test ' . $i;
 	 $contact->emailAddresses[0] = (new \go\modules\community\addressbook\model\EmailAddress($contact))->setValue('email', 'email@'.uniqid());
	 $contact->save();

	 $contact->emailAddresses[0]->email = 'test@test.nl';

	 $contact->save();

 }catch(PDOException $e) {
	 echo getmypid() . " ". $e->getMessage() ."\n";
 }
}
go()->getDebugger()->debugTiming("end");

echo go()->getDebugger()->getMicroTime() - $start;
echo "\n";
//go()->getDbConnection()->commit();