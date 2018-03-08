<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: President.php 7607 2012-05-23 14:03:42Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * The President model
 * 
 * @property int $id
 * @property int $party_id
 * @property string $firstname
 * @property string $lastame
 * @property date $tookoffice
 * @property date $leftoffice
 * @property float $income
 */

namespace GO\Presidents\Model;


class President extends \GO\Base\Db\ActiveRecord {
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return President 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	protected function init() {
		$this->columns["income"]["gotype"]="number";
		return parent::init();
	}
	
	public function tableName(){
		return 'pm_presidents';
	}
	
	public function customfieldsModel(){
		return "GO\Presidents\Customfields\Model\President";
	}
	
	public function hasLinks() {
		return true;
	}
	
	public function getFullname()
	{
		return $this->firstname . " " . $this->lastname;
	}
	
	protected function getCacheAttributes() {
		return array("name"=>$this->fullname, "description"=>$this->party->name);
	}

	public function relations(){
		return array(	
				'party' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Presidents\Model\Party', 'field'=>'party_id'),		);
	}

}