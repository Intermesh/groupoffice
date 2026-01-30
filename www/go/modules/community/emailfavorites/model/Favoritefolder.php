<?php

namespace go\modules\community\emailfavorites\model;

use go\core\jmap\Entity;
use go\core\orm\Mapping;

class Favoritefolder extends Entity
{
	public $id;
	public $account_id;
	public $mailbox;
	public $name;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('em_folders_favorites', 'eff');
	}

	public function internalSave(): bool
	{
		return parent::internalSave();
	}
}
