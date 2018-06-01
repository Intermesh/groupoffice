<?php

/**
 * Model that keeps the records of the categorycount print
 */

namespace GO\Calendar\Model;


class PrintCategoryCount extends \GO\Base\Model {

	public $startDate;
	public $endDate;
	
	public $categories;
	public $calendars;
	
	private $_rows = array();
	private $_headers = array();
	private $_totals = array();
	
	/**
	 * Constructor
	 *  
	 * @param StringHelper $startDate
	 * @param StringHelper $endDate
	 */
	public function __construct($startDate,$endDate) {
		
		$this->startDate = $startDate;
		$this->endDate = $endDate;
		
		
		$this->calendars = Calendar::model()->find()->fetchAll();
		
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->join('cal_events', \GO\Base\Db\FindCriteria::newInstance()->addRawCondition('t.id', 'e.category_id'),'e')
						->group('t.id')
						->having('count(*)>0');
		
		$calendars=array(0);
		
		foreach($this->calendars as $calendar){
			$calendars[]=$calendar->id;
		}
		
		$findParams->getCriteria()->addInCondition('calendar_id', $calendars);
		
		
		$this->categories = Category::model()->find($findParams)->fetchAll(); //GLOBAL
		
		
	
	}
	
	/**
	 * Return an array of the headers of the table
	 * 
	 * @return array
	 */
	public function getHeaders(){
		
		if(empty($this->_headers)){
			$this->_headers[] = \GO::t("Calendars", "calendar");

			foreach($this->categories as $cat){
				$this->_headers[] = $cat->name;
			}
		}
		return $this->_headers;
	}
	
	/**
	 * Get the table rows that need to be printed in the pdf
	 * 
	 * @return array
	 */
	public function getRows(){

		if(empty($this->_rows)){
			foreach($this->calendars as $calendar){

				$row = array(
						'name'=>$calendar->name
				);
				
//				foreach($this->categories as $category){

					$findParams = \GO\Base\Db\FindParams::newInstance()
									->ignoreAcl()
									->select('COUNT(*) as count, category_id')									
									->group('category_id');
//					$findParams->ignoreAcl();							// Only count items that are visible for this user.
	//				$findParams->group('calendar_id');

					$findCriteria = \GO\Base\Db\FindCriteria::newInstance();
					
					$findCriteria->addCondition('calendar_id', $calendar->id);
					$findCriteria->addCondition('start_time', strtotime($this->startDate),'>');
					$findCriteria->addCondition('end_time', strtotime($this->endDate),'<');

					$findParams->criteria($findCriteria);

					$catRecord = array();
					foreach(Event::model()->find($findParams) as $record){
						 $catRecord[intval($record->category_id)]=$record->count;
					}
					
					foreach($this->categories as $category){
						$row[]=isset($catRecord[$category->id]) ? $catRecord[$category->id] : 0;
					}
					
					
					$this->_rows[] = $row;


				}

				
//			}
		}

		return $this->_rows;
	}
	
	/**
	 * Get the total row as an array
	 * 
	 * @return array
	 */
	public function getTotals(){
		
		if(empty($this->_totals)){
			$rows = $this->getRows();
			
			$this->_totals[] =\GO::t("Total", "calendar");
			
			foreach($rows as $row){
				$i = 1;
				foreach($row as $col){
					if($i > 1){
						if(!isset($this->_totals[$i]))
							$this->_totals[$i] = 0;
						
						$this->_totals[$i] += (int)$col; 
					}
					$i++;
				}
			}
		}

		return $this->_totals;
	}
}
