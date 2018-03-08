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

namespace GO\Notes;

use GO;
use GO\Base\Model\Acl;
use GO\Base\Model\User;
use GO\Base\Module;
use GO\Notes\Model\Category;

/**
 * 
 * The Notes module maintenance class
 * 
 */
class NotesModule extends Module{
	
	public function autoInstall() {
		return true;
	}
	
	public function author() {
		return 'Merijn Schering';
	}
	
	public function authorEmail() {
		return 'mschering@intermesh.nl';
	}
	
	/**
	 * 
	 * When a user is created, updated or logs in this function will be called.
	 * The function can check if the default calendar, addressbook, notebook etc.
	 * is created for this user.
	 * 
	 */
	public static function firstRun(){
		parent::firstRun();
		$category = self::getDefaultNoteCategory(GO::user()->id);
		
		return array('exportVariables'=>array(
				'GO'=>array(
						"notes"=>array(
								"defaultCategory"=>array(
									'id'=>$category->id,
									'name'=>$category->name
									)
						)
				)
		));
	}

	
	public static function getDefaultNoteCategory($userId){
		$user = User::model()->findByPk($userId);
		if(!$user)
			return false;
		$category = Category::model()->getDefault($user);
		
		return $category;
	}
	
	public function install() {
		parent::install();
		
		$category = new Category();
		$category->name=GO::t('general','notes');
		$category->save();
		$category->acl->addGroup(GO::config()->group_everyone, Acl::READ_PERMISSION);
	}
}