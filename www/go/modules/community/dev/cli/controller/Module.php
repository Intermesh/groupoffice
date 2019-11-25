<?php

namespace go\modules\community\dev\cli\controller;

use Exception;
use GO;
use go\core\Controller;
use go\core\db\Column;
use go\core\fs\Folder;
use go\core\util\StringUtil;
use PDO;

class Module extends Controller {


	private function columnToPhpType(Column $column) {
		switch ($column->dbType) {
			case 'int':
			case 'tinyint':
			case 'bigint':
				if ($column->length === 1) {
					//Boolean fields in mysql are listed at tinyint(1);
					return "bool";
				} else {
					// Use floatval because of ints greater then 32 bit? Problem with floatval that ints will set as modified attribute when saving.
					return "int";
				}

			case 'float':
			case 'double':
			case 'decimal':
				return "double";

			case 'date':
			case 'datetime':
				return "\go\core\util\DateTime";

			default:
				return "string";
		}
	}

	private function getDefaultValue($column) {
		if (!isset($column->default)) {
			return "";
		}

		return " = " . var_export($column->default, true);
	}

	private function createModuleFile($folder, $namespace) {
		$moduleFile = $folder->getFile('Module.php');
		if (!$moduleFile->exists()) {


			$year = date('Y');

			$data = <<<EOD
<?php
namespace $namespace;
							
use go\core;
							
/**						
 * @copyright (c) $year, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Module extends core\Module {
							
	public function getAuthor() {
		return "Intermesh BV <info@intermesh.nl>";
	}
							
}
EOD;

			$moduleFile->putContents($data);
		}
	}

	/**
	 * mschering@mschering-UX31A:/var/www/groupoffice-server/GO/Modules/GroupOffice/Tasks$ ../../../../bin/groupoffice devtools/module/init --tablePrefix=tasks
	 * 
	 * @param type $tablePrefix
	 */
	public function init($package, $name, $tablePrefix = null) {

//		$className = \GO\Modules\GroupOffice\DevTools\Model\RecordTest::class;
//		$className = \GO\Core\Users\Model\User::class;
//		$this->convertClass($className);
//		
//		exit();
	
		$folder = \go\core\Environment::get()->getInstallFolder()->getFolder('go/modules/' . $package .'/' .$name);		
		$folder->create();
		

		$folder->getFolder('model')->create();
		$folder->getFolder('controller')->create();
		$folder->getFolder('language')->create();
		$folder->getFolder('install')->create();
		$folder->getFile('install/install.sql')->touch();
		$folder->getFile('install/uninstall.sql')->touch();
		
		
		$updatesFile = $folder->getFile('install/updates.php');
		if(!$updatesFile->exists()) {			
			$updatesFile->putContents("<?php\n\n\$updates = [];\n\n");			
		}
		
		
		$this->initView($folder, $package, $name);
		
		if(!isset($tablePrefix)) {
			$tablePrefix = $folder->getName();
		}

		$namespace = "go\\modules\\" . $package . "\\" .$name;	

		$this->createModuleFile($folder, $namespace);

		$result = go()->getDbConnection()->query("SHOW TABLES");

		while ($record = $result->fetch(PDO::FETCH_NUM)) {			
			if (strpos($record[0], $tablePrefix . '_') === 0) {
				$this->tableToModel($folder, $namespace, $tablePrefix, $record[0]);
			}
		}
		
		echo "Done\n";
	}
	
	private function createFile(\go\core\fs\File $file, $text) {
		if(!$file->exists()) {
			$file->putContents($text);
		}
	}
	
	private function initView(Folder $folder, $package, $module) {
		$folder->getFolder('views/extjs3')->create();
		$folder->getFile('views/extjs3/themes/default/style.css')->touch(true);
		
		$this->createFile($folder->getFile('views/extjs3/scripts.txt'), "Module.js\nMainPanel.js\n");
		$moduleUCFirst = ucfirst($module);
		$moduleJS = <<<EOD
go.Modules.register("$package", "$module", {
	mainPanel: "go.modules.$package.$module.MainPanel",
	title: t("$moduleUCFirst"),
	entities: [],
	initModule: function () {}
});

EOD;
		$this->createFile($folder->getFile('views/extjs3/Module.js'), $moduleJS);
		
		$mainPanelJS = <<<EOD
go.modules.$package.$module.MainPanel = Ext.extend(go.modules.ModulePanel, {
	html: "Hello world"
});

EOD;
		
		$this->createFile($folder->getFile('views/extjs3/MainPanel.js'), $mainPanelJS);
		
		
	}


	private function tableToController($namespace, $modelName, Folder $folder) {

		$file = $folder->getFolder('controller')->getFile($modelName . '.php');


		if (!$file->exists()) {
			
			echo "Generating controller/$modelName.php\n";

			$replacements = [
					'namespace' => $namespace,
					'model' => $modelName
			];

			$controllerTpl = file_get_contents(__DIR__ . '/../../Controller.tpl');

			foreach ($replacements as $key => $value) {
				$controllerTpl = str_replace('{' . $key . '}', $value, $controllerTpl);
			}
			$file->putContents($controllerTpl);
		}
	}

	private function tableToModel(Folder $folder, $namespace, $tablePrefix, $tableName) {

		$modelName = StringUtil::upperCamelCasify(str_replace($tablePrefix . '_', '', $tableName));
		$className = $namespace . '\\model\\' . $modelName;
		$tableAlias = strtolower($modelName);

		$file = $folder->getFolder('model')->getFile($modelName . '.php');

		if (!$file->exists()) {
			echo "Generating model/$modelName.php\n";
			
			$year = date('Y');

			$data = <<<EOD
<?php
namespace $namespace\model;
						
use go\core\orm\Property;
						
/**
 * $modelName model
 *
 * @copyright (c) $year, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class $modelName extends Property {
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("$tableName", "$tableAlias");
	}

}
EOD;
			$file->putContents($data);
		} else if(is_a($className, \go\core\orm\Entity::class, true))
		{
			$this->tableToController($namespace, $modelName, $folder);
		}


		
		$this->convertClass($className, $file);
	}

	protected function convertClass($className, $file) {

		$columns = [];
		
		foreach($className::getMapping()->getTables() as $table) {
			$columns = array_merge($columns, $table->getColumns());
		}

		/* @var $columns Column */


		$source = $file->getContents();

		$vars = '';

		foreach ($columns as $column) {

			//check if property is already defined
			if (preg_match('/(protected|public)\s+\$' . preg_quote($column->name, '/') . '[;\s]/', $source)) {

//				echo $column->name . " found\n";
				continue;
			}

			$vars .= <<<EOD
	/** @var {$this->columnToPhpType($column)} {$column->comment} */							
	public \${$column->name}{$this->getDefaultValue($column)};


EOD;
		}
		
		if(empty($vars)) {
			return;
		}
		
		echo "Updating ".$className." with new properties\n";

		//find position to insert properties
		preg_match('/class .*\{\s*\n/', $source, $matches, PREG_OFFSET_CAPTURE);
		$pos = $matches[0][1] + strlen($matches[0][0]);

		$source = substr($source, 0, $pos) . $vars . substr($source, $pos);

		$file->putContents($source);
	}

}

