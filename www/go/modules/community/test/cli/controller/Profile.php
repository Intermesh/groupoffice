<?php
namespace go\modules\community\test\cli\controller;

use go\core\Controller;
use go\modules\community\history\Module;
use go\modules\community\test\model\A;

class Profile extends Controller {

	public function __construct()
	{
		go()->getDebugger()->enabled = false;

		Module::$enabled = false;
	}

	public function query() {
		$query = A::find(['id']);

		$all = $query->all();

		foreach($all as $a) {
			echo $a->id ."\n";
		}

		echo "Done\n";
	}

	public function create() {

		for($i = 0; $i < 100; $i++) {
			$a = new A();
			$a->propA = "test";
			$a->save();

			echo $a->id . "\n";
		}

		echo "Done\n";
	}
}