<?php

use go\core\fs\File;

function tz_convert($input, $tz) {
	if(!isset($input)) {
		return $input;
	}
	$datetime = new DateTime($input, new DateTimeZone("UTC")); // tz during upgrade is UTC
	if(!empty($tz))
		$datetime->setTimezone(new DateTimeZone($tz));
	return $datetime;
}

// TODO: Remove extension module for old calendar: categoryfilter, 2weekview, caltimetracking, jitsi, defaultcalendaracl, external calendar poll,

$updates['202402221543'][] = function() {
	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/calendar/install/migrate.sql"));
};

$updates['202402221543'][] = function(){ // insert excluded event overrides
	$stmt = go()->getDbConnection()->query("SELECT event_id, FROM_UNIXTIME(time) as recurrenceId, ce.timeZone FROM cal_exceptions e JOIN calendar_event ce ON ce.eventId = e.event_id WHERE exception_event_id=0");
	$insertExcludeStmt = go()->getDbConnection()->getPDO()->prepare("INSERT IGNORE INTO calendar_recurrence_override (fk, recurrenceId, patch) VALUES (?,?,?)");

	while($row = $stmt->fetch()) {
		$id = tz_convert($row['recurrenceId'],$row['timeZone'])->format('Y-m-d H:i:s');
		$insertExcludeStmt->execute([$row['event_id'],$id,'{"excluded":true}']);
	}
};

