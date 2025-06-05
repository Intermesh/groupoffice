<?php
namespace go\modules\community\comments\model;

use go\core\orm\Mapping;
use go\core\orm\Property;

/**
 * EmailAddress model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class CommentAttachment extends Property {

	public int $id;
	public string $commentId;
	public string $blobId;
	public string $name;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("comments_comment_attachment");
	}

}