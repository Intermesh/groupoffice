<?php

namespace go\modules\community\tasks\model;

use GO\Calendar\Model\Calendar;
use go\core\db\Criteria;
use go\core\model\User;
use go\core\orm\exception\SaveException;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\model;
use go\core\util\Color;
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
	public $rememberLastItems = true;

	/** @var string */
	protected $lastTasklistIds;

	/**
	 * Set due and start to the current time for new tasks
	 *
	 * @var bool
	 */
	public $defaultDate = false;

	/**
	 * @return Mapping
	 * @throws \ReflectionException
	 */

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable("tasks_user_settings", "tus");
	}

	public function getDefaultTasklistId() {
		if(isset($this->defaultTasklistId)) {
			return $this->defaultTasklistId;
		}

		if(!model\Module::isAvailableFor('community', 'tasks', $this->userId)) {
			return null;
		}

		$tasklist = TaskList::find()->where('createdBy', '=', $this->userId)->single();
		if(!$tasklist) {
			$user = User::findById($this->userId, ['displayName', 'enabled']);
			if(!$user || !$user->enabled) {
				return null;
			}

			$tasklist = TaskList::createFor($this->userId);
			$tasklist->createdBy = $this->userId;
			$tasklist->name = $user->displayName;
			if(!$tasklist->save()) {
				throw new SaveException($tasklist);
			}
		}

		if($tasklist) {
			$this->defaultTasklistId = $tasklist->id;

			//when coming here the models might be read only so we use this query
			$stmt = go()->getDbConnection()->update("tasks_user_settings", ['defaultTasklistId' => $this->defaultTasklistId], ['userId' => $this->userId]);
			$stmt->execute();
			if(!$stmt->rowCount()) {
				$stmt = go()->getDbConnection()->insertIgnore("tasks_user_settings", ['defaultTasklistId' => $this->defaultTasklistId, 'userId' => $this->userId]);
				$stmt->execute();
			}
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
		if (is_array($ids)) {
			$this->lastTasklistIds = JSON::encode($ids);
		} else {
			$this->lastTasklistIds = '';
		}
	}


	public function getSyncTaskListIds() {
		return TaskList::findFor($this->userId)
			->where('syncToDevice', '=', true)
			->selectSingleValue('CAST(tasklist.id as CHAR)')
			->all();
	}

	public function setSyncTaskListIds(array $taskListIds) {

		$tasklists = TaskList::findFor($this->userId)
			->where('syncToDevice', '=', true)
			->andWhere('tasklist.id', '!=', $taskListIds);

		foreach($tasklists as $tasklist) {
			$tasklist->syncToDevice = false;
			$tasklist->save();
		}

		$tasklists = TaskList::findFor($this->userId)
			->where(
				(new Criteria())
					->where('syncToDevice', '=', false)
					->orWhere('syncToDevice', '=', null)
			)
			->andWhere('tasklist.id', '=', $taskListIds);

		foreach($tasklists as $tasklist) {
			$tasklist->syncToDevice = true;
			$tasklist->save();
		}

	}


	public function getSubscribedTaskListIds() {
		return TaskList::findFor($this->userId)
			->where('isSubscribed', '=', true)
			->selectSingleValue('tasklist.id')
			->all();
	}

	public function setSubscribedTaskListIds(array $tasklistIds) {

		$tasklists = TaskList::findFor($this->userId)
			->where('isSubscribed', '=', true)
			->andWhere('tasklist.id', '!=', $tasklistIds);
		foreach($tasklists as $tasklist) {
			$tasklist->isSubscribed = false;
			$tasklist->save();
		}

		$tasklists = TaskList::findFor($this->userId)
			->where('isSubscribed', '=', false)
			->andWhere('tasklist.id', '=', $tasklistIds);
		foreach($tasklists as $tasklist) {
			$tasklist->isSubscribed = true;
			$tasklist->save();
		}

	}


}
