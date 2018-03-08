#!/usr/bin/php
<?php
if(!isset($argv[1]) || !isset($argv[2]))
{
	exit('usage ./minify.php scriptsfile.txt output.js');
}

$full_path = dirname(__FILE__);
$compressor="java -jar $full_path/yuicompressor-2.4.2.jar";

//chdir($full_path);

$tempfile = uniqid(time()).'.js';

echo getcwd()."\n";
echo "Concatting scripts\n";

$scripts_fp = fopen($argv[1], 'r');
while($script = fgets($scripts_fp))
{
	$script = trim($script);
	
	if(!empty($script))
	{		
		$contents = file_get_contents($script);	
		if(!$contents)
		{
			exit('Could not get contents from '.$script);
		}	
		file_put_contents($tempfile, $contents.';', FILE_APPEND);
	}
}
fclose($scripts_fp);

echo "Compressing scripts\n";

exec($compressor.' '.$tempfile.' -o '.$argv[2]);
unlink($tempfile);

echo "Finished!\n";
?>