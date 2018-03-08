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
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * The Party model
 * 
 * @property int $id
 * @property string $name
 */

namespace GO\Presidents\Model;


class Party extends \GO\Base\Db\ActiveRecord {
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Party 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}

	public function tableName() {
		return 'pm_parties';
	}


	public function relations() {
		return array(
			'presidents' => array('type' => self::HAS_MANY, 'model' => 'GO\Presidents\Model\President', 'field' => 'party_id', 'delete' => true)
		);
	}

}