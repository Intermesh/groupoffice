<?php
namespace go\modules\community\tasks\convert;

use Exception;
use go\core\data\convert\AbstractConverter;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\orm\Entity;
use go\core\util\StringUtil;
use go\modules\community\tasks\model\Task;
use Sabre\VObject\Component\VCalendar as VCalendarComponent;
use Sabre\VObject\Reader;
use Sabre\VObject\Splitter\ICalendar as VCalendarSplitter;

use function GuzzleHttp\json_encode;

/**
 * VCalendar converter
 * 
 * Converts tasks from and to VCalendar 3.0 format files.
 * 
 * When importing it also keeps the original VCalendar data.
 */
class VCalendar extends AbstractConverter {

	
	const EMPTY_NAME = '(no name)';

	/**
	 * 
	 * @param Task $task
	 * @return VCalendarComponent
	 */
	private function getVTodo(Task $task) {
		
		if ($task->vcalendarBlobId) {
			//task has a stored VCalendar 
			$blob = Blob::findById($task->vcalendarBlobId);
			$VCalendar = Reader::read($blob->getFile()->open("r"), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);			
			
			if($VCalendar->VTODO) {
				return $VCalendar->VTODO;
			}
		} 
		
		$calendar = new \Sabre\VObject\Component\VCalendar();
		return $calendar->createComponent('VTODO');
	
	}

	/**
	 * Parse an Event object to a VObject
	 * @param task $task
	 */
	public function export(Task $task) {
		$rrule = $task->getRecurrenceRule();
		$newrrule = "";

		if(is_array($rrule)) {
			foreach($rrule as $key => $value) {
				switch($key) {
					case "frequency":
						$newrrule .= "FREQ=" . $value . ";";
					break;
					case "interval":
					case "until":
					case "count":
						$newrrule .= strtoupper($key) . "=" . $value . ";";
					break;
					case "byDay":
						$allDays = "";
						if(is_array($value)) {
							$itemCount = count($value);
							$count = 0;
							foreach($value as $days) {
								$day = $days["day"];
								$position = $days["position"];

								if($position != -1) {
									$day = $position . $day;
								}

								if(++$count === $itemCount) {
									$allDays .= $day;
								} else {
									$allDays .= $day . ",";
								}
								
							}
						}
						if(!empty($allDays)) {
							$newrrule .= "BYDAY=" . $allDays . ";";
						}


					break;
				}
			}
		}

		$vtodo = $this->getVTodo($task);
		$vtodo->RRULE = $newrrule;
		$vtodo->UID = $task->getUid();
		$vtodo->SUMMARY = $task->title;
		$vtodo->PRIORITY = $task->priority;
		$vtodo->DTSTART = $task->start;
		$vtodo->DUE = $task->due;

		if(is_array($task->categories)) {
			$data = [];
			foreach($task->categories as $category) {
				$results = go()->getDbConnection()->select("name")->from("task_category")->where('id=' . $category);

				foreach($results as $result) {
					$data[] = $result["name"];
				}
			}
			$vtodo->CATEGORIES = $data;
		}

		$vtodo->DESCRIPTION = $task->description;
		return $vtodo->serialize();
	}
	

	
	protected function exportEntity(Entity $entity, $fp, $index, $total) {
		$str = $this->export($entity);
		fputs($fp, $str);
	}

	protected function importEntity($entity, $fp, $index, array $params) {
		$t = "";
	}
	

	protected function internalExport($fp, $entities, $total) {

		$this->vcalendar =  new VCalendarComponent();
		$this->vcalendar->LANGUAGE = go()->getSettings()->language;
		$this->vcalendar->PRODID = '-//Intermesh//NONSGML Group-Office ' . go()->getVersion() . '//EN';

		fputs($fp, "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Intermesh//NONSGML Group-Office ".go()->getVersion()."//EN\r\n");

		$t = new \GO\Base\VObject\VTimezone();
		fputs($fp, $t->serialize());

		$i = 0;
		foreach($entities as $entity) {
			$this->exportEntity($entity, $fp, $i, $total);
			$i++;
		}

		fputs($fp, "END:VCALENDAR\r\n");
	}

	public function getFileExtension() {
		return 'ics';
	}

