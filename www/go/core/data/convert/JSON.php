<?php
namespace go\core\data\convert;

use go\core\orm\Entity;

class JSON extends AbstractConverter {	
	
	private $entityCls;
	
	public function setEntityClass($cls) {
		$this->entityCls = $cls;
	}
	
	public function import($data, Entity $entity = null) {
		$props = json_decode($data, true);
		$cls = $this->entityCls;
		
		$e = new $cls;
		$e->setValues($cls);
		
		return $e;
	}

	public function export(Entity $entity) {
		$properties = $entity->toArray();
		return json_encode($properties, JSON_PRETTY_PRINT);
	}

	public function importFile(\go\core\fs\File $file, $values = array()){
		
	}
	
	protected function exportEntityToBlob(Entity $entity, $fp, $index, $total) {
		if($index == 0) {
			$str = "[\n";
		}
		else
		{
			$str = "";
		}
		$str .= parent::exportEntityToBlob($entity, $fp, $index, $total);
		
		if($index == $total) {
			$str .= "\n]\n";
		} else
		{
			$str .= "\n,\n";
		}
		
		return $str;
	}

}
