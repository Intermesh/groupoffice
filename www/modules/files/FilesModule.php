<?php
/*
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * This class is used to parse and write RFC822 compliant recipient lists
 * 
 * @package GO.modules.files
 * @version $Id: RFC822.class.inc 7536 2011-05-31 08:37:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Copyright Intermesh BV.
 */


namespace GO\Files;

use go\core\model\User;
use go\core\util\ClassFinder;
use GO\Files\Filehandler\FilehandlerInterface;
use GO\Base\Util\ReflectionClass;
use go\core\Module;
use go\core\model\Module as GoModule;
use GO\Files\Model\Folder;

class FilesModule extends \GO\Base\Module{	
	
	
	public static function initListeners() {
		\GO\Base\Model\User::model()->addListener('save', "GO\Files\FilesModule", "saveUser");
		\GO\Base\Model\User::model()->addListener('delete', "GO\Files\FilesModule", "deleteUser");
		
		$c = new \GO\Core\Controller\BatchEditController();
		
		$c->addListener('store', "GO\Files\FilesModule", "afterBatchEditStore");
	}
	

	public function checkDatabase(&$response) {
		
		//create user home folders
		$stmt = \GO\Base\Model\User::model()->find(array('ignoreAcl'=>true));
		
		while($user = $stmt->fetch()){
			$folder = Model\Folder::model()->findHomeFolder($user);
			//$folder->syncFilesystem();
			
			//$folder = Model\Folder::model()->findByPath('users/'.$user->username, true);
			
			//In some cases the acl id of the home folder was copied from the user. We will correct that here.
			if(!$folder->acl){
				$folder->setNewAcl($user->id);				
			}
			
			$folder->user_id=$user->id;
			$folder->visible=1;
			$folder->readonly=1;			
			$folder->save();
			
			$folder->fsFolder->create();
			//$folder->syncFilesystem();		
			
		}
		
		$folder = Model\Folder::model()->findByPath("log", true);
		if(!$folder->acl || $folder->acl_id==\GO::modules()->files->acl_id){
			$folder->setNewAcl();
			$folder->readonly=1;
			$folder->save();
		}

		go()->getDbConnection()->exec("update fs_folders set acl_id = 0 where acl_id not in (select id from core_acl);");

		parent::checkDatabase($response);
	}


	public static function cleanup() {

		echo "Cleaning up home folders...\n";
		$users = Folder::model()->findByPath('users');

		foreach($users->folders as $folder) {

			$user = User::find(['id','username'])->where('username', '=', $folder->name)->single();
			if(!$user) {
				echo "Deleting: " . $folder->name . "\n";
				$folder->delete(true);
			}
		}
	}

	public static function saveUser($user, $wasNew)
	{
		if ($wasNew) {
			$folder = Model\Folder::model()->findHomeFolder($user);
		} elseif ($user->isModified('username')) {
			$folder = Model\Folder::model()->findByPath('users/' . $user->getOldAttributeValue('username'));
			if ($folder) {
				$folder->name = $user->username;
				$folder->systemSave = true;
				$folder->save();
			}
		}
	}
	
	public static function deleteUser($user) {
		$folder = Model\Folder::model()->findByPath('users/'.$user->username, true);
		if($folder)
			$folder->delete(true);
	}
	
	public function autoInstall() {
		return true;
	}
	
