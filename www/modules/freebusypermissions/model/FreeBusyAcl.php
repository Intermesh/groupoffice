<?php
/**
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @package GO.modules.Freebusypermissions
 * @version $Id: FreeBusyAcl.php 7607 2012-07-03 10:31:57Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 */

namespace GO\Freebusypermissions\Model;

/**
 * The FreeBusyAcl model
 *
 * @package GO.modules.Freebusypermissions
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 *
 * @property int $user_id
 * @property int $acl_id
 */
class FreeBusyAcl extends \GO\Base\Db\ActiveRecord {
	
	public function tableName() {
		 return 'fb_acl';
	}
	
	public function primaryKey() {
		return ['user_id','acl_id'];
	}
	
}
