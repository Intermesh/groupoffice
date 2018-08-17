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
	 * @var \IFW\Util\DateTime
	 */							
	public $releaseDate;
	
	/**
	 * The album genres
	 * 
	 * @var AlbumGenre[]
	 */
	public $genres;	
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("music_album")
						->addRelation('genres', AlbumGenre::class, ['id' => 'albumId']);
	}

}