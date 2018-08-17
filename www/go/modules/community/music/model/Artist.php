<?php
namespace go\modules\community\music\model;

use go\core\orm\Entity;
						
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
	 * @var \IFW\Util\DateTime
	 */							
	public $createdAt;

	/**
	 * 
	 * @var \IFW\Util\DateTime
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
						->addTable("music_artist")
						->addRelation('albums', Album::class, ['id' => 'artistId']);
	}
	
	/**
	 * Filter entities See JMAP spec for details on the $filter array.
	 * 
	 * @link https://jmap.io/spec-core.html#/query
	 * @param Query $query
	 * @param array $filter key value array eg. ["q" => "foo"]
	 * @return Query
	 */
	public static function filter(\go\core\db\Query $query, array $filter) {
		
		//Handle quick search filter parameter
		if(isset($filter['q'])) {
			$query->where('name','LIKE', $filter['q'] .'%');
		}
		
		//An array of Genre ID's can be passed
		if(!empty($filter['genres'])) {
			//filter artists on their album genres
			$query->join('music_album', 'a', 'a.artistId = t.id')
					->join('music_album_genre', 'g', 'a.id = g.albumId')
					->groupBy(['t.id']) // group the results by id to filter out duplicates because of the join
					->where(['g.genreId' => $filter['genres']]);			
		}
		
		//Always return parent filter function because it may implement core filters.
		return parent::filter($query, $filter);
	}

}