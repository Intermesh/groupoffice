<?php
namespace go\modules\community\wopi\controller;

use go\core\Controller;
use go\core\exception\Forbidden;
use go\core\model\Acl;
use go\modules\community\wopi\model;
use GO\Files\Model\File;

/**
 * The controller for the Service entity
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Edit extends Controller
{
  public function launch($serviceId, $fileId)
  {
	  $service = model\Service::findById($serviceId);

	  if(!$service) {
	  	throw new \Exception("Invalid service ID " . $serviceId);
	  }

	  if(!$service->hasPermissionLevel(Acl::LEVEL_READ)) {
	  	throw new Forbidden();
	  }

    $token = model\Token::get($serviceId, go()->getUserId());

	  $file = File::model()->findByPk($fileId);

    $actions = $this->findActions($serviceId, $file);

    $defaultAction = $file->hasPermissionLevel(Acl::LEVEL_WRITE) && $service->hasPermissionLevel(Acl::LEVEL_WRITE) ? 'edit' : 'view';

    // Default to 'edit' action. If it's not there use the first action
    $action = isset($actions[$defaultAction]) ? $actions[$defaultAction] : array_shift($actions);


		// define origin for iframe
		$parts = parse_url($action);

		$origin = $parts['scheme'] . '://' . $parts['host'];

		if (isset($parts['port'])) {
			$origin .= ':' . $parts['port'];
		}

    require(dirname(__DIR__) . '/view/edit.php');
  }

  private function getLanguage() {
  	if(strpos(go()->getAuthState()->getUser(['language'])->language, '_') !== false) {
  		return str_replace('_', '-', go()->getAuthState()->getUser(['language'])->language);
	  } else{
  		switch(go()->getAuthState()->getUser(['language'])->language) {
			  case 'en':
			  	return 'en-us';

			  default:
			  	return go()->getAuthState()->getUser(['language'])->language . '-' . go()->getAuthState()->getUser(['language'])->language;

		  }
	  }
  }

  private function findActions($serviceId, File $file)
  {

    $service = model\Service::findById($serviceId);
    return array_map(function ($a) use ($file, $service) {
      $wopiSrc = urlencode($service->autoWopiClientUri()  . "files/" . $file->id);
      $a = preg_replace_callback('/<([a-z_-]+)=([a-z_-]+)(&)?>/i', function(array $matches) use ($wopiSrc) {
        switch($matches[2]) {
          case "WOPI_SOURCE":
            $replacement = $wopiSrc;
          break;

          case "UI_LLCC":
            $replacement = $this->getLanguage();
          break;

          case "DC_LLCC":
	          $replacement = $this->getLanguage();
          break;

          case "BUSINESS_USER":
            //See https://wopi.readthedocs.io/en/latest/scenarios/business.html#business-editing
            $replacement = "1";
          break;
        }

        if(!isset($replacement)) {
          return "";
        }

        if(!empty($matches[3])) {
          $replacement .= '&';
        }

        return $matches[1] . '=' . $replacement;
      }, $a);

      if(strpos($a, $wopiSrc) === false) {
        //for collabora
        $a .= 'WOPISrc=' . $wopiSrc . '&lang=' . $this->getLanguage();
      }

      return $a;
    }, $service->findActions($file->extension));
  }
}
