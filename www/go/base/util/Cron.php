<?php
namespace GO\Base\Util;

use \Cron\FieldFactory;
use \Cron\CronExpression;

require_once \GO::config()->root_path."go/vendor/Cron/FieldInterface.php";
require_once \GO::config()->root_path."go/vendor/Cron/AbstractField.php";
require_once \GO::config()->root_path."go/vendor/Cron/CronExpression.php";
require_once \GO::config()->root_path."go/vendor/Cron/YearField.php";
require_once \GO::config()->root_path."go/vendor/Cron/MonthField.php";
require_once \GO::config()->root_path."go/vendor/Cron/MinutesField.php";
require_once \GO::config()->root_path."go/vendor/Cron/HoursField.php";
require_once \GO::config()->root_path."go/vendor/Cron/FieldFactory.php";
require_once \GO::config()->root_path."go/vendor/Cron/DayOfWeekField.php";
require_once \GO::config()->root_path."go/vendor/Cron/DayOfMonthField.php";

class Cron extends CronExpression {
	
	public function __construct($expression) {
		$fieldFactory = new FieldFactory();		
		return parent::__construct($expression, $fieldFactory);
	}
	
}