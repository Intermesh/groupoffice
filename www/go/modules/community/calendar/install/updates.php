<?php

use go\core\fs\File;
use go\modules\community\calendar\cron\ScanEmailForInvites;
use go\modules\community\calendar\model\ICalendarHelper;

// TODO: Remove extension module for old calendar: categoryfilter, 2weekview, caltimetracking, jitsi, defaultcalendaracl, external calendar poll,

$updates['202402221543'][] = function() {
	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/calendar/install/migrate.sql"));
};

$updates['202402221543'][] = function() {
	// fix timezones
	$stmt = go()->getDbConnection()->query("SELECT eventId, `start`,`timeZone`,`lastOccurrence` FROM calendar_event WHERE calendar_event.showWithoutTime=0 AND timeZone IS NOT null");
	function tz_convert($input, $tz) {
		$datetime = new DateTime($input); // tz during upgrade is UTC
		$datetime->setTimezone(new DateTimeZone($tz));
		return $datetime;
	}
	while($row = $stmt->fetch()) {

		$data = [
			'start' => tz_convert($row['start'], $row['timeZone']),
			'lastOccurrence' => tz_convert($row['lastOccurrence'], $row['timeZone'])
		];
		go()->getDbConnection()->updateIgnore('calendar_event', $data, ['eventId' => $row['eventId']])->execute();
	}
};

$updates['202402221543'][] = function(){ // migrate recurrence rules and fix lastOccurrence

	$stmt = go()->getDbConnection()->query("SELECT eventId, recurrenceRule,`start`,`timeZone`,`duration` FROM calendar_event WHERE recurrenceRule IS NOT NULL AND recurrenceRule != ''");

	while($row = $stmt->fetch()) {

			if($row['recurrenceRule'][0] == '{') continue; // already done
			$start = new DateTime($row["start"]);
			try {
				$rrule = \go\core\util\Recurrence::fromString($row['recurrenceRule'], $start);
			} catch(Exception $e) {
				die("RRULE Exception: " . $e->getMessage() ."\n");
			}
			$recurrenceRule = json_encode($rrule->toArray());
			$data = ['recurrenceRule' => $recurrenceRule];
			if($rrule->isInfinite()) {
				$data['lastOccurrence'] = null;
			} else if(isset($rrule->until)) {
				$data['lastOccurrence'] = $rrule->until->add(new \DateInterval($row['duration']));
			} else if(isset($rrule->count)) {
				$lastOccurrence = (clone $start)->add(new \DateInterval($row['duration']));
				$it = new \Sabre\VObject\Recur\RRuleIterator($row['recurrenceRule'], $start);
				$maxDate = new \DateTime('2058-01-01');
				while ($it->valid() && $lastOccurrence < $maxDate) {
					$lastOccurrence = $it->current(); // will clone :(
					$it->next();
				}
				$lastOccurrence->add(new \DateInterval($row["duration"]));
				$data['lastOccurrence'] = $lastOccurrence;
			}
			go()->getDbConnection()->updateIgnore('calendar_event', $data, ['eventId' => $row['eventId']])->execute();

	}
};


$updates['202402221543'][] = function(){ // insert event overrides (excludes are already migrated in migrate.sql

	$stmt = go()->getDbConnection()->query("SELECT e.id,e.start_time, e.end_time, e.name, e.location, e.description, e.status, e.private,e.exception_for_event_id,FROM_UNIXTIME(ex.time) as recurrence_id 
		FROM cal_events e  JOIN cal_exceptions ex ON ex.exception_event_id = e.id WHERE exception_for_event_id != 0 ORDER BY exception_for_event_id;");
	$participantsStmt = go()->getDbConnection()->getPDO()->prepare("SELECT * FROM cal_participants WHERE event_id = ?");
	$mainEventStmt = go()->getDbConnection()->getPDO()->prepare("SELECT id,start_time, end_time, name, location, description, status, private FROM cal_events WHERE id = ?");
	$insertPatchStmt = go()->getDbConnection()->getPDO()->prepare("INSERT IGNORE INTO calendar_recurrence_override (fk, recurrenceId, patch) VALUES (?,?,?)");

	while($row = $stmt->fetch()) {
		$mainEventStmt->execute([$row['exception_for_event_id']]);
		$event = $mainEventStmt->fetch();
		if(empty($event))
			continue; // skip
		$diff = array_diff_assoc($row, $event);
		// props: start, end, name, description, location, busy, status, private
		$patch = (object)[];
		if(!empty($diff['start_time'])) {
			$patch->start = (new DateTime('@'.$diff['start_time']))->format('Y-m-d\TH:i:s');
		}
		if(!empty($diff['end_time'])) {
			$end = new DateTime('@'.$diff['end_time']);
			$start = new DateTime('@'.$row['start_time']);
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
		$insertPatchStmt->execute([$event['id'],$row['recurrence_id'],json_encode($patch)]);
	}
};

$updates['202403121146'][] = function(){ // migrate files to blob and add as calendar_link
	$pdo = go()->getDbConnection()->getPDO();
	$stmt = go()->getDbConnection()->query("SELECT e.id, FROM_UNIXTIME(e.start_time, '%Y') as year, e.name, cal.name as calendarName, e.files_folder_id FROM cal_events e JOIN cal_calendars cal ON calendar_id = cal.id  WHERE e.files_folder_id != 0;");
	$filesStmt = $pdo->prepare("SELECT id, name FROM fs_files WHERE folder_id = ?");
	$foldersStmt = $pdo->prepare("SELECT id, name FROM fs_folders WHERE parent_id = ?");
	$insertLinkStmt = $pdo->prepare("INSERT INTO calendar_event_link (eventId, title, contentType,size,rel,blobId) VALUES (?, ?, ?, ?, 'enclosure', ?)");
	function buildFilesPath($calendarName, $yearOfStartTime, $title, $id) {

		return 'calendar/' . File::stripInvalidChars($calendarName) . '/' . $yearOfStartTime . '/' . File::stripInvalidChars($title).' ('.$id.')';
	}

	$insertFolder = function($row, $folderId, $path) use ($filesStmt,$foldersStmt, $insertLinkStmt, &$insertFolder) {
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
			$insertFolder($row, $folderRow, $path.'/'.$folderRow['name']);
		}
	};

	while($row = $stmt->fetch()) {
		$path = GO::config()->file_storage_path.buildFilesPath($row['calendarName'], $row['year'], $row['name'], $row['id']);
		$insertFolder($row, $row['files_folder_id'], $path);
	}
};


$updates['202404071212'][] = "update core_entity set clientName = 'CalendarCategory' where name = 'Category' and moduleId = (select id from core_module where name ='calendar' and package='community')";


// TODO: calendar views -> custom filters