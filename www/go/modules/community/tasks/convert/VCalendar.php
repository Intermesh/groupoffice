<?php
namespace go\modules\community\tasks\convert;

use Exception;
use go\core\data\convert\AbstractConverter;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\orm\Entity;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\tasks\model\Category;
use go\modules\community\tasks\model\Progress;
use go\modules\community\tasks\model\Recurrence;
use go\modules\community\tasks\model\Task;
use Sabre\VObject\Component\VCalendar as VCalendarComponent;
use Sabre\VObject\Component\VCard as VCardComponent;
use Sabre\VObject\Component\VTimeZone;
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

	public function __construct()
	{
		parent::__construct('ics', Task::class);
	}
	
	const EMPTY_NAME = '(no name)';


	/**
	 *
	 * @param Task $task
	 * @return VCalendar
	 */

	private function getVCalendar(Task $task) {

		if ($task->vcalendarBlobId) {
			//Contact has a stored VCard
			$blob = Blob::findById($task->vcalendarBlobId);
			$file = $blob->getFile();
			if($file->exists()) {
				$vcalendar = Reader::read($file->open("r"), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);

				return $vcalendar;
			}
		}

		$calendar = new \Sabre\VObject\Component\VCalendar();
		$vtodo = $calendar->createComponent('VTODO');
		$calendar->add($vtodo);

		return $calendar;
	}

	/**
	 * Parse an Event object to a VObject
	 * @param task $task
	 */
	public function export(Task $task) {
		$calendar = $this->getVCalendar($task);
		$vtodo = $calendar->vtodo;

		$vtodo->dtstamp = new DateTime('now', new \DateTimeZone('utc'));
		$vtodo->{"last-modified"} = $task->modifiedAt;
		$vtodo->CREATED = $task->createdAt;

		$rule = $task->getRecurrenceRule();

		if($rule && !empty($task->start)) {
			$rrule = Recurrence::fromArray((array)$rule, $task->start);
			$vtodo->RRULE = $rrule->toString();
		}

		$vtodo->UID = $task->getUid();
		$vtodo->SUMMARY = $task->title;
//		$vtodo->PRIORITY = $task->priority;
		$vtodo->remove("PRIORITY");
		if(!empty($task->start)) {
			$vtodo->add('DTSTART', $task->start, ['VALUE' => 'DATE']);
		}else {
			$vtodo->remove("DTSTART");
		}
		if(!empty($task->due)) {
			$vtodo->add('DUE', $task->due, ['VALUE' => 'DATE']);
		} else{
			$vtodo->remove("DUE");
		}
		$vtodo->DESCRIPTION = $task->description;

		if(!empty($task->categories) && is_array($task->categories)) {
			$vtodo->CATEGORIES = go()->getDbConnection()->select("name")
				->from("tasks_category")
				->where(['id' => $task->categories])
				->fetchMode(\PDO::FETCH_COLUMN, 0)
				->execute();
		}

		$vtodo->status = strtoupper($task->getProgress());
		if($vtodo->status == "COMPLETED") {
			$vtodo->add('COMPLETED', $task->progressUpdated, ['VALUE' => 'DATE']);
		} else{
			$vtodo->remove("COMPLETED");
		}

		if(!empty($task->percentComplete)) {
			$vtodo->{"PERCENT-COMPLETE"} = $task->percentComplete;
		} else{
			$vtodo->remove("PERCENT-COMPLETE");
		}

		//MS:: TODO VALARM

		//todo export needs one vcalendar but breaks caldav
		return $calendar->serialize();
//		$calendar->add($vtodo);
//		return $calendar->serialize();
	}

	private $tempFile;
	private $fp;
	protected function initExport()
	{
		$this->tempFile = File::tempFile($this->getFileExtension());
		$this->fp = $this->tempFile->open('w+');
		fputs($this->fp, "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Intermesh//NONSGML Group-Office ".go()->getVersion()."//EN\r\n");
		fputs($this->fp, (new \GO\Base\VObject\VTimezone())->serialize());
	}

	protected function finishExport()
	{
		fputs($this->fp, "END:VCALENDAR\r\n");

		$cls = $this->entityClass;
		$blob = Blob::fromTmp($this->tempFile);
		$blob->name = $cls::entityType()->getName() . "-" . date('Y-m-d-H:i:s') . '.'. $this->getFileExtension();
		if(!$blob->save()) {
			throw new Exception("Couldn't save blob: " . var_export($blob->getValidationErrors(), true));
		}

		return $blob;
	}

	protected function exportEntity(Entity $entity) {
		$data = $this->export($entity);
		fputs($this->fp, $data);
	}

	protected function internalExport($fp, $entities, $total) {

		foreach($entities as $entity) {
			fputs($fp, $this->export($entity));
			//$i++;
		}
	}

	public function getFileExtension() {
		return 'ics';
	}

	private $importSplitter;
	private $currentRecord;
	protected function initImport(File $file) {
		$contents = $file->getContents();
		$this->importSplitter = new VCalendarSplitter(StringUtil::cleanUtf8($contents), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);

	}
	protected function nextImportRecord() {
		$this->currentRecord = $this->importSplitter->getNext();
		return $this->currentRecord;
	}
	protected function importEntity() {
		$vcal = $this->currentRecord;
		$tasklistId = $this->clientParams['values']['tasklistId'];

		return $this->vtodoToTask($vcal, $tasklistId, $this->findTask($vcal, $tasklistId));
	}

	/**
	 * @param VCalendarComponent $todo
	 * @param int $tasklistId
	 * @param Task|null $task pass task to update
	 * @return Task
	 * @throws \Sabre\VObject\InvalidDataException
	 */
	public function vtodoToTask(VCalendarComponent $vcal, $tasklistId, $task = null) {
		$todo = $vcal->VTODO;
		$categoryIds = Category::find()->selectSingleValue('id')
			->where('name', 'IN', explode(",",(string)$todo->CATEGORIES))
			->all();
		if(!empty($todo->RRULE) && !empty($todo->DTSTART))
			$rule = (new Recurrence((string)$todo->RRULE, $todo->DTSTART->getDateTime()))->toArray();
		else
			$rule = null;
		if($task === null) {
			$task = new Task();
		}

		if($todo->{"last-modified"}) {
			$modifiedAt = $todo->{"last-modified"}->getDateTime();
		}else if($vcal->dtstamp) {
			$modifiedAt = $todo->dtstamp->getDateTime();
		} else {
			$modifiedAt = new DateTime();
		}


		$blob = Blob::fromString($vcal->serialize());
		$blob->type = 'text/vcalendar';
		$blob->name = ($vcal->uid ?? 'nouid' ) . '.ics';
		$blob->modifiedAt = $modifiedAt;
		if(!$blob->save()) {
			throw new \Exception("could not save VCalendar blob");
		}

		// no task found
		$task->setValues([
			"uid" => (string) $todo->UID,
			"tasklistId" => (int) $tasklistId, //int is important
			"start" => $todo->DTSTART,
			"modifiedAt" => $modifiedAt,
			'recurrenceRule' => $rule,
			"due" => $todo->DUE,
			"title" => (string) $todo->SUMMARY,
			"description" => (string) $todo->DESCRIPTION,
			"priority" => !empty((string) $todo->PRIORITY) ? intval((string) $todo->PRIORITY) : 5, // default normal
			"categories" => $categoryIds,
			"vcalendarBlobId" => $blob->id,
		]);

		if($todo->STATUS) {
			$task->setProgress(strtolower($todo->STATUS));
		}

		if($todo->duration){
			//TODO test
//			$duration = \GO\Base\VObject\Reader::parseDuration($vcal->duration);
			$task->due = clone $task->start;
			$task->due->add(new \DateInterval($todo->duration));
		}

		if($todo->completed) {
			$task->setProgress("completed");
			$task->progressUpdated = $todo->completed->getDateTime();
		}

		if(!empty($todo->{"percent-complete"})) {
			$task->percentComplete = (int) $todo->{"percent-complete"}->getValue();
		}

//		TODO alarms
//		if($vobject->valarm && $vobject->valarm->trigger){
//			$date = $vobject->valarm->getEffectiveTriggerTime();
//			if($date) {
//				$this->reminder = $date->format('U');
//			}
//		}


		return $task;
	}

	public static function supportedExtensions()
	{
		return ['ics'];
	}
}
