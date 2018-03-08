<?php
if(\GO\Base\Db\Utils::fieldExists('go_users', 'company') && \GO::modules()->addressbook){
	$ab = \GO\Addressbook\Model\Addressbook::model()->findSingleByAttribute('users', '1');//\GO::t('users','base'));

	$pdo = \GO::getDbConnection();

	$pdo->query("INSERT INTO ab_companies (`addressbook_id`,`name`, `email`, `country`, `state`, `city`, `zip`, `address`, `address_no`) SELECT {$ab->id},`company`, `email`, `country`, `state`, `city`, `zip`, `address`, `address_no`  FROM `go_users` WHERE company!=''");

	$sql = "UPDATE ab_companies SET post_address=address,post_address_no=address_no,post_country=country,post_state=state,post_city=city,post_zip=zip WHERE addressbook_id={$ab->id}";
	$pdo->query($sql);

	$sql = "UPDATE ab_contacts SET company_id=(select id from ab_companies where email=ab_contacts.email collate utf8_general_ci and addressbook_id={$ab->id} LIMIT 0,1) WHERE addressbook_id={$ab->id}";
	$pdo->query($sql);
}