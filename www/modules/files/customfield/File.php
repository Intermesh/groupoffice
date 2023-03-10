<?php

namespace GO\Files\Customfield;

use go\core\customfield\Base;
use GO\Files\Model;

class File extends Base {

	public function getModelClass(): string
	{
		return Model\File::class;
	}
	
	protected function getFieldSQL(): string
	{
		$d = $this->field->getDefault();
		$d = isset($d) ? (int) $d : "NULL";
		return "VARCHAR(512) DEFAULT " . $d;
	}
}
