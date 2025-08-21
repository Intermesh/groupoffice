<?php
namespace go\core\model;


use go\core\cron\GarbageCollection;
use go\core\db\Criteria;
use go\core\fs\Blob;
use go\core\jmap\Entity;
use go\core\model\Module as ModuleModel;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\validate\ErrorCode;

/**
 * The Pdf model
 *
 * For usage see {@see PdfRenderer}
 *
 * Because these models are polymorphic relations they need to be cleaned up by the code.
 * You could to this with the garbage collection event.
 * @see GarbageCollection::EVENT_RUN
 *
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class PdfTemplate extends Entity {

	public ?string$id;

	public string $moduleId;

	/**
	 * Arbitrary string to identity where the template belongs to. For exampkle a bussinessId in the
	 * quote module
	 *
	 */
	public ?string $key = null;

	/**
	 * 
	 * @var string
	 */							
	public string $language;

	/**
	 * 
	 * @var string
	 */							
	public string $name;

	protected ?string $stationaryBlobId = null;

	/**
	 *
	 * @var string
	 */
	protected ?string $logoBlobId = null;

	/**
	 * 
	 * @var double
	 */							
	public float $marginLeft = 10.0;

	/**
	 * 
	 * @var double
	 */							
	public float $marginRight = 10.0;

	/**
	 * 
	 * @var double
	 */							
	public float $marginTop = 10.0;

	/**
	 * 
	 * @var double
	 */							
	public float $marginBottom = 10.0;

	/**
	 * 
	 * @var bool
	 */							
	public bool $landscape = false;

	/**
	 * Defaults to A4
	 * @var string
	 */							
	public string $pageSize = 'A4';

	/**
	 * Defaults to mm
	 * @var string
	 */							
	public string $measureUnit = 'mm';

	public string $fontFamily = "dejavusans";

	public int $fontSize = 10;

	/**
	 * @var PdfBlock[]
	 */
	public array $blocks = [];


	public ?string $footer = null;
	public ?string $header = null;

	public float $headerX = 0;
	public float $headerY = 20;


	public float $footerX = 0;
	public float $footerY = -10;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('core_pdf_template')
			->addArray('blocks', PdfBlock::class, ['id' => 'pdfTemplateId']);

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
	 * @return PdfTemplate|null
	 */
	public static function findByModule(string $package, string $name, ?string $preferredLanguage = null, string|null $key = null) : ?PdfTemplate {
		$moduleModel = ModuleModel::findByName($package, $name);

		$template = isset($preferredLanguage) ? static::find()->where(['moduleId' => $moduleModel->id, 'key'=> $key, 'language' => $preferredLanguage])->single() : null;
		if (!$template) {

			if($preferredLanguage != go()->getSettings()->language) {
				return self::findByModule($package, $name, go()->getSettings()->language, $key);
			}

			$template = static::find()->where(['moduleId' => $moduleModel->id, 'key'=> $key])->single();
		}

		return $template;
	}

	/**
	 * @todo Template permissions should be connected to an entity just like a comment.
	 * @return int
	 */
	protected function internalGetPermissionLevel(): int
	{
		return Module::findById($this->moduleId)->getPermissionLevel() > 0 ? Acl::LEVEL_MANAGE : 0;
	}

	protected function canCreate(): bool
	{
		return Module::findById($this->moduleId)->getPermissionLevel() > 0;
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

	/**
	 * Get stationary PDF blob
	 *
	 * @return Blob
	 * @throws \Exception
	 */
	public function getStationary() {
		if(!empty($this->stationaryBlobId)){
			return Blob::findById($this->stationaryBlobId);
		}
		return null;
	}

	public function setStationary($blob) {
		if(!$blob) {
			$this->stationaryBlobId = NULL;
		} else{
			$blob = (array) $blob;
			$this->stationaryBlobId = $blob['id'];
		}
	}

	/**
	 * Get stationary PDF blob
	 *
	 * @return Blob
	 * @throws \Exception
	 */
	public function getLogo() {
		if(!empty($this->logoBlobId)){
			return Blob::findById($this->logoBlobId);
		}
		return null;
	}

	public function setLogo($blob) {
		if(!$blob) {
			$this->logoBlobId = NULL;
		} else{
			$blob = (array) $blob;
			$this->logoBlobId = $blob['id'];
		}
	}
}
