<?php


namespace GO\Email\Model;


class ComposerMessage extends Message
{
	public function addTo($email, $personal = null)
	{
		if(!isset($this->attributes['to'])){
			$this->attributes['to'] = new \GO\Base\Mail\EmailRecipients();
		}
		
		$this->attributes['to']->addRecipient($email, $personal);
	}
	
	public function addCc($email, $personal = null)
	{
		if(!isset($this->attributes['cc'])){
			$this->attributes['cc'] = new \GO\Base\Mail\EmailRecipients();
		}
		
		$this->attributes['cc']->addRecipient($email, $personal);
	}
	
	public function addBcc($email, $personal = null)
	{
		if(!isset($this->attributes['bcc'])){
			$this->attributes['bcc'] = new \GO\Base\Mail\EmailRecipients();
		}
		
		$this->attributes['bcc']->addRecipient($email, $personal);
	}

	public function getHtmlBody()
	{
		return '';
	}
	
	public function getPlainBody()
	{
		return '';
	}
	
	public function getSource()
	{
	
	}
}
