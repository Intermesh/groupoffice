<?php


namespace GO\Log\Controller;

use Exception;
use GO;
use GO\Base\Controller\AbstractModelController;
use GO\Base\Data\ColumnModel;
use GO\Base\Data\DbStore;
use GO\Base\Db\FindParams;
use GO\Base\Fs\CsvFile;
use GO\Base\Util\Date;
use GO\Log\Model\Log;


class LogController extends AbstractModelController {

	protected $model = 'GO\Log\Model\Log';
	
	protected function allowGuests() {
		return array('rotate');
	}
	
	protected function getStoreParams($params) {
		
		return FindParams::newInstance()->export("log");
	}

	protected function actionRotate($params){
		
		$this->requireCli();
		
		$findParams = FindParams::newInstance();
		
		$findParams->getCriteria()->addCondition('ctime', Date::date_add(time(),-GO::config()->log_max_days), '<');
		
		$stmt = Log::model()->find($findParams);
		
		$count = $stmt->rowCount();
		echo "Dumping ".$count." records to CSV file\n";
		if($count){
			$logPath = '/var/log/groupoffice/'.GO::config()->id.'.csv';

			$csvLogFile = new CsvFile($logPath);
			$csvLogFile->parent()->create();

			while($log = $stmt->fetch()){
				if(!$csvLogFile->putRecord(array_values($log->getAttributes('formatted'))))
					throw new Exception("Could not write to CSV log file: ".$csvLogFile->path());

				$log->delete();
			}
		}
		
		echo "Done\n";
	}
	
	/**
	 * 
	 * http://sony.localhost/?r=log/log/recordsForEntity&entityType=Asset&entityId=7&security_token=7uDs5HkaIVyn26pghe48
	 * 
	 * @param type $params
	 * @return type
	 * @throws Exception
	 */
	public function actionRecordsForEntity($params){
		if(!isset($params['entityType'])){
			throw new Exception("Please provide the \"entityType\" property.");
		}
		
		if(!isset($params['entityId'])){
			throw new Exception("Please provide the \"entityId\" property.");
		}
		
		$entityType = \go\core\orm\EntityType::findByName($params['entityType']);
		
		if(!$entityType){
			throw new Exception("Unknown entityType ".$params['entityType']);
		}
		
		$columnModel = new ColumnModel(Log::model());
		$findParams = FindParams::newInstance()->select()->order('ctime','DESC');
		$findParams->getCriteria()
						->addCondition('model', $entityType->getName())
						->addCondition('model_id', $params['entityId']);
		
		//Create store
		$store = new DbStore('GO\Log\Model\Log', $columnModel, $params, $findParams);
		
		return $store->getData();
	}
	
	/**
	 * 
	 * http://sony.localhost/?r=log/log/recordsForModel&model=GO\Sonyassets\Model\Asset&model_id=7&security_token=7uDs5HkaIVyn26pghe48
	 * 
	 * @param type $params
	 * @return type
	 * @throws Exception
	 */
	public function actionRecordsForModel($params){
		
		if(!isset($params['model'])){
			throw new Exception("Please provide the \"model\" property.");
		}
		
		if(!isset($params['model_id'])){
			throw new Exception("Please provide the \"model_id\" property.");
		}
		
		$columnModel = new ColumnModel(Log::model());
		$findParams = FindParams::newInstance()->select()->order('ctime','DESC');
		$findParams->getCriteria()
						->addCondition('model', $params['model'])
						->addCondition('model_id', $params['model_id']);
		
		//Create store
		$store = new DbStore('GO\Log\Model\Log', $columnModel, $params, $findParams);
		
		return $store->getData();
	}
}
