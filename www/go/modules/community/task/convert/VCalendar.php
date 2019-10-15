<?php
namespace go\modules\community\task\convert;

use Exception;
use GO;
use go\core\data\convert\AbstractConverter;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\orm\Entity;
use go\core\util\StringUtil;
use go\modules\community\task\model\Task;
use Sabre\VObject\Component\VCalendar as VCalendarComponent;
use Sabre\VObject\Reader;
use Sabre\VObject\Splitter\ICalendar as VCalendarSplitter;

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
			
			//remove all supported properties
			// $VCalendar->remove('EMAIL');
			// $VCalendar->remove('TEL');
			// $VCalendar->remove('ADR');
			// $VCalendar->remove('ORG');
			// $VCalendar->remove('PHOTO');
			// $VCalendar->remove('BDAY');
			// $VCalendar->remove('ANNIVERSARY');
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

		$vtodo->UID = $task->getUid();
		$vtodo->STATUS = $task->status;
		$vtodo->PRIORITY = $task->priority;
		$vtodo->CATEGORIES = $task->categories;
		$vtodo->SUMMARY = $task->title;
		$vtodo->START = $task->start;
		$vtodo->DUE = $task->due;
		$vtodo->DESCRIPTION = $task->description;
		$vtodo->RECURRENCERULE = $task->getRrule();
		$vtodo->CREATED = $task->createdAt;
