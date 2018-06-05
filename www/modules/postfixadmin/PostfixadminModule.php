<?php


namespace GO\Postfixadmin;


class PostfixadminModule extends \GO\Base\Module {

	public function install() {

		parent::install();		
		
		$domains = empty(\GO::config()->serverclient_domains) ? array() : explode(',', \GO::config()->serverclient_domains);

		foreach ($domains as $domain) {
			if (!empty($domain)) {
				
				$domainModel = new Model\Domain();
				$domainModel->domain=$domain;
				$domainModel->save();
				
				$mailboxModel = new Model\Mailbox();
				$mailboxModel->domain_id=$domainModel->id;
				$mailboxModel->username='admin@'.$domain;
				$mailboxModel->password='admin';
				$mailboxModel->name="System administrator";
				$mailboxModel->save();				
			}
		}
	}
}
