<?php
class TagParser{
	function parseAttributes($attributes_string) {
		preg_match_all('/\s*([^=]+)="([^"]*)"/', ' '.$attributes_string, $attribute_arr);
		$attr=array();

		for($i=0;$i<count($attribute_arr[1]);$i++) {
			$attr[$attribute_arr[1][$i]]=$attribute_arr[2][$i];
		}

		return $attr;
	}

	function parseTag($tagname, $content){
		preg_match_all('/<'.$tagname.'([^>]*)>(.*)<\/'.$tagname.'>/sU',$content,$matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER );
		$tags = array();
		for($n=0;$n<count($matches);$n++) {

			$tag=array(
				'tag' => $matches[$n][0][0],
				'startPos'=>$matches[$n][0][1],
				'content' => $matches[$n][2][0],
				'attributes'=>$this->parseAttributes($matches[$n][1][0])
			);
			$tags[]=$tag;
		}
		return $tags;
	}


}