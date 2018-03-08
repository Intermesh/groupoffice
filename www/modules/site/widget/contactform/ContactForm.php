<?php


namespace GO\Site\Widget\Contactform;


class ContactForm extends \GO\Base\Model {
	
	
	/**
	 * @var string URL anti spam trap for bots filling in all the fields. It must always be empty.
	 */
	public $url;
	
	/**
	 * @var string email from input
	 */
	public $email;
	
	/**
	 * @var StringHelper name input 
	 */
	public $name;
	
	/**
	 * @var StringHelper message input
	 */
	public $message;
	
	/**
	 * @var StringHelper email to input
	 */
	public $receipt;
	
	/**
	 * Returns the validation rules of the model.
	 * @return array validation rules
	 */
	public function validate()
	{		
		if(empty($this->name))
			$this->setValidationError('name', sprintf(\GO::t("Field %s is required"),'name'));
		if(empty($this->email))
			$this->setValidationError('email', sprintf(\GO::t("Field %s is required"),'email'));
		if(empty($this->message))
			$this->setValidationError('message', sprintf(\GO::t("Field %s is required"),'message'));
		if(!\GO\Base\Util\Validate::email($this->email))
			$this->setValidationError('email', \GO::t("The e-mail address was invalid"));
			
		return parent::validate();
	}
	
	/**
	 * send an email to webmaster_email in config
	 * @return boolean true when successfull
	 */
	public function send(){
		
		if(!$this->validate())
			return false;
		
		if(empty($this->url)) {
			$message = \GO\Base\Mail\Message::newInstance();
			$message->setSubject("Groupoffice contact form");
			$message->setBody($this->message);
			$message->addFrom($this->email, $this->name);
			$message->addTo($this->receipt);
			return \GO\Base\Mail\Mailer::newGoInstance()->send($message);
		}else
		{
			return false;
		}
	}
}
