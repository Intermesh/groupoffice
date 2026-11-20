<?php

namespace go\core\controller;

use go\core\Controller;
use go\core\util\DateTime;
use go\core\model\Holiday as HolidayModel;

class Holiday extends Controller
{
	/**
	 * Get the public holidays of a holiday set in a period
	 *
	 * Pass the country code as "set" (e.g. "NL") or the user's holiday set as "holidaySet" (e.g. "en_gb" or "sv").
	 *
	 * @apiPermission Module access: returns public holidays generated from the holiday files shipped with GroupOffice. No user data is read.
	 * @apiReadOnly
	 */
	public function fetch(array $params): array
	{
		// params need '
		$from = new DateTime($params['from']);
		$till = new DateTime($params['till']);
		$list = [];
		$set = isset($params['holidaySet']) ? HolidayModel::countryForHolidaySet($params['holidaySet']) : $params['set'];
		if(!isset($set)) {
			return ['list' => []];
		}
		foreach(HolidayModel::generate($set,$params['lang'],$from, $till) as $holiday){
			$key = $holiday->title .'-'.$holiday->start;
			if(isset($list[$key])) {
				if(isset($list[$key]->region)) {
					$list[$key]->region .= ', ' . $holiday->region;
				}
			} else {
				$list[$key] = $holiday;
			}
		}
		return [
			'list'=> array_values($list)
		];
	}
}