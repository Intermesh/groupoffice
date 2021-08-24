<?php
namespace go\modules\community\multi_instance;

use go\core\App;
use go\core\http\Request;
use go\core\http\Response;
use go\core\Installer;
use go\core\webclient\Extjs3;
use go\modules\community\multi_instance\model\Instance;

class Module extends \go\core\Module {
	
	public function getAuthor() {
		return "Intermesh BV";
	}


	protected function afterInstall(\go\core\model\Module $model) {
		
		$cron = new \go\core\model\CronJobSchedule();
		$cron->moduleId = $model->id;
		$cron->name = "InstanceCron";
		$cron->expression = "* * * * *";
		$cron->description = "Cron for instances";
		
		if(!$cron->save()) {
			throw new \Exception("Failed to save cron job: " . var_export($cron->getValidationErrors(), true));
		}
		
		
		$cron = new \go\core\model\CronJobSchedule();
		$cron->moduleId = $model->id;
		$cron->name = "DeactivateTrials";
		$cron->expression = "0 10 * * *";
		$cron->description = "Deactivate trials";
		
		if(!$cron->save()) {
			throw new \Exception("Failed to save cron job: " . var_export($cron->getValidationErrors(), true));
		}
		
		return parent::afterInstall($model);
	}

	public function defineListeners()
	{
		parent::defineListeners();

		go()->getInstaller()->on(Installer::EVENT_UPGRADE, static::class, 'upgradeInstances');

		go()->on(App::EVENT_INDEX, static::class, 'checkUrl');
	}

	public static function checkUrl() {
		$configUrl = go()->getSettings()->URL;
		$p = parse_url($configUrl, PHP_URL_HOST);

		if($p != Request::get()->getHost())  {
			Extjs3::get()->renderPage(
				"<section><div class='card'><h1>" .go()->t("Not found") . "</h1><p>" .
				go()->t("Sorry, this instance wasn't found. Please double check the URL you've entered.")
				."</p></div></section>"

				,
				go()->t("Not found"));
			exit();
		}

	}

	public static function upgradeInstances() {

		echo "\nUpgrading all instances\n";
		echo "-------------------------------\n\n";

		$failed = 0;

		foreach(Instance::find() as $instance) {
			if(!$instance->isInstalled()) {
				echo "Skipping not installed instance: " . $instance->hostname ."\n";
				continue;
			}
			echo "Upgrading instance: " . $instance->hostname . ": ";
			flush();
			$success = $instance->upgrade();

			echo $success ? "ok" : "!!! FAILED !!!";

			if(!$success) {
				$failed++;
			}

			echo "\n";
		}

		if(!$failed) {
			echo "All OK!\n";
		} else{
			echo "\n\nWARNING: There are $failed failed upgrades. Please investigate!\n\n";
		}
	}


	public function downloadSiteConfig() {

		Response::get()->setContentType('text/plain');
		Response::get()->sendHeaders();

		$i = [];

		foreach(Instance::find() as $instance) {
			$version = $instance->getMajorVersion();
			if(!$version || $version == go()->getMajorVersion()) {
				$version = 'DEFAULT';
			}
			if(!isset($i[$version])) {
				$i[$version] = [];
			}

			$i[$version][] = $instance->hostname;
		}

	//	$i['6.5'] = ['test.65', 'test2.65', 'test.65', 'test2.65', 'test.65', 'test2.65'];

		$tpl = file_get_contents(__DIR__ . '/site-conf.tpl');

		foreach($i as $version => $hostnames) {
			if($version == 'DEFAULT') {
				continue;
			}

			echo $this->parseTemplate($tpl, $version, $hostnames);
		}

		echo $this->parseTemplate($tpl, "DEFAULT", $i['DEFAULT']);
	}

	private function parseTemplate($tpl, $version, $hostnames) {

		$tld = substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], '.') + 1);

		$replacements = [
			'{docroot}' => $version == 'DEFAULT' ? go()->getEnvironment()->getInstallFolder()->getPath() : '/usr/local/share/groupoffice-' . $version . '/www',
			'{aliases}' => $version == 'DEFAULT' ? '*.' . $tld .' ' .$this->implode($hostnames) : $this->implode($hostnames),
			'{tld}' => $tld,
			'{servername}' => strtolower(str_replace('.', '', $version)) . '.' . $tld,
			'{version}' => str_replace('.', '', $version)
		];

		return str_replace(array_keys($replacements), array_values($replacements), $tpl);


	}

	private function implode($aliases) {

		$str = "";
		$i = 0;

		foreach($aliases as $a) {
			$str .= $a;
			$i++;

			if($i == 4) {
				$i = 0;

				$str .= " \\\n    ";

			} else{
				$str .= ' ';
			}
		}

		return trim($str);
	}
}
