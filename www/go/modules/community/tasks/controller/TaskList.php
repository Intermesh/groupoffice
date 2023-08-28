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
use go\core\model\Module as CoreModule;
use go\modules\community\tasks\model;

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
		// Support module should not be dependent on mayChangeTasklists permission
		if (!property_exists($this->rights, 'mayChangeTasklists')) {
			$yesWeCan = false;
			foreach (['create', 'update'] as $action) {
				if (!isset($params[$action])) {
					continue;
				}
				foreach ($params[$action] as $k => $p) {
					if ($p["role"] === model\TaskList::Roles[model\TaskList::Support]) {
						$tmpRights = go()->getAuthState()->getClassRights(\go\modules\business\support\controller\Migrate::class);
						if ($tmpRights->mayManage) {
							$yesWeCan = true;
							break 2;
						}
					}
				}
			}
			if (!$yesWeCan) {
				throw new Forbidden();
			}
		} elseif (!$this->rights->mayChangeTasklists) {
			throw new Forbidden();
		}
		return $this->defaultSet($params);
	}

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