<?php
namespace go\core\data\convert;

use go\core\orm\Entity;

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
 * 		var callId = go.Jmap.request({
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
 * 					resultOf: callId,
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
 * @see \go\core\jmap\EntityController::export()
 */
abstract class AbstractConverter {
	
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
	 * Convert an entity into another format
	 * 
	 * @param Entity $entity
	 * @return string 
	 */
	abstract public function export(Entity $entity);
	
	/**
	 * Convert input data to an entity
	 * 
	 * @return Entity
	 */
	abstract public function import($data, Entity $entity = null);
	
	/**
	 * Read file and import them into Group-Office
	 * 
	 * @return int[] id's of imported entities
	 */
	abstract public function importFile(\go\core\fs\File $file, $values = []);
	
	
	protected function exportEntityToBlob(Entity $entity, $fp, $index, $total) {
		$str = $this->export($entity);
		fputs($fp, $str);
	}
	
	/**
	 * Export entities to a blob
	 * 
	 * @param \go\core\db\Query $entities
	 * @return \go\core\fs\Blob
	 * @throws \Exception
	 */
	public function exportToBlob(\go\core\orm\Query $entities) {		
		$tempFile = \go\core\fs\File::tempFile($this->getFileExtension());
		$fp = $tempFile->open('w+');
		
		$total = $entities->getIterator()->rowCount();
		
		$i = 0;
		foreach($entities as $entity) {
			$this->exportEntityToBlob($entity, $fp, $i, $total);
			$i++;
		}
		
		fclose($fp);
		
		$blob = \go\core\fs\Blob::fromTmp($tempFile);
		$blob->name = "Export-" . date('Y-m-d-H:i:s') . '.'. $this->getFileExtension();
		if(!$blob->save()) {
			throw new \Exception("Couldn't save blob: " . var_export($blob->getValidationErrors(), true));
		}
		
		return $blob;
	}
}