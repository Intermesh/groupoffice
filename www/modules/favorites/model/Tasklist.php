<?php
/**
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @package GO.modules.Favorites
 * @version $Id: Tasklist.php 16715 2014-01-27 10:14:00Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 */
 
/**
 * The Tasklist model
 *
 * @package GO.modules.Favorites
 * @version $Id: Tasklist.php 16715 2014-01-27 10:14:00Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 *
 * @property int $user_id
 * @property int $tasklist_id
 * @property int $sort
 */


namespace GO\Favorites\Model;


class Tasklist extends \GO\Base\Db\ActiveRecord{

	/**
	 * Returns the table name
	 */
	 public function tableName() {
		 return 'fav_tasklist';
	 }
	 	 
	 public function primaryKey() {
		 return array('user_id','tasklist_id');
	 }

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	 public function relations() {
		 return array(
				 'tasklist' => array('type' => self::BELONGS_TO, 'model' => 'GO\Tasks\Model\Tasklist', 'field' => 'tasklist_id', 'delete' => false),
		 );
	 }	
}
