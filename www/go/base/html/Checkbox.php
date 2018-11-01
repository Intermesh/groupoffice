<?php


namespace GO\Base\Html;


class Checkbox extends Input {

	public static function render($attributes,$echo=true) {
		$i = new self($attributes);
		if($echo)
			echo $i->getHtml();
		else
			return $i->getHtml();
	}
	
	protected function init() {
		$this->attributes['type']='checkbox';
		
//		if($this->isPosted)
//			$this->attributes['extra']='checked';
		
		$this->attributes['class']='checkbox';
	}


}
