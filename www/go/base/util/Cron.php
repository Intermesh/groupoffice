<?php
namespace GO\Base\Util;

use \Cron\FieldFactory;
use \Cron\CronExpression;


class Cron extends CronExpression {
	
	public function __construct($expression) {
		$fieldFactory = new FieldFactory();		
		return parent::__construct($expression, $fieldFactory);
	}
	
}
