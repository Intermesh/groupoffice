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

use Faker\Generator;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\User;
use go\core\util\ClassFinder;
use GO\Files\Filehandler\FilehandlerInterface;
use GO\Base\Util\ReflectionClass;
use go\core\Module;
use go\core\model\Module as GoModule;
use GO\Files\Model\Folder;

class FilesModule extends \GO\Base\Module{


	public function getRights()
	{
		return ['mayManage' => 1, 'mayAccessMainPanel' => 2];
	}
	
	public static function initListeners() {

		$c = new \GO\Core\Controller\BatchEditController();
		
		$c->addListener('store', "GO\Files\FilesModule", "afterBatchEditStore");

	}

	/**
	 * Default sort order when installing. If null it will be auto generated.
	 * @return int|null
	 */
	public static function getDefaultSortOrder() : ?int{
		return 30;
	}
	

	public function checkDatabase(&$response) {
		
		//create user home folders
		$stmt = \GO\Base\Model\User::model()->find(array('ignoreAcl'=>true));
		
		while($user = $stmt->fetch()){
			$folder = Model\Folder::model()->findHomeFolder($user);

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
		if(!$folder->acl){
			$folder->setNewAcl();
			$folder->readonly=1;
			$folder->save();
		}

		go()->getDbConnection()->exec("update fs_folders set acl_id = 0 where acl_id not in (select id from core_acl);");

		$mod = \go\core\model\Module::findByName(null, "files", null);
		go()->getDbConnection()->exec("update fs_folders set readonly=1, acl_id = ".$mod->getShadowAclId()." where acl_id=0 and parent_id=0;");
		go()->getDbConnection()->exec("delete s from core_search s inner join fs_folders f on f.id=s.entityId where entityTypeId=(select id from core_entity where name='Folder') and f.visible=0;");

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



	public static function deleteUser($user) {
		$folder = Model\Folder::model()->findByPath($user->homeDir);
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
				} else {
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


		//create public shared folder
		$admin = \GO\Base\Model\User::model()->findByPk(1);

		$shared = Folder::model()->findHomeFolder($admin)->addFolder(go()->t("Public"));
		$acl = $shared->setNewAcl(1);
		$acl->addGroup(Group::ID_INTERNAL, \GO\Base\Model\Acl::DELETE_PERMISSION);
		$shared->save();


		// Set access to main screen default
		$stmt = go()->getDbConnection()->replace('core_permission',
			[
				'moduleId' => go()->getDbConnection()->selectSingleValue('id')->from('core_module')->where(['name' => 'files', 'package' => null]),
				'groupId' => \go\core\model\Group::ID_INTERNAL,
				'rights' => 2
			]);
		$stmt->execute();


		$folder = Model\Folder::model()->findByPath("trash", true);
		$folder->setNewAcl();
		$folder->visible = 1;
		$folder->readonly = 1;
		$folder->save();
		$folder->acl->addGroup(\GO::config()->group_root, \GO\Base\Model\Acl::MANAGE_PERMISSION);
	}
	
	
	public static function afterBatchEditStore($controller, &$response, &$tmpModel, &$params) {
	}

	public function demo(Generator $faker)
	{

		$demo = \GO\Base\Model\User::model()->findSingleByAttribute('username', 'demo');

		$demoHome = \GO\Files\Model\Folder::model()->findHomeFolder($demo);
		$file = new \GO\Base\Fs\File(\GO::modules()->files->path.'install/templates/empty.docx');
		$copy = $file->copy($demoHome->fsFolder);

		$file = new \GO\Base\Fs\File(\GO::modules()->files->path.'install/templates/empty.odt');
		$copy = $file->copy($demoHome->fsFolder);


		$file = new \GO\Base\Fs\File(\GO::modules()->files->path . 'demo/Demo letter.docx');
		$copy = $file->copy($demoHome->fsFolder);


		$file = new \GO\Base\Fs\File(\GO::modules()->files->path . 'demo/wecoyote.png');
		$copy = $file->copy($demoHome->fsFolder);

		$file = new \GO\Base\Fs\File(\GO::modules()->files->path . 'demo/noperson.jpg');
		$copy = $file->copy($demoHome->fsFolder);

		//add files to db.
		$demoHome->syncFilesystem();
	}

}
