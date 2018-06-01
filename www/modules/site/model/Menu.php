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
 * @version $Id: Menu.php 18376 2014-10-31 16:05:27Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 */
 
/**
 * The Menu model
 *
 * @package GO.modules.Site
 * @version $Id: Menu.php 18376 2014-10-31 16:05:27Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 *
 * @property int $id The id of this menu item
 * @property int $site_id The site_id of this menu
 * @property String $label The label of the menu item
 * @property String $menu_slug The slug of the menu item
 */


namespace GO\Site\Model;


class Menu extends \GO\Base\Db\ActiveRecord{
	
	/**
	 * Returns the table name
	 */
	 public function tableName() {
		 return 'site_menu';
	 }

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 * 
	 * "childContentItems" can only be used when the "content_id" property is set.
	 * 
	 */
	 public function relations() {
		 return array(
			'site'=>array('type'=>self::BELONGS_TO, 'model'=>"GO\Site\Model\Site", 'field'=>'site_id'),
			'children' => array('type' => self::HAS_MANY, 'model' => 'GO\Site\Model\MenuItem', 'field' => 'menu_id', 'delete' => false, 'findParams' =>\GO\Base\Db\FindParams::newInstance()->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('parent_id', null))->select('*')->order(array('sort_order')))
		 );
	 }
	 
	 /**
	  * Get the root menu tree nodes
	  * 
	  * @param Site $site
	  * @return array
	  */
	 public static function getTreeMenuNodes(Site $site){
			 
		 $tree = array();
		 $menus = Menu::model()->findByAttribute('site_id',$site->id,  \GO\Base\Db\FindParams::newInstance());
		 
		 foreach($menus as $menu){

			 $hasChildren = $menu->hasChildren();
			 
			 $node = array(
				'id' => $menu->site_id.'_menu_'.$menu->id,
				'menu_id' => $menu->id,
				'cls' => 'site-node-menu',
				'site_id'=>$menu->site->id, 
				'iconCls' => 'go-model-icon-Menu',
				'text' => $menu->label,
				'hasChildren' => $hasChildren,
				'expanded' => !$hasChildren || \GO\Site\Model\Site::isExpandedNode($menu->site_id.'_menu_'.$menu->id),	 
				'children'=> $hasChildren ? $menu->getMenuChildrenTree():null,
			 );
			 $tree[] = $node;
		 }
		 		 
		 return $tree;
	 }
	 
	  /**
	  * # Backend Functionality
	  * 
	  * Get the tree array for the children of the current item
	  * 
	  * @return array
	  */
	 public function getMenuChildrenTree(){
		 $tree = array();
		 $children = $this->children;
		 		 	 
		 foreach($children as $child){
			 
			 $hasChildren = $child->hasChildren();

			 $childNode = array(
				'id' => $this->site_id.'_menuitem_'.$child->id,
				'menu_id'=> $this->id,
				'menu_item_id'=>$child->id,
				'cls' => 'site-node-menuitem',
				'site_id'=>$this->site_id,
				'iconCls' => 'go-model-icon-Menuitem', 
				'text' => $child->label,
				'hasChildren' => $hasChildren,
				'expanded' => !$hasChildren || \GO\Site\Model\Site::isExpandedNode($this->site_id.'_menu_'.$child->id),	 
				'children'=> $hasChildren ? null : $child->getChildrenTree(),
			);
			 
			$tree[] = $childNode;
		 }
		 
		 return $tree;
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
	 
	 public static function setTreeSort($extractedParent,$sortOrder,$allowedTypes){
		 
		 $sort = 0;
		 
		 
		 \GO::debug(implode(',',$extractedParent));
		 \GO::debug(implode(',',$allowedTypes));
		 
		 foreach($sortOrder as $sortItem){
			 \GO::debug($sortItem);
			 $extrChild = \GO\Site\SiteModule::extractTreeNode($sortItem);
			 
			 if(in_array($extrChild['type'],$allowedTypes)){
				 
				 $modelName = \GO\Site\SiteModule::getModelNameFromTreeNodeType($extrChild['type']);
				 
				 $model = $modelName::model()->findByPk($extrChild['modelId']);
				 $model->parent_id = NULL;
				 $model->menu_id = $extractedParent['modelId'];
				 $model->sort_order = $sort;
				 if($model->save())				 
					$sort++;
			 }
		 }
		 
		 return array("success"=>true);
	 }
}
