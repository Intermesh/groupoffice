<?php
namespace go\core\model;

use Exception;
use GO\Base\Mail\SmimeMessage;
use GO\Base\Util\StringHelper;
use go\core\cron\GarbageCollection;
use go\core\db\Criteria;
use go\core\fs\Blob;
use go\core\acl\model\AclOwnerEntity;
use go\core\fs\Folder;
use go\core\jmap\Entity;
use go\core\mail\Message;
use go\core\model\Module as ModuleModel;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\TemplateParser;
use go\core\validate\ErrorCode;
use Swift_Attachment;
use Swift_EmbeddedFile;

/**
 * E-mail template model
 *
 *
 * Because these models are polymorphic relations they need to be cleaned up by the code.
 * You could to this with the garbage collection event.
 * @see GarbageCollection::EVENT_RUN
 *
 * @example
 * ```
 * $template = EmailTemplate::find()
 * ->filter([
 *  'module' => ['name' => 'contracts', 'package' => 'business'],
 *  'key' => null,
 *  'language' => 'en'
 * ])
 * ->single();
 *```
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class EmailTemplate extends Entity
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
	public $moduleId;

	public $key;

	public $language = "en";

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


	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()		
			->addTable("core_email_template", "newsletter")
			->addArray('attachments', EmailTemplateAttachment::class, ['id' => 'emailTemplateId']);
	}


	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('module', function (Criteria $criteria, $module){
        $module = Module::findByName($module['package'], $module['name']);
				$criteria->where(['moduleId' => $module->id]);
			})
			->add('language' , function(Criteria $criteria, $language){
				$criteria->where('language', '=',$language);
			})
			->add('key', function (Criteria $criteria, $value){
				$criteria->where(['key' => $value]);
			});
					
	}

	protected static function textFilterColumns(): array
	{
		return ['name'];
	}


	/**
	 * Find templates by module key and language
	 *
	 * @param string $package
	 * @param string $name
	 * @param string|null $preferredLanguage
	 * @param string|null $key
	 * @return EmailTemplate|null
	 */
	public static function findByModule(string $package, string $name, ?string $preferredLanguage = null, string $key = null) : ?EmailTemplate {
		$moduleModel = ModuleModel::findByName($package, $name);

		$template = isset($preferredLanguage) ? static::find()->where(['moduleId' => $moduleModel->id, 'key'=> $key, 'language' => $preferredLanguage])->single() : null;
		if (!$template) {
			$template = static::find()->where(['moduleId' => $moduleModel->id, 'key'=> $key])->single();
		}

		return $template;
	}
	
	/**
	 * @param $module array{package:string, module:string} | int
	 */ 
  public function setModule( $module) {

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

	protected function internalSave(): bool
	{		
		$this->parseImages();

		return parent::internalSave();
	}

	public static function fromBlob(Blob $blob) {

		if(!class_exists("\ZipArchive")) {
			throw new \Exception('ZIP extension is not available for PHP please install it before uploading templates');
		}
		$folder = Folder::tempFolder();
		$zip = new \ZipArchive;
		if(!$zip->open($blob->path()) === true) {
			throw new \Exception('Failed to open uploaded Zip file');
		}

		$zip->extractTo($folder->getPath());
		$zip->close();

		$tpl = new self();
		$indexFile = $folder->getFiles()[0];
		$tpl->body = $indexFile->getContents();

		$imgFolder = $folder->getFolder('images');
		$tpl->attachments = [];
		foreach($imgFolder->getFiles() as $imageFile) {
			$imgBlob = Blob::fromFile($imageFile);
			$tpl->body = str_replace('images/'.$imageFile->getName(), Blob::url($imgBlob->id), $tpl->body);
			$imgBlob->save();
			$tpl->attachments[] = (new EmailTemplateAttachment($tpl))->setValues(['blobId' => $imgBlob->id, 'name' => $imgBlob->name, 'inline' => true]);
		}

		return $tpl;

	}

//	protected function internalGetPermissionLevel(): int
//	{
//		return Module::findById($this->moduleId)->getPermissionLevel();
//	}

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
				$this->attachments[] = (new EmailTemplateAttachment($this))->setValues(['blobId' => $blobId, 'name' => $blob->name, 'inline' => true]);
			}			
		}

		foreach ($existing as $a) {
			if ($a->attachment) {
				$this->attachments[] = $a;
			}
		}
	}

	public function toArray(array $properties = null): array
	{
		$array =  parent::toArray($properties);

		if(isset($array['attachments'])) {
			$array['attachments'] = array_values(array_filter($array['attachments'], function($a) {
				return $a['attachment'] == true;
			}));
		}

		return $array;
	}

	/**
	 * Create message from this template
	 *
	 * @param TemplateParser $templateParser
	 * @return Message
	 * @throws Exception
	 */
	public function toMessage(TemplateParser $templateParser): Message
	{
  	$message = go()->getMailer()->compose();
		$subject = $templateParser->parse($this->subject);
		$body = $templateParser->parse($this->body);

		$message->setSubject($subject)
			->setBody($body, 'text/html');

		foreach($this->attachments as $attachment) {
			$blob = Blob::findById($attachment->blobId);

			if($attachment->inline) {
				$img = Swift_EmbeddedFile::fromPath($blob->getFile()->getPath());
				$img->setContentType($blob->type);
				$img->setFilename($attachment->name);
				$contentId = $message->embed($img);

				$body = Blob::replaceSrcInHtml($body, $blob->id, $contentId);
			}

			if($attachment->attachment) {
				$a = Swift_Attachment::fromPath($blob->getFile()->getPath());
				$a->setContentType($blob->type);
				$a->setFilename($attachment->name);
				$message->attach($a);
			}
		}

		return $message;
	}

	public function toMessageArray(TemplateParser $templateParser) : array {

		$blobs = [];
		foreach($this->attachments as $attachment) {
			if($attachment->attachment) {
				$blobs[] = Blob::findById($attachment->blobId);
			}
		}

		return [
			"subject" => $templateParser->parse($this->subject),
			"body" => $templateParser->parse($this->body),
			"blobs" => $blobs
			];
	}
}
