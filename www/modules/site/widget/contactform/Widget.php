<?php

namespace GO\Site\Widget\Contactform;


class Widget extends \GO\Site\Components\Widget {

	public $receipt;						//send to email
	public $emailFieldOptions=array();		//html attributes for email field
	public $messageFieldOptions=array();	//html attributes for message field
	public $fieldSeparator = '';			//html between input fields
	public $submitButtonText = 'Send';		//text in submit button
	public $successText = "Thank you! Your message was sent successfully.";
	public $submitButtonOptions=array();
	protected $formModel;
	protected $form;
	
	
	public $sentSuccess=false;
	
	public function init() {
		$this->formModel = new \GO\Site\Widget\ContactForm\ContactForm();
		$this->formModel->receipt = isset($this->receipt) ? $this->receipt : \GO::config()->webmaster_email;
		$this->formModel->name = \GO::user() ? \GO::user()->name : 'Website Guest';

		$this->form = new \GO\Site\Widget\Form();
	}
	
	public function render()
	{
		$result = '';
		//URL field is for anti spam. bots fill in all the fields. It must be a hidden field in the view.

		if(isset($_POST['ContactForm']) && is_numeric($_POST['ContactForm']['url']) && $_POST['ContactForm']['url'] > strtotime("-1 hours") && $_POST['ContactForm']['url'] < time() - 5 ) {
			$this->formModel->email=$_POST['ContactForm']['email'];
			$this->formModel->message=$_POST['ContactForm']['message'];
			if($this->formModel->send()) {
				$this->sentSuccess=true;
				return $this->successText; 
			} else
				$result .= "Error sending message";
		}

		
		$result .= $this->form->beginForm("#contact");

		$result .= $this->form->textField($this->formModel, 'email', $this->emailFieldOptions);
		$result .= $this->form->error($this->formModel, 'email');
		$result .= $this->fieldSeparator;
		
		$this->formModel->url = time(); //will be checked for 5 secs
		
		$result .= '<div class="contact-form-url">';
		$result .= $this->form->textField($this->formModel, 'url', array());		
		$result .= '</div>';
		
		

		$result .= $this->form->textArea($this->formModel, 'message', $this->messageFieldOptions);
		$result .= $this->form->error($this->formModel, 'message');
		$result .= $this->fieldSeparator;

		$result .=$this->form->submitButton($this->submitButtonText, $this->submitButtonOptions);
		$result .= $this->form->endForm();

		return $result;
	}
}
