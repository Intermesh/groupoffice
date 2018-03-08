<?php
/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @package GO.modules.dropbox.model
 * @version $Id: DropboxUser.php 18034 2018-02-20 10:18:31Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 */
 
/**
 * The DropboxUser model
 *
 * @package GO.modules.dropbox.model
 * @property string $access_token
 * @property string $dropbox_user_id
 */


namespace GO\Dropbox\Model;

use GO\Base\Db\ActiveRecord;
use GO\Files\Model\Folder;


class DropboxUser extends ActiveRecord{
		
	const GO_ROOT = '/Dropbox';
	
	public function tableName() {
		return 'dbx_users';
	}
	
	public function primaryKey() {
		return 'user_id';
	}
	
	public function relations() {
		return array('user'=>array('type'=>self::BELONGS_TO,'model'=>"GO\Base\Model\User","field"=>"user_id"));
	}
	
	public function getDropboxFolder(){
		return Folder::model()->findByPath('users/' . $this->user->username . DropboxUser::GO_ROOT, true);
	}
	
	
}