//-----------------------------------------------------
		$vtodo->TASKLISTID = $task->tasklistId;
		// $vtodo->CREATEDBY = $task->createdBy;
		// $vtodo->CREATEDAT = $task->createdAt;
		// $vtodo->TITLE = $task->title;
		// $vtodo->DESCRIPTION = $task->description;
		// $vtodo->STATUS = $task->status;
		// $vtodo->RECURRENCERULE = $task->getRrule();
		return $vtodo->serialize();
	}
	

	
	protected function exportEntity(Entity $entity, $fp, $index, $total) {
		$str = $this->export($entity);
		fputs($fp, $str);
	}

	protected function importEntity(Entity $entity, $fp, $index, array $params) {
		$t = "";
	}
	

	protected function internalExport($fp, $entities, $total) {

		$this->vcalendar =  new VCalendarComponent();
		$this->vcalendar->LANGUAGE = go()->getSettings()->language;
		$this->vcalendar->PRODID = '-//Intermesh//NONSGML Group-Office ' . go()->getVersion() . '//EN';

		fputs($fp, "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Intermesh//NONSGML Group-Office ".go()->getVersion()."//EN\r\n");

		//TODO move to new framework
		$t = new \GO\Base\VObject\VTimezone();
		fputs($fp, $t->serialize());

		$i = 0;
		foreach($entities as $entity) {
			$this->exportEntity($entity, $fp, $i, $total);
			$i++;
		}

		fputs($fp, "END:VCALENDAR\r\n");
	}

	/**
	 * Parse a VObject to an task object
	 * @param VCalendarComponent $VCalendarComponent
	 * @param task $entity
	 * @return task[]
	 */
	public function import(VCalendarComponent $VCalendarComponent, Task $task) {
		$vtodo = $this->getVTodo($task);
		$t = "";
		// if ($VCalendarComponent->VERSION != "3.0") {
		// 	$VCalendarComponent->convert(\Sabre\VObject\Document::VCalendar30);
		// }
		
		// if (!isset($entity)) {
		// 	$entity = new task();
		// }
		
		// if(!$entity->hasUid() && isset($VCalendarComponent->uid)) {
		// 	$entity->setUid((string) $VCalendarComponent->uid);
		// }

		// if(isset($VCalendarComponent->{"X_GO-GENDER"})) {
		// 	$gender = (string) $VCalendarComponent->{"X_GO-GENDER"};
		// 	switch ($gender) {
		// 		case 'M':
		// 		case 'F':
		// 			$entity->gender = $gender;
		// 			break;
		// 		default:
		// 			$entity->gender = null;
		// 	}
		// }

		// if(isset($VCalendarComponent->{"X-ABShowAs"})) {
		// 	$entity->isOrganization = $VCalendarComponent->{"X-ABShowAs"} == "COMPANY";
		// }
		
		// if(isset($VCalendarComponent->{"X-GO-IS-ORGANIZATION"})) {
		// 	$entity->isOrganization = !empty($VCalendarComponent->{"X-GO-IS-ORGANIZATION"});
		// }

		// $n = $VCalendarComponent->N->getParts();
		// $entity->lastName = $n[0] ?? null;
		// $entity->firstName = $n[1] ?? null;
		// $entity->middleName = $n[2] ?? null;
		// $entity->prefixes = $n[3] ?? null;
		// $entity->suffixes = $n[4] ?? null;
		// $entity->name = (string) $VCalendarComponent->FN ?? self::EMPTY_NAME;

		// $this->importDate($entity, Date::TYPE_BIRTHDAY, $VCalendarComponent->BDAY);
		// $this->importDate($entity, Date::TYPE_ANNIVERSARY, $VCalendarComponent->ANNIVERSARY);

		// empty($VCalendarComponent->NOTE) ?: $entity->notes = (string) $VCalendarComponent->NOTE;
		// $entity->emailAddresses = $this->importHasMany($entity->emailAddresses, $VCalendarComponent->EMAIL, EmailAddress::class, function($value) {
		// 	return ['email' => (string) $value];
		// });

		// $entity->phoneNumbers = $this->importHasMany($entity->phoneNumbers, $VCalendarComponent->TEL, PhoneNumber::class, function($value) {
		// 	return ['number' => (string) $value];
		// });

		// $entity->addresses = $this->importHasMany($entity->addresses, $VCalendarComponent->ADR, Address::class, function($value) {
		// 	$a = $value->getParts();
		// 	$addr = [];
			
		// 	//iOS Accepts street2 but sends back {street}\n{street2} in the street value :(
		// 	if(empty($a[1])){
		// 		$parts = explode("\n", $a[2]);
		// 		if(count($parts) > 1) {
		// 			$a[1] = array_pop($parts);
		// 			$a[2] = implode("\n", $parts);
		// 		}
		// 	}			
			
		// 	$addr['street2'] = $a[1] ?? null;
		// 	$addr['street'] = $a[2] ?? null;
		// 	$addr['city'] = $a[3] ?? null;
		// 	$addr['state'] = $a[4] ?? null;
		// 	$addr['zipCode'] = $a[5] ?? null;
		// 	$addr['country'] = $a[6] ?? null;
		// 	return $addr;
		// });

		// $this->importPhoto($entity, $VCalendarComponent);

		// if (!$entity->save()) {
		// 	throw new Exception("Could not save task");
		// }

		// if(!$entity->isOrganization) {
		// 	$this->importOrganizations($entity, $VCalendarComponent);
		// }
		
		// return $entity;
	}

	public function getFileExtension() {
		return 'vcf';
	}

	public function importFile(File $file, $entityClass, $params = []) {

		$response = [
				'ids' => [],
				'errors' => [],
				'count' => 0
		];
		
		$values = $params['values'] ?? [];
		
		if(!isset($values['taskId'])) {
			$values['taskId'] = go()->getAuthState()->getUser(['taskSettings'])->taskSettings->default_tasklist_id;
		}

		$contents = $file->getContents();
		
		$splitter = new VCalendarSplitter(StringUtil::cleanUtf8($file->getContents()), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);

		while ($VCalendarComponent = $splitter->getNext()) {
			
			$vtodo = $VCalendarComponent->VTODO;
			$dtstamp = $VCalendarComponent->VTODO->DTSTAMP;
			$uid = $VCalendarComponent->VTODO->UID;
			$status = $VCalendarComponent->VTODO->STATUS;
			$priority = $VCalendarComponent->VTODO->PRIORITY;
			$categories = $VCalendarComponent->VTODO->CATEGORIES;
			$summary = $VCalendarComponent->VTODO->SUMMARY;
			$start = $VCalendarComponent->VTODO->START;
			$due = $VCalendarComponent->VTODO->DUE;
			$description = $VCalendarComponent->VTODO->DESCRIPTION;
			$recurrenceRule = $VCalendarComponent->VTODO->RECURRENCERULE;
			$created = $VCalendarComponent->VTODO->CREATED;
			$taskListId = $VCalendarComponent->VTODO->tasklistid;
			$response['errors'][] = "test " . $categories;
			$t = "";
			// try {
			// 	$task = $this->findOrCreatetask($VCalendarComponent, $values['taskId']);
			// 	$task->setValues($values);
			// 	$this->import($VCalendarComponent, $task);
			// 	$response['ids'][] = $task->id;
			// }
			// catch(\Exception $e) {
			// 	ErrorHandler::logException($e);
			// 	$response['errors'][] = "Failed to import vcalendar: ". $e->getMessage();
			// }			
			// $response['count']++;
		}

		return $response;
	}
	
	/**
	 * 
	 * @param VCalendarComponent $VCalendarComponent
	 * @param int $taskId
	 * @return Task
	 */
	private function findOrCreatetask(VCalendarComponent $VCalendarComponent, $taskId) {
			$task = false;

			if(isset($VCalendarComponent->VTODO->uid)) {
				$task = Task::find()->where(['tasklistId' => $taskId, 'uid' => (string) $VCalendarComponent->VTODO->uid])->single();
			}
			
			if(!$task) {
				$task = new Task();				
			}
			
			//Serialize data to store VCalendar
			$blob = $this->saveBlob($VCalendarComponent);			
			$task->VCalendarBlobId = $blob->id;
			
			return $task;
	}
	/**
	 * 
	 * @param VCalendarComponent $VCalendarComponent
	 * @return Blob
	 * @throws Exception
	 */
	private function saveBlob(VCalendarComponent $VCalendarComponent){
		$blob = Blob::fromString($VCalendarComponent->serialize());
		$blob->type = 'text/VCalendar';
		$blob->name = ($VCalendarComponent->uid ?? 'nouid' ) . '.vcf';
		if(!$blob->save()) {
			throw new \Exception("could not save VCalendar blob");
		}
		
		return $blob;
	}

}
