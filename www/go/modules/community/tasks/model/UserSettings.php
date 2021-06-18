<?php

namespace go\modules\community\tasks\model;

use go\core\model\User;
use go\core\orm\Property;
use go\core\model;
use go\core\util\JSON;

class UserSettings extends Property {

	/**
	 * Primary key to User id
	 * 
	 * @var int
	 */
	public $userId;
	
	/**
	 * Default Note book ID
	 * 
	 * @var int
	 */
	protected $defaultTasklistId;

	/**
	 * @var bool
	 */
	public $rememberLastItems;

	/** @var string */
	protected $lastTasklistIds;

	/**
	 * @return \go\core\orm\Mapping
	 * @throws \ReflectionException
	 */

	protected static function defineMapping() {
		return parent::defineMapping()->addTable("tasks_user_settings", "tus");
	}

	public function getDefaultTasklistId() {
		if(isset($this->defaultTasklistId)) {
			return $this->defaultTasklistId;
		}

		if(!model\Module::isAvailableFor('community', 'tasks', $this->userId)) {
			return null;
		}

		$tasklist = Tasklist::find()->where('createdBy', '=', $this->userId)->single();
		if(!$tasklist) {
			$tasklist = new Tasklist();
			$tasklist->createdBy = $this->userId;
			$tasklist->name = User::findById($this->userId, ['displayName'])->displayName;
			if(!$tasklist->save()) {
				throw new \Exception("Could not create default Note book");
			}
		}

		if($tasklist) {
			$this->defaultTasklistId = $tasklist->id;
			go()->getDbConnection()->update("tasks_user_settings", ['defaultTasklistId' => $this->defaultTasklistId], ['userId' => $this->userId])->execute();
		}

		return $this->defaultTasklistId;
		
	}

	public function setDefaultTasklistId($id) {
		$this->defaultTasklistId = $id;
	}



	/**
	 * @return array
	 */
	public function getLastTasklistIds(): array
	{
		if (!empty($this->lastTasklistIds)) {
			return JSON::decode($this->lastTasklistIds);
		}
		return [$this->getDefaultTasklistId()]; // The default notebook id makes sense in this case
	}

	/**
	 * @param array|null $ids
	 */
	public function setLastTasklistIds(?array $ids = null)
	{
		if (is_array($ids) && count($ids) > 0) {
			$this->lastTasklistIds = JSON::encode($ids);
		} else {
			$this->lastTasklistIds = '';
		}
	}


}
