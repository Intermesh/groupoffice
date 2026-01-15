<?php
require('../../../../vendor/autoload.php');
use go\core\App;
use go\core\jmap\State;
use go\core\http\Router;
use go\modules\business\wopi\controller\Wopi;
use go\modules\business\wopi\controller\Edit;

// MS https://ffc-onenote.officeapps.live.com/hosting/discovery

// For Office online use alternative WOPI client url and make a symlink in /etc/groupoffice
// For example demo.group-office.eu and demo.wopi.group-office.eu

App::get();
App::get()->setAuthState(new State());

$router = (new Router())
  ->addRoute('/edit\/([0-9]+)\/([0-9]+)$/', 'GET', Edit::class, 'launch')

  ->addRoute('/files\/([0-9]+)$/', "GET", Wopi::class, 'checkFileInfo')
  ->addRoute('/files\/([0-9]+)$/', "LOCK", Wopi::class, 'lock')
  ->addRoute('/files\/([0-9]+)$/', "GET_LOCK", Wopi::class, 'getLock')
  ->addRoute('/files\/([0-9]+)$/', "REFRESH_LOCK", Wopi::class, 'refreshLock')
  ->addRoute('/files\/([0-9]+)$/', "UNLOCK", Wopi::class, 'unlock')
  ->addRoute('/files\/([0-9]+)$/', "PUT_RELATIVE", Wopi::class, 'putRelative')
  ->addRoute('/files\/([0-9]+)$/', "RENAME_FILE", Wopi::class, 'renameFile')
  ->addRoute('/files\/([0-9]+)$/', "DELETE", Wopi::class, 'delete')
  ->addRoute('/files\/([0-9]+)$/', "PUT_USER_INFO", Wopi::class, 'putUserInfo')

  ->addRoute('/files\/([0-9]+)\/contents$/', "GET", Wopi::class, 'GetFile')
  ->addRoute('/files\/([0-9]+)\/contents$/', "POST", Wopi::class, 'PutFile')
  ->addRoute('/files\/([0-9]+)\/contents$/', "PUT", Wopi::class, 'PutFile')

  ->run();