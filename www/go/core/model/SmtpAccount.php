<?php

namespace go\core\model;

use go\core\model\Acl;
use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\orm\EntityType;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\core\util\Crypt;

class SmtpAccount extends AclOwnerEntity {

	public $id;
  protected $moduleId;  
  public $hostname;
  public $port;
  public $username;
  protected $password;
  public $encryption;
  public $verifyCertificate;
  public $fromName;
  public $fromEmail;
 
  protected static function defineMapping(): Mapping
  {
    return parent::defineMapping()
    ->addTable('core_smtp_account', 'account');    
  }
  
  public function setModule($module) {
    $module = Module::findByName($module['package'], $module['name']);
    if(!$module) {
      $this->setValidationError('module', ErrorCode::INVALID_INPUT, 'Module was not found');
    }
    $this->moduleId = $module->id;
  }
	
	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
						->add('module', function (Criteria $criteria, $module){
              $module = Module::findByName($module['package'], $module['name']);
							$criteria->where(['moduleId' => $module->id]);		
						});
					
  }
  
  public function decryptPassword() {
    return Crypt::decrypt($this->password);
	}
	
	public function setPassword($value) {
		$this->password = Crypt::encrypt($value);
	}

	protected static function textFilterColumns(): array
	{
		return ['hostname', 'fromName', 'fromEmail'];
	}

}
