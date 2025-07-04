<?php

namespace go\core\controller;

use go\core\Controller;

class Holiday extends Controller
{
	public function fetch(array $params): array
	{
		// params need '
		$from = new \DateTime($params['from']);
		$till = new \DateTime($params['till']);
		$list = [];
		foreach(\go\core\model\Holiday::generate($params['set'],$params['lang'],$from, $till) as $holiday){
			$list[] = $holiday;
		}
		return [
			'list'=> $list
		];
	}
}