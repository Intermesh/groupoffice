<?php


namespace GO\Base\Mail;


use GO\Smime\Model\Smime;

class SystemMessage extends SmimeMessage
{

	private $_account;
	private $_alias;

	/**
	 * @param string|null $subject
	 * @param string|null $body
	 * @param string|null $contentType
	 * @param string|null $charset
	 * @throws \GO\Base\Exception\NotFound
	 */
	public function __construct(string $subject = "", string $body = "", string $contentType = "text/plain")
	{
		parent::__construct($subject, $body, $contentType);

		if (!empty(\GO::config()->smtp_account_id)){
			// Check if the account needs to be set
			$this->_setAccountFromObsoleteConfig();
		
			// Check if the message needs to be signed with smime
			if(!empty(\GO::config()->smtp_account_smime_sign)) {
				$this->_setSmime();
			}
		} else {
			$this->setFrom(\GO::config()->webmaster_email, \GO::config()->title);
		}
	}
	
	/**
	 * This function will be called when the $config['smtp_account_id'] is set in the Group-Office config file.
	 * If the account cannot be found then this function will return an exception
	 * 
	 * @throws \GO\Base\Exception\NotFound
	 */
	private function _setAccountFromObsoleteConfig()
	{
		$this->setAccount(\GO::config()->smtp_account_id);
	}

	/**
	 * Override a default account from the constructor
	 *
	 * @param int $accountId
	 * @throws \GO\Base\Exception\NotFound
	 */
	public function setAccount(int $accountId)
	{
//		$findParams = \GO\Base\Db\FindParams::newInstance()->ignoreAcl();
		$this->_account = \GO\Email\Model\Account::model()->findByPk($accountId,false,true);
		if(!$this->_account) {
			throw new \GO\Base\Exception\NotFound('This mail account cannot be found');
		}
		$this->_alias = $this->_account->defaultAlias;

		$this->setFrom($this->_alias->email,$this->_alias->name);

		$this->getMailer()->setEmailAccount($this->_account);
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
		if(!\GO::modules()->isInstalled("smime")) {
			throw new \Exception('Smime module not installed');
		}
		if(empty(\GO::config()->smtp_account_smime_password)) {
			throw new \Exception('No password for smime set in the Group-Office config file');
		}
		// Check for a certificate for the give email account
		$cert = (new Smime($this->_account->id))->latestCert();
		
		if(!$cert || empty($cert->cert)) {
			throw new \Exception('No certificate enabled for the given account');
		}

		// If the certificate is found, then get the password and attach the certificate to the message
		$this->setSignParams($cert->cert, \GO::config()->smtp_account_smime_password);
	}
	
	/**
	 * Get the alias of the account
	 * If no account is set then this function will return false
	 * 
	 * @return mixed boolean/\GO\email\Model\Alias
	 */
	public function getAccountAlias()
	{
		if(!$this->hasAccount()) {
			return false;
		}
		
		return $this->_alias;
	}
	
	/**
	 * Check if the account is set for this message
	 * 
	 * @return bool
	 */
	public function hasAccount(): bool
	{
		return !empty($this->_account);
	}

	
	
}
