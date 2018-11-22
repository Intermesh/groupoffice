<?php
namespace GO\Email\Model;

use go\core\acl\model\AclOwnerEntity;

class EmailTemplate extends AclOwnerEntity {
	
	public $id;
	
	public $ownedBy;
	
	public $subject;
	
	public $body;
	
	public $name;
	
	/**
	 *
	 * @var EmailTemplateAttachment[]
	 */
	public $attachments;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("email_template")
						->addRelation('attachments', EmailTemplateAttachment::class, ['id' => 'templateId']);
	}	
	
	protected function internalSave() {		
		$this->parseImages();		
		return parent::internalSave();
	}
	
	private function parseImages() {
		$cids = \go\core\fs\Blob::parseFromHtml($this->body);
		
		$existing = [];
		foreach($this->attachments as $a) {
			$existing[$a->blobId] = $a;
		}
		$this->attachments = [];
		foreach($cids as $blobId) {
			$this->attachments[] = $existing[$blobId] ?? (new EmailTemplateAttachment())->setValues(['blobId' => $blobId]);
		}
	}
	
	public static function filter(\go\core\orm\Query $query, array $filter) {
		
		if(array_key_exists('ownedBy', $filter)) {
			$query->where('ownedBy','=', $filter['ownedBy']);
		}
		
		return parent::filter($query, $filter);
	}
}
