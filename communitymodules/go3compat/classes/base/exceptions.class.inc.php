<?php
/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: exceptions.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.basic.exceptions
 */

/**
 * Thrown when a user doesn't have access
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: exceptions.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @package go.basic.exceptions
 * 
 * @uses Exception
 */

class AccessDeniedException extends Exception
{

	public function __construct() {
		parent::__construct($GLOBALS['lang']['common']['accessDenied']);
	}
}

/**
* Thrown when an SQL insert query failes
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: exceptions.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @package go.basic.exceptions
 * 
 * @uses Exception
 */



class DatabaseInsertException extends Exception
{
	public function __construct() {
		parent::__construct($GLOBALS['lang']['common']['saveError']);
	}
}

/**
 * Thrown when an SQL Update query failes
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: exceptions.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @package go.basic.exceptions
 * 
 * @uses Exception
 */


class DatabaseUpdateException extends Exception
{
	public function __construct() {
		parent::__construct($GLOBALS['lang']['common']['saveError']);
	}
}

/**
 * Thrown when an error ocurred while deleting data
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: exceptions.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @package go.basic.exceptions
 * 
 * @uses Exception
 */


class DatabaseDeleteException extends Exception
{
	public function __construct() {
		parent::__construct($GLOBALS['lang']['common']['deleteError']);
	}
}

/**
* Thrown when a select SQL query fails
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: exceptions.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @package go.basic.exceptions
 * 
 * @uses Exception
 */


class DatabaseSelectException extends Exception
{
	public function __construct() {
		parent::__construct($GLOBALS['lang']['common']['selectError']);
	}
}

/**
 * Thrown when a replace SQL query fails
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: exceptions.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @package go.basic.exceptions
 * 
 * @uses Exception
 */


class DatabaseReplaceException extends Exception
{
	public function __construct() {
		parent::__construct($GLOBALS['lang']['common']['saveError']);
	}
}

/**
 * Thrown when a user does not supply all required info in a form
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: exceptions.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @package go.basic.exceptions
 * 
 * @uses Exception
 */


class MissingFieldException extends Exception
{
	public function __construct() {
		parent::__construct($GLOBALS['lang']['common']['missingField']);
	}
}

class FileNotFoundException extends Exception
{
	public function __construct($path = '') {
		$m = 'File not found';
		if($path!='')
			$m .= ': '.$path;
		parent::__construct($m);
	}
}