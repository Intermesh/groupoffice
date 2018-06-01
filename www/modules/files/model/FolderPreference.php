<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: Folder.php 7607 2011-09-01 15:44:36Z <<USERNAME>> $
 * @copyright Copyright Intermesh
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */

/**
 * The Folder model
 * 

 * @property int $user_id
 * @property int $folder_id
 * @property boolean $thumbs
 */

namespace GO\Files\Model;


class FolderPreference extends \GO\Base\Db\ActiveRecord {


	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return FolderNotification
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}


	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'fs_folder_pref';
	}
	
	public function primaryKey() {
		return array('user_id', 'folder_id');
	}
}
