<?php

/**
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @package GO.modules.Site
 * @version $Id: Site.php 7607 2013-03-27 15:35:31Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 */

/**
 * The Site model
 *
 * @package GO.modules.Site
 * @version $Id: Site.php 7607 2013-03-27 15:35:31Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 *
 * @property int $id
 * @property String $name
 * @property int $user_id
 * @property int $mtime
 * @property int $ctime
 * @property String $domain
 * @property String $module
 * @property int $ssl
 * @property int $mod_rewrite
 * @property String $mod_rewrite_base_path
 * @property String $base_path
 * @property int $acl_id
 * @property string $language
 * @property type $files_folder_id
 */

namespace GO\Site\Model;

use go\core\http\Request;

class Site extends \GO\Base\Db\ActiveRecord {
	
	use \go\core\orm\CustomFieldsTrait;
	
	/**
	 *
	 * @var \GO\Site\Components\Config 
	 */
	private $_config;

	private $_treeState;
	
	private $_cf=array();	
	
	private static $fields;
	
	protected function init()
	{
		parent::init();

		if(Request::get()->isHttps()) {
			$this->ssl = true;
		}
	}

	
//	private function _loadFields(){
//		//load cf
//		if(!isset(self::$fields) && \GO::modules()->isInstalled('customfields')){
//			$fields = \GO\Customfields\Model\Field::model()->findByModel('GO\Site\Model\Site', false);
//
//			self::$fields=array();
//			foreach($fields as $field){
//				self::$fields[$field->name]= $field;
//			}
//		}
//	}
	
	/**
	 * Site model is cached in the session so we need to reload the static variables
	 * on wake up.
	 */
//	public function __wakeup() {
//		parent::__wakeup();
//		
//		$this->_loadFields();
//	}
//	
//	public function __get($name) {
//		
//		$this->_loadFields();
//		
//		if(isset(self::$fields[$name])){
//			return $this->getCustomFieldValueByName($name);
//		}  else {
//			return parent::__get($name);
//		}
//
//	}
//
//	/*
//	 * Attach the customfield model to this model.
//	 */
//	public function customfieldsModel() {
//		return 'GO\Site\Customfields\Model\Site';
//	}
	
	/**
	 * Enable this function if you want this model to check the acl's automatically.
	 */
	public function aclField() {
		return 'acl_id';
	}

	public function hasFiles() {
		return true;
	}
	
	
	
	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'site_sites';
	}

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	public function relations() {
		return array(
			'content' => array('type' => self::HAS_MANY, 'model' => 'GO\Site\Model\Content', 'field' => 'site_id', 'findParams'=>  \GO\Base\Db\FindParams::newInstance()->select('*')->order('sort_order'),  'delete' => true),
			'contentNodes' => array('type' => self::HAS_MANY, 'model' => 'GO\Site\Model\Content', 'field' => 'site_id', 'findParams'=> \GO\Base\Db\FindParams::newInstance()->select('*')->order('sort_order')->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('parent_id', null)),  'delete' => true)
		);
	}

//	/**
//	 * Get the path to the site's file storage. It is web accessible through an 
//	 * alias /public. This folder contains template files and component assets.
//	 * 
//	 * @return \GO\Base\Fs\Folder
//	 */
//	public function getFileStorageFolder(){
//		
//		$folder = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path.'site/'.$this->id.'/');
//		$folder->create();
//		
//		return $folder;
//	}
	
	/**
	 * Get the config parameters of the site.
	 * 
	 * @return \GO\Site\Components\Config
	 */
	public function getConfig(){
		if(!isset($this->_config))
		{
			$this->_config = new \GO\Site\Components\Config($this);
		}
		
		return $this->_config;
	}
	
	/**
	 * Return the module that handles the view of the site.
	 * 
	 * @return \GO\Base\Model\Module
	 * @throws Exception
	 */
	public function getSiteModule(){
		
		$module = \GO::modules()->isInstalled($this->module);		
		
		if(!$module)
			throw new \Exception("Module ".$this->module." not found!");
		
		return $module;
	}
	
	public static function getTreeNodes(){
		
		$tree = array();
//		$findParams = \GO\Base\Db\FindParams::newInstance();
		
		$sites = self::model()->find();
		
		foreach($sites as $site){

			// Site node
			$siteNode = array(
				'id' => 'site_' . $site->id,
				'cls' => 'site-node-site',
				'site_id'=>$site->id, 
				'iconCls' => 'go-model-icon-GO_Site_Model_Site', 
				'text' => $site->name, 
				'expanded' => true,
				'children' => array(
						// Content treeitems
						array(
							'id' => $site->id.'_content',
							'draggable' => false,
							'cls' => 'site-readonly',
							'site_id'=>$site->id, 
							'iconCls' => 'go-icon-layout', 
							'text' => \GO::t("Content", "site"),
							'expanded' => self::isExpandedNode('site_' . $site->id),
							'children' => $site->loadContentNodes()
						),
						// Menu treeitems
						array(
							'id' => $site->id.'_menu',
							'draggable' => false,
							'cls' => 'site-readonly',
							'site_id'=>$site->id, 
							'iconCls' => 'go-model-icon-Menuroot', 
							'text' => \GO::t("Menu's", "site"),
							'expanded' => self::isExpandedNode('site_' . $site->id),
							'children' => Menu::getTreeMenuNodes($site)
					)
				)
			);

			$tree[] = $siteNode;
		}
		
		return $tree;
	}
	
	public function loadContentNodes(){
		$treeNodes = array();
		
		$contentItems = $this->contentNodes;
			
		foreach($contentItems as $content){			
			$treeNodes[] = $content->getTreeNodeAttributes();
		}
		
		return $treeNodes;
	}

	public static function isExpandedNode($nodeId) {
		$state = \GO::config()->get_setting("site_tree_state", \GO::user()->id);

		if (empty($state)) {
			$decoded = base64_decode($nodeId);

			if (stristr($decoded, 'root') || stristr($decoded, 'content'))
				return true;
			else
				return false;
		}

		$treeState = json_decode($state);
		

		return in_array($nodeId, $treeState);
	}
	
	public function buildFilesPath() {
		return 'public/site/'.$this->id.'/files';
	}
	
	public function getPublicUrl(){
		return '/public/site/'.$this->id.'/';
	}
	
	public function getPublicPath(){
		return \GO::config()->file_storage_path.'public/site/'.$this->id.'/';
	}

}
