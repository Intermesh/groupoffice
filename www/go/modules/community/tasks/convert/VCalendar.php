<?php
namespace go\modules\community\tasks\convert;

use Exception;
use go\core\data\convert\AbstractConverter;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\orm\Entity;
use go\core\util\StringUtil;
use go\modules\community\tasks\model\Recurrence;
use go\modules\community\tasks\model\Task;
use Sabre\VObject\Component\VCalendar as VCalendarComponent;
use Sabre\VObject\Property\ICalendar\Recur;
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

		$vtodo = $this->getVTodo($task);
		$rule = $task->getRecurrenceRule();
		if($rule) {
			$rrule = Recurrence::fromArray($rule, $task->start);
			$vtodo->RRULE = $rrule->toString();
		}
		$vtodo->RRULE = $rrule->toString();
		$vtodo->UID = $task->getUid();
		$vtodo->SUMMARY = $task->title;
		$vtodo->PRIORITY = $task->priority;
		$vtodo->DTSTART = $task->start;
		$vtodo->DUE = $task->due;
		$vtodo->DESCRIPTION = $task->description;

		if(is_array($task->categories)) {
			$vtodo->CATEGORIES = go()->getDbConnection()->select("name")
				->from("tasks_category")
				->where(['id' => $task->categories])
				->fetchMode(\PDO::FETCH_COLUMN)
				->execute();
		}

		return $vtodo->serialize();
	}
	

	
	protected function exportEntity(Entity $entity, $fp, $index, $total) {
		$str = $this->export($entity);
		fputs($fp, $str);
	}

	protected function importEntity($entityClass, $fp, $index, array $params){
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

			$todo = $VCalendarComponent->VTODO;
			$categories = explode(",",(string)$todo->CATEGORIES);
			$rule = new Recurrence((string)$todo->RRULE, $todo->DTSTART);

			try {
				$task = $this->findTask($VCalendarComponent, $tasklistId);
				// no task found
				if(!$task) {
					$task = new Task();
					$task->setValues([
						"uid" => (string)$todo->UID,
						"tasklistId" => $tasklistId,
						"start" => $todo->DTSTART,
						'recurrenceRule' => $rule->toArray(),
						"due" => $todo->DUE,
						"title" => (string)$todo->SUMMARY,
						"description" => (string)$todo->DESCRIPTION,
						"priority" => (string)$todo->PRIORITY
					]);
					$task->save();

					$categories = (string)$todo->CATEGORIES;
					if(!empty($categories)) {
						$sql = "INSERT INTO tasks_task_category (taskId, categoryId) SELECT $task->id , `id` FROM tasks_category WHERE `name` IN ( $categories )";
						go()->getDbConnection()->query($sql)->execute();
					}
//					$categoryNames = explode(",",$categories);
//					foreach($categoryNames as $categoryName) {
//						$categoryId = (int) go()->getDbConnection()->selectSingleValue('id')->from('tasks_category')->where(['name' => $categoryName])->execute()->fetch();
//						if($categoryId > 0) {
//							$data = ['taskId'=> $task->id, 'categoryId'=>$categoryId];
//							go()->getDbConnection()->insert("tasks_task_category", $data)->execute();
//						}
//					}
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
