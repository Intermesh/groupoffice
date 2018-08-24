<?php
namespace go\modules\core\core\controller;


class System extends \go\core\cli\Controller {
	public function garbageCollect() {
		$blobs = \go\core\fs\Blob::find()->execute();
		
		echo "Processing: ".$blobs->rowCount() ." blobs\n";
		$staleCount = 0;
		foreach($blobs as $blob) {
			if($blob->setStaleIfUnused()) {
				echo 'D';
				$staleCount++;
			}else
			{
				echo '.';
			}
		}
		
		echo "\n\nFound " . $staleCount ." stale blobs\n";
	}
}
