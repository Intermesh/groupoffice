<?php
namespace go\modules\community\pages\model;
						
use go\core\acl\model\AclEntity;
use go\core\db\Criteria;
use go\core\db\Query;
						
/**
 * Site model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Site extends AclEntity {
	
	/**
	 * 
	 * @var int
	 */							
	public $id;

	/**
	 * 
	 * @var string
	 */							
	protected $siteName;

	/**
	 * 
	 * @var int
	 */							
	public $fileFolderId;

	/**
	 * 
	 * @var int
	 */							
	public $aclId;

	/**
	 * 
	 * @var int
	 */							
	public $modifiedBy;

	/**
	 * 
	 * @var int
	 */							
	public $createdBy;

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
	 * @var string
	 */							
	public $documentFormat = 'html';

	/**
	 * 
	 * @var string
	 */							
	protected $slug;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("pages_site");
	}
	
	public static function filter(Query $query, array $filter) {
		
		if(!empty($filter['q'])) {
			$query->andWhere(
							(new Criteria())
							->where('siteName', 'LIKE', $filter['q'] . '%')
							->orWhere('slug', 'LIKE', '%'. $filter['q'] .'%')
							->orWhere('modifiedBy', 'LIKE', $filter['q'] .'%')
							->orWhere('createdBy', 'LIKE', $filter['q'] .'%')
							);
		}
		return parent::filter($query, $filter);
	}
	public function getSiteName(){
	    return $this->siteName;
	}
	
	public function setSiteName($name){
	    $this->siteName = $name;
	    if(empty($this->slug)) {
		$this->slug = strtolower(preg_replace('/[ \'\"\&\#\(\)\[\]\{\}\$\+\,\.\/\\\:\;\=\?\@\^\<\>\!\*\|\%]/', '_', $name));
	    }
	}
	
	public static function findBySlug($slug){
	    return self::find()->where(['slug' => $slug])->single();
	    
	}
	

}