<?php


namespace GO\Base\Html;


class Hidden extends Input {
	
	public static function render($attributes,$echo=true) {
		$i = new self($attributes);
		if($echo)
			echo $i->getHtml();
		else
			return $i->getHtml();
	}
	
	protected function init() {
		$this->attributes['type']='hidden';		
		$this->attributes['class'].=' hidden';
		$this->attributes['renderContainer'] = false;
		$this->attributes['label'] = false;
	}


}
