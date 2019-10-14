<?php


namespace GO\Customfields;

use GO;

class CustomfieldsModule extends \GO\Base\Module {

	public function autoInstall() {
		return false; //is installed as core module!
	}

	public static function getCustomfieldTypes($extendModel=false) {

		$types = array();

		$modules = \GO::modules()->getAllModules();
		
		//hack for refactoring module
		$cfModule = new \stdClass();
		$cfModule->moduleManager = new CustomfieldsModule();
		$modules[] = $cfModule;

		while ($module = array_shift($modules)) {
			if ($module->moduleManager instanceof \GO\Base\Module) {
				
				
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
			
			if($moduleObject->package) {
				//module is refactored
				continue;
			}
			
			$file = $moduleObject->path . ucfirst($moduleObject->name) . 'Module.php';
			//todo load listeners
			if (file_exists($file)) {
//		require_once($file);
				$class = 'GO\\' . ucfirst($moduleObject->name). '\\' . ucfirst($moduleObject->name) . 'Module';

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
	
	public static function getExtendableModels() {
		$m = [];
		foreach(self::getCustomfieldModels() as $cfModel) {
			$cls = $cfModel->getName();
			$m[] = (new $cls)->extendsModel();
		}
		
		
		//for new framework
		
		$cf = new \go\core\util\ClassFinder();
		$m = array_merge($m, $cf->findByTrait(\go\core\orm\CustomFieldsTrait::class));
		return $m;
		
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
					'extendsModel' => $targetModelName
			));
		}

		$sql = "INSERT INTO `".$cfTargetModel->tableName()."` SELECT * FROM `".$cfSourceModel->tableName()."`";
		GO::getDbConnection()->query($sql);
	}

}
