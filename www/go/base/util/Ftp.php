<?php
/*
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

// Example Usage
// 
//$ftp = new ftp('ftp.example.com');
//$ftp->ftp_login('username','password');
//var_dump($ftp->ftp_nlist());
// See php.net/manual/en/book.ftp.php for more information

/**
 * Ftp connection class
 * 
 * Example Usage
 * 
 * $ftp = new ftp('ftp.example.com');
 * $ftp->ftp_login('username','password');
 * var_dump($ftp->ftp_nlist());
 * 
 * See php.net/manual/en/book.ftp.php for more information
 * 
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @version $Id: config.class.inc.php 7687 2011-06-23 12:00:34Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @package GO.base.util 
 */

namespace GO\Base\Util;


class Ftp{
	
    private $_conn;

    public function __construct($url){
        $this->_conn = ftp_connect($url);
    }
   
    public function __call($func,$a){
			$func = 'ftp_'.$func;
        if(function_exists($func)){
            array_unshift($a,$this->_conn);
            return call_user_func_array($func,$a);
        }else{
            // replace with your own error handler.
            die("$func is not a valid FTP function");
        }
    }
}
?>
