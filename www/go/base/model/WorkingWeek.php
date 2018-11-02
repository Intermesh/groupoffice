<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */
 
/**
 * @property int $user_id
 * @property double $mo_work_hours
 * @property double $tu_work_hours
 * @property double $we_work_hours
 * @property double $th_work_hours
 * @property double $fr_work_hours
 * @property double $sa_work_hours
 * @property double $su_work_hours
 */


namespace GO\Base\Model;


class WorkingWeek extends \GO\Base\Db\ActiveRecord {
	
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function primaryKey() {
		return 'user_id';
	}
	
	protected function getLocalizedName() {
		return \GO::t("workingWeek");
	}

	public function tableName() {
		return 'go_working_weeks';
	}
	
	public function getHoursForDay($time){
		
		switch(date('w', $time)){
			case 0:
				return $this->su_work_hours;
				break;
			case 1:
				return $this->mo_work_hours;
				break;
			case 2:
				return $this->tu_work_hours;
				break;
			case 3:
				return $this->we_work_hours;
				break;
			case 4:
				return $this->th_work_hours;
				break;
			case 5:
				return $this->fr_work_hours;
				break;
			case 6:
				return $this->sa_work_hours;
				break;
			
		}
		
	}
	
	/**
	 * Get the total amount of hours that are set for a workingweek
	 * 
	 * @return double
	 */
	public function getTotalHoursForWeek(){
		 return 
			$this->su_work_hours + 
			$this->mo_work_hours +
			$this->tu_work_hours +
			$this->we_work_hours +
			$this->th_work_hours +
			$this->fr_work_hours +
			$this->sa_work_hours;		
	}
	
//	private $_leftOverHours=0;
	
	public function getNextDate($startDate, $workingHours, &$leftOverHours=0){
		$hoursForDay = $this->getHoursForDay($startDate);

//		\GO::debug('getNextDate('.date('Ymd',$startDate).', '.$workingHours.')');
		
//		\GO::debug("Left: ".$this->_leftOverHours);
		
//		\GO::debug("Hours for day: ".$hoursForDay);

//		$workingHours+=$this->_leftOverHours;
		
		$workingHours -= $hoursForDay;
		
		
//		\GO::debug($workingHours);
		
		if($workingHours>=0){

			for($i=0;$i<7;$i++){
				$startDate=\GO\Base\Util\Date::date_add($startDate,1);
				$hoursForDay = $this->getHoursForDay($startDate);
				
				$workingHours-=$hoursForDay;
				if($workingHours<0){
					
					break;
				}
			}
		}
		
//		$this->_leftOverHours=$hoursForDay - $workingHours*-1;
		
		$leftOverHours=$hoursForDay - $workingHours*-1;
		
		
		return $startDate;
	}

}
