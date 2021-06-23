<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

namespace GO\Files\Model;

/**
 * The \GO\files\Model\Template model
 *
 * @package GO.modules.files
 * @version $Id: \GO\files\Model\Template.php 7607 2011-09-29 08:41:31Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>
 *
 * @property int $id
 * @property int $file_id
 * @property int $user_id 
 * @property int $mtime 
 * @property File $file
 * @property StringHelper $path
 * @property int $version
 * @property int $size_bytes the file size of this version in bytes
 */
class Version extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\files\Model\Template
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * Enable this function if you want this model to check the acl's automatically.
	 */
	public function aclField() {
		return 'file.folder.acl_id';
	}

	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'fs_versions';
	}

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	public function relations() {
		return array(
				'file' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Files\Model\File', 'field'=>'file_id')
		);
	}
	
	/**
	 * 
	 * @return \GO\Base\Fs\File
	 */
	public function getFilesystemFile(){
		return new \GO\Base\Fs\File(\GO::config()->file_storage_path.$this->path);
	}
	
	protected function beforeSave() {
		
		$this->mtime=$this->file->fsFile->mtime();
		$this->path = $this->file->getVersionStoragePath().'/'.date('Ymd_Gis', $this->file->fsFile->mtime()).'_'.$this->file->name;
		
		$lastVersion = $this->_findLastVersion();
		if($lastVersion)
			$this->version = $lastVersion->version+1;
		
		return parent::beforeSave();
	}
	
	private function _findLastVersion(){
		$params = \GO\Base\Db\FindParams::newInstance()
						->ignoreAcl()
						->single()
						->order('mtime','DESC');
		
		$params->getCriteria()->addCondition('file_id', $this->file_id);
		
		return $this->find($params);
	}
	
	protected function afterSave($wasNew) {
		$file = $this->getFilesystemFile();
		$folder = $file->parent();
		$folder->create();		
		
		$quotaUser = $this->file->folder->quotaUser;
		if($quotaUser) {
			$quotaUser->calculatedDiskUsage($this->size_bytes)->save(true); //user quota
		}
		\GO::config()->save_setting("file_storage_usage", (int) \GO::config()->get_setting('file_storage_usage', 0, 0) + $this->size_bytes);
		
		$this->file->fsFile->move($folder, $file->name());
		
		$this->_deleteOld(); 
		
		return parent::afterSave($wasNew);
	}
	
	protected function beforeDelete() {
		
		$file = $this->getFilesystemFile();

		$old = \GO\Base\Fs\File::setAllowDeletes(true);
		try {
			$file->delete();
		} finally {
			\GO\Base\Fs\File::setAllowDeletes($old);
		}
		
		$quotaUser = $this->file->folder->quotaUser;
		if($quotaUser) {
			$quotaUser->calculatedDiskUsage(0 - $this->size_bytes)->save(true); //user quota
		}

		\GO::config()->save_setting("file_storage_usage", \GO::config()->get_setting('file_storage_usage', 0, 0) - $this->size_bytes);

		return parent::beforeDelete();
	}
	
	private function _deleteOld(){	

		if(!empty(\GO::config()->max_file_versions)){
			$params = \GO\Base\Db\FindParams::newInstance()
							->ignoreAcl()
							->start(\GO::config()->max_file_versions)
							->limit(10)
							->order('mtime','DESC');

			$params->getCriteria()->addCondition('file_id', $this->file_id);

			$stmt = $this->find($params);

			foreach($stmt as $version){
				$version->delete(true);
			}
				
		//	$stmt->callOnEach('delete');
		}
	}
	
	
	public function checkPermissionLevel($level) {
			

		//If this folder belongs to a contact or project etc. then we only need write permission to delete it.
		if($level == \GO\Base\Model\Acl::DELETE_PERMISSION && $this->file->folder->acl->usedIn != 'fs_folders.acl_id') {
			$level = \GO\Base\Model\Acl::WRITE_PERMISSION;
		}
		
		return parent::checkPermissionLevel($level);
	}
}

