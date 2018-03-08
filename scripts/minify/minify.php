#!/usr/bin/php
<?php
if(!isset($argv[1])) {
	exit('usage ./minify.php /path/to/group-office <optional module name or string all> <optional commit string to commit to svn>');
}

$commit = isset($argv[3]) && $argv[3]=='commit';
$module = isset($argv[2]) ? $argv[2] : 'all';

$full_path = dirname(__FILE__);
//$compressor="java -jar $full_path/yuicompressor-2.4.2.jar";
$compressor="java -jar \"$full_path/yuicompressor-2.4.2.jar\"";
//chdir($full_path);

if(!@chdir($argv[1])) {
	exit('Could not change directory to '.$argv[1]);
}
echo getcwd()."\n";

if($commit)
{
	echo "Updating sources...\n";
	system('../scripts/updatepromodules.sh');
}

if($module=='all') {
	echo "Processing main scripts\n";

	if(file_exists('javascript/go-all.js')) {
		unlink('javascript/go-all.js');
	}

	$scripts_fp = fopen('javascript/scripts.txt', 'r');
	while($script = fgets($scripts_fp)) {
		$script = trim($script);

		if(!empty($script)) {
			$contents = file_get_contents($script);
			if(!$contents) {
				exit('Could not get contents from '.$script);
			}
			file_put_contents('javascript/go-all.js', $contents.';', FILE_APPEND);
		}
	}
	fclose($scripts_fp);

	exec($compressor.' "'.getcwd().'/javascript/go-all.js" -o "'.getcwd().'/javascript/go-all-min"');
	//exec($compressor.' "'.getcwd().'/'.$all_scripts_file.'" -o "'.getcwd().'/modules/'.$module.'/all-module-scripts-min"');
	unlink('javascript/go-all.js');

	if($commit){
		echo "Committing to SVN...\n";
		system('svn commit -m "minified" '.getcwd());
	}


	$modules=array();
	if($dir = @opendir('modules')) {
		while($module=readdir($dir)) {
			$modules[]=$module;
		}
	}

}else {
	$modules=array($module);
}

foreach($modules as $module) {
	if(is_dir('modules/'.$module) && $module[0] != '.' && file_exists('modules/'.$module.'/scripts.txt')) {
		echo "Processing $module\n";

		$all_scripts_file = 'modules/'.$module.'/all-module-scripts.js';

		if(file_exists($all_scripts_file)) {
			unlink($all_scripts_file);
		}
		touch($all_scripts_file);

		$scripts_fp = fopen('modules/'.$module.'/scripts.txt', 'r');
		while($script = fgets($scripts_fp)) {
			$script = trim($script);

			if(!empty($script)) {
				$contents = file_get_contents($script);
				if(!$contents) {
					exit('Could not get contents from '.$script);
				}
				file_put_contents($all_scripts_file, $contents.";\n", FILE_APPEND);				
			}
		}
		fclose($scripts_fp);

		exec($compressor.' '.$all_scripts_file.' -o modules/'.$module.'/all-module-scripts-min');
		exec($compressor.' "'.getcwd().'/'.$all_scripts_file.'" -o "'.getcwd().'/modules/'.$module.'/all-module-scripts-min"');
		unlink($all_scripts_file);

		if($commit){
			echo "Committing to SVN...\n";
			system("svn commit -m 'minified' modules/".$module);
		}
	}
}

echo "Finished!\n";
?>
