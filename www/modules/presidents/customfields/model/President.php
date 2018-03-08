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

/**
 * 
 * The president model custom fields model.
 * 
 */

namespace GO\Presidents\Customfields\Model;


class President extends \GO\Customfields\Model\AbstractCustomFieldsRecord{
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\Presidents\Model\CustomFieldsRecord 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}

	public function extendsModel(){
		return "GO\Presidents\Model\President";
	}
}