	private static $fileHandlers;
	/**
	 * 
	 * @return Filehandler\FilehandlerInterface
	 */
	public static function getAllFileHandlers(){
		if(!isset(self::$fileHandlers)){
			
			self::$fileHandlers = \GO::cache()->get('files-file-handlers');
		
			
			if(!self::$fileHandlers){

				$modules = \GO::modules()->getAllModules();

				self::$fileHandlers=array();
				foreach($modules as $module){
					if($module->moduleManager instanceof \GO\Base\Module) {
						self::$fileHandlers = array_merge(self::$fileHandlers, array_map(function($c){return $c->name;}, $module->moduleManager->findClasses('filehandler')));
					}
				}

				//For new framework
				$cf = new ClassFinder();
				self::$fileHandlers = array_merge(self::$fileHandlers, $cf->findByParent(FilehandlerInterface::class));

				\GO::cache()->set('files-file-handlers', self::$fileHandlers);
			}
			
			// Check if the found filehandlers are in modules that are enabled for the current user.
			// If not, then delete them from the array
			foreach(self::$fileHandlers as $key=>$handler){
				// $handler->name holds the namespace path of the handler. Based on that we can determine if the module is enabled.
				$nsArr = explode('\\',$handler);

				if($nsArr[0] == "GO") {
					$moduleName = strtolower($nsArr[1]);
					
					if(!\GO::modules()->$moduleName){
						// Remove if the module is not enabled for this user
						unset(self::$fileHandlers[$key]);
					}
				} else{
					if(!GoModule::isAvailableFor($nsArr[2], $nsArr[3])) {
						unset(self::$fileHandlers[$key]);
					}
				}
			}
		}		
		return self::$fileHandlers;
	}
	
	public function install() {
		parent::install();
		
		$template = new \GO\files\Model\Template();
		$template->name=\GO::t("Microsoft Word document", "files");
		$template->content = file_get_contents(\GO::modules()->files->path.'install/templates/empty.docx');
		$template->extension='docx';
		$template->save();	
		$template->acl->addGroup(\GO::config()->group_internal, \GO\Base\Model\Acl::READ_PERMISSION);
		
		
		$template = new \GO\files\Model\Template();
		$template->name=\GO::t("Open-Office Text document", "files");
		$template->content = file_get_contents(\GO::modules()->files->path.'install/templates/empty.odt');
		$template->extension='odt';
		$template->save();	
		$template->acl->addGroup(\GO::config()->group_internal, \GO\Base\Model\Acl::READ_PERMISSION);
	}
	
	
	public static function afterBatchEditStore($controller, &$response, &$tmpModel, &$params) {
//		$countCustomfield = 0;
//		$countCustomfieldCategory = array();
//		
//		
//		if('GO\Files\Model\File' !== $tmpModel->className()) {
//			return $response['results'];
//		}
//		
//		$module = call_user_func_array($params['model_name'].'::model', array());
//		$stmt = call_user_func_array(array($module, 'find'), 
//							array(\GO\Base\Db\FindParams::newInstance()->debugSql()->ignoreAcl()
//							->criteria(
//											\GO\Base\Db\FindCriteria::newInstance()
//											->addInCondition($params['primaryKey'], json_decode($params['keys']))
//											)
//									)
//							);		
//		
//		foreach ($stmt as $model) {
//			
//			$customfields = \GO\Customfields\Controller\CategoryController::getEnabledCategoryData("GO\Files\Model\File", $model->folder_id);
//			
//			$countCustomfield++;
//			if(isset($customfields['enabled_categories'])) {
//				foreach ($customfields['enabled_categories'] as $id) {
//
//						if(isset($countCustomfieldCategory[$id])) {
//							$countCustomfieldCategory[$id]++;
//						} else {
//							$countCustomfieldCategory[$id] = 1;
//						}
//
//				}
//			}else
//			{
//				if(!isset($allCats)) {
//					$allCats = \GO\Customfields\Model\Category::model()->findByModel("GO\Files\Model\File")->fetchAll();
//				}
//				
//				foreach($allCats as $cat) {
//					$id = $cat->id;
//					if(isset($countCustomfieldCategory[$id])) {
//						$countCustomfieldCategory[$id]++;
//					} else {
//						$countCustomfieldCategory[$id] = 1;
//					}
//				}
//			}
//			
//		}
//		
//		// remove fields
//		foreach ($response['results']  as $key => $results) {
//			if($results['gotype'] == 'customfield') {
//				
//				
//				if(!isset($countCustomfieldCategory[$results['category_id']]) || $countCustomfieldCategory[$results['category_id']] != $countCustomfield) {
//					
//					unset($response['results'][$key]);
//				}
//				
//			}
//		}
//		
//		$response['results'] = array_values($response['results']);
//		
	}
	
}
