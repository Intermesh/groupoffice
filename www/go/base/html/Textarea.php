<?php


namespace GO\Base\Html;


class Textarea extends Input {

	public static function render($attributes,$echo=true) {
		$i = new self($attributes);
		if($echo)
			echo $i->getHtml();
		else
			return $i->getHtml();
	}
	
	protected function init() {
		$this->attributes['type']='textarea';
		$this->attributes['class'].=' textarea';
	}
}
