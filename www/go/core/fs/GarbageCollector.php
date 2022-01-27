<?php

namespace go\core\fs;

use PDOException;

class GarbageCollector {

	const forSystem = true;
	const name = "Garbage Collector";
	const description = "Clean all Binary objects that are not used.";

	public function execute() {
		
		$query = Blob::find(['id', 'name'])->execute();
		
		while($blob = $query->fetch()){
			try{
				if($blob->delete()) {
					echo 'Removed '. $blob->name ."<br>";
				}
			} catch(PDOException $e) {
				// wont remove blobs that are referenced
			}
		}
		//$this->removeEmptyDirs(\go()->getDataFolder()->getPath() .'/data/');
	}
	
	private function removeEmptyDirs($path): bool
	{
		$empty=true;
		foreach (glob($path."/*") as $file) {
			$empty &= is_dir($file) && $this->removeEmptyDirs($file);
		}
		return $empty && rmdir($path);
	 }
	
}
