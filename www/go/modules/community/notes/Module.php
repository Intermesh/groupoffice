<?php
namespace go\modules\community\notes;

use Faker\Generator;
use go\core;
use go\core\model\User;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\modules\community\notes\model\Note;
use go\modules\community\notes\model\NoteBook;
use go\modules\community\notes\model\UserSettings;

class Module extends core\Module {	

	public function getAuthor() {
		return "Intermesh BV";
	}

	public function autoInstall()
	{
		return true;
	}

	
	protected function afterInstall(ModuleModel $model) {	
		
		$noteBook = new NoteBook();
		$noteBook->name = go()->t("Shared");
		$noteBook->setAcl([
			Group::ID_INTERNAL => Acl::LEVEL_DELETE
		]);
		$noteBook->save();

		if(!$model->findAcl()
						->addGroup(Group::ID_INTERNAL)
						->save()) {
			return false;
		}

		
		return parent::afterInstall($model);
	}
	

	
	
	public function defineListeners() {
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
		User::on(User::EVENT_BEFORE_DELETE, static::class, 'onUserDelete');
		User::on(User::EVENT_BEFORE_SAVE, static::class, 'onUserBeforeSave');
	}
	
	public static function onMap(Mapping $mapping) {
		$mapping->addHasOne('notesSettings', UserSettings::class, ['id' => 'userId'], true);
	}

	public static function onUserDelete(core\db\Query $query) {
		NoteBook::delete(['createdBy' => $query]);
	}

	public static function onUserBeforeSave(User $user)
	{
		if (!$user->isNew() && $user->isModified('displayName')) {
			$oldName = $user->getOldValue('displayName');
			$nb = NoteBook::find()->where(['createdBy' => $user->id, 'name' => $oldName])->single();
			if ($nb) {
				$nb->name = $user->displayName;
				$nb->save();
			}
		}
	}

	public function demo(Generator $faker)
	{
		$noteBooks = NoteBook::find();

		foreach($noteBooks as $noteBook) {
			$count = $faker->numberBetween(3, 20);
			for($i = 0; $i < $count; $i++) {
				echo ".";
				$note = new Note();
				$note->name = $faker->realText(20);
				$note->content = nl2br($faker->realText(1000, 5));
				$note->createdBy = $noteBook->createdBy;
				$note->noteBookId = $noteBook->id;
				$note->createdAt = $faker->dateTimeBetween("-1 years", "now");
				$note->modifiedAt = $faker->dateTimeBetween($note->createdAt, "now");

				if(!$note->save()) {
					throw new core\orm\exception\SaveException($note);
				}

				if(core\model\Module::isInstalled("community", "comments")) {
					\go\modules\community\comments\Module::demoComments($faker, $note);
				}
			}
		}

	}

}
