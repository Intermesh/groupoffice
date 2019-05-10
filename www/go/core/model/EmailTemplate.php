<?php
namespace go\core\model;

use go\core\db\Criteria;
use go\core\fs\Blob;
use go\core\acl\model\AclOwnerEntity;

/**
 * Newsletter model
 *
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class EmailTemplate extends AclOwnerEntity
{

	/**
	 * 
	 * @var int
	 */
	public $id;


	/**
	 * 
	 * @var int
	 */
	protected $moduleId;

	/**
	 * 
	 * @var string
	 */
	public $body;

	/**
	 * 
	 * @var string
	 */
	public $name;

	/**
	 * 
	 * @var string
	 */
	public $subject;

	/**
	 * 
	 * @var EmailTemplateAttachment[]
	 */
	public $attachments = [];


	protected static function defineMapping()
	{
		return parent::defineMapping()		
			->addTable("core_email_template", "newsletter")
			->addRelation('attachments', EmailTemplateAttachment::class, ['id' => 'emailTemplateId'], true);
	}


	protected static function defineFilters() {
		return parent::defineFilters()
						->add('module', function (Criteria $criteria, $module){
              $module = Module::findByName($module['package'], $module['name']);
							$criteria->where(['moduleId' => $module->id]);		
						});
					
	}
	
	 
  public function setModule($module) {
    $module = Module::findByName($module['package'], $module['name']);
    if(!$module) {
      $this->setValidationError('module', ErrorCode::INVALID_INPUT, 'Module was not found');
    }
    $this->moduleId = $module->id;
  }

	protected function internalSave()
	{		
		$this->parseImages();

		return parent::internalSave();
	}

	
	private function parseImages()
	{
		$cids = Blob::parseFromHtml($this->body);

		$existing = [];
		foreach ($this->attachments as $a) {
			$existing[$a->blobId] = $a;
		}
		$this->attachments = [];
		foreach ($cids as $blobId) {
			$blob = Blob::findById($blobId);
			$this->attachments[] = $existing[$blobId] ?? (new EmailTemplateAttachment())->setValues(['blobId' => $blobId, 'name' => $blob->name, 'inline' => true]);
			unset($existing[$blobId]);
		}

		foreach ($existing as $a) {
			if ($a->attachment) {
				$this->attachments[] = $a;
			}
		}
	}
}
