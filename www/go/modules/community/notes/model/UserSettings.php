<?php

namespace go\modules\community\notes\model;

use go\core\model\User;
use go\core\orm\exception\SaveException;
use go\core\orm\Mapping;
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
	protected $defaultNoteBookId;

	/**
	 * @var bool
	 */
	public $rememberLastItems;

	/** @var string */
	protected $lastNoteBookIds;

	/**
	 * @return Mapping
	 * @throws \ReflectionException
	 */

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable("notes_user_settings", "abs");
	}

	public function getDefaultNoteBookId() {
		if(isset($this->defaultNoteBookId)) {
			return $this->defaultNoteBookId;
		}

		if(!model\Module::isAvailableFor('community', 'notes', $this->userId)) {
			return null;
		}

		$noteBook = NoteBook::find()->where('createdBy', '=', $this->userId)->single();
		if(!$noteBook) {
			$noteBook = new NoteBook();
			$noteBook->createdBy = $this->userId;
			$noteBook->name = User::findById($this->userId, ['displayName'])->displayName;
			if(!$noteBook->save()) {
				throw new SaveException($noteBook);
			}
		}

		if($noteBook) {
			$this->defaultNoteBookId = $noteBook->id;
			go()->getDbConnection()->update("notes_user_settings", ['defaultNoteBookId' => $this->defaultNoteBookId], ['userId' => $this->userId])->execute();
		}

		return $this->defaultNoteBookId;
		
	}

	public function setDefaultNoteBookId($id) {
		$this->defaultNoteBookId = $id;
	}



	/**
	 * @return array
	 */
	public function getLastNoteBookIds(): array
	{
		if (!empty($this->lastNoteBookIds)) {
			return JSON::decode($this->lastNoteBookIds);
		}
		return [$this->getDefaultNoteBookId()]; // The default notebook id makes sense in this case
	}

	/**
	 * @param array|null $ids
	 */
	public function setLastNoteBookIds(?array $ids = null)
	{
		$this->lastNoteBookIds = JSON::encode($ids);

	}
}
