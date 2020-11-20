<?php

namespace go\core\model;

abstract class CronJob {
	abstract public function run();
}