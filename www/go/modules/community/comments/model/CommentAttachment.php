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

	public $id;
	/**
	 *
	 * @var int
	 */
	public $commentId;

	/**
	 *
	 * @var string
	 */
	public $blobId;


	public $name;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("comments_comment_attachment");
	}

}