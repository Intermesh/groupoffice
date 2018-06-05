<?php

namespace GO\Base\Storeexport;

use GO;

abstract class AbstractExport {
	
	/**
	 *
	 * @var \GO\Base\Data\AbstractStore
	 */
	protected $store;
	
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
	 * These columns will be totalized if possible
	 * 
	 * @var array 
	 */
	protected $totalizeColumns=array();
	
	public $totals=array();
	
	
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
	 * Here you can add extra data(lines) that will be parsed after the store data
	 * 
	 * @var array 
	 */
	protected $_lines = false;
	
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
	public function __construct(\GO\Base\Data\AbstractStore $store, $header=true,$humanHeaders=true, $title=false, $orientation=false) {
		$this->store = $store;
		$this->header = $header;
		$this->title = $title;
		$this->orientation = $orientation;
		$this->humanHeaders= $humanHeaders;
		
		if(is_a($store, "\GO\Base\Data\DbStore")){
			$exportName = $this->store->getFindParams()->getParam('export');
			
			$this->totalizeColumns = isset(\GO::session()->values[$exportName]['totalizeColumns']) ? \GO::session()->values[$exportName]['totalizeColumns'] : array();
			foreach($this->totalizeColumns as $column){
				$this->totals[$column]=0;
			}
		}

	}
	
	
	protected function increaseTotals($record){
		foreach($this->totalizeColumns as $column){
			if(isset($record[$column]))
				$this->totals[$column]+=\GO\Base\Util\Number::unlocalize ($record[$column]);
		}
	}

	
	/**
	 * Return an array with all the labels of the columns
	 * 
	 * @return array 
	 */
	public function getLabels(){
		$columns = $this->store->getColumnModel()->getColumns();
		$labels = array();
		foreach($columns as $column)		
			$labels[$column->getDataIndex()]=$this->humanHeaders ? $column->getLabel() : $column->getDataIndex();
		
		return $labels;
	}
	
	protected function prepareRecord($record){
		
		$this->increaseTotals($record);
		
		$c = array_keys($this->getLabels());
		$frecord = array();
		
		foreach($c as $key){
			$frecord[$key] = html_entity_decode($record[$key]);
		}

		return $frecord;
	}
	
	/**
	 * Get the record of the totalized values
	 * 
	 * @return array
	 */
	public function getTotals(){
		if(empty($this->totalizeColumns)){
			return false;
		}	
			
		$record = array();
		
		$columns = $this->store->getColumnModel()->getColumns();
		
		foreach($columns as $column){
			$record[$column->getDataIndex()]=isset($this->totals[$column->getDataIndex()]) ? \GO\Base\Util\Number::localize($this->totals[$column->getDataIndex()]) : '';
		}
		
		return $record;
	}
	
	/**
	 * Add extra lines to the end of the document
	 * 
	 * @param array $lines key value array
	 */
	public function addLines($lines){
		$this->_lines = $lines;
	}
	
	/**
	 * Output's all data to the browser.
	 */
	abstract public function output();
	
}
