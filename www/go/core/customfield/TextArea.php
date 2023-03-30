<?php

namespace go\core\customfield;

class TextArea extends Base {
	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	protected function getFieldSQL(): string
	{
		return "TEXT NULL";
	}
	
}
