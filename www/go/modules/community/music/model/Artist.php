<?php
namespace go\modules\community\music\model;

use go\core\jmap\Entity;
use go\core\orm\Query;
use go\core\util\DateTime;
						
/**
 * Artist model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Artist extends Entity {
	
	/**
	 * 
	 * @var int
	 */							
	public $id;

	/**
	 * 
	 * @var string
	 */							
	public $name;

	/**
	 * 
	 * @var DateTime
	 */							
	public $createdAt;

	/**
	 * 
	 * @var DateTime
	 */							
	public $modifiedAt;

	/**
	 * 
	 * @var int
	 */							
	public $createdBy;

	/**
	 * 
	 * @var int
	 */							
	public $modifiedBy;
		
	/**
	 * The albums created by the artist
	 * 
	 * @var Album[]
	 */
	public $albums;	
	
	/**
	 * The photo of the artist (BLOB ID)
	 * 
	 * @var string
	 */
	public $photo;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("music_artist", "artist")
						->addRelation('albums', Album::class, ['id' => 'artistId']);
	}
	

	protected static function searchColumns() {
		return ['name'];
	}
	
	protected static function defineFilters() {
		return parent::defineFilters()
						->add('genres', function (Query $query, $value, array $filter) {
							if(!empty($value)) {
								$query->join('music_album', 'album', 'album.artistId = artist.id')
									->groupBy(['artist.id']) // group the results by id to filter out duplicates because of the join
									->where(['album.genreId' => $value]);	
							}
						});
	}

}