<?php

namespace GO\Files\Customfield;

use go\core\customfield\Base;

class File extends Base {

	protected function getFieldSQL() {
		$d = $this->field->getDefault();
		$d = isset($d) ? (int) $d : "NULL";
		return "VARCHAR(512) DEFAULT " . $d;
	}
}
