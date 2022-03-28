<?php
// run in parallel:
// php deadlock.php & php deadlock.php
require ('../www/GO.php');
go()->getDebugger()->output = true;

for($i = 0; $i < 1; $i++) {

	echo $i ."\n";
 try {
	 $contact = new \go\modules\community\addressbook\model\Contact();
	 $contact->addressBookId = \go\modules\community\addressbook\model\AddressBook::find()->selectSingleValue('id')->single();
	 $contact->name = 'test ' . $i;
	 $contact->save();
 }catch(PDOException $e) {
	 echo getmypid() . " ". $e->getMessage() ."\n";
 }
}