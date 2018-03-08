<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

namespace GO\Notes\Customfields\Model;

use GO\Customfields\Model\AbstractCustomFieldsRecord;

/**
 * 
 * The note model custom fields model.
 * 
 */

class Note extends AbstractCustomFieldsRecord{

	public function extendsModel(){
		return "GO\Notes\Model\Note";
	}
}