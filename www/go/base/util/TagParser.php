<?php

namespace GO\Base\Util;


class TagParser{
	
	/**
	 * Parse as string for tags. Tags must be closed.
	 * 
	 * @param string $tagName eg. img
	 * @param string $text The text to parse
	 * 
	 * @return array Example of $tag array:
	 * 
	 * array (size=4)
   * 'outerText' => string '{site:thumb path="Tickets.png" lw="300" ph="300" zoom="true" lightbox="docs" caption="Screenshot of the tickets module"}{/site:thumb}' (length=133)
   * 'tagName' => string 'thumb' (length=5)
   * 'params' => 
   *   array (size=6)
   *     'path' => string 'Tickets.png' (length=11)
   *     'lw' => string '300' (length=3)
   *     'ph' => string '300' (length=3)
   *     'zoom' => string 'true' (length=4)
   *     'lightbox' => string 'docs' (length=4)
   *     'caption' => string 'Screenshot of the tickets module' (length=32)
   * 'innerText' => string '' (length=0)
	 * 
	 */
	
	public $tagStart='<';
	public $tagEnd='>';
	
	public function getTags($text){
		
		$closeTagStart = strlen($this->tagStart) > 1 ? substr($this->tagStart,0,1).'/'.substr($this->tagStart,1) : $this->tagStart.'/';
		
				
		//$pattern = '/'.preg_quote($this->tagStart,'/').'([a-zA-Z0-9-^ ]+) ([^'.preg_quote($this->tagEnd,'/').']*)'.preg_quote($this->tagEnd,'/').'(.*?)'.preg_quote($closeTagStart,'/').'[^'.preg_quote($this->tagEnd,'/').']+'.preg_quote($this->tagEnd,'/').'/s';		
		$pattern = '/'.preg_quote($this->tagStart,'/').'([a-zA-Z0-9-^ ]+) ([^'.preg_quote($this->tagEnd,'/').']*)'.preg_quote($this->tagEnd,'/').'((.*?)'.preg_quote($closeTagStart,'/').'\1'.preg_quote($this->tagEnd,'/').')?/s';		

//		var_dump($pattern);
		
		$matched_tags=array();		
		preg_match_all($pattern,$text,$matched_tags, PREG_SET_ORDER);
		
		
		$tags = array();	
		for($n=0;$n<count($matched_tags);$n++) {			
			// parse params
			$params_array = array();
			$params=array();
			preg_match_all('/\s*([^=]+)="([^"]*)"/',$matched_tags[$n][2],$params, PREG_SET_ORDER);
			for ($i=0; $i<count($params);$i++) {
				$right = $params[$i][2];
				$left = $params[$i][1];
				$params_array[$left]= $right;
			}
			
			$tag = array(
					
					'outerText'=>$matched_tags[$n][0],
					'tagName'=>$matched_tags[$n][1],
					'params'=>$params_array,
					'innerText'=>isset($matched_tags[$n][4]) ? $matched_tags[$n][4]	: null
					);
			
			$tags[] = $tag;
		}
		
		return $tags;
	}
}
