<?php
namespace go\modules\community\files\model;

use go\core\orm;

class Version extends orm\Property {
	
	public $createdAt;
	public $byteSize;
	public $blobId;
}