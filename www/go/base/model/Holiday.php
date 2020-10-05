<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * The Holiday model
 * 
 * @version $Id: Holiday.php 7607 2011-08-04 13:41:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.model 
 * 
 * @property int $id 
 * @property String $date 
 * @property String $name
 * @property String $region
 * @property boolean $free_day
 */

namespace GO\Base\Model;


class Holiday extends \GO\Base\Db\ActiveRecord {

	/**
	 * The mapping for the holiday files
	 * 
	 * [ countryCode | localeFile ]
	 * 
	 * @var array
	 */
	public static $mapping = array(
			'at'=>'de-at',
			'ch'=>'de-ch',
			'au'=>'en-au',
			'uk'=>'en_UK'
	);

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Module 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}

	public function tableName() {
		return 'go_holidays';
	}
	
	/**
	 * Get the holidays between the given start and end date
	 * By default this will return all the holidays within all the locales.
	 * You can pass a locale to return only the holidays of that locale
	 * Example locales: 'en','nl','no'
	 * 
	 * When the $check parameter is set to true then the function will check the 
	 * holidays table for existing holidays in the given locale and year.
	 * If the holidays don't exist then it will generate them automatically
	 * 
	 * If $force is set to true then the current holidays in the given period and 
	 * locale will be deleted and recreated from the holidays file.
	 * 
	 * @param StringHelper $startDate
	 * @param StringHelper $endDate
	 * @param StringHelper $locale
	 * @param boolean $check
	 * @param boolean $force
	 * @return \GO\Base\Db\ActiveStatement 
	 */
	public function getHolidaysInPeriod($startDate,$endDate,$locale=false,$check=true,$force=false){
		
		if(empty($locale)){		
			$locale = go()->getAuthState()->getUser(['holidayset'])->holidayset;
			if(!$locale)
				return false;
		}
		
		
		$startDate = strtotime($startDate);
		$endDate = strtotime($endDate);
		
		if(!empty($locale) && $check){
			$year = date('Y',$startDate);
						
		if($force || !$this->checkHolidaysExist($year,$locale))
				$this->generateHolidays($year,$locale);
		}
		
		$findCriteria = \GO\Base\Db\FindCriteria::newInstance()
						->addCondition('date', date('Y-m-d',$startDate),'>=')
						->addCondition('date', date('Y-m-d',$endDate), '<=');	
					
		$findCriteria->addCondition('region', $locale);
			
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->criteria($findCriteria);
		
		return Holiday::model()->find($findParams);
	}
	
	/**
	 * Check if the requested holidays are available in the database.
	 * 
	 * @param StringHelper $year
	 * @param StringHelper $locale
	 * @return int
	 * @throws Exception 
	 */
	public function checkHolidaysExist($year,$locale){

		if(empty($year) || empty($locale))
			Throw new \Exception('No year or locale given for the holidays checker.');
		
//		$startYear = mktime(0, 0, 0, 1, 1, $year);
//		$endYear   = mktime(23, 59, 59, 12, 31, $year);
		
		$findCriteria = \GO\Base\Db\FindCriteria::newInstance()
					->addCondition('date', $year.'-01-01','>=')
					->addCondition('date', ($year+1).'-01-01', '<')
					->addCondition('region', $locale);

		$findParams = \GO\Base\Db\FindParams::newInstance()
					->criteria($findCriteria)
					->single();

		$result = Holiday::model()->find($findParams);		

		return $result!=false;
	}
	
	/**
	 * Delete all the holidays of the given year and locale
	 * 
	 * @param StringHelper $year
	 * @param StringHelper $locale
	 * @throws Exception 
	 */
	public function deleteHolidays($year,$locale='en'){
		
		if(empty($year) || empty($locale))
			Throw new \Exception('No year or locale given for the holidays delete function.');
		
		$startYear = mktime(0, 0, 0, 1, 1, $year);
		$endYear   = mktime(23, 59, 59, 12, 31, $year);
		
		$findCriteria = \GO\Base\Db\FindCriteria::newInstance()
					->addCondition('date', date('Y-m-d', $startYear),'>=')
					->addCondition('date',  date('Y-m-d', $endYear), '<=')
					->addCondition('region', $locale);

		$findParams = \GO\Base\Db\FindParams::newInstance()
					->criteria($findCriteria);

		$holidays = Holiday::model()->find($findParams);
		
		while($holiday = $holidays->fetch()){
			$holiday->delete();
		}
	}
	
	/**
	 * Get all the available holiday files
	 * 
	 * @return array key => label
	 */
	public static function getAvailableHolidayFiles(){
		$holidays = array();
		
		$lang = go()->getLanguage()->getLanguages();
		
		$folderPath = \GO::config()->root_path.'language/holidays/';
		$folder = new \GO\Base\Fs\Folder($folderPath);
		
		$children = $folder->ls();
		foreach($children as $child){
			$iso = $child->nameWithoutExtension();
			$label = $lang[$iso] ?? $iso;
			$holidays[$label] = array('iso'=>$child->nameWithoutExtension(),'label'=>$label);
		}
		ksort($holidays);
		return array_values($holidays);
	}	
	
	/**
	 * Generate the holidays from the holidays file for the given year and locale.
	 * 
	 * @param StringHelper $year
	 * @param StringHelper $locale
	 * @throws Exception 
	 */
	public function generateHolidays($year,$locale='en')
	{
		$this->deleteHolidays($year,$locale);
		
		// Load the holidays file for the given $locale
		if(is_file(\GO::config()->root_path.'language/holidays/'.$locale.'.php'))
			require(\GO::config()->root_path.'language/holidays/'.$locale.'.php');
//		else
//			throw new \Exception('No holidays file for this language: '.$locale.'.');
		
		if(empty($year)) {			
			$year = date('Y');
		}
		
		$holidays = array();
		$defEasterCalendar = CAL_EASTER_DEFAULT;
		
		if(!empty($input_holidays)) {
			$holidays = $input_holidays;
			if(isset($input_holidays['conf']['easter_calendar'])) {
				$defEasterCalendar = $input_holidays['conf']['easter_calendar'];
			}
		}
		// Set the fixed holidays from the holidays file
		if(isset($holidays['fix'])) {
			foreach($holidays['fix'] as $key => $record) {
				if (is_string($record)) {
					$month_day = explode("-", $key);
					$date = mktime(0,0,0,$month_day[0],$month_day[1],$year);

					$holiday = new Holiday();
					$holiday->name = $record;
					$holiday->date = date('Y-m-d',$date);
					$holiday->region = $locale;
					$holiday->save();
				} else if (is_array($record)) {
					$month_day = explode("-", $key);
					$date = mktime(0,0,0,$month_day[0],$month_day[1],$year);

					$holiday = new Holiday();
					$holiday->name = $record['name'];
					$holiday->date = date('Y-m-d',$date);
					$holiday->region = $locale;
					$holiday->free_day = $record['free'];
					$holiday->save();
				}
			}
		}
		
		// Set the variable holidays
		if(isset($holidays['var']) && function_exists('easter_date') && $year > 1969 && $year < 2037) {
//			$easter_day = easter_date($year);
			
			$easterDT = \GO\Base\Util\Date\DateTime::getEasterDatetime($year, $defEasterCalendar);
			$easter_day = $easterDT->format('U');
			
			foreach($holidays['var'] as $key => $record) {
				if (is_string($record)) {
					$date = strtotime($key." days", $easter_day);


					$holiday = new Holiday();
					$holiday->name = $record;
					$holiday->date = date('Y-m-d',$date);
					$holiday->region = $locale;
					$holiday->save();
				} else if (is_array($record)) {
					$date = strtotime($key." days", $easter_day);

					$holiday = new Holiday();
					$holiday->name = $record['name'];
					$holiday->date = date('Y-m-d',$date);
					$holiday->region = $locale;
					$holiday->free_day = $record['free'];
					$holiday->save();
				}
			}
		}

		if(isset($holidays['spc'])) {
			$weekday = $this->get_weekday("24","12",$year);
			foreach($holidays['spc'] as $key => $record) {
				if (is_string($record)) {
					$count = $key - $weekday;
					$date = strtotime($count." days", mktime(0,0,0,"12","24",$year));

					$holiday = new Holiday();
					$holiday->name = $record;
					$holiday->date = date('Y-m-d',$date);
					$holiday->region = $locale;
					$holiday->save();
				} else if (is_array($record)) {
					$count = $key - $weekday;
					$date = strtotime($count." days", mktime(0,0,0,"12","24",$year));

					$holiday = new Holiday();
					$holiday->name = $record['name'];
					$holiday->date = date('Y-m-d',$date);
					$holiday->region = $locale;
					$holiday->free_day = $record['free'];
					$holiday->save();
				}
			}
		}
		
		if(isset($holidays['fn'])) {
	
			foreach($holidays['fn'] as $def) {
			
				$d = call_user_func($def[1], $year);
				if(!empty($d)){
					$holiday = new Holiday();
					$holiday->name = $def[0];
					$holiday->date = $d;
					$holiday->region = $locale;
					$holiday->save();
				}
			}
		}
	}
	
	private function get_weekday($day, $month, $year) {
		$date = getdate(mktime(0, 0, 0, $month, $day, $year));
		return $date["wday"];
	}
	
	public function getJson(){
		$dayString = \GO::t("full_days");
		return array(
//			'id'=>$response['count']++,
			'name'=>htmlspecialchars($this->name, ENT_COMPAT, 'UTF-8'),
			'description'=>'',
			'time'=>date(\GO::user()->time_format, strtotime($this->date)),
			'all_day_event'=>1,
			'start_time'=>$this->date.' 00:00',
			'end_time'=>$this->date.' 23:59',
			//'background'=>$calendar->displayColor,
			'background'=>'f1f1f1',
			'model_name'=>'',
			'day'=>$dayString[date('w', strtotime($this->date))].' '.\GO\Base\Util\Date::get_timestamp(strtotime($this->date),false),
			'read_only'=>true,
			'is_virtual'=>true,
			);
	}
	
	
	/**
	 * Get the holiday locale from the $countryCode that is provided.
	 * 
	 * If no match can be found then the self::$systemDefaultLocale variable is used.
	 * 
	 * @param StringHelper $countryCode
	 * @return mixed the locale for the holidays or false when none found
	 */
	public static function localeFromCountry($countryCode){

		if(key_exists($countryCode,self::$mapping))
			$countryCode = self::$mapping[$countryCode];
		else if(key_exists(strtolower($countryCode),self::$mapping))
			$countryCode = self::$mapping[strtolower($countryCode)];
		
		$languageFolderPath = \GO::config()->root_path.'language/holidays/';
		
		$file = new \GO\Base\Fs\File($languageFolderPath.$countryCode.'.php');
		
		if($file->exists()){
			return $countryCode;
		}else{
			$file = new \GO\Base\Fs\File($languageFolderPath.strtolower($countryCode).'.php');
			if($file->exists())
				return strtolower($countryCode);
		}
		
		return false;
	}
	
	
}
