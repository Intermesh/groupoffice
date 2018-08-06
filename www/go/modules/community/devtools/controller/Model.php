<?php

namespace go\modules\community\devtools\controller;

class Model extends \go\core\cli\Controller {
	
	public function test() {
		echo "Hello\n";
	}

	/**
	 * /usr/local/bin/docker exec --user www-data go_web php /usr/local/share/groupoffice/cli.php community/devtools/model/props --name="go\modules\community\addressbook\model\Contact"
	 * 
	 * @param type $name
	 * @throws \go\core\exception\NotFound
	 */
	public function props($name) {
		
		if(!class_exists($name)) {
			throw new \go\core\exception\NotFound("Model '" . $name ."' not found");
		}
		
		//no cache
		GO()->setCache(new \go\core\cache\None());

		$model = new $name;

		/* @var $model \go\core\orm\Entity */

		$tables = $model->getMapping()->getTables();

		foreach ($tables as $table) {
			
			$columns = $table->getColumns();

			foreach ($columns as $colName => $column) {	
				switch ($column->dbType) {
					case 'double':
					case 'float':
						$type = $column->dbType;
						break;
					case 'int':
					case 'tinyint':
					case 'bigint':
						$type = $column->length == 1 ? 'boolean' : 'int';
						break;
					case 'date':
					case 'datetime':
						$type = '\go\core\util\DateTime';
						break;
					default:
						$type = 'string';
						break;
				}
				
				$comment = !empty($column->comment) ?  $column->comment ."\n\t * \n\t *" : "";

				echo "\t/**\n\t * " .$comment ."@var " . $type . "\n\t */\n\tpublic \$" . $colName . ";\n\n";			
				
			}
		}

		echo "\n *";

		echo "\n * @copyright (c) " . date('Y') . ", Intermesh BV http://www.intermesh.nl" .
		"\n * @author Merijn Schering <mschering@intermesh.nl>" .
		"\n * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3";

		echo "\n */\n\n";
	}

}
