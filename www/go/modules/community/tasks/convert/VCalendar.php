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
use go\modules\community\tasks\model\Alert;
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
	 * Parse an Event object to a VObject
	 *
	 *
	 * @param task $task
	 */
	public function export(Task $task) {
		if ($task->vcalendarBlobId) {
			//Contact has a stored VCard
			$blob = Blob::findById($task->vcalendarBlobId);
			$file = $blob->getFile();
			if($file->exists()) {
				$calendar = Reader::read($file->open("r"), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);
				if($blob->modifiedAt >= $task->modifiedAt) {
					return $calendar->serialize();
				}
			}
		}

		if(!isset($calendar)) {
			$calendar = new \Sabre\VObject\Component\VCalendar();
			$vtodo = $calendar->createComponent('VTODO');
			$calendar->add($vtodo);
		}


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

		$vtodo->PRIORITY = $task->priority;

		$vtodo->remove("DTSTART");
		if(!empty($task->start)) {
			$vtodo->add('DTSTART', $task->start, ['VALUE' => 'DATE']);
		}

		$vtodo->remove("DUE");
		if(!empty($task->due)) {
			$vtodo->add('DUE', $task->due, ['VALUE' => 'DATE']);
		}
		$vtodo->DESCRIPTION = $task->description;
		$vtodo->LOCATION = $task->location;

		if(!empty($task->categories) && is_array($task->categories)) {
			$vtodo->CATEGORIES = go()->getDbConnection()->select("name")
				->from("tasks_category")
				->where(['id' => $task->categories])
				->fetchMode(\PDO::FETCH_COLUMN, 0)
				->execute();
		}

		$vtodo->status = strtoupper($task->getProgress());
		$vtodo->remove("COMPLETED");
		if($vtodo->status == "COMPLETED") {
			$vtodo->add('COMPLETED', $task->progressUpdated, ['VALUE' => 'DATE']);
		}

		if(!empty($task->percentComplete)) {
			$vtodo->{"PERCENT-COMPLETE"} = $task->percentComplete;
		} else{
			$vtodo->remove("PERCENT-COMPLETE");
		}

		$vtodo->remove("VALARM");

		if(isset($task->alerts)) {
			foreach ($task->alerts as $alert) {
				$a = $calendar->createComponent('VALARM');

//			BEGIN:VALARM
//ACTION:DISPLAY
//TRIGGER;VALUE=DURATION:-PT5M
//DESCRIPTION:Default Mozilla Description
//END:VALARM

				$trigger = $alert->getTrigger();

				$a->action = 'DISPLAY';
				$a->add('trigger', $trigger['when']->format("Ymd\THis\Z"), array('value' => 'DATE-TIME'));
				$a->description = "Group-Office Alert";

				$vtodo->add($a);
			}
		}

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

	protected function finishExport(): Blob
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

	protected function exportEntity(Entity $entity): bool
	{
		$data = $this->export($entity);
		fputs($this->fp, $data);
	}

	protected function internalExport($fp, $entities, $total) {

		foreach($entities as $entity) {
			fputs($fp, $this->export($entity));
			//$i++;
		}
	}

	public function getFileExtension(): string
	{
		return 'ics';
	}

	private $importSplitter;
	private $currentRecord;
	protected function initImport(File $file) {
		$contents = $file->getContents();
		$this->importSplitter = new VCalendarSplitter(StringUtil::cleanUtf8($contents), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);

	}
	protected function nextImportRecord(): bool
	{
		$this->currentRecord = $this->importSplitter->getNext();
		return $this->currentRecord;
	}
	protected function importEntity() {
		$vcal = $this->currentRecord;
		$tasklistId = $this->clientParams['values']['tasklistId'];

		return $this->vtodoToTask($vcal, $tasklistId, $this->findTask($vcal, $tasklistId));
	}

	private function importPriority($todo) {
		$prio = (string) $todo->PRIORITY;
		if(empty($prio) || $prio == 5) {
			return Task::PRIORITY_NORMAL;
		}

		if($prio > 5) {
			return Task::PRIORITY_LOW;
		}else {
			return Task::PRIORITY_HIGH;
		}
	}

	/**
	 *
	 * BEGIN:VCALENDAR
	CALSCALE:GREGORIAN
	PRODID:-//Apple Inc.//iOS 11.3.1//EN
	VERSION:2.0
	BEGIN:VTIMEZONE
	TZID:Europe/Amsterdam
	BEGIN:DAYLIGHT
	DTSTART:19810329T020000
	RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
	TZNAME:CEST
	TZOFFSETFROM:+0100
	TZOFFSETTO:+0200
	END:DAYLIGHT
	BEGIN:STANDARD
	DTSTART:19961027T030000
	RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
	TZNAME:CET
	TZOFFSETFROM:+0200
	TZOFFSETTO:+0100
	END:STANDARD
	END:VTIMEZONE
	BEGIN:VTODO
	CREATED:20210521T150049Z
	DESCRIPTION:
	DTSTAMP:20210525T083512Z
	DTSTART;TZID=Europe/Amsterdam:20210525T110000
	DUE;TZID=Europe/Amsterdam:20210525T110000
	LAST-MODIFIED:20210525T083510Z
	STATUS:NEEDS-ACTION
	SUMMARY:test 11
	UID:38b7aa9d-c55d-47fb-84c3-7016820b1960
	BEGIN:VALARM
	ACTION:DISPLAY
	DESCRIPTION:Reminder
	TRIGGER;VALUE=DATE-TIME:20210525T090000Z
	UID:FE1FBAB8-3BBC-4699-900D-78FAA53F6D3C
	X-WR-ALARMUID:FE1FBAB8-3BBC-4699-900D-78FAA53F6D3C
	END:VALARM
	BEGIN:VALARM
	ACTION:DISPLAY
	DESCRIPTION:Reminder
	LOCATION:wherever
	TRIGGER;VALUE=DATE-TIME:19760401T005545Z
	UID:35D25525-F9DE-4E38-A777-C75AFB7D8940
	X-APPLE-PROXIMITY:ARRIVE
	X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-ADDRESS=Munteltuinen 50\\n5212 P
	M 's-Hertogenbosch\\nNetherlands;X-APPLE-RADIUS=100;X-APPLE-REFERENCEFRA
	ME=1;X-TITLE=Merijn’s Home:geo:51.691450,5.313180
	X-WR-ALARMUID:35D25525-F9DE-4E38-A777-C75AFB7D8940
	END:VALARM
	END:VTODO
	END:VCALENDAR
	 *
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
			"location" => (string) $todo->LOCATION,
			"priority" => $this->importPriority($todo),
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

		if($todo->valarm && $todo->valarm->trigger){
			$date = $todo->valarm->getEffectiveTriggerTime();
			$alert = new Alert();
			$alert->when($date);
			$task->alerts = [$alert];
		}

		return $task;
	}

	public static function supportedExtensions(): array
	{
		return ['ics'];
	}
}