$updates['202402221543'][] = function(){ // insert event overrides

	$stmt = go()->getDbConnection()->query("SELECT e.id,FROM_UNIXTIME(e.start_time) as start_time, e.end_time, e.name, e.location, e.description, e.status, e.private,e.exception_for_event_id,FROM_UNIXTIME(ex.time) as recurrence_id 
		FROM cal_events e  JOIN cal_exceptions ex ON ex.exception_event_id = e.id WHERE exception_for_event_id != 0 ORDER BY exception_for_event_id;");
	$participantsStmt = go()->getDbConnection()->getPDO()->prepare("SELECT * FROM cal_participants WHERE event_id = ?");
	$mainEventStmt = go()->getDbConnection()->getPDO()->prepare("SELECT id,FROM_UNIXTIME(start_time) as start_time, end_time, name, location, description, status, private, timezone FROM cal_events WHERE id = ?");
	$insertPatchStmt = go()->getDbConnection()->getPDO()->prepare("INSERT IGNORE INTO calendar_recurrence_override (fk, recurrenceId, patch) VALUES (?,?,?)");

	while($row = $stmt->fetch()) { // for each exception
		$mainEventStmt->execute([$row['exception_for_event_id']]);
		$event = $mainEventStmt->fetch(); // grab main event
		if(empty($event))
			continue; // skip
		$diff = array_diff_assoc($row, $event);
		// props: start, end, name, description, location, busy, status, private
		$patch = (object)[];
		if(!empty($diff['start_time'])) {
			$diffStart = tz_convert($diff['start_time'], $event['timezone']);
			$patch->start = $diffStart->format('Y-m-d\TH:i:s');
		}
		if(!empty($diff['end_time'])) {
			$end = new DateTime('@'.$diff['end_time']);
			$start = new DateTime(!empty($diff['start_time']) ? $diff['start_time'] : $row['start_time'], new DateTimeZone("UTC"));
			// start and end are in the same time zone.
			$timeDiff = $end->diff($start);
			$patch->duration = \go\core\util\DateTime::intervalToISO($timeDiff);
		}
		if(!empty($diff['name'])) {
			$patch->title = $diff['name'];
		}
		if(!empty($diff['location'])) {
			$patch->location = $diff['location'];
		}
		if(!empty($diff['description'])) {
			$patch->description = $diff['description'];
		}
//		if(!empty($diff['busy'])) { // wasn't per-user in old database
//			$patch->freeBusyStatus = $diff['busy']==1? 'busy':'free';
//		}
		if(!empty($diff['status'])) {
			$patch->status = strtolower($diff['status']);
		}
		$participantsStmt->execute([$row['id']]);
		$exParticipants = $participantsStmt->fetchAll();
		if(count($exParticipants) > 1) {
			$patch->participants = [];
			foreach($exParticipants as $i => $p) {
				$roles = [];
				if($p['role']=='REQ-PARTICIPANT'){
					$roles['attendee']=true;
				}
				if($p['is_organizer']) {
					$roles['owner'] = true;
				}
				$patch->participants[$i] = (object)[
					'name' => $p['name'],
					'kind' => 'individual',
					'email' => $p['email'],
					'roles' => $roles,
					'participationStatus' => strtolower($p['status'])
				];
			}
		}

		// add patch to calendar_recurrence_override
		$recurrenceId = tz_convert($row['recurrence_id'],$event['timezone'])->format('Y-m-d H:i:s');
		$insertPatchStmt->execute([$event['id'],$recurrenceId, json_encode($patch)]);
	}
};

$updates['202402221543'][] = function(){ // migrate recurrence rules and fix lastOccurrence and firstOccurrence

	$stmt = go()->getDbConnection()->query("SELECT eventId, recurrenceRule,`start`,`timeZone`,`duration` FROM calendar_event WHERE recurrenceRule IS NOT NULL AND recurrenceRule != ''");

	while($row = $stmt->fetch()) {

		if($row['recurrenceRule'][0] == '{')
			continue; // already done

		$start = new DateTime($row["start"]);
		try {
			$rrule = \go\core\util\Recurrence::fromString($row['recurrenceRule'], $start);

			$recurrenceRule = json_encode($rrule->toArray());
			$data = ['recurrenceRule' => $recurrenceRule];
			if(isset($rrule->until)) {
				$data['lastOccurrence'] = clone $rrule->until;
			} else if(isset($rrule->count)) {
				$lastOccurrence = clone $start;
				$it = new \Sabre\VObject\Recur\RRuleIterator($row['recurrenceRule'], $start);
				$maxDate = new \DateTime('2058-01-01');
				while ($it->valid() && $lastOccurrence < $maxDate) {
					$lastOccurrence = $it->current(); // will clone :(
					$it->next();
				}
				$data['lastOccurrence'] = $lastOccurrence;
			} else {
				$data['lastOccurrence'] = null;
			}
			// check max exception vs currentEnd if not null
			if($data['lastOccurrence'] !== null) {
				$lastEx = go()->getDbConnection()->query("SELECT FROM_UNIXTIME(MAX(`start_time`)) FROM cal_events WHERE exception_for_event_id = " . $row['eventId'])->fetchColumn();
				if(!empty($lastEx)) {
					$dt = tz_convert($lastEx, $row['timeZone']);
					if ($dt > $data['lastOccurrence']) {
						$data['lastOccurrence'] = $dt;
					}
				}
				$data['lastOccurrence']->add(new \DateInterval($row['duration']));
			}
			// check min exception vs start
			$firstEx = go()->getDbConnection()->query("SELECT FROM_UNIXTIME(MIN(`start_time`)) FROM cal_events WHERE exception_for_event_id = ".$row['eventId'])->fetchColumn();
			if(!empty($firstEx)) {
				$dt = tz_convert($firstEx, $row['timeZone']);
				if($dt < $start) {
					$data['firstOccurrence'] = $dt;
				}
			}
		} catch(Exception $e) {
			echo "Remove invalid RRULE for event:".$row['eventId'].": " . $e->getMessage() ."\n";
			$data = ['recurrenceRule' => null];
		}

		go()->getDbConnection()->updateIgnore('calendar_event', $data, ['eventId' => $row['eventId']])->execute();

	}
};

//delete empty event folders
$updates['202403121146'][] = "DELETE f FROM fs_folders f
	LEFT JOIN fs_files fi ON fi.folder_id = f.id
	LEFT JOIN fs_folders ff ON ff.parent_id = f.id
	WHERE 
		 fi.folder_id IS NULL 
		 AND ff.parent_id IS NULL
		 AND f.id IN (SELECT files_folder_id FROM cal_events);";

// unset files id of event with folders that no longer exist
$updates['202403121146'][] = "UPDATE cal_events e 
   LEFT JOIN fs_folders f on e.files_folder_Id = f.id
   SET files_folder_id = 0
   WHERE e.files_folder_id != 0 AND f.id IS NULL";


$updates['202403121146'][] = function(){ // migrate files to blob and add as calendar_link
	$pdo = go()->getDbConnection()->getPDO();
	$stmt = go()->getDbConnection()->query("SELECT e.id, FROM_UNIXTIME(e.start_time, '%Y') as year, e.name, cal.name as calendarName, e.files_folder_id FROM cal_events e JOIN cal_calendars cal ON calendar_id = cal.id  WHERE e.files_folder_id != 0;");
	$filesStmt = $pdo->prepare("SELECT id, name FROM fs_files WHERE folder_id = ?");
	$foldersStmt = $pdo->prepare("SELECT id, name FROM fs_folders WHERE parent_id = ?");
	$insertLinkStmt = $pdo->prepare("INSERT INTO calendar_event_link (eventId, title, contentType,size,rel,blobId) VALUES (?, ?, ?, ?, 'enclosure', ?)");
	function buildFilesPath($calendarName, $yearOfStartTime, $title, $id) {

		return 'calendar/' . File::stripInvalidChars($calendarName) . '/' . $yearOfStartTime . '/' . File::stripInvalidChars($title).' ('.$id.')';
	}

	function insertFolder($row, $folderId, $path, PDOStatement $filesStmt,$foldersStmt, $insertLinkStmt) {
		$filesStmt->bindValue(1, $folderId);
		$filesStmt->execute();
		foreach($filesStmt as $fileRow) {
			$file = new File($path . '/' . $fileRow['name']);
			if(!$file->exists()) continue; // skip missing file. TODO: text with files
			$blob = \go\core\fs\Blob::fromFile($file, true);
			if($blob->save()) {
				$insertLinkStmt->execute([$row['id'], $file->getName(), $file->getContentType(), $file->getSize(), $blob->id]);
			}
		}
		$foldersStmt->bindValue(1, $folderId);
		$foldersStmt->execute();
		foreach($foldersStmt as $folderRow) {
			insertFolder($row, $folderRow['id'], $path.'/'.$folderRow['name'], $filesStmt,$foldersStmt, $insertLinkStmt);
		}
	};



	while($row = $stmt->fetch()) {
		$path = GO::config()->file_storage_path.buildFilesPath($row['calendarName'], $row['year'], $row['name'], $row['id']);
		insertFolder($row, $row['files_folder_id'], $path, $filesStmt,$foldersStmt, $insertLinkStmt);
	}

	unset($insertFolder, $filesStmt,$foldersStmt, $insertLinkStmt, $pdo, $stmt);
};



$updates['202404071212'][] = "update core_entity set clientName = 'CalendarCategory' where name = 'Category' and moduleId = (select id from core_module where name ='calendar' and package='community')";



$updates['202404071212'][] = function() {

	// fix timezones
	$stmt = go()->getDbConnection()->query("SELECT eventId, `start`,`timeZone`,`lastOccurrence` FROM calendar_event WHERE timeZone IS NOT null");

	while($row = $stmt->fetch()) {

		try {
			$data = [
				'start' => tz_convert($row['start'], $row['timeZone']),
				'lastOccurrence' => tz_convert($row['lastOccurrence'], $row['timeZone'])
			];

			go()->getDbConnection()->updateIgnore('calendar_event', $data, ['eventId' => $row['eventId']])->execute();
		}catch(Exception $e) {
			echo "Exception: " . $e->getMessage() ."\n";
		}
	}
//	exit();
};

// after timezone conversion make all full day event floating-time
$updates['202404071212'][] = "UPDATE calendar_event SET timeZone = NULL WHERE showWithoutTime = 1;";
// remove orphan categories
$updates['202502261353'][] = "DELETE cat FROM calendar_category cat LEFT JOIN calendar_calendar c on c.id = cat.calendarId WHERE c.id IS NULL;";
// fix: set duration at 1 hour if duration is negative
$updates['202503101510'][] = "UPDATE calendar_event SET duration = 'PT1H' WHERE duration LIKE 'PT-%';";

// TODO: calendar views -> custom filters