<?php
$string =  json_encode($data);

if(strpos($string,'startjs:')!==false){
	preg_match_all('/"startjs:(.*?):endjs"/usi', $string, $matches, PREG_SET_ORDER);

	for($i=0;$i<count($matches);$i++){
		$string = str_replace($matches[$i][0], stripslashes(str_replace(array('\t','\n'),'',$matches[$i][1])), $string);
	}
}

echo $string;