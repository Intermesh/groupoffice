<?php
namespace GO\Email\Model;

use go\core\orm\Property;

class EmailTemplateAttachment extends Property {
	
	public $id;
	
	public $templateId;
	public $blobId;
	
	protected static function defineMapping() {
		return parent::defineMapping()->addTable("email_template_attachment");
	}	
}
