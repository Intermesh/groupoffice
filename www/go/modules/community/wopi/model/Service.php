<?php
namespace go\modules\business\wopi\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\http\Client;
use go\core\jmap\Request;
use go\core\orm\Mapping;
use go\core\validate\ErrorCode;
use PHPUnit\Util\ErrorHandler;
use go\core\ErrorHandler as GoErrorHandler;
use go\core\orm\Query;
use PHPUnit\Framework\Error\Error;

class Service extends AclOwnerEntity
{
  const TYPE_COLLABORA = 'collabora';
  const TYPE_OFFICE_ONLINE = 'officeonline';

  public ?string $id;
  public string $name = "Office Online";
  protected string $url;

  public string $type = self::TYPE_OFFICE_ONLINE;

  protected ?string $wopiClientUri;

  public static function getClientName(): string
  {
    return "WopiService";
  }

  public function getWopiClientUri() {
  	return $this->wopiClientUri;
  }

  public function autoWopiClientUri() {
	  if(isset($this->wopiClientUri)){
	  	return $this->wopiClientUri;
	  }

	  $url = Request::get()->isHttps() ? 'https://' : 'http://';
	  $url .= Request::get()->getHost(false) . '/wopi/';
	  return $url;
  }

  public function setWopiClientUri($uri) {
  	$this->wopiClientUri = !empty($uri) ? trim($uri, ' /') . '/' : null;
  }

  protected static function defineMapping(): Mapping
  {
    return parent::defineMapping()->addTable('wopi_service', 's');
  }

  public function getName() {
    return $this->name;
  }

  public function setUrl($url)
  {
    $this->url = trim($url, '/ ');
  }

  public function getUrl()
  {
    return $this->url;
  }

  private $actions;

  protected function internalValidate()
  {
    try{
			if($this->isNew() || $this->isModified(['url'])) {
				$this->discover();
			}
    } catch(\Exception $e) {
      GoErrorHandler::logException($e);
      $this->setValidationError('url', ErrorCode::INVALID_INPUT, go()->t("The given URL is not a valid WOPI client") . ": ". $e->getMessage());
    }

    return parent::internalValidate();
  }

  protected function internalSave(): bool
  {
    \GO::cache()->delete("files-file-handlers");
    
    if (!parent::internalSave()) {

	    if($this->getValidationError('type')) {
		    $this->setValidationError('type', ErrorCode::UNIQUE, go()->t("You can only add one service of the same type"));
	    }

      return false;
    }

    return $this->saveActions();
  }

  protected static function internalDelete(Query $query): bool
  {
    \GO::cache()->delete("files-file-handlers");
    return parent::internalDelete($query);
  }

  private function saveActions() {

    if(!isset($this->actions)) {
      return true;
    }

     go()->getDbConnection()
      ->delete('wopi_action', ['serviceId' => $this->id])
      ->execute();

    foreach($this->actions as $action) {
      $action['serviceId'] = $this->id;

      go()->getDbConnection()
        ->insert('wopi_action', $action)->execute();
    }

    return true;
  }

  private function discover()
  {
    $c = new Client();
		$c->setOption(CURLOPT_TIMEOUT, 10);

    $result = $c->get($this->url . '/hosting/discovery');

    go()->debug($result);
    $discoveryParsed = simplexml_load_string($result['body']);

    if (!$discoveryParsed) {
      $this->setValidationError('url', ErrorCode::INVALID_INPUT, "The URL is not a valid WOPI source");
      return false;
    }

    $apps = $discoveryParsed->xpath('/wopi-discovery/net-zone/app');

    $this->actions = [];

    $capabilitiesUrl = null;

    foreach ($apps as $app) {

      $appName = (string) $app->attributes()->name;

      foreach ($app->action as $action) {
        $record = [
          'app' => $appName,
          'ext' => (string)$action->attributes()->ext,
          'name' => (string)$action->attributes()->name,
          'url' => (string)$action->attributes()->urlsrc
        ];
        $this->actions[] = $record;

        if($appName == 'Capabilities' && $record['name'] == 'getinfo') {
          $capabilitiesResult = $c->get($record['url']);
          $c = json_decode($capabilitiesResult['body']);
          $this->name = $c->productName;
          $this->type = self::TYPE_COLLABORA;
        } else
        {
          $this->name = "Office Online";
          $this->type = self::TYPE_OFFICE_ONLINE;
        }
       
      }
    }
  }

  /**
   * Find's actions by file extension
   * 
   * @param string $ext
   * @return array eg. [edit' => 'https://..']
   */
  public function findActions($ext) {
    $a = [];
    foreach(go()->getDbConnection()
      ->select('name,url')
      ->from('wopi_action')
      ->where(['serviceId' => $this->id, 'ext' => $ext]) as $record) {
        $a[$record['name']] = $record['url'];
    }

    return $a;
  }
}
