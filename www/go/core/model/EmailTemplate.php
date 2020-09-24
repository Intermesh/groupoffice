<?php
namespace go\core\model;

use go\core\db\Criteria;
use go\core\fs\Blob;
use go\core\acl\model\AclOwnerEntity;
use go\core\validate\ErrorCode;

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
			->addArray('attachments', EmailTemplateAttachment::class, ['id' => 'emailTemplateId']);
	}


	protected static function defineFilters() {
		return parent::defineFilters()
						->add('module', function (Criteria $criteria, $module){
              $module = Module::findByName($module['package'], $module['name']);
							$criteria->where(['moduleId' => $module->id]);		
						});
					
	}

	protected static function textFilterColumns() {
		return ['name'];
	}
	
	/**
	 *  
	 */ 
  public function setModule($module) {

		if(is_int($module)) {
			$this->moduleId = $module;
			return;
		}
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
			if(isset($existing[$blobId])) {
				$existing[$blobId]->inline = true;
				$this->attachments[] = $existing[$blobId];
			} else {
				$this->attachments[] = (new EmailTemplateAttachment())->setValues(['blobId' => $blobId, 'name' => $blob->name, 'inline' => true]);
			}			
		}

		foreach ($existing as $a) {
			if ($a->attachment) {
				$this->attachments[] = $a;
			}
		}
	}

	public function toArray($properties = [])
	{
		$array =  parent::toArray($properties);

		if(isset($array['attachments'])) {
			$array['attachments'] = array_filter($array['attachments'], function($a) {
				return $a['attachment'] == true;
			});
		}

		return $array;
	}
}
