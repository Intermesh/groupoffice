<?php
namespace GO\Base\Util;

class Fpdi extends \setasign\Fpdi\Tcpdf\Fpdi { // change for FPDI v2.2
	
	/**
	 * Pass error message in Exception
	 *
	 * @param string $msg  Error-Message
	 */
	function error($msg) {
		throw new \Exception('<b>FPDI Error:</b> ' . $msg);	
	}		
	
}
