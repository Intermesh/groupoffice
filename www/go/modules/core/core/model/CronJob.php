<?php

namespace go\modules\core\core\model;

abstract class CronJob {
	abstract function run();
}