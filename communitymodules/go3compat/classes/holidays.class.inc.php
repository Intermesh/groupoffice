<?php
/*
Copyright Intermesh 2003
Author: Georg Lorenz <georg@lonux.de>
Version: 1.0 Release date: 08 July 2003

This program is free software; you can redistribute it and/or modify it
under the terms of the GNU General Public License as published by the
Free Software Foundation; either version 2 of the License, or (at your
option) any later version.
*/

class holidays extends db {
	var $locale;
	var $in_holidays=false;
	var $holidays = array();

	private function load_holidays(){
		if(!$this->in_holidays){
			global $GO_LANGUAGE;
			
			require($GLOBALS['GO_LANGUAGE']->language_path.'holidays.inc.php');

			$this->in_holidays = $input_holidays;
		}
	}

	public function get_regions($language) {
		global $GO_LANGUAGE;

		require_once($GLOBALS['GO_LANGUAGE']->language_path.'languages.inc.php');
		$regions = array();
		foreach($locale as $reg => $lang) {
			if($this->holidays_for_region_exist($reg)) {
				$regions[] = $reg;
			}
		}

		return $regions;
	}

	private function holidays_for_region_exist($region){

		$this->load_holidays();

		return isset($this->in_holidays["fix"][$region]) || isset($this->in_holidays["var"][$region]) || isset($this->in_holidays["spc"][$region]);
	}

	/*public function add_holidays($user_id, $year, $region) {
		if($this->generate_holidays($region, $year)) {
			$this->delete_holidays($user_id, $year);
			foreach($this->holidays as $date => $name) {
				$this->add_holiday($user_id, $region, $date, $name);
			}
		}else {
			return false;
		}
		return true;
	}*/

	private function add_holiday($region, $date, $name) {

		go_debug("add_holiday($region, ".Date::get_timestamp($date).", $name)");
		
		$next_id = $this->nextid("go_holidays");
		if ($next_id > 0) {
			$name = addslashes($name);
			$sql = "INSERT INTO go_holidays (id, region, date, name) VALUES ('$next_id', '$region', '$date', '$name')";
			return $this->query($sql);
		}
		return false;
	}

	/*public 	function update_holiday($id, $date, $name) {
		$sql = "UPDATE go_holidays SET date='$date', name='$name'";
		$sql .= " WHERE id='$id'";
		return ($this->query($sql));
	}

	public function delete_holiday($id) {
		$sql = "DELETE FROM go_holidays WHERE id='$id'";
		$this->query($sql);
		if ($this->affected_rows() > 0)
			return true;
		else
			return false;
	}*/

	public function delete_holidays($region, $year=""){

		$sql = "DELETE FROM go_holidays WHERE region='$region'";

		if(!empty($year)) {
			$date_from = mktime(0,0,0,12,31,$year-1);
			$date_to = mktime(0,0,0,1,1,$year+1);
			$sql .= " AND date>'$date_from' AND date<'$date_to'";
		}

		return $this->query($sql);
	}

	/*public function get_region($user_id) {
		$sql = "SELECT region FROM go_holidays WHERE user_id='$user_id'";

		$this->query($sql);
		return $this->next_record();
	}

	public function get_holiday($user_id, $date) {
		$sql = "SELECT * FROM go_holidays WHERE  user_id='$user_id' AND date='$date'";

		$this->query($sql);
		return $this->next_record();
	}

	public function get_holiday_by_id($id) {
		$sql = "SELECT * FROM go_holidays WHERE id='$id'";

		$this->query($sql);
		return $this->next_record();
	}*/

	public function get_holidays_for_period($region, $start, $end) {
		
		if(empty($start) || empty($end))
			return false;

		if(!$this->holidays_for_region_exist($region)){
			return false;
		}

		$start_year = date('Y', $start);
		$end_year = date('Y', $end);
		if(!$this->check_holidays($region, $start_year)){
			$this->generate_holidays($region, $start_year);
		}
		if($start_year != $end_year && !$this->check_holidays($region, $end_year)){
			$this->generate_holidays($region, $end_year);
		}


		$sql = "SELECT * FROM go_holidays WHERE region='$region'".
						" AND date>=$start AND date<$end ORDER BY date ASC";
		$this->query($sql);
		return $this->num_rows();
	}

	private function check_holidays($region, $year){

		if(!isset($_SESSION['GO_SESSION']['check_holidays_'.$year])){
			$start = mktime(0,0,0,1,1,$year);
			$end = mktime(0,0,0,1,0,$year+1);

			$sql = "SELECT * FROM go_holidays WHERE region='$region'".
							" AND date>=$start AND date<$end";
			$this->query($sql);

			$_SESSION['GO_SESSION']['check_holidays_'.$year]=true;
			return $this->num_rows();
		}else
		{
			return true;
		}
	}


	private function generate_holidays($region, $year="") {

		go_debug("generate_holidays($region, $year)");

		$holidays = array();

		$this->delete_holidays($region, $year);
		
		$this->load_holidays();

		foreach($this->in_holidays as $key => $sub_array) {
			if(array_key_exists($region, $sub_array)) {
				if($sub_array[$region]) {
					$holidays[$key] = $sub_array[$region];
				}
			}
		}

		if(empty($year)) {			
			$year = date('Y');
		}

		if(isset($holidays['fix'])) {
			foreach($holidays['fix'] as $key => $name) {
				$month_day = explode("-", $key);
				$date = mktime(0,0,0,$month_day[0],$month_day[1],$year);

				$this->add_holiday($region, $date, $name);
			}
		}

		if(isset($holidays['var']) && function_exists('easter_date') && $year > 1969 && $year < 2037) {
			$easter_day = easter_date($year);
			foreach($holidays['var'] as $key => $name) {
				$date = strtotime($key." days", $easter_day);
				$this->add_holiday($region, $date, $name);
			}
		}

		if(isset($holidays['spc'])) {
			$weekday = $this->get_weekday("24","12",$year);
			foreach($holidays['spc'] as $key => $name) {
				$count = $key - $weekday;
				$date = strtotime($count." days", mktime(0,0,0,"12","24",$year));
				$this->add_holiday($region, $date, $name);
				
			}
		}
	}
	
	private function get_weekday($day, $month, $year) {
		$date = getdate(mktime(0, 0, 0, $month, $day, $year));
		return $date["wday"];
	}
}
