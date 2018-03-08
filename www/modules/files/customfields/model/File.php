<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: \GO\Files\Model\FileCustomFieldsRecord.php 7607 2011-09-01 16:00:16Z <<USERNAME>> $
 * @copyright Copyright Intermesh
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */  

/**
 * The \GO\Files\Model\FileCustomFieldsRecord model
 *
 */


namespace GO\Files\Customfields\Model;


class File extends \GO\Customfields\Model\AbstractCustomFieldsRecord{

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\Notes\Model\CustomFieldsRecord 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}

	public function extendsModel(){
		return "GO\Files\Model\File";
	}
}