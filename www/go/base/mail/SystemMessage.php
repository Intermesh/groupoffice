<?php


namespace GO\Base\Mail;


class SystemMessage extends SmimeMessage {

	private $_account;
	private $_alias;
		
	public function __construct($subject = null, $body = null, $contentType = null, $charset = null) {
		parent::__construct($subject, $body, $contentType, $charset);
		
		// Check if the account needs to be set
		if(!empty(\GO::config()->smtp_account_id)){
			
			$this->_setAccount(); 
		
			// Check if the message needs to be signed with smime
			if(!empty(\GO::config()->smtp_account_smime_sign))
				$this->_setSmime();
		}else
		{
			$this->setFrom(\GO::config()->webmaster_email, \GO::config()->title);
		}
	}
	
	/**
	 * This function will be called when the $config['smtp_account_id'] is set in the Group-Office config file.
	 * If the account cannot be found then this function will return an exception
	 * 
	 * @throws \GO\Base\Exception\NotFound
	 */
	private function _setAccount(){
		
		$findParams = \GO\Base\Db\FindParams::newInstance()->ignoreAcl();
		$this->_account = \GO\Email\Model\Account::model()->findByPk(\GO::config()->smtp_account_id,$findParams,true);
			
		if(!$this->_account)
			throw new \GO\Base\Exception\NotFound('The mailaccount given in the Group-Office config file cannot be found');

		$this->_alias = $this->_account->defaultAlias;

		$this->setFrom($this->_alias->email,$this->_alias->name);
	}
	
	/**
	 * Enable Smime for this message.
	 * The Smime module needs to be installed for this function to work. Otherwise it will return an exception.
	 * The Smime password needs to be set in the $config['smtp_account_smime_password'] parameter in the Group-Office config file otherwise this function will throw an error.
	 * 
	 * @throws Exception
	 */
	private function _setSmime(){
		
		// Check if the smime module is installed
		if(!\GO::modules()->isInstalled("smime"))
			Throw new \Exception('Smime module not installed');

		if(empty(\GO::config()->smtp_account_smime_password))
			Throw new \Exception('No password for smime set in the Group-Office config file');
		
		// Check for a certificate for the give email account
		$cert = \GO\Smime\Model\Certificate::model()->findByPk($this->_account->id);
		
		if(!$cert || empty($cert->cert))
			Throw new \Exception('No certificate enabled for the given account');

		// If the certificate is found, then get the password and attach the certificate to the message
		$this->setSignParams($cert->cert, \GO::config()->smtp_account_smime_password);
	}
	
	/**
	 * Get the alias of the account
	 * If no account is set then this function will return false
	 * 
	 * @return mixed boolean/\GO\email\Model\Alias
	 */
	public function getAccountAlias(){
		
		if(!$this->hasAccount())
			return false;
		
		return $this->_alias;
	}
	
	/**
	 * Check if the account is set for this message
	 * 
	 * @return boolean
	 */
	public function hasAccount(){
		return !empty($this->_account);
	}

	/**
	 * Get the Transport object for this message (Based on the account)
	 * 
	 * @return mixed Transport/\GO\Email\Transport
	 */
	public function getTransport(){
		if(!$this->hasAccount()){
			return Transport::newGoInstance ();
		}else {
			return \GO\Email\Transport::newGoInstance($this->_account);
		}
	}
	
	/**
	 * Send the message with the GO mailer
	 * Use this send function to be sure that the mailer is using the Transporter of the 
	 * 
	 * @return boolean
	 */
	public function send(){
		return Mailer::newGoInstance($this->getTransport())->send($this);
	}
	
	
}
