<?php
namespace go\modules\community\phpbb;

use go\core;
use go\core\model\Module as ModuleModel;
use go\modules\community\phpbb\model\Settings;
use go\core\model\FieldSet;
use go\core\model\Field;
use go\core\model\User;

class Module extends core\Module {	

	public function getAuthor() {
		return "Intermesh BV";
	}
	
	protected function afterInstall(ModuleModel $model) {	

		$field = Field::findByEntity('User')->where(['databaseName' => 'postCount'])->single();

		if(!$field) {

			$fieldSet = FieldSet::findByEntity('User')->where(['name' => 'Forum'])->single();
			if(!$fieldSet) {
				$fieldSet = new FieldSet();		
				$fieldSet->name = "Forum";
				$fieldSet->setEntity("User");
				if(!$fieldSet->save()) {
					throw new \Exception("Could not save fieldset");
				}
			}

			$field = new Field();
			$field->databaseName = 'numberOfPosts';
			$field->name = "Number of posts";
			$field->type = "Number";
			$field->fieldSetId = $fieldSet->id;
			if(!$field->save()) {
				throw new \Exception("Could not save field");
			}

			$field = new Field();
			$field->databaseName = 'isForumUser';
			$field->name = "Is forum user";
			$field->type = "Checkbox";
			$field->fieldSetId = $fieldSet->id;
			if(!$field->save()) {
				throw new \Exception("Could not save field");
			}
			
		}

		$cron = new \go\core\model\CronJobSchedule();
		$cron->moduleId = $model->id;
		$cron->name = "StatsCron";
		$cron->expression = "0 0 * * *";
		$cron->description = "Gets forum posts";
		
		if(!$cron->save()) {
			throw new \Exception("Failed to save cron job: " . var_export($cron->getValidationErrors(), true));
		}
		
		return parent::afterInstall($model);
	}
	
	public function getSettings() {
		return Settings::get();
	}
	
	
}
