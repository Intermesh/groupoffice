<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author WilmarVB <wilmar@intermesh.nl>
 * @property int $user_id
 * @property int $folder_id
 */


namespace GO\Files\Model;


class Bookmark extends \GO\Base\Db\ActiveRecord {
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\Addressbook\Model\Company 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function tableName(){
		return 'fs_bookmarks';
	}
	
	public function primaryKey() {
		return array('user_id','folder_id');
	}
	
//	public function aclField() {
//		return 'folder.acl_id';
//	}
	
	public function relations() {
		return array(
			'folder' => array('type' => self::BELONGS_TO, 'model' => 'GO\Files\Model\Folder', 'field' => 'folder_id'),
		);
	}
	
	
	protected function beforeSave() {
		
		$folderModel = Folder::model()->findByPk($this->folder_id);
		
		$existingBookmarkModel = Bookmark::model()->findSingleByAttributes(
				array('user_id'=>\GO::user()->id,'folder_id'=>$folderModel->id)
			);
		if (!empty($existingBookmarkModel))
			throw new \Exception(str_replace('%fn',$folderModel->name,\GO::t('bookmarkAlreadyExists','files')));
		
		
		return parent::beforeSave();
	}
	
}