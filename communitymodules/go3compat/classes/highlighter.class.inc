<?php
class highlight {
	var $keyword;
	var $replacement;
	var $hightlight_bad_tags = array("A","IMG","DIV","P", "SPAN");
	var $begin_boundary;
	var $end_boundary;

	function  __construct($keyword=false,$replacement=false) {
		$this -> keyword = $keyword;
		$this -> replacement = $replacement;
	}
	function highlighter($matches) {

		
			$regex = "/".$this->begin_boundary."(".$this -> keyword.")".$this->end_boundary."/sui";

			//$proceed =  stripslashes(preg_replace($regex,str_replace("{keyword}",$matches[3],$this -> replacement),$matches[0]));
			$proceed =  str_replace($this->keyword, str_replace("{keyword}",$matches[3],$this->replacement),$matches[0]);
			go_debug($matches);
			go_debug($proceed);

		return $proceed;
	}

	function replace_in_tags($matches){
		return stripslashes(str_replace($matches[1], '{TEMP}', $matches[0]));
	}

	function process($text,$keyword = false,$replacement = false) {

		//echo '/<[^>]*('.$keyword.')[^>]*>/is';
		

		if($keyword != false) $this -> keyword = $keyword;
		if($replacement != false) $this -> replacement = $replacement;

		if((isset($this -> keyword)) AND (isset($this -> replacement))) {			

			if(substr($this->keyword,0,1)=='*') {
				$this->keyword=substr($this->keyword,1);
				$this->begin_boundary='';
			}else {
				$this->begin_boundary='\\b';
			}

			if(substr($this->keyword,-1)=='*') {
				$this->keyword=substr($this->keyword,0,-1);
				$this->end_boundary='';
			}else {
				$this->end_boundary='\\b';
			}
			
			$text = preg_replace_callback('/<[^>]*('.$this->keyword.')[^>]*>/uis',array(&$this, 'replace_in_tags'), $text);
			//$text = str_ireplace($this->keyword, str_replace('{keyword}', $this->keyword, $this->replacement), $text);

			$regex = "/".$this->begin_boundary."(".$this -> keyword.")".$this->end_boundary."/sui";

			$replacements = explode('{keyword}', $this->replacement);

			$text = preg_replace($regex, $replacements[0].'\\1'.$replacements[1], $text);

			go_debug($replacements);

			//$regex="/(<([A-Za-z]+)[^>]*[\>]*)*".$this->begin_boundary."(".$this -> keyword.")".$this->end_boundary."(.*?)(<\/\\2>)*/sui";
			//$text =  preg_replace_callback($regex,array(&$this, 'highlighter'), $text);

			//go_debug(strlen($text));
			$text = str_replace('{TEMP}', $this->keyword, $text);			
		}
		
		return $text;
	}
}
