<?php

namespace go\core\data\convert;

use Exception;
use go\core\event\EventEmitterTrait;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\model\Acl;
use go\core\model\Field;
use go\core\orm\Entity;
use go\core\orm\Property;
use go\core\orm\Query;
use go\core\orm\Relation;
use go\core\util\DateTime as GoDateTime;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as PhpSpreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;
use Sabre\VObject\Property\VCard\DateTime;

/**
 * CSV converter.
 * 
 * Imports a CSV file to entities.
 * 
 * A mapping can be supplied to the JMAP controller or importFile() function. {@see importFile()}
 * 
 * The key is the CSV record index and value the 
 * 	property path. "propName" or "prop.name" if it's a relation.
 * 	If the relation is a has many values can be separated with " ::: ".
 * 
 * For example pass to the options:
 * 
 * [
 * 		"mapping" => [
 * 			"firstName",
 * 			"emailAddresses.email"
 * 	]
 * ]
 */
class Spreadsheet extends AbstractConverter {

	use EventEmitterTrait;

	const EVENT_INIT = 0;
	
	/**
	 *
	 * @var array
	 */
	private $headers;
	
	/**
	 * Delimits multiple values in single CSV field
	 * 
	 * @var string
	 */
	public static $multipleDelimiter = ',';

	protected $delimiter = ',';

	protected $enclosure = '"';
	
	/**
	 * List headers to exclude
	 * @var string[]
	 */
	public static $excludeHeaders = [];

	/**
	 * Can be set to 'id' or an arbitrary value that an extended version understands with an override for {@see createEntity()}
	 *
	 * @var string
	 */
	public $updateBy = null;

	protected $fp;
	/**
	 * @var File
	 */
	protected $tempFile;

	/**
	 * @var PhpSpreadsheet
	 */
	protected $spreadsheet;

	/**
	 * Row index while exporting
	 * @var int
	 */
	protected $spreadSheetIndex = 1;
	/**
	 * @var RowIterator
	 */
	protected $spreadsheetRowIterator;

	/**
	 * @inheritDoc
	 */
	public static function supportedExtensions()
	{
		return ['csv', 'xlsx'];
	}

	protected function init()
	{
		parent::init();

		$user = go()->getAuthState()->getUser(['listSeparator', 'textSeparator']);
		$this->delimiter = $user->listSeparator;
		$this->enclosure = $user->textSeparator;

		//try to set high memory limit as phpoffice likes to eat RAM
		go()->getEnvironment()->setMemoryLimit("2G");


		static::fireEvent(static::EVENT_INIT, $this);
	}

	protected function initExport()
	{
		$this->tempFile = File::tempFile($this->getFileExtension());
		$this->fp = $this->tempFile->open('w+');

		if($this->extension != 'csv') {
			$this->spreadsheet = new PhpSpreadsheet();
			$this->spreadSheetIndex = 1;
		}else{
			//add UTF-8 BOM char for excel to recognize UTF-8 in the CSV
			fputs($this->fp, chr(239) . chr(187) . chr(191));
		}
	}


	protected function arrayToSpreadSheet($index, $array) {
		for($colIndex = 0, $count = count($array);$colIndex < $count; $colIndex++) {
			$v = $array[$colIndex];
			//add 1 to index for headers
			if(is_string($v) && $v[0] == '=') {
				//prevent formula detection
				$this->spreadsheet->getActiveSheet()->setCellValueExplicitByColumnAndRow($colIndex + 1, $index, $v, DataType::TYPE_STRING);
			} else {
				$this->spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($colIndex + 1, $index, $v);
			}
		}
	}

	protected function writeRecord($array) {
		if($this->extension == 'csv') {
			fputcsv($this->fp, $array, $this->delimiter, $this->enclosure);
		} else{
			$this->arrayToSpreadSheet($this->spreadSheetIndex++, $array);
		}
	}

	protected function exportEntity(Entity $entity) {

		if ($this->index == 0) {
			$this->writeRecord(array_column($this->getHeaders($entity), 'name'));
		}

		$headers = $this->getHeaders($entity);
		//set custom fields to text mode
		if(property_exists($entity, "returnAsText")) {
			$entity->returnAsText = true;
		}
		$templateValues = $entity->toArray();

		$record = [];
		foreach($headers as $header) {
			$record[] = $this->getValue($entity, $templateValues, $header['name']);
		}

		$this->writeRecord($record);
	}


