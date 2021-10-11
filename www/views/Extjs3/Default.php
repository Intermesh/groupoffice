<?php
//Breaks forms with file inputs :(
//header('Content-Type: application/json;charset=utf-8');

// some JSON can be malformed due to UTF-8 mistakes and json_encode() will die on it. We need to sanitize it
// Using function_exists() because this file gets included many times and otherwise the function would be declared more than once

if (!function_exists('utf8ize')) {
        function utf8ize( $mixed ) {
                if (is_array($mixed)) {
                        foreach ($mixed as $key => $value) {
                                $mixed[$key] = utf8ize($value);
                        }
                } elseif (is_string($mixed)) {
                        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
                }
                return $mixed;
        }
}

$string = json_encode($data);

if($string === false) {
	
	// Second attempt at encoding using our function
	if (function_exists('utf8ize')) {
		$string = json_encode(utf8ize($data));
	}
	
	if($string === false) {
		// It really doesn't work. Throw Exception
		if(\GO::config()->debug){
			var_dump($data);
		}
		throw new \Exception("JSON encoding error ".json_last_error_msg());
	}
}

if(strpos($string,'startjs:')!==false){
	preg_match_all('/"startjs:(.*?):endjs"/usi', $string, $matches, PREG_SET_ORDER);

	for($i=0;$i<count($matches);$i++){
		$string = str_replace($matches[$i][0], stripslashes(str_replace(array('\t','\n'),'',$matches[$i][1])), $string);
	}
}

echo $string;
