<?php

namespace go\core\model;

use Cron\CronExpression;
use GO;
use go\core\jmap\Entity;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use go\core\model\Module;
use Exception;
use go\core\db\Criteria;

class CronJobSchedule extends Entity {

	/**
	 * The Entity ID
	 * 
	 * @var int
	 */
	public $id;

	public $moduleId;
	public $description;
	public $name;
	public $expression;
	public $enabled = true;

	/**
	 *
	 * @var DateTime
	 */
	public $nextRunAt;

	/**
	 *
	 * @var DateTime
	 */
	public $lastRunAt;
	
	/**
	 *
	 * @var DateTime
	 */
	public $runningSince;
	
	public $lastError;
	
	protected static function defineMapping() {
		return parent::defineMapping()->addTable('core_cron_job');
	}

	protected function internalValidate() {

		if (isset($this->expression) && !CronExpression::isValidExpression($this->expression)) {
			$this->setValidationError('expression', ErrorCode::MALFORMED);
		}
		
		if($this->isModified('name')) {
			$cls = $this->getCronClass();
			if(!class_exists($cls)) {
				$this->setValidationError('name', ErrorCode::NOT_FOUND);
			}
			
			if(!is_a($cls, CronJob::class, true)) {
				$this->setValidationError('name', ErrorCode::INVALID_INPUT, "The given name is not a CronJob class.");
			}
		}
		
		if ($this->isModified('nextRunAt') && isset($this->nextRunAt)) {
			//round to next minute
			if (!go()->getDebugger()->enabled) {
				$seconds = 60 - $this->nextRunAt->format('s');
				$this->nextRunAt->modify("+$seconds second");
			}
		} else if (($this->isModified('expression') || (!isset($this->nextRunAt)) && $this->enabled)) {
			$this->nextRunAt = $this->getNextRunDate();
		}



		if (!$this->enabled) {
			$this->nextRunAt = null;
		}

		return parent::internalValidate();
	}
	
	private function getNextRunDate() {

		if (!isset($this->expression)) {
			return null;
		}
		
		$now = new \DateTime();
		$cronExpression = CronExpression::factory($this->expression);
		return $cronExpression->getNextRunDate($now);
	}
	
	public function getCronClass() {
		$module = Module::findById($this->moduleId);
		
		if($module->package == "core" && $module->name == "core") {
			return "go\\core\\cron\\" . $this->name;
		}
		
		return "go\\modules\\" . $module->package . "\\" . $module->name . "\\cron\\" . $this->name;
	}
	
	
	/**
	 * Run the job or schedule it if it has not been scheduled yet.
	 */
	public function run() {
		//Set nextRun to null so it won't run more then once at a time
		$this->nextRunAt = null;
		//set runningSince to now
		$this->runningSince = new \DateTime();
		$this->lastError = null;

		if (!$this->save()) {
			throw new \Exception("Could not save CRON job");
		}
		
		$cls = $this->getCronClass();

		go()->debug("Running CRON method: " . $cls);
		
		try {
			$cron = new $cls;			
			$cron->run();
			
		} catch (Exception $ex) {			
			$errorString = \go\core\ErrorHandler::logException($ex);		
			echo $errorString . "\n";
			$this->lastError = $errorString;
		}

		$this->lastRunAt = new \DateTime();
		$this->runningSince = null;

		$this->nextRunAt = $this->getNextRunDate();

		if (!$this->save()) {
			throw new \Exception("Could not save CRON job");
		}
	}

	/**
	 * Finds all cronjobs that should be executed.
	 *
	 * It also finds cron jobs that are not scheduled yet and it will calculate the
	 * scheduled date for the jobs.
	 *
	 * @return bool true if a job was ran
	 * @throws Exception
	 */
	public static function runNext() {

		$job = self::find()->where('enabled', '=', true)
						->andWhere((new Criteria())
							->andWhere('nextRunAt', '<=', new DateTime())
							->orWhere('nextRunAt', 'IS', null)
						)
						->orderBy(['nextRunAt' => 'ASC'])->single();

		if ($job) {
			$job->run();

			return true;
		} else {
			go()->debug("No cron job to run a this time");
			return false;
		}
	}

}
