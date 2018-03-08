<?php

namespace GO\Base\Controller;


abstract class AbstractSyncController extends AbstractController {
	
	protected function init() {
		parent::init();
		
		header('Content-Type: application/json');
	}
	
	protected $returnRelations = [];
	
	abstract protected function recordClassName();
	
	
	/**
	 * 
	 * @return \GO\Base\Db\FindParams
	 */
	protected function getStatFindParams() {
		return (new \GO\Base\Db\FindParams())->select('id,mtime');
	}
	
	protected function actionStat() {
		$cls = $this->recordClassName();
		
		$as = $cls::model()->find($this->getStatFindParams());
		
		/* @var $as \GO\Base\Db\ActiveStatement  */
		
		
		
		echo "[";
		$first = true;
		while($record = $as->fetch(\GO\Base\Db\PDO::FETCH_ASSOC)) {
			
			$record['mtime'] = date('Y-m-d H:i:s', $record['mtime']);			
			
			if(!$first) {
				echo "\n,\n";
			}else {
				$first = false;
			}
			echo json_encode($record);
			
		}
		
		
		echo "]";
		
	}
	
	
	protected function toArray(\GO\Base\Db\ActiveRecord $record) {
		$data = $record->getAttributes('raw');
		
		foreach($this->returnRelations as $relationName) {
			$relation = $record->{$relationName};
			
			if($relation instanceof \GO\Base\Db\ActiveRecord) {
				$data[$relationName] = $relation->getAttributes('raw');
			}else if(!isset($relation)) {
				$data[$relationName] = null;
			} else
			{
				$data[$relationName] = [];
				
				foreach($relation as $r) {
					$data[$relationName][] = $r->getAttributes('raw');
				}
			}
		}
		
		return $data;
	}
	
	
	
	protected function actionRead($id) {
		$cls = $this->recordClassName();
		$record = $cls::model()->findByPk($id);
		
		echo json_encode($this->toArray($record));
	}
}
