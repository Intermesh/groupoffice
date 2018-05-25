<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */
 
/**
 * The PortletTasklist model
 *
 * @package GO.modules.Tasks
 * @version $Id: PortletTasklist.php 7607 2011-09-20 10:07:07Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 *
 * @property int $user_id
 * @property int $tasklist_id
 */


namespace GO\Tasks\Model;


class PortletTasklist extends \GO\Base\Db\ActiveRecord {
	
	/**
	 *
	 * @param type $className
	 * @return PortletTasklist 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function primaryKey() {
		return array('tasklist_id','user_id');
	}
	
	public function tableName() {
		return 'ta_portlet_tasklists';
	}
	
	public function relations() {
		return array(
			'tasklist' => array('type' => self::BELONGS_TO, 'model' => 'GO\Tasks\Model\Tasklist', 'field' => 'tasklist_id', 'delete' => false),
			);
	}
	
}
