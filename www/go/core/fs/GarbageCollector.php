<?php

namespace go\core\fs;

class GarbageCollector {

	const forSystem = true;
	const name = "Garbage Collector";
	const description = "Clean all Binary objects that are not used.";

	public function execute() {
		
		$query = \go\core\fs\Blob::find(['id', 'name'])->execute();
		
		while($blob = $query->fetch()){
			try{
				if($blob->delete()) {
					echo 'Removed '. $blob->name ."<br>";
				}
			} catch(\PDOException $e) {
				// wont remove blobs that are referrenced
			}
		}
		//$this->removeEmptyDirs(\go()->getDataFolder()->getPath() .'/data/');
	}
	
	private function removeEmptyDirs($path) {
		$empty=true;
		foreach (glob($path."/*") as $file) {
			$empty &= is_dir($file) && $this->removeEmptyDirs($file);
		}
		return $empty && rmdir($path);
	 }
	
}
