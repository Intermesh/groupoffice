<?php


namespace GO\Base\Html;


class Select extends Input {

	public static function render($attributes,$echo=true) {
		$i = new self($attributes);
		if($echo)
			echo $i->getHtml();
		else
			return $i->getHtml();
	}
	
	protected function init() {
		$this->attributes['type']='select';
		$this->attributes['class'].=' select';
	}
}
