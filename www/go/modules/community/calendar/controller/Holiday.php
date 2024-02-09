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
			$list[] = $holiday;
		}
		return [
			'list'=> $list
		];
	}
}