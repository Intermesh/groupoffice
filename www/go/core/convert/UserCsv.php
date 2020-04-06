<?php
namespace go\core\convert;

use go\core\data\convert\Csv;
use go\core\model\Module;
use go\core\orm\Entity;
use go\modules\community\serverclient\model\MailDomain;
use go\modules\community\serverclient\Module as GoModule;

class UserCsv extends Csv {

	public static $excludeHeaders = ['syncSettings', 'taskSettings', 'notesSettings', 'addressBookSettings', 'calendarSettings', 'emailSettings', 'googleauthenticator'];

  protected function init()
  {
    parent::init();

    $this->addColumn('createEmailAccount', go()->t("Create E-mail account"));
  }

  public function exportCreateEmailAccount(Entity $entity, array $templateValues, $columnName) {
    if(!Module::isInstalled('community', 'serverclient')) {
      return "0";
    }
  }

  public function importCreateEmailAccount(Entity $entity, $value, $values) {
    $this->postFixAdminDomain = false;
    $this->postFixAdminPassword = false;

    if(empty($value) || !Module::isInstalled('community', 'serverclient')) {
      return true;
    }
    

    if(empty($values['password'])) {
      throw new \Exception("Field 'password' is required for createMailAccount");
    }

    if(empty($values['email'])) {
      throw new \Exception("Field 'email' is required for createMailAccount");
    }
    
    $this->postFixAdminDomain = explode('@', $values['email'])[1];

    if(!in_array($this->postFixAdminDomain, GoModule::getDomains())) {
      throw new \Exception("Domain ". $this->postFixAdminDomain ." is not listed in the server client domains!");
    }
    $this->postFixAdminPassword = $values['password'];
  }

  protected $postFixAdminDomain = false;
  protected $postFixAdminPassword = false;

  protected function afterSave(Entity $entity)
  {
    if($this->postFixAdminDomain) {
      $postfixAdmin = new MailDomain($this->postFixAdminPassword);
      $postfixAdmin->addMailbox($entity, $this->postFixAdminDomain);
      $postfixAdmin->addAccount($entity, $this->postFixAdminDomain);    
    }

    return true;
  }
}