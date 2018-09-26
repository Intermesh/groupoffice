<?php

namespace GO\Customfields\Customfieldtype;


class Textarea extends Text{
	
	protected $maxLength = 0;
	
	public function name(){
		return 'Textarea';
	}
	
	public function fieldSql(){
		return "TEXT NULL";
	}
	
	public function selectForGrid(){
		return false;
	}
}
