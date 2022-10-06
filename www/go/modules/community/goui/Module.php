<?php
namespace go\modules\community\goui;

use go\core\App;
use go\core;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\modules\community\test\model\A;
use go\modules\community\test\model\ADynamic;
use go\modules\community\test\model\B;

class Module extends core\Module {	

	public function getAuthor(): string
	{
		return "Intermesh BV";
	}
}
