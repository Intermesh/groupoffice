<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author Wesley Smits<wsmits@intermesh.nl>
 * @property string $name
 * @property int $id
 */


namespace GO\Addressbook\Model;

class AddresslistGroup extends \GO\Base\Db\ActiveRecord {

	public function tableName(){
		return 'ab_addresslist_group';
	}
}
