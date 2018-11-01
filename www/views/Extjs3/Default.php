<?php
//Breaks forms with file inputs :(
//header('Content-Type: application/json;charset=utf-8');
$string = json_encode($data);

if($string === false) {
	if(\GO::config()->debug){
		var_dump($data);
	}
	throw new \Exception("JSON encoding error ".json_last_error_msg());
}

if(strpos($string,'startjs:')!==false){
	preg_match_all('/"startjs:(.*?):endjs"/usi', $string, $matches, PREG_SET_ORDER);

	for($i=0;$i<count($matches);$i++){
		$string = str_replace($matches[$i][0], stripslashes(str_replace(array('\t','\n'),'',$matches[$i][1])), $string);
	}
}

echo $string;
