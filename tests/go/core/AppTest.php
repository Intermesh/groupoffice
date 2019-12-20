<?php

namespace go\core;

use GO\Base\Model\Module;
use go\core\util\ClassFinder;

class AppTest extends \PHPUnit\Framework\TestCase
{
	public function testSettings()
	{
		App::get()->getSettings()->language = 'en';
		$success = App::get()->getSettings()->save();

		$this->assertEquals(true, $success);
	}


	public function _testReinstallModules()
	{
		//todo dependencies of modules



		$modules = Module::model()->find();

		$mc = new controller\Module();

		foreach ($modules as $module) {
			if ($module->package == "core") {
				continue;
			}
			$props = $module->getAttributes(['name', 'package']);

			if (isset($module->package)) {
				$response = $mc->uninstall($props);
				$this->assertEquals(true, $response['success']);

				// $response = $mc->install($props);
				// $this->assertEquals(1, count($response['ids']));
			} else {
				$success = $module->delete();
				$this->assertEquals(true, $success);
			}
		}
		//Now the database may only contain tables with go_ and core_ prefixes

		$tables = go()->getDatabase()->getTables();

		$tables = array_filter($tables, function ($table) {
			$parts = explode('_', $table->getName());
			return $parts[0] != 'go' && $parts[0] != 'core';
		});

		$this->assertEquals(0, count($tables));

		//now install everything again

		$cf = new ClassFinder();
		$modules = $cf->findByParent(\go\core\Module::class);
		foreach ($modules as $module) {
			$m = new $module;
			$success = $m->install();
			$this->assertEquals(true, $success);
		}

		$modules = \GO::modules()->getAvailableModules();

		foreach ($modules as $moduleClass) {

			$moduleController = new $moduleClass;
			if ($moduleController instanceof \go\core\Module) {
				continue;
			}
			if ($moduleController->autoInstall() && $moduleController->isInstallable()) {
				$module = new Module();
				$module->name = $moduleController->name();
				$success = $module->save();

				$this->assertEquals(true, $success);
			}
		}


	}
}
