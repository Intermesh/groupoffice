<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

namespace GO\Notes\Model;

use GO\Base\Model\AbstractUserDefaultModel;

/**
 * 
 * The Category model
 * 
 * @property String $name The name of the category
 * @property int $files_folder_id
 * @property int $acl_id
 * @property int $user_id
 * 
 * @method Category model Returns a static model of itself
 */




class Category extends AbstractUserDefaultModel {

	
	public function aclField() {
		return 'acl_id';
	}

	public function tableName() {
		return 'no_categories';
	}
	
	public function hasFiles(){
		return true;
	}

	public function relations() {
		return array(
				'notes' => array('type' => self::HAS_MANY, 'model' => 'GO\Notes\Model\Note', 'field' => 'category_id', 'delete' => true)		);
	}
	
	protected function init() {
		$this->columns['name']['unique']=true;
		return parent::init();
	}
}