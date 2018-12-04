<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: \GO\Tasks\Model\TaskCustomFieldsRecord.php 7607 2011-09-20 09:51:47Z <<USERNAME>> $
 * @copyright Copyright Intermesh
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 * @package GO.modules.Tasks
 */  
	 
/**
 * The CustomField Model for the \GO\Tasks\Model\Task
 *
 * @package GO.modules.Tasks
 *
 */	
 

namespace GO\Tasks\Customfields\Model;


class Task extends \GO\Customfields\Model\AbstractCustomFieldsRecord{
	
	
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\Tasks\Model\CustomFieldsRecord 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}

	public function extendsModel(){
		return "GO\Tasks\Model\Task";
	}
}
