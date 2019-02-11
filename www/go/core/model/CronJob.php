<?php

namespace go\core\model;

abstract class CronJob {
	abstract function run();
}