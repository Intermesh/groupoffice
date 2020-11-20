<?php


namespace go\modules\community\notes\convert;

use Exception;
use go\core\data\convert;
use go\core\fs\File;
use go\core\model\Acl;
use go\core\orm\Entity;
use go\modules\community\notes\model\Note;


class Spreadsheet extends convert\Spreadsheet
{
	public static $excludeHeaders = [ 'password', 'images'];

}