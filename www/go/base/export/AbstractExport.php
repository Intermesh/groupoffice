<?php

namespace GO\Base\Export;


abstract class AbstractExport {
	
	/**
	 *
	 * @var \GO\Base\Data\Store
	 */
	protected $store;
	
	/**
	 *
	 * @var \GO\Base\Data\ColumnModel 
	 */
	protected $columnModel;
	
	/**
	 *
	 * @var Boolean 
	 */
	protected $header;
	
	/**
	 *
	 * @var Boolean 
	 */
	protected $humanHeaders = true;
	
	/**
	 *
	 * @var String 
	 */
	protected $title;
	
	/**
	 * 
	 * @var String 
	 */
	protected $orientation;
	
	/**
	 * 
	 * @var \GO\Base\Db\ActiveRecord 
	 */
	protected $model;
	/**
	 * 
	 * @var \GO\Base\Db\FindParams 
	 */
	protected $findParams;
	
	/**
	 * @var array Extra parameters passed by the client.
	 */
	protected $params;
	
	/**
	 * Display the exporter in the exportDialog?
	 * @var Boolean 
	 */
	public static $showInView=false;
	
	/**
	 * The name that will be displayed in the frontend for this exporter.
	 * 
	 * @var String 
	 */
	public static $name="No name given";

	/**
	 * Can the orientation of this exporter be given by the front end user?
	 * 
	 * @var Boolean 
	 */
	public static $useOrientation=false;
	
	/**
	 * The constructor for the exporter
	 * 
	 * @param \GO\Base\Data\Store $store
	 * @param \GO\Base\Data\ColumnModel $columnModel
	 * @param Boolean $header
	 * @param Boolean $humanHeaders
	 * @param String $title
	 * @param Mixed $orientation ('P' for Portrait,'L' for Landscape of false for none) 
	 */
	public function __construct(\GO\Base\Data\Store $store, \GO\Base\Data\ColumnModel $columnModel, \GO\Base\Db\ActiveRecord $model, \GO\Base\Db\FindParams $findParams, $header=true,$humanHeaders=true, $title=false, $orientation=false, $params=array()) {
		$this->store = $store;
		$this->columnModel = $columnModel;
		$this->header = $header;
		$this->title = $title;
		$this->orientation = $orientation;
		$this->model = $model;
		$this->findParams= $findParams;
		$this->params= $params;
		$this->humanHeaders= $humanHeaders;
		
		$this->setStatement();
	}
	
	protected function setStatement(){
		$stmt = $this->model->find($this->findParams);
		$this->store->setStatement($stmt);		
	}
	
	/**
	 * Return an array with all the labels of the columns
	 * 
	 * @return array 
	 */
	public function getLabels(){
		$columns = $this->columnModel->getColumns();
		$labels = array();
		foreach($columns as $column)		
			$labels[$column->getDataIndex()]=$column->getLabel();
		
		return $labels;
	}
	
	protected function prepareRecord($record){
		$c = array_keys($this->getLabels());
		$frecord = array();
		
		foreach($c as $key){
            if(is_array($record[$key])) {
                $frecord[$key] = html_entity_decode(implode(', ',$record[$key]));
            } else {
                $frecord[$key] = html_entity_decode($record[$key]);
            }
		}

		return $frecord;
	}
	
	/**
	 * Output's all data to the browser.
	 */
	abstract public function output();
	
}
