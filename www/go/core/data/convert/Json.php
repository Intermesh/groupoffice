<?php
namespace go\core\data\convert;

use go\core\fs\Blob;
use go\core\fs\File;
use go\core\orm\Entity;
use go\core\util\JSON as GoJSON;

class Json extends AbstractConverter {	

	protected $fp;
	/**
	 * @var File
	 */
	protected $tempFile;

	protected $data;
	protected $record;

	/**
	 * Number of entities written to the export. Entities that failed to export are skipped.
	 */
	private int $written = 0;

	/**
	 * The properties of an entity in the export. Override to leave out properties that only make sense on this system or
	 * to add related data.
	 */
	protected function entityToArray(Entity $entity): array
	{
		return $entity->toArray();
	}

	protected function exportEntity(Entity $entity): void
	{
		$string = GoJSON::encode($this->entityToArray($entity), JSON_PRETTY_PRINT);
		if($this->written > 0) {
			fputs($this->fp, "\n,\n");
		}
		fputs($this->fp, $string);
		$this->written++;
	}

	public function getFileExtension(): string
	{
		return 'json';
	}


	/**
	 * @inheritDoc
	 */
	public static function supportedExtensions(): array
	{
		return ['json'];
	}

	protected function initImport(File $file): void
	{
		$this->data = GoJSON::decode($file->getContents(), true);
		if(!is_array($this->data) || !array_is_list($this->data)) {
			throw new \InvalidArgumentException("Invalid JSON import file. It must contain an array of entities.");
		}
		$this->total = count($this->data);
	}

	/**
	 * The id and ACL are not imported. The file may come from another installation where the id's and groups in the
	 * ACL refer to something else. Imported entities get a new id and the default ACL owned by the importing user. An
	 * ACL can be set for all records with the 'values' import parameter.
	 */
	protected function nextImportRecord(): bool
	{
		if(empty($this->data)) {
			return false;
		}
		$this->record = array_shift($this->data);
		// An item that is not an object fails in importEntity() and is reported as an error
		if(is_array($this->record)) {
			unset($this->record['id'], $this->record['acl']);
		}
		return true;
	}

	protected function importEntity()
	{
		$cls = $this->entityClass;

		$e = new $cls;
		$e->setValues($this->record);
		if(isset($this->clientParams['values'])) {
			$e->setValues($this->clientParams['values']);
		}

		return $e;
	}

	protected function finishImport(): void
	{
		//nothing todo
	}

	protected function initExport(): void
	{
		$this->tempFile = File::tempFile($this->getFileExtension());
		$this->fp = $this->tempFile->open('w+');
		$this->written = 0;
		fputs($this->fp, "[\n");
	}

	protected function finishExport(): Blob
	{
		fputs($this->fp, "\n]\n");
		fclose($this->fp);

		$cls = $this->entityClass;
		$blob = Blob::fromTmp($this->tempFile);
		$blob->name = $cls::entityType()->getName() . "-" . date('Y-m-d-H:i:s') . '.'. $this->getFileExtension();
		if(!$blob->save()) {
			throw new \Exception("Couldn't save blob: " . var_export($blob->getValidationErrors(), true));
		}

		return $blob;
	}

}
