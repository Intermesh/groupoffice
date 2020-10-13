<?php
namespace go\core\model;

use go\core\orm\Property;


/**
 * The PdfBlock model
 *
 *
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class PdfBlock extends Property {
	
	/**
	 * 
	 * @var int
	 */							
	public $id;

	/**
	 * 
	 * @var int
	 */							
	public $pdfTemplateId;

	/**
	 * 
	 * @var string
	 */							
	public $type = 'text';

	/**
	 * If x is null then the left margin will be used
	 * @var double
	 */							
	public $x;

	/**
	 * If y is null then it will continue on where last block had the highest y
	 * @var double
	 */							
	public $y;

	/**
	 * If null then the full page width will be used
	 * @var double
	 */							
	public $width;

	/**
	 * If null then the height will be automatic depending on the content.
	 * @var double
	 */							
	public $height;

	/**
	 * See tcpdf align. Default to L for left.
	 * @var string
	 */							
	public $align = 'L';

	/**
	 * JSON content
	 * @var string
	 */							
	public $content;

	protected static function defineMapping()
	{
		return parent::defineMapping()
			->addTable('core_pdf_block');
	}
}

