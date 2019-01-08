<?php
namespace go\modules\community\music\model;

use go\core\orm\Property;
						
/**
 * Album model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Album extends Property {
	
	/**
	 * 
	 * @var int
	 */							
	public $id;

	/**
	 * 
	 * @var int
	 */							
	public $artistId;

	/**
	 * 
	 * @var string
	 */							
	public $name;

	/**
	 * 
	 * @var \go\core\util\DateTime
	 */							
	public $releaseDate;
	
	/**
	 * The Album genre
	 * 
	 * @var int 
	 */
	public $genreId;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("music_album");
	}

}