<?php
namespace go\modules\community\pages\model;
						
use go\core\acl\model\AclItemEntity;
						
/**
 * Page model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Page extends AclItemEntity {
	
	/**
	 * 
	 * @var int
	 */							
	public $siteId;

	/**
	 * 
	 * @var int
	 */							
	public $id;

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
	protected $pageName = 'page';

	/**
	 * 
	 * @var string
	 */							
	protected $content;

	/**
	 * 
	 * @var int
	 */							
	public $sortOrder;

	/**
	 * 
	 * @var string
	 */							
	public $plainContent;

	/**
	 * 
	 * @var string
	 */							
	public $slug;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("pages_page");
	}
	protected static function aclEntityClass() {
		return Site::class;
	}

	protected static function aclEntityKeys() {
		return ['siteId' => 'id'];
	}
	
	public function getPageName() {
	    return $this->pageName;
	}
	
	public function setPageName($name){
	    $this->pageName = $name;
	    if(empty($this->slug)) {
		$this->slug = strtolower(preg_replace('/[ \&\#\(\)\[\]\{\}\$\+\,\.\/\\\:\;\=\?\@\^\<\>\!\*\|\%]/', '_', $name));
	    }
	}
	public function getContent() {
	    return $this->content;
	}
	
	public function setContent($content){
	    $this->content = $content;
	    if(empty($this->plainContent)) {
		$this->plainContent = strip_tags($content, '<h1><h2>');
	    }
	}
}