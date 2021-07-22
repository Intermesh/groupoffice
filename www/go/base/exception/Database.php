<?php

/*
 * Copyright Intermesh BV
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * Exception thrown on database errors
 *
 * @package GO.Exception
 * @copyright Copyright Intermesh
 * @version $Id Database.php 2012-06-14 10:36:28 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 */

namespace GO\Base\Exception;

use Exception;

class Database extends Exception
{

	public function __construct($message,$code=0,$errorInfo=null) {
		
		$message = empty($message) ? 'Database' : "Database\n\n".$message;
		
		parent::__construct($message);
	}
}
