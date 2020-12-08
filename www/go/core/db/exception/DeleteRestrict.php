<?php
namespace go\core\db\exception;

use Exception;
use go\core\orm\Record;
use go\core\orm\Relation;

/**
 * Throw when an operation was forbidden.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class DeleteRestrict extends Exception
{
	public function __construct(Record $model, Relation $relation) {
		parent::__construct("model: ".$model->getClassName().' relation: '.$relation->getName());
	}
}
