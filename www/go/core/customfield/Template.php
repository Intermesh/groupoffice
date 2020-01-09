<?php

namespace go\core\customfield;

class Template extends Base {

	public function onFieldDelete()
	{
		return true;
	}

	public function hasColumn()
	{
		return false;
	}

	public function onFieldSave()
	{
		return true;
	}

	public function dbToApi($value, &$values, $entity) {
    	return null;
    }

}

