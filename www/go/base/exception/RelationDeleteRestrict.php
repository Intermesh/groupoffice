<?php
/*
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * Thrown when a user doesn't have access
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: exceptions.class.inc.php 6002 2010-10-27 13:21:25Z mschering $
 * @copyright Copyright Intermesh
 * @package GO.base.exception
 * 
 * @uses Exception
 */


namespace GO\Base\Exception;


class RelationDeleteRestrict extends \Exception
{

	public function __construct($model, $relation) {
		
		$relationModelName = \GO::getModel($relation['model'])->localizedName;	
		
		parent::__construct(sprintf(\GO::t("You can't delete this %s because it contains '%s' items. Please remove those first."), strtolower($model->localizedName), $relationModelName));
	}
}
