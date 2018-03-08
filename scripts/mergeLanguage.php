<?php

require('../www/GO.php');

$folder = new \GO\Base\Fs\Folder(\GO::config()->root_path . 'language');

$children = $folder->ls();

foreach ($children as $child) {
	if ($child instanceof \GO\Base\Fs\Folder) {

		$section = $child->name();

		foreach ($child->ls() as $file) {
			if (preg_match('/^[^\.]+\.php/', $file->name()))
				$file->delete();
		}

		foreach ($child->ls() as $file) {
			//echo $file->name()."\n";
			
			merge($file, $child, $section);
		}
	}
}

$folder = new \GO\Base\Fs\Folder(\GO::config()->root_path . 'modules');

$children = $folder->ls();

foreach ($children as $moduleFolder) {
	if ($moduleFolder instanceof \GO\Base\Fs\Folder) {
		
		$section = $moduleFolder->name();
		
//		if($section!='addressbook')
//			continue;
		
		$child = new \GO\Base\Fs\Folder($moduleFolder->path().'/language');
	
		if($child->exists()){
			
			foreach ($child->ls() as $file) {
				if (preg_match('/^[^\.]+\.php/', $file->name()))
					$file->delete();
			}
			
			
			foreach ($child->ls() as $file) {
				merge($file, $child, $section);
			}
		}	
		
	}
}


function merge($file, $child, $section) {
	
	echo $section."\n";
	echo $file."\n";
	echo "---\n\n";
	
	
	$iso = substr($file->name(), 0, strpos($file->name(), '.'));

	$newFile = $child->path() . '/' . $iso . '.php';

	$contents = file_get_contents($file->path());

	$contents = str_replace('<?php', '', $contents);

	$contents = preg_replace('/\/\*.*\*\//mUs', "", $contents);
	$contents = preg_replace("/^\/\/.*/m", "", $contents);
	$contents = preg_replace("/require\(.*\);/", "", $contents);


	if ($file->extension() == 'php') {
		$contents = str_replace('$lang[\'' . $section . '\']', '$l', $contents);
		$contents = str_replace('$lang["' . $section . '"]', '$l', $contents);
		$contents = str_replace('?>','',$contents);
	} else {
		
		$contents = preg_replace('/^GO\.' . $section . '\.lang[\s]*=[\s]*\{\};/m', '', $contents);
		
		$contents = preg_replace('/^Ext\.namespace.*/m','',$contents);
		
		$contents = preg_replace('/^GO\.lang[\s]*=[\s]*\{\};/m', '', $contents);
		$contents = preg_replace('/^GO\.' . $section . '\.lang\.([a-zA-Z0-9_]+)[\s]*=/m', '$l["\\1"]=', $contents);
		$contents = preg_replace('/^GO\.lang\.([a-zA-Z0-9_]+)[\s]*=/m', '$l["\\1"]=', $contents);
		
		$contents = str_replace("',\n","';\n",$contents);
		$contents = str_replace("'\n","';\n",$contents);
		$contents = str_replace("\",\n","\";\n",$contents);
		$contents = str_replace("\"\n","\";\n",$contents);
	}

	$contents = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $contents);

	$contents = preg_replace('/([^;]+)[\n]{1}/', "\\1;\n", $contents);

	if (!file_exists($newFile))
		file_put_contents($newFile, "<?php\n\n");

	file_put_contents($newFile, $contents, FILE_APPEND);
}