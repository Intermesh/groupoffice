<?php
namespace go\core\model;


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
class Pdf extends Entity {
	
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
	public $stationaryBlobId;

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
}
