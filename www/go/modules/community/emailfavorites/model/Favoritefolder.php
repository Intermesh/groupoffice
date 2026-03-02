<?php

namespace go\modules\community\emailfavorites\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\model\Module;
use go\core\orm\Filters;
use go\core\orm\Mapping;

class Favoritefolder extends Entity
{
	public $id;
	public $userId;
	public $account_id;
	public $mailbox;
	public $name;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('em_folders_favorites', 'eff');
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('userId', function (Criteria $criteria, $value) {
				$criteria->where('userId', '=', $value);
			});
	}

	protected function canCreate(): bool
	{
		return Module::findByName('community', 'emailfavorites')
			->getUserRights()->mayManage;
	}

	public function internalSave(): bool
	{
		return parent::internalSave();
	}
}
