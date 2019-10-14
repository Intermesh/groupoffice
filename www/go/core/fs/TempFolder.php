<?php
namespace go\core\fs;

/**
 * A folder object
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class TempFolder extends Folder {
	
	public function __construct() {
		
		$tmp = \go()->getTempFolder()->getFolder(uniqid());
		$tmp->create();
		
		parent::__construct($tmp->getPath());
	}

	public function __destruct() {
		$this->delete();
	}
}
