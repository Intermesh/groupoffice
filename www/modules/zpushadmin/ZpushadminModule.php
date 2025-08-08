<?php

namespace GO\Zpushadmin;

class ZpushadminModule extends \GO\Base\Module {


	public function autoInstall()
	{
		return true;
	}

	public function depends() {
		return array('sync');
	}

	public function adminModule()
	{
		return true;
	}


	public static function checkZPushVersion($versionToCompare) {

//		if (!defined('ZPUSH_VERSION')) {
			self::includeZpushFiles();
//		}

		$shortversion = false;
		if (defined('ZPUSH_VERSION')) {
				$shortversion = substr(ZPUSH_VERSION, 0, 3);
		}else
		{
			throw new \Exception("Z-Push was not found. Is it installed?");
		}

		if ($versionToCompare === $shortversion) {
			return true;
		} else {
			return false;
		}
	}

	public static function getModuleFolder() {
		return 'z-push';
	}

	public static function includeZpushFiles() {
		
		if (defined('ZPUSH_VERSION')) {
			return;
		}

			
		require_once(\GO::config()->root_path . 'modules/z-push/bootstrap.php');
		require_once ZPUSH_CONFIG;


		set_include_path(get_include_path() . PATH_SEPARATOR . BASE_PATH);
		\ZPush::CheckConfig();
	}

}
