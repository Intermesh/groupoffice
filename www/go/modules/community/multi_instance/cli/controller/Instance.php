<?php
namespace go\modules\community\multi_instance\cli\controller;

use go\core\Controller;
use go\core\exception\NotFound;
use go\core\fs\Folder;

class Instance extends Controller {
	/**
	 * docker compose exec --user www-data groupoffice php ./www/cli.php community/multi_instance/Instance/restore --name=test.example.com
	 *
	 * @param $name
	 * @param null $trashPath
	 * @throws NotFound
	 */
	public function restore($params) {

		extract($this->checkParams($params, ['name', 'trashPath'=>null]));

		if(isset($trashPath)) {
			$trashFolder = new Folder($trashPath);
		} else {
			$trashFolder = \go\modules\community\multi_instance\model\Instance::getTrashFolder()->getFolder($name);
		}

		if(!$trashFolder->exists()) {
			throw new NotFound('Folder ' . $trashFolder->getName() .' wasn\'t found in the trash folder');
		}

		require($trashFolder->getFile('config.php'));

		$instance = new \go\modules\community\multi_instance\model\Instance();
		$instance->hostname = $name;
		$instance->setInstanceConfig($config);
		if(!$instance->save()) {
			throw new \Exception("Could not save instance");
		}

		$instance->restoreDump($trashFolder->getFile('database.sql'));

		$dest = new Folder($config['file_storage_path']);
		$dest->delete();
		$trashFolder->move($dest);

		echo "$name is restored!\n";
	}

	/**
	 * docker compose exec --user www-data groupoffice php ./www/cli.php community/multi_instance/Instance/allowModuleForAll --package=community --name=pwned
	 *
	 * @param array $params
	 * @return void
	 * @throws \Exception
	 */
	public function allowModuleForAll(array $params): void
	{
		$this->checkParams($params, ['name', 'package']);

		foreach(\go\modules\community\multi_instance\model\Instance::find() as $instance) {
			echo "Allow for ". $instance->hostname ."\n";
			$instance->addAllowedModule($params['package'], $params['name']);
		}

		echo "All done\n";
	}
}