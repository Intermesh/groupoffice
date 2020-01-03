<?php
namespace go\core\data\convert;

use Exception;
use go\core\db\Query as Query2;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\jmap\EntityController;
use go\core\orm\Entity;
use go\core\orm\Query;
use Traversable;

/**
 * Abstract converter class
 * 
 * Used for converting entities into other formats.
 * 
 * Converters must be put in a "convert" folder / namespace to work with the
 * \go\core\jmap\EntityController::export() function
 * 
 * 
 * @example Client javascript
 * 
 * ```
 * onExport: function () {
 * 		
 * 		var win = window.open("about:blank");
 * 		
 * 		var promise = go.Jmap.request({
 * 			method: "Contact/query",
 * 			params: Ext.apply(this.grid.store.baseParams, this.grid.store.lastOptions.params, {limit: 0, start: 0}),
 * 			callback: function (options, success, response) {
 * 			}
 * 		});
 * 		
 * 		go.Jmap.request({
 * 			method: "Contact/export",
 * 			params: {
 * 				converter: "JSON",
 * 				"#ids": {
 * 					resultOf: promise.callId,
 * 					path: "/ids"
 * 				}
 * 			},
 * 			callback: function (options, success, response) {
 * 				win.location = go.Jmap.downloadUrl(response.blobId);
 * 			}
 * 		});
 * 	}
 * ```
 * 
 * 
 * @see EntityController::export()
 */
abstract class AbstractConverter {
	
	public function __construct() {
		$this->init();
	}
	
	protected function init() {
		
	}
	
	/**
	 * The name of the convertor
	 * 
	 * @return string eg, JSON or CSV
	 */
	public function getName() {
		return array_pop(explode("\\", static::class));
	}
	
	/**
	 * Get the file name extention
	 * 
	 * @return string eg. "csv"
	 */
	abstract public function getFileExtension();

  /**
   * Read file and import them into Group-Office
   *
   * @param File $file the source file
   * @param string $entityClass The entity class model. eg. go\modules\community\addressbook\model\Contact
   * @param array $params Extra import parameters. By default this can only hold 'values' which is a key value array that will be set on each model.
   * @return array ['count', 'errors', 'success']
   * @throws Exception
   */
	public function importFile(File $file, $entityClass, $params = array()) {
		$response = ['count' => 0, 'errors' => [], 'success' => true];		
		
		$fp = $file->open('r');
		
		$index = 0;
		
		while(!feof($fp)) {		
			try {
				
				$entity = new $entityClass;
				if(isset($params['values'])) {
					$entity->setValues($params['values']);
				}

				go()->getDbConnection()->beginTransaction();

				$entity = $this->importEntity($entity, $fp, $index++, $params);
				
				//ignore when false is returned. This is not an error. But intentional. Like CSV skipping a blank line for example.
				if($entity === false) {
					go()->getDbConnection()->rollBack();
					continue;
				}			

				$entity->save();

				if($entity->hasValidationErrors()) {
					go()->getDbConnection()->rollBack();
					$response['errors'][] = "Item ". $index . ": ". var_export($entity->getValidationErrors(), true);				
				} elseif($this->afterSave($entity)) {
					go()->getDbConnection()->commit();
					$response['count']++;
				} else{
					go()->getDbConnection()->rollBack();
					$response['errors'][] = "Item ". $index . ": Import afterSave returned false";				
				}				
			}
			catch(Exception $e) {
				go()->getDbConnection()->rollBack();
				ErrorHandler::logException($e);
				$response['errors'][] = "Item ". $index . ": ".$e->getMessage();
			}
		}
		
		return $response;
	}


	protected function afterSave(Entity $entity) {
		return true;
	}

	/**
	 * Handle's the import. 
	 * 
	 * It must read from the $fp file pointer and return the entity object. The entity is not saved yet.
	 * 
	 * When false is returned the result will be ignored. For example when you want to skip a CSV line because it's empty.
	 * 
	 * @param Entity $entity
	 * @param resource $fp
	 * @param int $index
	 * @param array $params
	 * @return Entity|false
	 */
	abstract protected function importEntity(Entity $entity, $fp, $index, array $params);
	
	abstract protected function exportEntity(Entity $entity, $fp, $index, $total);
		
	
	protected function internalExport($fp, $entities, $total) {
		$i = 0;
		foreach($entities as $entity) {
			$this->exportEntity($entity, $fp, $i, $total);
			$i++;
		}
  }

  /**
   * @var Query
   */
  private $entitiesQuery;

  /**
   * @return Query
   */
	protected function getEntitiesQuery(){
	  return $this->entitiesQuery;
  }

  /**
   * Export entities to a blob
   *
   * @param $name
   * @param Query|array $entities
   * @return Blob
   * @throws Exception
   */
	public final function exportToBlob($name, Query $entities) {		
		$tempFile = File::tempFile($this->getFileExtension());
		$fp = $tempFile->open('w+');

		$this->entitiesQuery = $entities;
		
		$total = $entities->getIterator()->rowCount();
		
		$this->internalExport($fp, $entities, $total);
		
		fclose($fp);
		
		$blob = Blob::fromTmp($tempFile);
		$blob->name = $name."-" . date('Y-m-d-H:i:s') . '.'. $this->getFileExtension();
		if(!$blob->save()) {
			throw new Exception("Couldn't save blob: " . var_export($blob->getValidationErrors(), true));
		}
		
		return $blob;
	}
}