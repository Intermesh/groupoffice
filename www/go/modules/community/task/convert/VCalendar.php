<?php
namespace go\modules\community\task\convert;

use Exception;
use GO;
use go\core\data\convert\AbstractConverter;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\orm\Entity;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\modules\community\task\model\Task;
use go\core\model\Link;
use Sabre\VObject\Component\VCalendar as VCalendarComponent;
use Sabre\VObject\Component\VTodo;
use Sabre\VObject\Reader;
use Sabre\VObject\Splitter\VCalendar as VCalendarSplitter;

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
	 * 
	 * @param array $prop
	 * @param \Sabre\VObject\Property  $VCalendarProp
	 * @param string $cls
	 * @param function $fn
	 * @return \go\modules\community\task\convert\cls
	 */
	private function importHasMany(array $prop, $VCalendarProp, $cls, $fn) {

		if (isset($VCalendarProp)) {		
			foreach ($VCalendarProp as $index => $value) {
				if (!isset($prop[$index])) {
					$prop[$index] = new $cls;
				}

				$prop[$index]->type = $this->convertType($value['TYPE']);
				$v = call_user_func($fn, $value);
				$prop[$index]->setValues($v);
			}
			$index++;
		}else
		{
			$index = 0;
		}
		
		
		$c = count($prop);
		if ($c > $index) {
			array_splice($prop, $index, $c - $index);
		}
		
		return $prop;
	}

	private function importDate(task $task, $type, $date) {
			
		$bday = $task->findDateByType($type, false);

		if (!empty($date)) {
			if (!$bday) {
				$bday = new Date();
				$bday->type = $type;
				$task->dates[] = $bday;
			}
			$bday->date = new DateTime((string) $date);
		} else {
			if ($bday) {
				$task->dates = array_filter($task->dates, function($d) use($bday) {
					$d !== $bday;
				});
			}
		}
		
	}

	/**
	 * Parse a VObject to an task object
	 * @param VCalendarComponent $VCalendarComponent
	 * @param task $entity
	 * @return task[]
	 */
	public function import(Task $task) {
		$t = "test";
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

	private function importPhoto(task $entity, VCalendarComponent $VCalendarComponent) {
		$VCalendarComponent = isset($VCalendarComponent->PHOTO) ? $VCalendarComponent->PHOTO->getValue() : null;
		if ($VCalendarComponent) {
			$blob = Blob::fromString($VCalendarComponent);
			$blob->type = 'image/jpeg';
			$blob->name = $entity->getUid() . '.jpg';
			if ($blob->save()) {
				$entity->photoBlobId = $blob->id;
			}
		} else {
			$entity->photoBlobId = null;
		}
	}

	private function getVCalendarOrganizations($VCalendar) {
		$VCalendarOrganizationNames = [];
		if(isset($VCalendar->ORG)) {
			foreach ($VCalendar->ORG as $org) {
				$VCalendarOrganizationNames = array_merge($VCalendarOrganizationNames, $this->splitOrganizationName((string) $org->getParts()[0]));
			}
		}
		
		return $VCalendarOrganizationNames;
	}
	
	/**
	 * Because iOS (or more?) clients only support one "ORG" element allthough
	 * the spec says cardinality *. We put multiple organizations in this format:
	 * 
	 * [1] Company A [2] Company B
	 * 
	 * We detect this syntax on import.
	 * 
	 * @param type $name
	 * @return type
	 */
	private function splitOrganizationName($name) {
		if(preg_match_all('/\[[0-9]+] ([^\[]*)/', $name, $matches)){
			return array_map('trim', $matches[1]);
		}
		
		return [$name];
	}
	
	private function importOrganizations(task $task, $VCalendar) {		
		
		$VCalendarOrganizationNames = $this->getVCalendarOrganizations($VCalendar);
		
		go()->debug($VCalendarOrganizationNames);

		//compare with existing.
		$goOrganizations = $task->isNew() ? [] : task::find()
										->withLink($task)
										->andWhere('isOrganization', '=', true)
										->all();

		$goOrganizationsNames = [];
		foreach ($goOrganizations as $o) {
			if (!in_array($o->name, $VCalendarOrganizationNames)) {
				Link::deleteLink($o, $task);
			} else {
				$goOrganizationsNames[] = $o->name;
			}
		}
		
		go()->debug($goOrganizationsNames);

		$newVCalendarOrgNames = array_diff($VCalendarOrganizationNames, $goOrganizationsNames);
		foreach ($newVCalendarOrgNames as $name) {
			$org = task::find()->where(['isOrganization' => true])->andWhere('name', 'LIKE', $name)->single();
			if (!$org) {
				go()->debug("Create org: " . $name);
				$org = new task();
				$org->name = $name;
				$org->isOrganization = true;
				$org->taskId = $task->taskId;
				if (!$org->save()) {
					throw new Exception("Could not save organization");
				}
			}
			
			go()->debug("Link org: " . $org->name);
			$link = Link::create($task, $org);
			if (!$link) {
				throw new Exception("Could not link organization");
			}
		}
	}

	private static function convertType($VCalendarType) {
		$types = explode(',', strtolower((string) $VCalendarType));
		foreach($types as $type) {
			
			//skip internet type.
			if($type != 'internet') {
				return $type;
			}
		}
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
			$values['taskId'] = go()->getAuthState()->getUser(['taskSettings'])->taskSettings->defaulttaskId;
		}

		$splitter = new VCalendarSplitter(StringUtil::cleanUtf8($file->getContents()), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);

		while ($VCalendarComponent = $splitter->getNext()) {			
			try {
				$task = $this->findOrCreatetask($VCalendarComponent, $values['taskId']);
				$task->setValues($values);
				$this->import($VCalendarComponent, $task);
				$response['ids'][] = $task->id;
			}
			catch(\Exception $e) {
				ErrorHandler::logException($e);
				$response['errors'][] = "Failed to import card: ". $e->getMessage();
			}			
			$response['count']++;
		}

		return $response;
	}
	
	/**
	 * 
	 * @param VCalendarComponent $VCalendarComponent
	 * @param int $taskId
	 * @return task
	 */
	private function findOrCreatetask(VCalendarComponent $VCalendarComponent, $taskId) {
		$task = false;
			if(isset($VCalendarComponent->uid)) {
				$task = task::find()->where(['taskId' => $taskId, 'uid' => (string) $VCalendarComponent->uid])->single();
			}
			
			if(!$task) {
				$task = new task();				
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
