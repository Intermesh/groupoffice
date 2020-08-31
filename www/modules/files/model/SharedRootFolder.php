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
 */

namespace GO\Files\Model;

use GO;


class SharedRootFolder extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'fs_shared_root_folders';
	}

	public function primaryKey() {
		return array('user_id', 'folder_id');
	}

	public function relations() {
		return array(
				'folder' => array('type' => self::BELONGS_TO, 'model' => 'GO\Files\Model\Folder', 'field' => 'folder_id')
		);
	}

	/**
	 * Find all shared folders for the current user
	 * 
	 * @param \GO\Base\Db\FindParams $findParams
	 * @return \GO\Base\Db\ActiveStatement
	 */
	private function _findShares($user_id) {


		$findParams = new \GO\Base\Db\FindParams();

		$findParams->getCriteria()
						->addModel(Folder::model())
						->addCondition('visible', 1);
//						->addCondition('user_id', $user_id, '!=');

		return Folder::model()->find($findParams);
	}

	private function _getLastMtime($user_id) {
		
		Folder::model()->addRelation('aclItem',array(
			"type"=>self::BELONGS_TO,
			"model"=>"GO\Base\Model\Acl",
			"field"=>'acl_id'
		));
		
		$findParams = \GO\Base\Db\FindParams::newInstance()->debugSql()
						->select("unix_timestamp(max(a.modifiedAt)) AS mtime")
						->single()
						->joinModel(array(
								'model'=>"GO\Base\Model\Acl",
								'localField'=>'acl_id',
								'tableAlias'=>'a'
						));
		
		$findParams->getCriteria()
						->addModel(Folder::model())
						->addCondition('visible', 1);
//						->addCondition('user_id', $user_id, '!=');
		
		
		$result = Folder::model()->find($findParams);
		
		return $result->mtime;		
	}

	public function rebuildCache($user_id, $force=false) {
		
		$lastBuildTime = $force ? 0 : \GO::config()->get_setting('files_shared_cache_ctime', $user_id);
		if(!$lastBuildTime || $this->_getLastMtime($user_id)>$lastBuildTime){	

			GO::setMaxExecutionTime(3600);
			
			$home = \GO\Files\Model\Folder::model()->findHomeFolder(GO::user());
			
			$homeFolder  = $home->path;

			\GO::debug("Rebuilding shared cache");
			$this->deleteByAttribute('user_id', $user_id);

			$stmt = $this->_findShares($user_id);
			//sort by path and only list top level shares
			$shares = array();
			while ($folder = $stmt->fetch()) {
				//$folder->checkFsSync();
				//sort by path and only list top level shares		
				$shares[$folder->path] = $folder;
			}
			ksort($shares);

			foreach ($shares as $path => $folder) {
				
				$isInHome = strpos($path . '/', $homeFolder . '/') === 0;
				
				if (!$isInHome && !$this->isParentShared($path, $folder, $shares)) {

					$sharedRoot = new SharedRootFolder();
					$sharedRoot->user_id = $user_id;
					$sharedRoot->folder_id = $folder->id;
					$sharedRoot->save();					
				}
			}
			
			\GO::config()->save_setting('files_shared_cache_ctime',time(), $user_id);
			
			return time();
		}else
		{
			return $lastBuildTime;
		}
	}
	
	private function isParentShared($path, Folder $folder, $shares) {		
		$parentPath = $path;
		$parent = $folder;
		while($parent = $parent->parent) {
			
			$parentPath = substr($parentPath, 0, strrpos($parentPath, '/'));

			
			if($parent->acl_id > 0) {
				//if folder is shared but not in the current user's shared folder list return false.
				return isset($shares[$parentPath]);
			}
		}
		
		return true;
	}

}
