<?php
namespace go\modules\business\wopi\controller;

use go\modules\business\wopi\model;
use go\modules\business\license\controller\Controller;
use go\core\http\Request;
use go\core\exception\NotFound;
use GO\Files\Model\File;
use go\core\model\Acl;
use go\core\exception\Forbidden;
use go\core\http\Exception;
use go\core\auth\TemporaryState;
use go\core\http\Response;
use go\modules\business\wopi\model\Lock;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\modules\community\history\Module;

/**
 * The controller for the Service entity
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Wopi extends Controller
{

  /**
   * The WOPI token that was used for authentication
   * 
   * @var model\Token
   */
  private $token;

  protected function authenticate() {
    $accessToken = Request::get()->getQueryParam("access_token");    
    if (!$accessToken) {
      throw new \Exception("No access_token query parameter given");
    }

    $token = model\Token::find()->where(['token' => $accessToken])->single();
    if (!$token || $token->isExpired()) {
      throw new Forbidden();
    }

    $this->token = $token;

    $s = new TemporaryState();
    $s->setUserId($token->userId);
    go()->setAuthState($s);    

    
  }

  const CAPABILITIES = [
    'SupportsGetLock' => true,
    'SupportsLocks' => true,
    'SupportsUpdate' => true,
    'SupportsDeleteFile' => true,
    // 'HidePrintOption' => false,
    // 'HideExportOption' => false,
    // 'EnableOwnerTermination' => true, // When WOPI UserId == OwnerId, then that user can close all sessions //NOT OFFICIAL WOPI STANDARD (Collabora only)
    // 'DisablePrint' => false,
    // 'DisableExport' => false,
    // 'DisableCopy' => false,
    'FileVersionPostMessage' => false,
    'SupportsExtendedLockLength' => false    
  ];

 
  /**
   * @return File
   */
  private function findFile($fileId)
  {
    $file = File::model()->findByPk($fileId);
    if (!$file) {
      throw new NotFound("File not found");
    }

    Response::get()->setHeader('X-WOPI-ItemVersion', (string) $file->version);

    return $file;
  }

  public function getFile($fileId)
  {
    $file = $this->findFile($fileId);

		if($file->fsFile->isLink())
			throw new Forbidden("Symlinks are forbidden");

    if(!$file->checkPermissionLevel(Acl::LEVEL_READ)) {
      throw new Forbidden();
    }

    Response::get()->setHeader('Content-Disposition', 'attachment');
    Response::get()->setHeader('Content-Type', 'application/octet-stream;');
    Response::get()->setHeader('X-WOPI-ItemVersion', (string) $file->version);
    Response::get()->output();

	  if(\go\core\model\Module::isInstalled('community', 'history')) {
		  Module::logActiveRecord($file, 'download');
	  }

		$file->fsFile->output();
  }

  private function checkLock($fileId, $requireLock = true) {

		go()->debug("checkLock($fileId, " . var_export($requireLock, true).")");

    $lockID = Request::get()->getHeader("X-WOPI-Lock");
		go()->debug("X-WOPI-Lock: " . $lockID);

		$file = $this->findFile($fileId);

		go()->debug("File lock: " . $file->locked_user_id ." / ". $file->lock_id);

    if(!$file->locked_user_id) {
      Response::get()->setHeader("X-WOPI-Lock", "");
      if(!$requireLock) {
        return;
      }       
      throw new Exception(409);
    }
    
    if($file->lock_id != $lockID) {
      Response::get()->setHeader("X-WOPI-Lock", $file->lock_id);
      throw new Exception(409);
    } else {
			// for co-editing set to current user
			$file->locked_user_id = go()->getUserId();
			$file->ignoreLockChange = true;
		}

		go()->debug("File lock: " . $file->locked_user_id ." / ". $file->lock_id);

  }

  private $service;

  /**
   * 
   * @return model\Service
   */
  private function getService() {
    if(!isset($this->service)) {
      $this->service = model\Service::findById($this->token->serviceId);
    }

    return $this->service;
  }

  public function putFile($fileId)
  {
    $file = $this->findFile($fileId);

    if(!$file->checkPermissionLevel(Acl::LEVEL_WRITE)) {
      throw new Forbidden();
    }

    $this->checkLock($fileId, $file->size > 0);

    $content = fopen('php://input', 'rb');

		// If versioning is enabled then replace the old file with the new one.
		$newFile = \GO\Base\Fs\File::tempFile();
		$newFile->putContents($content);

			//don't set isUploaded file because it fails on windows somehow.
		if(!$file->replace($newFile, false)){
			throw new \Exception("Unable to create version of file: ".$file->name);
    }
    
    Response::get()->setHeader("X-WOPI-ItemVersion", (string) $file->version);
    
		return $this->checkFileInfo($fileId);
  }

  public function checkFileInfo($fileId)
  {

		go()->debug("checkFileInfo($fileId)");

    $file = $this->findFile($fileId);

    if(!$file->checkPermissionLevel(Acl::LEVEL_READ)) {
      throw new Forbidden();
    }

    $canWrite = $file->checkPermissionLevel(Acl::LEVEL_WRITE) && !$file->isTempFile() && $this->getService()->hasPermissionLevel(Acl::LEVEL_WRITE);
    $user = go()->getAuthState()->getUser(['displayName', 'username']);


		$parts = parse_url(Request::get()->getBaseUrl());

		$origin = $parts['scheme'] . '://' . $parts['host'];

		if (isset($parts['port'])) {
			$origin .= ':' . $parts['port'];
		}

    $response = [
      'BaseFileName' => $file->name, // Required
      'OwnerId' => $user->username, // Required
      'Size' => $file->size, // Required
      'UserId' => $user->username, // Required
      'Version' => (string) $file->version, // Required
      'UserCanWrite' => $canWrite, // Required     
      'ReadOnly' => !$canWrite, 
      'UserCanRename' => $canWrite,
      'UserCanNotWriteRelative' => !$canWrite,
      'PostMessageOrigin' => $origin,
      'LastModifiedTime' => date('c', $file->mtime), // ISO 8601 TIME FORMAT	
      'UserFriendlyName' => $user->displayName,
      'HostEditUrl' => Request::get()->getBaseUrl() . "/wopi/edit/" . $this->token->serviceId . "/". $file->id,
      'HostViewUrl' => Request::get()->getBaseUrl() . "/wopi/edit/" . $this->token->serviceId . "/". $file->id,
      

      //See https://wopi.readthedocs.io/en/latest/scenarios/business.html#business-editing
      'LicenseCheckForEditIsEnabled' => true,
      'DownloadUrl' => go()->getSettings()->URL . "index.php?r=files/file/download&id=" . $file->id,
    ];

		go()->debug(json_encode($response, JSON_PRETTY_PRINT));
    
    return array_merge($response, self::CAPABILITIES);
  }

  // TODO lock in GO and webdav too!
  public function lock($fileId)
  {
		$file = $this->findFile($fileId);

    $oldLockId = Request::get()->getHeader("X-WOPI-OldLock");

    if($oldLockId) {
      if(!$file->lock_id) {
        Response::get()->setHeader("X-WOPI-Lock", "");
        throw new Exception(409);
      }
      if($file->lock_id != $oldLockId) {
        Response::get()->setHeader("X-WOPI-Lock", $file->lock_id);
        throw new Exception(409);
      }

			$file->locked_user_id = 0;
			$file->lock_id ="";

    }

    $id = Request::get()->getHeader("X-WOPI-Lock");

    if($file->lock_id) {
      if($file->lock_id == $id) {
        return;
      }
      Response::get()->setHeader("X-WOPI-Lock",$file->lock_id);
      throw new Exception(409, "Already locked");
    }
		$file->lock_id = $id;
		$file->locked_user_id = go()->getUserId();
		$file->save();

		go()->debug("WOPI: lock($fileId) = " . $file->lock_id);
		Response::get()->setHeader("X-WOPI-Lock",$file->lock_id);
  }

  public function getLock($fileId)
  { 
    $file = $this->findFile($fileId);


		go()->debug("WOPI: getLock($fileId) = " . $file->lock_id);

    if(!$file->lock_id) {
      Response::get()->setHeader("X-WOPI-Lock", "");   
    } else{
      Response::get()->setHeader("X-WOPI-Lock", $file->lock_id);
      if($file->locked_user_id != go()->getUserId()) {
        throw new Exception(409, "Locked by another user");
      }
    }     
  }

  public function refreshLock($fileId)
  { 
//    $this->findFile($fileId);
//    $id = Request::get()->getHeader("X-WOPI-Lock");
//    go()->debug($id);
//
//    $lock = Lock::find()->where(['id' => $id])->andWhere('expiresAt', '>', new DateTime())->single();
//
//    if(!$lock) {
//
//      $existing = Lock::find()->where(['fileId' => $fileId])->andWhere('expiresAt', '>', new DateTime())->single();
//      if($existing) {
//        Response::get()->setHeader("X-WOPI-Lock", $existing->id);
//        throw new Exception(409, "Locked by another");
//      }
//
//      throw new NotFound();
//    }
//
//    if(!$lock->refresh()->save()) {
//      throw new Exception(500, "Could not save lock");
//    }

  }

  public function unlock($fileId)
  { 
    $file = $this->findFile($fileId);
    $id = Request::get()->getHeader("X-WOPI-Lock");


		go()->debug("WOPI: unlock($fileId) " . $file->lock_id. " = ". $id);


    if($file->lock_id != $id) {
			if($file->lock_id) {
        Response::get()->setHeader("X-WOPI-Lock", $file->lock_id);
      } else {
        Response::get()->setHeader("X-WOPI-Lock", ""); 
      }
      throw new Exception(409, "Locked by another user");
    }

		$file->lock_id = "";
		$file->locked_user_id = 0;
		$file->save();
  }

  public function putRelative($fileId)
  {

		go()->debug("putRelative($fileId)");
    $baseFile = $this->findFile($fileId);   

    $filename = Request::get()->getHeader("X-WOPI-SuggestedTarget");
    
    if($filename) {

      if(Request::get()->getHeader("X-WOPI-RelativeTarget")) {
        throw new Exception(400, "Can't specify both SuggestedTarget and RelativeTarget");
      }
      $filename = mb_convert_encoding($filename, "UTF-8", "UTF-7");
      if(substr($filename, 0, 1) == ".") {
        //name is just an extension
        $filename = $baseFile->fsFile->nameWithoutExtension() . $filename;
      }

      if(StringUtil::length($filename) > 190) {
        throw new Exception(400, "Filename too long. Max is 190.");
      }

      $file = new File();
      $file->folder_id = $baseFile->folder_id;
      $file->name = $filename;
      $file->appendNumberToNameIfExists();
    } else{

      $filename = Request::get()->getHeader("X-WOPI-RelativeTarget");
      go()->debug("X-WOPI-RelativeTarget:" . $filename);
      
      if(!$filename) {
        throw new Exception(400, "No filename given");
      }
      $filename = mb_convert_encoding($filename, "UTF-8", "UTF-7");

      if(StringUtil::length($filename) > 190) {
        throw new Exception(400, "Filename too long. Max is 190.");
      }
      
      $overWrite = strtolower(Request::get()->getHeader("X-WOPI-OverwriteRelativeTarget")) == "true";

      $file = File::model()->findSingleByAttributes(['folder_id'=> $baseFile->folder_id, 'name' => $filename]);

      if($file) {
        $this->checkLock($file->id, false);

        if(!$overWrite) {        
          throw new Exception(409, "File already exists");
        }
      } else {
        $file = new File();
        $file->folder_id = $baseFile->folder_id;
        $file->name = $filename;
      }
    }


    Response::get()->setHeader("X-WOPI-ValidRelativeTarget", mb_convert_encoding($file->name, "UTF-7", "UTF-8"));
    $content = fopen('php://input', 'rb');		
		    
    if($file->isNew) {
       $file->putContents($content);
    } else
    {
      // If versioning is enabled then replace the old file with the new one.
      $newFile = \GO\Base\Fs\File::tempFile();
      $newFile->putContents($content);
      if(!$file->replace($newFile, false)){
        throw new \Exception("Unable to create version of file: ".$file->name);
      }
    }

    $response = [
      'Name' => $file->name,
      'Url' => $this->getService()->autoWopiClientUri() . "files/" . $file->id .'?access_token=' . Request::get()->getQueryParam('access_token'),
      'HostEditUrl' => Request::get()->getBaseUrl() . "/wopi/edit/" . $this->token->serviceId . "/". $file->id,
      'HostViewUrl' => Request::get()->getBaseUrl() . "/wopi/edit/" . $this->token->serviceId . "/". $file->id
    ];
    
    return $response;
  }

  public function renameFile($fileId)
  { 
    $this->checkLock($fileId);

    $file = $this->findFile($fileId);    

    $name = Request::get()->getHeader("X-WOPI-RequestedName");
    $name = mb_convert_encoding($name, "UTF-8", "UTF-7");

    $file->name = $name;
    if(!$file->save()) {
      throw new \Exception("Could not save file");
    }    
  }

  public function delete($fileId)
  {
    $this->checkLock($fileId, false);

    $file = $this->findFile($fileId);
    
    if(!$file->checkPermissionLevel(Acl::LEVEL_DELETE)) {
      throw new Forbidden();
    }

    $success = $file->delete();
    if(!$success) {
      throw new Exception(500, "Could not delete file");
    }
   }

  public function putUserInfo($fileId)
  { 
    throw new Exception(501);
  }
}
