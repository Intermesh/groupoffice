<?php
/**
 * Meant to be a log file created on the file system during the first log()
 * command. It is created in the current user's personal root folder the first
 * time the log() method is used to write data into it.
 */

namespace GO\Files\Fs;


class UserLogFile extends \GO\Base\Fs\File{
	
	
	
	public function __construct($prefixString='') {
		
		if (!\GO::modules()->isInstalled('files'))
			throw new \Exception('The current action requires the files module to be activated for the current user.');
		
		// Make sure the current user's folder exists.

		$userFolderModel = \GO\Files\Model\Folder::model()->findHomeFolder(\GO::user());

		if (empty($userFolderModel)) {
			$userFolder = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path . \GO::user()->username);
			$userFolder->create();
			$userFolderModel = new \GO\Files\Model\Folder();
			$userFolderModel->findByPath(\GO::user()->username,true);
		}
		
		parent::__construct(
				\GO::config()->file_storage_path.$userFolderModel->path.
				'/'.$prefixString.\GO\Base\Util\Date::get_timestamp(time(), true).'.log'
			);
	
	}
		
	/**
	 * Logs data in the file. If the file does not exist on the file system, it
	 * is created here.
	 * @param type $data Data to be logged in the file. Will be casted into a string
	 * if it is not a string.
	 */
	public function log($data){
	
		if(!$this->exists())
			$this->touch(true);
		
		if(!is_string($data))
			$data = var_export($data, true);
			
		$this->putContents($data."\n", FILE_APPEND);
	}
	
}
