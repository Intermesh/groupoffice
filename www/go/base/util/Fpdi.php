<?php
namespace GO\Base\Util;

class Fpdi extends \FPDI {
	
	/**
	 * Pass error message in Exception
	 *
	 * @param StringHelper $msg  Error-Message
	 */
	function error($msg) {
		throw new \Exception('<b>FPDI Error:</b> ' . $msg);	
	}		
	
}