	public function importFile(File $file, $entityClass, $params = []) {

		$response = [
				'ids' => [],
				'errors' => [],
				'count' => 0
		];
		
		$values = $params['values'] ?? [];
		$tasklistId = $values['tasklistId'];
		$contents = $file->getContents();
		
		$splitter = new VCalendarSplitter(StringUtil::cleanUtf8($contents), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);

		while ($VCalendarComponent = $splitter->getNext()) {
			
			$uid = (string)$VCalendarComponent->VTODO->UID;
			$priority = (string)$VCalendarComponent->VTODO->PRIORITY;
			$summary = (string)$VCalendarComponent->VTODO->SUMMARY;
			$start = $VCalendarComponent->VTODO->DTSTART;
			$due = $VCalendarComponent->VTODO->DUE;
			$description = (string)$VCalendarComponent->VTODO->DESCRIPTION;
			$categories = (string)$VCalendarComponent->VTODO->CATEGORIES;
			$rrule = (string)$VCalendarComponent->VTODO->RRULE;
			$kvArr = [];
			$rruleSplit = explode(";",$rrule);
			if(is_array($rruleSplit)) {
				foreach($rruleSplit as $keyValues) {
					$rruleKeyValues = explode("=",$keyValues);
					$key = $rruleKeyValues[0];
					$value = $rruleKeyValues[1];
					switch($key) {
						case "FREQ":
							$kvArr["frequency"] = $value;
						break;
						case "INTERVAL":
						case "COUNT":
							$kvArr[strtolower($key)] = (int)$value;
						break;
						case "UNTIL":
							$kvArr[strtolower($key)] = $value;
						break;
						case "BYDAY":
							$allDays = [];
							$daysArr = explode(",",$value);

							if(is_array($daysArr)) {
								foreach($daysArr as $day) {
									// contains the day and position
									if(strlen($day) > 2) {
										$allDays["day"] = substr($day,1,2);
										$allDays["position"] = substr($day,0,1);
									} else {
										$allDays["day"] = substr($day,0,2);
										$allDays["position"] = -1;
									}
									
									$byDaysArr[] = $allDays;

									if(!empty($allDays)) {
										$kvArr["byDay"] = $byDaysArr;
										$kvArr["bySetPosition"] = $allDays["position"];
									}
								}
							}

						break;
					}
				}
			}
			
			$taskValues = [
				"uid" => $uid,
				"tasklistId" => $tasklistId,
				"start" => $start,
				"due" => $due,
				"title" => $summary,
				"description" => $description,
				"priority" => $priority
			];
			
			try {
				$task = $this->findTask($VCalendarComponent, $tasklistId);
				// no task found
				if(!$task) {
					$task = new Task();
					$encodedRrule = json_encode($kvArr);
					$task->setRecurrenceRuleEncoded($encodedRrule);
					$task->setValues($taskValues);
					$task->save();
					$taskId = $task->id;
					$categoryNames = explode(",",$categories);
					foreach($categoryNames as $categoryName) {
						$categoryId = (int) go()->getDbConnection()->selectSingleValue('id')->from('task_category')->where(['name' => $categoryName])->execute()->fetch();
						if($categoryId > 0) {
							$data = [
								'taskId'=> $taskId,
								'categoryId'=>$categoryId
							];
							go()->getDbConnection()->insert("task_task_category", $data)->execute();
						}
					}
				} else {
					continue;
				}

			}
			catch(\Exception $e) {
				ErrorHandler::logException($e);
				$response['errors'][] = "Failed to import vcalendar: ". $e->getMessage();
			}			
			$response['count']++;
		}
		return $response;
	}
	
	/**
	 * 
	 * @param VCalendarComponent $VCalendarComponent
	 * @param int $taskId
	 * @return Task
	 */
	private function findtask(VCalendarComponent $VCalendarComponent, $tasklistId) {
			$task = false;

			if(isset($VCalendarComponent->VTODO->uid)) {
				$task = Task::find()->where(['tasklistId' => $tasklistId, 'uid' => (string) $VCalendarComponent->VTODO->uid])->single();
			}
			
			//Serialize data to store VCalendar
			// $blob = $this->saveBlob($VCalendarComponent);			
			// $task->VCalendarBlobId = $blob->id;
			
			return $task;
	}
	/**
	 * 
	 * @param VCalendarComponent $VCalendarComponent
	 * @return Blob
	 * @throws Exception
	 */
	// private function saveBlob(VCalendarComponent $VCalendarComponent){
	// 	$blob = Blob::fromString($VCalendarComponent->serialize());
	// 	$blob->type = 'text/VCalendar';
	// 	$blob->name = ($VCalendarComponent->uid ?? 'nouid' ) . '.ics';
	// 	if(!$blob->save()) {
	// 		throw new \Exception("could not save VCalendar blob");
	// 	}
		
	// 	return $blob;
	// }

	public static function supportedExtensions()
	{
		return ['ics'];
	}
}
