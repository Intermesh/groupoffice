<?php

namespace go\modules\community\calendar\controller;

use go\core\Controller;
use go\modules\community\calendar\model;

class Holiday extends Controller
{
	public function fetch($params) {
		// params need '
		$from = new \DateTime($params['from']);
		$till = new \DateTime($params['till']);
		$list = [];
		foreach(model\Holiday::generate($params['set'],$params['lang'],$from, $till) as $holiday){
			$key = $holiday->title .'-'.$holiday->start;
			if(isset($list[$key])) {
				$list[$key]->region .= ', ' . $holiday->region;
			} else {
				$list[$key] = $holiday;
			}
		}
		return [
			'list'=> array_values($list)
		];
	}
}