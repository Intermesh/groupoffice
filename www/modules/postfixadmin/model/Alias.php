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
 * @package GO.modules.postfixadmin.model
 * @version $Id: example.php 7607 20120101Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */
 
/**
 * The Alias model
 *
 * @package GO.modules.postfixadmin.model
 * @property int $domain_id
 * @property string $address
 * @property string $goto
 * @property int $ctime
 * @property int $mtime
 * @property boolean $active
 */


namespace GO\Postfixadmin\Model;


class Alias extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Alias 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}

	public function tableName() {
		return 'pa_aliases';
	}
	
	public function relations() {
		return array(
			'domain' => array('type' => self::BELONGS_TO, 'model' => 'GO\Postfixadmin\Model\Domain', 'field' => 'domain_id')
		);
	}
	
	protected function init() {
		$this->columns['address']['unique']=true;
		$this->columns['address']['required']=true;
		$this->columns['goto']['gotype']='textfield';
		$this->columns['goto']['required']=true;
		
		return parent::init();
	}
	
	public function validate() {
		
		$domainParts = explode('@', $this->address);
		
		if(isset($domainParts[1]) && ($domainParts[1] != $this->domain->domain)){
			$this->setValidationError('address', "The domain part of the address must match with the main domain");
		}
		
		return parent::validate();
	}
	
	
	protected function beforeSave() {
		if ($this->isNew && !empty($this->domain->max_aliases))
			if ($this->domain->getSumAliases() >= $this->domain->max_aliases)
				throw new \Exception('The maximum number of aliases for this domain has been reached.');
			
		//chop off wildcard because in db it must be @domain.com but we use *@domain.com

		if(substr($this->address, 0,2) == '*@') {
			$this->address = substr($this->address, 1);
		}
			
		return parent::beforeSave();
	}
	
	protected function afterLoad() {
		parent::afterLoad();
		
		//append wildcard
		if(isset($this->address) && substr($this->address,0,1) == '@') {
			$this->address = '*' . $this->address;
		}
	}
	
}
