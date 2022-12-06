<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @property int $user_id
 * @property int $due_time
 * @property int $ctime
 * @property int $mtime
 * @property string $title
 * @property string $content
 * @property int $acl_id
 */


namespace GO\Summary\Model;

use GO;


class Announcement extends \GO\Base\Db\ActiveRecord {
	
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function getLocalizedName(){
		return GO::t("Announcement", "summary");
	}
	
	public function tableName(){
		return 'su_announcements';
	}
	
	public function aclField(){
		return 'acl_id';
	}
	
	protected function init() {
		$this->columns['content']['gotype']='html';
		$this->columns['due_time']['gotype']='unixdate';
		return parent::init();
	}
	
}
