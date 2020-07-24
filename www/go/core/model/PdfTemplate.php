<?php
namespace go\core\model;


use go\core\db\Criteria;
use go\core\fs\Blob;
use go\core\jmap\Entity;
use \go\core\model\PdfBlock;

/**
 * The Pdf model
 *
 * For usage see {@see PdfRenderer}
 *
 *
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class PdfTemplate extends Entity {
	
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
	 * Arbitrary string to identity where the template belongs to. For exampkle a bussinessId in the
	 * quote module
	 *
	 * @var string
	 */
	public $key;

	/**
	 * 
	 * @var string
	 */							
	public $language;

	/**
	 * 
	 * @var string
	 */							
	public $name;

	/**
	 * 
	 * @var string
	 */							
	protected $stationaryBlobId;

	/**
	 * 
	 * @var double
	 */							
	public $marginLeft = 10.0;

	/**
	 * 
	 * @var double
	 */							
	public $marginRight = 10.0;

	/**
	 * 
	 * @var double
	 */							
	public $marginTop = 10.0;

	/**
	 * 
	 * @var double
	 */							
	public $marginBottom = 10.0;

	/**
	 * 
	 * @var bool
	 */							
	public $landscape = false;

	/**
	 * Defaults to A4
	 * @var string
	 */							
	public $pageSize = 'A4';

	/**
	 * Defaults to mm
	 * @var string
	 */							
	public $measureUnit = 'mm';

	/**
	 * @var PdfBlock[]
	 */
	public $blocks = [];

	protected static function defineMapping() {
		return parent::defineMapping()
			->addTable('core_pdf_template')
			->addArray('blocks', PdfBlock::class, ['id' => 'pdfTemplateId']);

	}

	protected static function defineFilters() {
		return parent::defineFilters()
			->add('module', function (Criteria $criteria, $module){
				$module = Module::findByName($module['package'], $module['name']);
				$criteria->where(['moduleId' => $module->id]);
			})
			->add('key', function (Criteria $criteria, $value){
				$criteria->where(['key' => $value]);
			});

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
}
