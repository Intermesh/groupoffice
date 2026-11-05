<?php

namespace go\core\controller;

use go\core\Controller;
use go\core\util\DateTime;
use go\core\model\Holiday as HolidayModel;

class Holiday extends Controller
{
	public function fetch(array $params): array
	{
		// params need '
		$from = new DateTime($params['from']);
		$till = new DateTime($params['till']);
		$list = [];
		foreach(HolidayModel::generate($params['set'],$params['lang'],$from, $till) as $holiday){
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