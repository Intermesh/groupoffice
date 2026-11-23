<?php
/**
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

namespace go\modules\community\tasks\controller;

use go\core\exception\Forbidden;
use go\core\jmap\Entity;
use go\core\jmap\EntityController;
use go\core\model\Acl;
use go\core\model\Module as CoreModule;
use go\modules\community\tasks\model;

/**
 * @extends EntityController<\go\modules\community\tasks\model\TaskList>
 */
class TaskList extends EntityController
{

	protected function entityClass(): string
	{
		return model\TaskList::class;
	}

	public function query($params)
	{
		return $this->defaultQuery($params);
	}

	public function get($params)
	{
		return $this->defaultGet($params);
	}

	public function set($params)
	{
		return $this->defaultSet($params);
	}

	/**
	 * Renaming a list or changing its permissions needs manage permission on the list. The module right
	 * "mayChangeTasklists" is only needed to create and delete lists.
	 *
	 * User properties, like the subscription, may be changed by everyone who can read the list.
	 */
	protected function canUpdate(Entity $entity): bool
	{
		if(!$this->onlyUserPropsModified($entity) && !$entity->hasPermissionLevel(Acl::LEVEL_MANAGE)) {
			return false;
		}
		return parent::canUpdate($entity);
	}

	/**
	 * Deleting a list needs the module right "mayChangeTasklists" and manage permission on the list.
	 */
	protected function canDestroy(Entity $entity): bool
	{
		if(!$this->rights->mayChangeTasklists) {
			return false;
		}
		return parent::canDestroy($entity);
	}

	public function changes($params)
	{
		return $this->defaultChanges($params);
	}
}