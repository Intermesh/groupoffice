<?php

namespace GO\Base\Html;


class Submit extends Input {
	
	public static function render($attributes,$echo=true) {
		$i = new self($attributes);
		if($echo)
			echo $i->getHtml();
		else
			return $i->getHtml();
	}
	
	protected function init() {
		$this->attributes['type']='submit';		
		$this->attributes['class'].=' button submit';
		
		//following does not work on chrome
		//$this->attributes['extra']='onclick="this.disabled=true;return true;"';
	}
}
