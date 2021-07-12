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
 * The Template model
 *
 * @package GO.modules.files
 * @version $Id: Template.php 7607 2011-09-29 08:41:31Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>
 *
 * @property int $id
 * @property int $user_id
 * @property String $name
 * @property int $acl_id
 * @property int $acl_write
 * @property mediumblob $content
 * @property String $extension
 */

namespace GO\files\Model;


class Template extends \GO\Base\Db\ActiveRecord {

	public static $trimOnSave = false;

	public static function getClientName()
	{
		return "FilesTemplate";
	}

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Template
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * Enable this function if you want this model to check the acl's automatically.
	 */
	public function aclField() {
		return 'acl_id';
	}

	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'fs_templates';
	}

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	public function relations() {
		return array();
	}

}

