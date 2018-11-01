<?php


namespace GO\Base\Util;

use GO;

class ConfigEditor {

	public static function save(\GO\Base\Fs\File $file, array $config) {
		$configData = "<?php\n/**\n".
			" * Group-Office configuration file.\n".
			" * Visit https://www.group-office.com/wiki/Configuration_file for available values\n".
			" */\n\n";
		
		$configReflection = new ReflectionClass(GO::config());
		
		
		$defaults = $configReflection->getDefaultProperties();
	
		
		foreach ($config as $key => $value) {
			if(!isset($defaults[$key]) || $defaults[$key]!==$value){
				$configData .= '$config["' . $key . '"]=' . var_export($value,true).';' . "\n";
			}
		}
		
		//make sure directory exists
		$file->parent()->create();
		
		//clear opcache in PHP 5.5
		if(function_exists('opcache_invalidate')){
			opcache_invalidate($file->path(), true);
		}


		return file_put_contents($file->path(), $configData);
	}
}
