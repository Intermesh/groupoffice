<?php


namespace GO\Base\Html;


class Radio extends Input {
	
	public static function render($attributes,$echo=true) {
		$i = new self($attributes);
		if($echo)
			echo $i->getHtml();
		else
			return $i->getHtml();
	}
	
	protected function init() {
		$this->attributes['type']='radio';		
		$this->attributes['class'].=' radio';
	}


}