	protected function finishExport()
	{
		if($this->extension != 'csv') {

			$headerStyle = [
				'font' => [
					'bold' => true,
					'color' => ['argb' => 'ffffff']
				],
				'fill' => [
					// SOLID FILL
					'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'color' => ['argb' => '0277bd']
				]
			];
			foreach($this->spreadsheet->getActiveSheet()->getColumnIterator() as $col) {
				$style = $this->spreadsheet->getActiveSheet()->getStyle($col->getColumnIndex() . "1");
				$style->applyFromArray($headerStyle);

				$colDim = $this->spreadsheet->getActiveSheet()->getColumnDimension($col->getColumnIndex());
				$colDim->setAutoSize(true);
			}

			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->spreadsheet);
			$writer->setPreCalculateFormulas(false);
			$writer->save($this->tempFile->getPath());

		}

		$cls = $this->entityClass;
		$blob = Blob::fromTmp($this->tempFile);
		$blob->name = $cls::entityType()->getName() . "-" . date('Y-m-d-H:i:s') . '.'. $this->getFileExtension();
		if(!$blob->save()) {
			throw new Exception("Couldn't save blob: " . var_export($blob->getValidationErrors(), true));
		}

		return $blob;
	}
	
	private $customColumns = [];
	
	/**
	 * Add a custom column to the export and import
	 * 
	 * @example
	 * 
	 *	//override init
	 * 	protected function init() {
	 *		$this->addColumn('status', go()->t("Status", 'sony', 'assets'));
	 *	}
	 * 
	 * @param string $name Column name
	 * @param string $label Column label
	 * @param Callable $exportFunction Defaults to "export" . ucfirst($name) The function is called with Entity $entity, array $templateValues $columnName
	 * @param Callable $importFunction Defaults to "import" . ucfirst($name) The import function is called with Entity $entity, $value, array $values
	 */
	public function addColumn($name, $label, $exportFunction = null, $importFunction = null) {
		if(!isset($exportFunction)) {
			$exportFunction = [$this, "export".ucfirst($name)];
		}
		if(!isset($importFunction)) {
			$importFunction = [$this, "import".ucfirst($name)];
		}
		
		$this->customColumns[$name] = [
				'name' => $name, 
				'label' => $label,

				'importFunction' => $importFunction, 
				'exportFunction' => $exportFunction
		];
	}
	
	/**
	 * Get a value for a header
	 * 
	 * @param Entity $entity
	 * @param array $templateValues
	 * @param string $header Header name delimited with a . for sub properties. eg. "emailAddresses.email"
	 * @return string
	 */
	protected function getValue(Entity $entity, $templateValues, $header) {
		
		if(isset($this->customColumns[$header])) {
			return $this->getCustomColumnValue($entity, $templateValues, $header);
		}
				
		$path = explode('.', $header);
		
		while($seg = array_shift($path)) {

			$index = $this->extractIndex($seg);
			if(isset($index)) {
				if(!isset($templateValues[$seg][$index])) {
					return "";
				}else{
					$templateValues = $templateValues[$seg][$index];
					continue;
				}
			}
			
			if(is_array($templateValues)) {
				if(!isset($templateValues[0])) {		
					$templateValues = $templateValues[$seg] ?? "";
				} else
				{
					$a = [];
				
					foreach($templateValues as $i) {
						if(is_array($i)) {
							$a[] = $i[$seg] ?? "";
						} else
						{
							$a[] = $i->$seg ?? "";
						}
					}

					$templateValues = $a;
				}
			}else
			{
				$templateValues = $templateValues->$seg ?? "";
			}
		}
		
		return is_array($templateValues) ? implode(static::$multipleDelimiter, $templateValues) : $templateValues;
	}

	/**
	 * Takes of [1] in emailAddresses[1] and returns 1
	 *
	 * Check if there's a [0] ending to indicate an array index for has many properties
	 * example column header emailAddresses[1].type
	 *
	 * @param $seg
	 * @return int|null
	 */
	private static function extractIndex(&$seg) {
		// Check if there's a [0] ending to indicate an array index for has many properties
		// example column header emailAddresses[1].type
		$index = null;
		if(preg_match("/\[([0-9]+)\]$/", $seg, $matches)) {
			//index starts with 1 in CSV but should start with 0 in code.
			$index = $matches[1] - 1;
			$seg = substr($seg,0, -(strlen($matches[0])));
		}

		return $index;
	}
	
	private function getCustomColumnValue(Entity $entity, $templateValues, $header) {
		return call_user_func($this->customColumns[$header]['exportFunction'], $entity, $templateValues, $header);
	}
	
	private function exportSubFields($record, $v) {
		if(!is_array($v)) {			
			$record[] = $v;
			return $record;
		}
		foreach($v as $key => $subvalue) {
			$record = $this->exportSubFields($record, $subvalue);
		}
		
		return $record;
	}

	public function getEntityMapping() {
		return $this->internalGetHeaders(true);
	}

  /**
   * Get all the CSV field headers
   *
   * Sub properties are delimnited with a . For example "emailAddresses.email".
   *
   * @return array[] [['name' => 'id', 'label' => "ID", 'many' => false], ...]
   * @throws Exception
   */
	public final function getHeaders() {
		
		if(!isset($this->headers)) {
			$this->headers = $this->internalGetHeaders();
		}
		
		return $this->headers;		
	}

	/**
	 * Override this to add custom headers
	 * Override "getValue" and "setVallue" too.
	 *
	 * @param string $entityCls
	 * @param bool $forMapping
	 * @return string[]
	 * @throws Exception
	 */
	protected function internalGetHeaders($forMapping = false) {

		$entityCls = $this->entityClass;

		//Write headers
		$properties = $entityCls::getMapping()->getProperties();

		if($forMapping) {
			$headers = ['id' =>	['name' => 'id', 'label' => "ID", 'many' => false]];
		}else{
			if(!empty($this->clientParams['columns']) && !in_array("id", $this->clientParams['columns'])) {
				$headers = [];
			} else {
				$headers = [
					['name' => 'id', 'label' => "ID", 'many' => false]
				];
			}
		}

		foreach($properties as $name => $value) {
			//Skip system data
			if(in_array($name, array_merge(['createdAt', 'createdBy', 'ownedBy', 'modifiedAt','aclId','filesFolderId', 'modifiedBy'], array_keys($this->customColumns)))){
				continue;
			}

			//client specified which columns to export
			if(!empty($this->clientParams['columns']) && !in_array($name, $this->clientParams['columns'])) {
				continue;
			}

			$headers = $this->addSubHeaders($headers, $name, $value, false, $forMapping);
		}
		if(method_exists($entityCls, 'getCustomFields')) {
			$fields = Field::findByEntity($entityCls::entityType()->getId());
			$customFieldProps = [];
			foreach($fields as $field) {

				if($forMapping) {
					$customFieldProps[$field->databaseName] = ['name' => $field->databaseName, 'label' => $field->name, 'many' => false];
				} else{
					//client specified which columns to export
					if(!empty($this->clientParams['columns']) && !in_array($field->databaseName, $this->clientParams['columns'])) {
						continue;
					}
					$headers[] =  ['name' => 'customFields.' . $field->databaseName, 'label' => $field->name, 'many' => false];
				}
			}

			if($forMapping) {
				$headers["customFields"] = ['name' => 'customFields', 'label' => null, 'many' => false, 'grouped'=>true, 'properties' => $customFieldProps];
			}
		}
		
		if($forMapping) {
			return array_merge($headers, $this->customColumns);
		} else{
			return array_merge($headers, array_filter(array_values($this->customColumns), function($col) {
				//client specified which columns to export
				return empty($this->clientParams['columns']) || in_array($col['name'], $this->clientParams['columns']);
			}));
		}
	}
	
	private function addSubHeaders($headers, $header, $prop, $many = false, $forMapping = false) {
		
		if(in_array($header, static::$excludeHeaders)) {
			return $headers;
		}
		
		if(!($prop instanceof Relation)) {
			if(!$prop->primary) {
				//client will define labels if not given. Only custom fields provide label
				$headers[$header] = ['name' => $header, 'label' => null, 'many' => $many];
			}
			return $headers;
		}

		if($prop->type == Relation::TYPE_SCALAR) {
			$headers[$header] = ['name' => $header, 'label' => null, 'many' => true];
			return $headers;
		}

		$cls = $prop->propertyName;
		$properties = $cls::getMapping()->getProperties();

		//Don't add multiple columns for has many if we need the headers for mapping
		if($forMapping) {

			$props = [];
			foreach ($properties as $name => $value) {

				if (in_array($name, $prop->keys)) {
					continue;
					//don't export relational keys like 'contactId';
				}

				//$subheader = $header . '.' . $name ;
				$props = $this->addSubHeaders($props, $name, $value, false, $forMapping);
			}

			$headers[$header] = ['name' => $header, 'label' => null, 'many' => $prop->type != Relation::TYPE_HAS_ONE, 'properties' => $props, 'grouped' => true];
		} else {

			//create multiple columns for has many like emailAddresses[1].type etc.
			$count = $this->countMaxRelated($prop);
			for ($i = 1; $i <= $count; $i++) {
				foreach ($properties as $name => $value) {

					if (in_array($name, $prop->keys)) {
						continue;
						//don't export relational keys like 'contactId';
					}

					$subheader = $header . '[' . $i . '].' . $name;
					$headers = $this->addSubHeaders($headers, $subheader, $value, false);
				}
			}
		}
		
		return $headers;
	}

	/**
	 * Returns the max count of a has many relation in the dataset to export
	 *
	 * @param Relation $relation
	 * @return bool|int|mixed
	 * @throws Exception
	 */
	private function countMaxRelated(Relation $relation) {
    if($relation->type == Relation::TYPE_HAS_ONE){
      return 1;
    }

    $key = key($relation->keys);
    $fk = current($relation->keys);

    $entitiesSub = clone $this->getEntitiesQuery();
    $entitiesSub->selectSingleValue($entitiesSub->getTableAlias() . '.' . $key);

    return go()->getDbConnection()
      ->selectSingleValue('coalesce(count(*), 0) AS count')
      ->from($relation->propertyName::getMapping()->getPrimaryTable()->getName(), 't')
      ->where($fk, 'IN', $entitiesSub)
      ->groupBy(['t.' . $fk])
      ->orderBy(['count' => 'DESC'])
      ->single();
  }




	
	private function recordIsEmpty(array $record) {
		foreach($record as $v) {
			if(!empty($v)) {
				return false;
			}
		}
		return true;
	}



	protected function initImport(File $file)
	{
		if($this->extension == 'csv') {
			$this->fp = $file->open('r');
			$this->delimiter = static::sniffDelimiter($file);
		} else{
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			$this->spreadsheet = $reader->load($file->getPath());
			$this->spreadsheet->setActiveSheetIndex(0);
			$this->spreadsheetRowIterator = $this->spreadsheet->getActiveSheet()->getRowIterator();
		}

		if(isset($this->clientParams['updateBy'])) {
			$this->updateBy = $this->clientParams['updateBy'];
		}
	}

	protected $record;

	protected function nextImportRecord()
	{
		if($this->index == 0) {
			//skip headers
			$headers = $this->readRecord();
		}
		$this->record = $this->readRecord();

		return $this->record !== false;
	}

	protected function readRecord() {
		if($this->extension != 'csv') {
			/**
			 * @var Row $row
			 */

			if(!$this->spreadsheetRowIterator->valid()) {
				return false;
			}

			$row = $this->spreadsheetRowIterator->current();
			$this->spreadsheetRowIterator->next();
			if(!$row) {
				return false;
			}

			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
			$cells = [];
			foreach ($cellIterator as $cell) {
				if(\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
					$v = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cell->getValue());
				} else{
					$v = $cell->getValue();
				}

				$cells[] = $v;
			}
			return $cells;
		} else{
			return fgetcsv($this->fp, 0, $this->delimiter, $this->enclosure);
		}
	}

	protected function finishImport()
	{
		if($this->extension == 'csv') {
			fclose($this->fp);
		}
	}


	/**
   * @inheritDoc
   */
	protected function importEntity() {

		if(!isset($this->clientParams['mapping'])) {
			throw new Exception("Mapping is required");
		}
		
		if($this->recordIsEmpty($this->record)) {
			return false;
		}

		$values = $this->convertRecordToProperties($this->record, $this->clientParams['mapping'], $this->getEntityMapping($this->entityClass));
		if(!$values) {
			return false;
		}

		$entity = $this->createEntity($values);
		$values = $this->importCustomColumns($entity, $values);
		unset($values['id']);

		if(isset($this->clientParams['values'])) {
			$values = array_merge($values, $this->clientParams['values']);
		}

		$this->setValues($entity, $values);

		return $entity;
	}

	protected function createEntity( $values) {

		$entityClass = $this->entityClass;



		$entity = false;
		//lookup entity by id if given
		if($this->updateBy == 'id' && !empty($values['id'])) {
			$entity = $entityClass::findById($values['id']);
			if($entity && $entity->getPermissionLevel() < Acl::LEVEL_WRITE) {
				$entity = false;
			}
		}
		if(!$entity) {
			$entity = new $entityClass;
		}
		return $entity;
	}
	
	protected function importCustomColumns(Entity $entity, $values){
		foreach($this->customColumns as $c) {
			call_user_func_array( $c['importFunction'], [$entity, $values[$c['name']] ?? null, &$values, $c['name']]);
			unset($values[$c['name']]);
		}
		return $values;
	}
	
	protected function setValues(Entity $entity, array $values) {
		$cf = $values['customFields'] ?? null;
		unset($values['customFields']);
		$entity->setValues($values);
		//Set custom fields as text. for example select fields not by id but with option text.
		if(isset($cf) && method_exists($entity, 'setCustomFields')) {
			$entity->setCustomFields($cf, true);
		}
	}

	/**
	 * Will convert the CSV record to a key value array to use in Entity::setValues();
	 *
	 * @param $record
	 * @param array $clientMapping
	 *
	 * eg.
	 * debtorNumber: {csvIndex: 14, fixed: ""},
	 * emailAddresses: Array (2)
	 * 0 {type: {csvIndex: 25, fixed: ""}, email: {csvIndex: 26, fixed: ""}}
	 * 1 {type: {csvIndex: 27, fixed: ""}, email: {csvIndex: 28, fixed: ""}}
	 *
	 * Array Prototype
	 *
	 * @param $serverMapping
	 * @return array|false
	 * @throws Exception
	 */
	private function convertRecordToProperties($record, $clientMapping, $serverMapping) {

		$v = [];
		$hasCsvData = false;

		foreach($clientMapping as $propName => $map) {

			//empty array for example emailAddresses
			if(empty($map)) {
				continue;
			}

			if(!is_array($map)) {
				throw new Exception("Invalid map: " .var_export($map, true));
			}

			$c = $serverMapping[$propName] ?? [];

			if (!isset($map['csvIndex'])) {
				//has many relations have numeric indexes. Has one is an object with key value

				if(isset($map[0])) {
					$v[$propName] = [];

					foreach ($map as $sub) {
						$item = $this->convertRecordToProperties($record, $sub, $c['properties'] ?? []);
						if($item) {
							$v[$propName][] = $item;
						}
					}
				} else{
					$item = $this->convertRecordToProperties($record, $map, $c['properties'] ?? []);
					if($item) {
						$v[$propName] = $item;
						$hasCsvData = true;
					}
				}
			}else {

				switch ($map['csvIndex']) {
					case -2:
						continue 2;

					case -1:
						$v[$propName] = $map['fixed'];
						break;
					default:
						if (empty($record[$map['csvIndex']])) {
							continue 2;
						}

						//track if data was set from CSV. We will remove objects without CSV data
						$hasCsvData = true;

						$v[$propName] = $record[$map['csvIndex']];
						break;
				}

				if(!empty($c['many'])) {
					$v[$propName] = explode(static::$multipleDelimiter, $v[$propName]);
				}
			}
		}

		if(!$hasCsvData) {
			return false;
		}

		return $v;
	}

	public static function sniffDelimiter(File $file) {
		$fp = $file->open('r');

		$delimiter = ',';
		$enclosure = '"';

		$headers = fgetcsv($fp, 0, $delimiter, $enclosure);
		
		if(!$headers || count($headers) == 1) {
			$delimiter = ';' ;

			$headers = fgetcsv($fp, 0, $delimiter, $enclosure);
			fclose($fp);
			if(!$headers || count($headers) == 1) {
				throw new Exception("Unable to detect delimiter");
			}
		} else{
			fclose($fp);
		}

		return $delimiter;		
	}
	
	/**
	 * Get headers from CSV
	 * 
	 * @param File $file
	 * @return string[]
	 * @throws Exception
	 */
	public function getCsvHeaders(File $file) {

		$this->initImport($file);
		$headers = $this->readRecord();

		if(!$headers) {
			throw new Exception("Could not read CSV file");
		}
		
		return $headers;
	}


}
