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
 * The CustomField Model for the Content
 *
 * @package GO.modules.Site
 * @version $Id: Content.php 7607 2013-03-27 15:41:11Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 */	
 

namespace GO\Site\Customfields\Model;


class Content extends \GO\Customfields\Model\AbstractCustomFieldsRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\Site\Model\CustomFieldsRecord 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}

	public function extendsModel(){
		return "GO\Site\Model\Content";
	}
}