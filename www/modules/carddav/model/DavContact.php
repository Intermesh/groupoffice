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
 * @package GO.modules.carddav.model
 * @version $Id: DavContact.php 17966 2014-01-24 16:30:50Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */
 
/**
 * The DavContact model
 *
 * @package GO.modules.carddav.model
 * @property int $id
 * @property int $mtime
 * @property string $data
 * @property string $uri
 */


namespace GO\CardDAV\Model;


class DavContact extends \GO\Base\Db\ActiveRecord{
	
	public function tableName() {
		return 'dav_contacts';
	}

	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}	
}
