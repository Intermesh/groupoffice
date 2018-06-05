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
 * @version $Id: MenuItem.php 16900 2014-02-24 13:31:41Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 */
 
/**
 * The MenuItem model
 *
 * @package GO.modules.Site
 * @version $Id: MenuItem.php 16900 2014-02-24 13:31:41Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 *
 * @property int $menu_id The menu_id of this menu
 * @property int $id The id of this menu item
 * @property int $parent_id Optional: The parent menuItem of this current item.
 * @property int $content_id Optional: The content_id where this menu items links to
 * @property String $label The label of the menu item
 * @property String $url Optional: url field to create menu items that link to a page that you can fill in manually
 * @property Boolean $display_children Only usable when content_id is set. When true this will load the child items of the current content_id.
 * @property int $sort_order The sort order for the menu items at the same level
 * @property String $target The target for the url in this menu item
 */


namespace GO\Site\Model;


class MenuItem extends \GO\Base\Db\ActiveRecord{
	
	/**
	 * Returns the table name
	 */
	 public function tableName() {
		 return 'site_menu_item';
	 }

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 * 
	 */
	 public function relations() {
		 return array(
			'menu'=>array('type'=>self::BELONGS_TO, 'model'=>"GO\Site\Model\Menu", 'field'=>'menu_id'),
			'children' => array('type' => self::HAS_MANY, 'model' => 'GO\Site\Model\MenuItem', 'field' => 'parent_id', 'delete' => false, 'findParams' =>\GO\Base\Db\FindParams::newInstance()->select('*')->order(array('sort_order'))),
			'parent'=>array('type'=>self::BELONGS_TO, 'model'=>"GO\Site\Model\MenuItem", 'field'=>'parent_id'),
			'content'=>array('type'=>self::BELONGS_TO, 'model'=>"GO\Site\Model\Content", 'field'=>'content_id'),
		 );
	 }
		 
	 /**
	  * Check if this menu item has children
	  * 
	  * @return boolean
	  */
	 public function hasChildren(){
		 $child = $this->children(\GO\Base\Db\FindParams::newInstance()->single());
		 return !empty($child); 
	 }
	 
	  /**
	  * # Backend Functionality
	  * 
	  * Get the tree array for the children of the current item
	  * 
	  * @return array
	  */
	 public function getChildrenTree(){
		 $tree = array();
		 $children = $this->children;
		 		 	 
		 foreach($children as $child){
			 
			 $hasChildren = $child->hasChildren();

			 $childNode = array(
				'id' => $this->menu->site_id.'_menuitem_'.$child->id,
				'menu_id'=> $this->menu->id,
				'menu_item_id'=>$child->id,
				'cls' => 'site-node-menuitem',
				'site_id'=>$this->menu->site_id, 
				'iconCls' => 'go-model-icon-Menuitem', 
				'text' => $child->label,
				'hasChildren' => $hasChildren,
				'expanded' => !$hasChildren || \Site::isExpandedNode($this->menu->site_id.'_menu_'.$child->id),	 
				'children'=> $hasChildren ? null : $child->getChildrenTree(),
			);
			 
			$tree[] = $childNode;
		 }
		 
		 return $tree;
	 }
	 
	 public static function setTreeSort($extractedParent,$sortOrder,$allowedTypes){
		 
		 $sort = 0;
		 
		 foreach($sortOrder as $sortItem){
			 
			 $extrChild = \GO\Site\SiteModule::extractTreeNode($sortItem);
			 
			 if(in_array($extrChild['type'],$allowedTypes)){
				 
				 $modelName = \GO\Site\SiteModule::getModelNameFromTreeNodeType($extrChild['type']);
				 
				 $model = $modelName::model()->findByPk($extrChild['modelId']);
				 $model->parent_id = $extractedParent['modelId'];
				 $model->sort_order = $sort;
				 if($model->save())				 
					$sort++;
			 }
		 }
		 
		 return array("success"=>true);
	 }
	 
	 public function getUrl(){
		 
		 $url = '#';
		 
		 if(!empty($this->content_id))
			 $url = $this->content->getUrl();
		 else
			 $url = $this->url;
		 return $url;
	 }
}
