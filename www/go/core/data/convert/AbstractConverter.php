<?php
namespace go\core\data\convert;

use Exception;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\jmap\EntityController;
use go\core\model\Acl;
use go\core\model\Alert;
use go\core\orm\Entity;
use go\core\orm\EntityType;
use go\core\orm\exception\SaveException;
use go\core\orm\Query;
use go\core\util\DateTime;
use go\modules\business\support\Module;

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




	/**
	 * The index number of the import
	 *
	 * @var int
	 */
	protected $index;

	/**
	 * Extra parameters sent by the client for importing.
	 *
	 * Typically, a 'values' property is sent which you can apply to each entity in {@see importEntity()}:
	 *
	 * ```
	 * if(isset($this->clientParams['values'])) {
	 *  $entity->setValues($this->clientParams['values']);
	 * }
	 * ```
	 *
	 * @var array
	 */
	protected $clientParams;

	/**
	 * The class name of the entity we're importing
	 * @var class-string<Entity>
	 */
	protected $entityClass;

	/**
	 * The extension provided by the client.
	 *
	 * @var string
	 */
	protected $extension;

	protected Alert $alert;

	/**
	 * AbstractConverter constructor.
	 * @param string $extension eg. "csv"
	 * @param string $entityClass The entity class model. eg. go\modules\community\addressbook\model\Contact
	 */
	public function __construct(string $extension, string $entityClass) {
		$this->extension = strtolower($extension);
		$this->entityClass = $entityClass;
		$this->init();
	}
	
	protected function init() {
		
	}

	/**
	 * Return list of supported file extensions in lower case!
	 * eg. ['csv'];
	 *
	 * @return string[]
	 */
	abstract public static function supportedExtensions(): array;

	/**
	 * Check if this converter supports the given extension
	 * @param string $extension eg. "csv"
	 * @return bool
	 */
	public static function supportsExtension(string $extension): bool
	{
		return in_array(strtolower($extension), static::supportedExtensions());
	}
	
	/**
	 * The name of the convertor
	 * 
	 * @return string eg, JSON or CSV
	 */
	public function getName(): string
	{
		$classParts = explode("\\", static::class);
		return array_pop($classParts);
	}
	
	/**
	 * Get the file name extention
	 * 
	 * @return string eg. "csv"
	 */
	public function getFileExtension(): string
	{
		return $this->extension;
	}

	private function notifyStart() {
		$this->alert = new Alert();

		$cls = $this->entityClass;

		$module = \go\core\model\Module::findByClass($this->entityClass, ['id', 'name', 'package']);

		$this->alert->setEntity($module);
		$this->alert->userId = go()->getUserId();
		$this->alert->triggerAt = new DateTime();
		$this->alert->setData([
				'title' => go()->t("Importing"),
				'body' => go()->t("The import has started in the background")
			]
		);

		if (!$this->alert->save()) {
			throw new SaveException($this->alert);
		}
	}

	private function notifyEnd(int $count, int $errorCount) {
		$this->alert->setData([
				'title' => go()->t("Import finished"),
				'body' => go()->t("Imported") . ": ". $count ."\n". go()->t("Errors"). ": ".$errorCount
			]
		);
		if (!$this->alert->save()) {
			throw new SaveException($this->alert);
		}
	}

	private function notifyCount(int $count, int $errorCount) {
		$this->alert->setData([
				'title' => go()->t("Import in progress"),
				'body' => go()->t("Imported") . ": ". $count ."\n". go()->t("Errors"). ": ".$errorCount
			]
		);
		if (!$this->alert->save()) {
			throw new SaveException($this->alert);
		}
	}

	private function notifyError(string $error) {
		$this->alert = new Alert();


		$module = \go\core\model\Module::findByClass($this->entityClass, ['id', 'name', 'package']);

		$this->alert->setEntity($module);
		$this->alert->userId = go()->getUserId();
		$this->alert->triggerAt = new DateTime();
		$this->alert->setData([
				'title' => go()->t("Import error"),
				'body' => $error
			]
		);

		if (!$this->alert->save()) {
			throw new SaveException($this->alert);
		}
	}


  /**
   * Read file and import them into Group-Office
   *
   * The flow:
   *
   * {@see initImport()}
   *
   * For each record:
   *
   * {@see nextImportRecord()}
   * {@see importEntity()}
   * {@see afterSave()}
   *
   * And finally
   *
   * {@see finishImport()}
   *
   * @param File $file the source file
   * @param array $params Extra import parameters. By default this can only hold 'values' which is a key value array that will be set on each model.
   * @return array ['count', 'errors', 'success']
   * @throws Exception
   */
	public final function importFile(File $file, array $params = array()): array
	{
		$response = ['count' => 0, 'errors' => [], 'success' => true];

		$this->clientParams = $params;

		$this->initImport($file);

		$this->notifyStart();

		$this->index = 0;
		
		while($this->nextImportRecord()) {

			try {
				echo $this->index ."\n";

				$entity = $this->importEntity();
				
				//ignore when false is returned. This is not an error. But intentional. Like CSV skipping a blank line for example.
				if (is_null($entity)) {
					$this->index++;
					continue;
				}

				if($entity->hasPermissionLevel(Acl::LEVEL_CREATE)) {
					$entity->save();
				} else {
					$msg = "Item ". $this->index . ": access denied";
					$this->notifyError($msg);

					$response['errors'][] = $msg;
					continue;
				}

				//push changes after each 100 imports
				EntityType::push(100);

				if($entity->hasValidationErrors()) {
					$msg = "Item ". $this->index . ": ". var_export($entity->getValidationErrors(), true);
					$this->notifyError($msg);

					$response['errors'][] = $msg;
				} elseif($this->afterSave($entity)) {
					$response['count']++;
				} else{
					$msg = "Item ". $this->index . ": Import afterSave returned false";
					$response['errors'][] = $msg;

					$this->notifyError($msg);
				}

				EntityType::push();

				$this->notifyCount($response['count'], count($response['errors']));
			}
			catch(Exception $e) {
				ErrorHandler::logException($e);
				$response['errors'][] = "Item ". $this->index . ": ".$e->getMessage();
			}

			$this->index++;
		}

		$this->finishImport();

		$this->notifyEnd($response['count'], count($response['errors']));
		
		return $response;
	}

	/**
	 * Setup file reader
	 *
	 * @param File $file
	 */
	abstract protected function initImport(File $file): void;

	/**
	 * Reads next record from file. Returns true on success or false when done.
	 *
	 * This method must store the record inside this convertor so that {@see importEntity()} can use
	 * ot for importing.
	 *
	 * @return bool
	 */
	abstract protected function nextImportRecord(): bool;

	/**
	 * Import's a single entity
	 * 
	 * It uses the data stored in {@see nextImportRecord()} to create an entity and stores it.
	 * 
	 * When null is returned the result will be ignored. For example when you want to skip a CSV line because it's empty.
	 *
	 * @return ?Entity
	 */
	abstract protected function importEntity();


	/**
	 * Called after the entity has been imported.
	 *
	 * When you return false the import of the single entity will be rolled back.
	 *
	 * @param Entity $entity
	 * @return bool
	 */
	protected function afterSave(Entity $entity): bool
	{
		return true;
	}


	/**
	 * This method is called after all entities have been imported. Useful to clean things up.
	 *
	 * @return void
	 */
	protected function finishImport(): void
	{

	}


	/** start of export */


	/**
	 * Export entities to a blob
	 *
	 * @param Query|array $entities
	 * @param array $params
	 * @return Blob
	 */
	public final function exportToBlob(Query $entities, array $params = []): Blob
	{
		$this->clientParams = $params;
		$this->entitiesQuery = $entities;
		$this->initExport();
		//	$total = $entities->getIterator()->rowCount();

		$this->index = 0;
		foreach($entities as $entity) {
			$this->exportEntity($entity);
			$this->index++;
		}

		return $this->finishExport();

	}

  /**
   * @var Query
   */
  private $entitiesQuery;

  /**
   * The query used for exporting entities
   *
   * @return Query
   */
	protected function getEntitiesQuery(): Query
	{
	  return $this->entitiesQuery;
  }

	/**
	 * Initialize the import. For example create temporary file and open it.
	 *
	 * @return void
	 */
	abstract protected function initExport(): void;



	/**
	 * Export the given entity
	 *
	 * @param Entity $entity
	 * @return void
	 */
	abstract protected function exportEntity(Entity $entity): void;

	/**
	 * Finish the export retuning a Blob with the data
	 *
	 * @return Blob
	 */
	abstract protected function finishExport(): Blob;

}