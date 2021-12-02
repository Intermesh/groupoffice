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


	protected function exportEntity(Entity $entity): bool
	{
				
		if($this->index == 0) {
			fputs($this->fp, "[\n");
		}
		$properties = $entity->toArray();
		$string = GoJSON::encode($properties, JSON_PRETTY_PRINT);
		fputs($this->fp, $string);

		if($this->index == $this->getEntitiesQuery()->getIterator()->rowCount() - 1) {
		  fputs($this->fp, "\n]\n");
		} else
		{
			fputs($this->fp, "\n,\n");
		}
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

	protected function initImport(File $file)
	{
		$this->data = GoJSON::decode($file->getContents(), true);
	}

	protected function nextImportRecord(): bool
	{
		$this->record =  array_shift($this->data);
		unset($this->record['id']);
		return $this->record != false;
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

	protected function finishImport()
	{
		//nothing todo
	}

	protected function initExport()
	{
		$this->tempFile = File::tempFile($this->getFileExtension());
		$this->fp = $this->tempFile->open('w+');
	}

	protected function finishExport(): Blob
	{
		$cls = $this->entityClass;
		$blob = Blob::fromTmp($this->tempFile);
		$blob->name = $cls::entityType()->getName() . "-" . date('Y-m-d-H:i:s') . '.'. $this->getFileExtension();
		if(!$blob->save()) {
			throw new Exception("Couldn't save blob: " . var_export($blob->getValidationErrors(), true));
		}

		return $blob;
	}

}
