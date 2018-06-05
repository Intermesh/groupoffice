<?php


namespace GO\Imapauth;


class Authenticator {

	public $config;
	public $goUsername;
	public $imapUsername;
	public $imapPassword;
	public $email;
	public $user;

	public function setCredentials($username, $password) {
		\GO::debug('IMAPAUTH: module active');
		$arr = explode('@', $username);

		$this->email = trim($username);
		$mailbox = trim($arr[0]);
		$domain = isset($arr[1]) ? trim($arr[1]) : '';

		$config = $this->getDomainConfig($domain);

		if (!$config) {
			\GO::debug('IMAPAUTH: No config for domain found');
			return false;
		} else {
			\GO::debug($config);
			$this->config = $config;

			$this->goUsername = $this->imapUsername = $this->email;
			if ($config['remove_domain_from_username']) {
				$this->imapUsername = $mailbox;
			}

			$this->imapPassword = $password;

			\GO::debug('IMAPAUTH: Attempt IMAP login');

			return true;
		}
	}

	/**
	 * Authenticate to imap and return 
	 * @return \GO\Base\Model\User 
	 */
	
	public function imapAuthenticate() {
		
		//disable password validation because we can't control the external passwords
		\GO::config()->password_validate=false;
		
		$imap = new \GO\Base\Mail\Imap();
		
		
		try {		
			
			$imap->ignoreInvalidCertificates = !empty($this->config['novalidate_cert']);			
			
			$imap->connect(
							$this->config['host'], $this->config['port'], $this->imapUsername, $this->imapPassword, $this->config['ssl'], !empty($this->config['tls']));

			\GO::debug('IMAPAUTH: IMAP login succesful');
			$imap->disconnect();


			$user = \GO\Base\Model\User::model()->findSingleByAttribute('username', $this->goUsername);
			if ($user) {
				\GO::debug("IMAPAUTH: Group-Office user already exists.");
				if (!$user->checkPassword($this->imapPassword)) {
					\GO::debug('IMAPAUTH: IMAP password has been changed. Updating Group-Office database');

					$user->password = $this->imapPassword;
					if(!$user->save()){
						throw new \Exception("Could not save user: ".implode("\n", $user->getValidationErrors()));
					}
				}
				$this->user = $user;

				if (\GO::modules()->isInstalled('email')) {
					if (!$this->checkEmailAccounts($this->user, $this->config['host'], $this->imapUsername, $this->imapPassword)) {
						$this->createEmailAccount($this->user, $this->config, $this->imapUsername, $this->imapPassword);
					}
				}
			}

			return true;
		} catch (\Exception $e) {
			\GO::debug('IMAPAUTH: Authentication to IMAP server failed with Exception: ' . $e->getMessage() . ' IMAP error:' . $imap->last_error());
			$imap->clear_errors();

			\GO::session()->logout(); //for clearing remembered password cookies

			return false;
		}
	}
	
	public function checkEmailAccounts($user, $host, $imapUsername, $password){
		$stmt = \GO\Email\Model\Account::model()->findByAttributes(array(
					'host' => $host,
					'username' => $imapUsername
							));
		$foundAccount = false;		
		while ($account = $stmt->fetch()) {
			
			\GO::debug("IMAPAUTH: Updating account ".$account->id);

			if($account->user_id==$user->id)
				$foundAccount=true;
			
			$account->password = $password;
			$account->store_password = !isset($this->config['store_password']) || !empty($this->config['store_password']) ? 1 : 0;
			$account->store_smtp_password = !empty($account->store_password) && !empty($this->config['smtp_use_login_credentials']) ? 1 : 0;
			
			if (!empty($this->config['smtp_use_login_credentials'])) {
				\GO::debug("IMAPAUTH: Setting SMTP password too");
				$account->smtp_username = $imapUsername;
				$account->smtp_password = $password;
			}
			if(!$account->save()){
				throw new \Exception("Could not save e-mail account: ".implode("\n", $account->getValidationErrors()));				
			}
		}
		
		return $foundAccount;
	}
	
	public function createEmailAccount($user, $config, $username, $password) {
		
		if(isset($config['create_email_account']) && $config['create_email_account']==false){
			\GO::debug('IMAPAUTH: E-mail account creation disabled for '.$username);
			return false;
		}
		
		if (\GO::modules()->isInstalled('email') && \GO\Base\Model\Acl::getUserPermissionLevel(\GO::modules()->email->acl_id, $user->id)) {
			
			\GO::debug('IMAPAUTH: Creating IMAP account for user');
			$account['user_id'] = $user->id;
			$account['type'] = 'imap'; //$config['proto'];
			$account['host'] = $config['host'];
			$account['smtp_host'] = $config['smtp_host'];
			$account['smtp_port'] = $config['smtp_port'];
			$account['smtp_encryption'] = $config['smtp_encryption'];

			if (!empty($config['smtp_use_login_credentials'])) {
				$account['smtp_username'] = $username;
				$account['smtp_password'] = $password;
			} elseif (isset($config['smtp_username'])) {
				$account['smtp_username'] = $config['smtp_username'];
				$account['smtp_password'] = $config['smtp_password'];
			}

			$account['imap_allow_self_signed'] = !empty($config['novalidate_cert']);
			$account['port'] = $config['port'];
//			$account['use_ssl'] = empty($config['ssl']) ? 0 : 1;
			
			$account['imap_encryption'] = null;
			
			if(!empty($config['ssl'])){
				$account['imap_encryption'] = 'ssl';
			}
			
			if(!empty($config['tls'])){
				$account['imap_encryption'] = 'tls';
			}
			
			$account['mbroot'] = $config['mbroot'];
			$account['username'] = $username;
			
			$account['store_password']= !isset($config['store_password']) || !empty($config['store_password']) ? 1 : 0;
			$account['store_smtp_password']= !empty($account['store_password']) && !empty($config['smtp_use_login_credentials']) ? 1 : 0;
			$account['password'] = $password;

			//set session pass.


			$model = new \GO\Email\Model\Account();
			$model->setAttributes($account);
			$model->save();
			if(!$model->save()){
				throw new \Exception("Could not save e-mail account: ".implode("\n", $model->getValidationErrors()));				
			}
			$model->addAlias($user->email, $user->name);
			
		}else
		{
			\GO::debug('IMAPAUTH: E-mail module not installed. Skipping e-mail account creation.');
		}
	}

	public function getDomainConfig($domain) {
		
		\GO::debug("IMAPAUTH: Finding config for domain: ".$domain);
		if (!empty($domain)) {
			$conf = str_replace('config.php', 'imapauth.config.php', \GO::config()->get_config_file());

			if (file_exists($conf)) {
				require($conf);
				$configs = isset($config) ? $config : array();
			} else {
				$configs = array();
			}
			foreach ($configs as $config) {
				if ($config['domains'] == '*') {
					return $config;
				}
				$domains = explode(',', $config['domains']);
				$domains = array_map('trim', $domains);

				if (in_array($domain, $domains)) {
					return $config;
				}
			}
		}
		return false;
	}

}
