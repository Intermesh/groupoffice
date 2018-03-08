<?php


namespace GO\Customfields;

use GO;

class CustomfieldsModule extends \GO\Base\Module {

	public function autoInstall() {
		return true;
	}

	public static function getCustomfieldTypes($extendModel=false) {

		$types = array();

		$modules = \GO::modules()->getAllModules();

		while ($module = array_shift($modules)) {
			if ($module->moduleManager) {
				$classes = $module->moduleManager->findClasses('customfieldtype');

				foreach ($classes as $class) {

					if ($class->isSubclassOf('GO\Customfields\Customfieldtype\AbstractCustomfieldtype') && $class->getName() != 'GO\Customfields\Customfieldtype\TreeselectSlave') {

						$className = $class->getName();
						$t = new $className;
						
						if(!empty($extendModel)){
							
							$supportedModels = $t->supportedModels();
							
							if(empty($supportedModels) || in_array($extendModel, $supportedModels))					
								$types[] = array('className' => $className, 'type' => $t->name(), 'hasLength' => $t->hasLength());
						
						} else {
							$types[] = array('className' => $className, 'type' => $t->name(), 'hasLength' => $t->hasLength());
						}
					}
				}
			}
		}
		return $types;
	}

	/**
	 * 
	 * @return \GO\Base\Util\ReflectionClass[]
	 */
	public static function getCustomfieldModels() {
		
		$cfModels=array();
		$moduleObjects = \GO::modules()->getAllModules();
		foreach ($moduleObjects as $moduleObject) {
			$file = $moduleObject->path . ucfirst($moduleObject->id) . 'Module.php';
			//todo load listeners
			if (file_exists($file)) {
//		require_once($file);
				$class = 'GO\\' . ucfirst($moduleObject->id) . '\\' . ucfirst($moduleObject->id) . 'Module';

				$object = new $class;
				$models = $object->findClasses("customfields/model");

				foreach ($models as $customFieldModel) {

					if ($customFieldModel->isSubclassOf('GO\Customfields\Model\AbstractCustomFieldsRecord')) {
						$cfModels[]=$customFieldModel;
					}
				}
			}
		}
		
		return $cfModels;
	}
	
	
	
	
	public static function replaceRecords($sourceModelName, $targetModelName){
		
		//delete existing
		$stmt = \GO\Customfields\Model\Category::model()->findByModel($targetModelName);
		$stmt->callOnEach('delete');


		$cfTargetModel = GO::getModel(GO::getModel($targetModelName)->customfieldsModel());
		$cfSourceModel = GO::getModel(GO::getModel($sourceModelName)->customfieldsModel());


		$sql = "DROP TABLE IF EXISTS `".$cfTargetModel->tableName()."`;";
		GO::getDbConnection()->query($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `".$cfTargetModel->tableName()."` (
			`model_id` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`model_id`)
		) ENGINE=InnoDB;";

		GO::getDbConnection()->query($sql);


		$stmt = \GO\Customfields\Model\Category::model()->findByModel($sourceModelName);

		foreach ($stmt as $category) {
			$category->duplicate(array(
					'extends_model' => $targetModelName
			));
		}

		$sql = "INSERT INTO `".$cfTargetModel->tableName()."` SELECT * FROM `".$cfSourceModel->tableName()."`";
		GO::getDbConnection()->query($sql);
	}

}