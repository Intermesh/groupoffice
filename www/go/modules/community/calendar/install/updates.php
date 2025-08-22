<?php

use go\core\fs\File;

if (!function_exists('tz_convert')) {
	function tz_convert($input, $tz) {
		if(!isset($input)) {
			return $input;
		}
		$datetime = new DateTime($input, new DateTimeZone("UTC")); // tz during upgrade is UTC
		if(!empty($tz))
			$datetime->setTimezone(new DateTimeZone($tz));
		return $datetime;
	}
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
			$participants = [];
			foreach($exParticipants as $i => $p) {
				$roles = [];
				if($p['role']=='REQ-PARTICIPANT'){
					$roles['attendee']=true;
				}
				if($p['is_organizer']) {
					$roles['owner'] = true;
				}
				$participants[$p['user_id'] ? $p['user_id'] : $p['id']] = (object)[
					'name' => $p['name'],
					'kind' => 'individual',
					'email' => $p['email'],
					'roles' => $roles,
					'participationStatus' => strtolower($p['status'])
				];
			}

			$patch->participants = (object) $participants;
		}

		// add patch to calendar_recurrence_override
		$recurrenceId = tz_convert($row['recurrence_id'],$event['timezone'])->format('Y-m-d H:i:s');
		$insertPatchStmt->execute([$event['id'],$recurrenceId, json_encode($patch)]);
	}
};

$updates['202402221543'][] = function(){ // migrate recurrence rules and fix lastOccurrence and firstOccurrence

	$stmt = go()->getDbConnection()->query("SELECT eventId, recurrenceRule,`start`,`timeZone`,`duration`,`showWithoutTime` FROM calendar_event WHERE recurrenceRule IS NOT NULL AND recurrenceRule != ''");

	while($row = $stmt->fetch()) {

		if($row['recurrenceRule'][0] == '{')
			continue; // already done
		$tz = $row['timeZone'] ? new DateTimeZone($row['timeZone']) : null;


		$start = new DateTime($row["start"], $tz);
		try {
			$rrule = \go\core\util\Recurrence::fromString($row['recurrenceRule'], $start);

			$recurrenceRule = json_encode($rrule->toArray($row['showWithoutTime']));
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
$updates['202403121146'][] = function() {
	if(go()->getModule(null, "files")) {
		go()->getDbConnection()->exec("DELETE f FROM fs_folders f
	LEFT JOIN fs_files fi ON fi.folder_id = f.id
	LEFT JOIN fs_folders ff ON ff.parent_id = f.id
	WHERE 
		 fi.folder_id IS NULL 
		 AND ff.parent_id IS NULL
		 AND f.id IN (SELECT files_folder_id FROM cal_events);");

		go()->getDbConnection()->exec("UPDATE cal_events e 
   LEFT JOIN fs_folders f on e.files_folder_Id = f.id
   SET files_folder_id = 0
   WHERE e.files_folder_id != 0 AND f.id IS NULL");


		$pdo = go()->getDbConnection()->getPDO();
		$stmt = go()->getDbConnection()->query("SELECT e.id, FROM_UNIXTIME(e.start_time, '%Y') as year, e.name, cal.name as calendarName, e.files_folder_id FROM cal_events e JOIN cal_calendars cal ON calendar_id = cal.id  WHERE e.files_folder_id != 0;");
		$filesStmt = $pdo->prepare("SELECT id, name FROM fs_files WHERE folder_id = ?");
		$foldersStmt = $pdo->prepare("SELECT id, name FROM fs_folders WHERE parent_id = ?");
		$insertLinkStmt = $pdo->prepare("INSERT INTO calendar_event_link (eventId, title, contentType,size,rel,blobId) VALUES (?, ?, ?, ?, 'enclosure', ?)");
		function buildFilesPath($calendarName, $yearOfStartTime, $title, $id)
		{

			return 'calendar/' . File::stripInvalidChars($calendarName) . '/' . $yearOfStartTime . '/' . File::stripInvalidChars($title) . ' (' . $id . ')';
		}

		function insertFolder($row, $folderId, $path, PDOStatement $filesStmt, $foldersStmt, $insertLinkStmt)
		{
			$filesStmt->bindValue(1, $folderId);
			$filesStmt->execute();
			foreach ($filesStmt as $fileRow) {
				$file = new File($path . '/' . $fileRow['name']);
				if (!$file->exists()) continue; // skip missing file. TODO: text with files
				$blob = \go\core\fs\Blob::fromFile($file, true);
				if ($blob->save()) {
					$insertLinkStmt->execute([$row['id'], $file->getName(), $file->getContentType(), $file->getSize(), $blob->id]);
				}
			}
			$foldersStmt->bindValue(1, $folderId);
			$foldersStmt->execute();
			foreach ($foldersStmt as $folderRow) {
				insertFolder($row, $folderRow['id'], $path . '/' . $folderRow['name'], $filesStmt, $foldersStmt, $insertLinkStmt);
			}
		}

		;

		while ($row = $stmt->fetch()) {
			$path = GO::config()->file_storage_path . buildFilesPath($row['calendarName'], $row['year'], $row['name'], $row['id']);
			insertFolder($row, $row['files_folder_id'], $path, $filesStmt, $foldersStmt, $insertLinkStmt);
		}

		unset($insertFolder, $filesStmt, $foldersStmt, $insertLinkStmt, $pdo, $stmt);
	}
};

// unset files id of event with folders that no longer exist
$updates['202403121146'][] = ""; // empty by reason
$updates['202403121146'][] = "";


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
$updates['202502261353'][] = "DELETE cat FROM calendar_category cat LEFT JOIN calendar_calendar c on c.id = cat.calendarId WHERE c.id IS NOT NULL;";
// fix: set duration at 1 hour if duration is negative
$updates['202503101510'][] = "UPDATE calendar_event SET duration = 'PT1H' WHERE duration LIKE 'PT-%';";
$updates['202503111342'][] = function(){
	\go\modules\community\calendar\cron\ScanEmailForInvites::install("*/5 * * * *", true);
};

$updates['202503131043'][] = "UPDATE IGNORE core_link l
	JOIN core_entity et ON et.id = l.fromEntityTypeId
	JOIN calendar_calendar_event e on e.eventId = l.fromId AND et.name = 'CalendarEvent'
	SET l.fromId = e.id;";

$updates['202503131043'][] = "UPDATE IGNORE core_link l
	JOIN core_entity et ON et.id = l.toEntityTypeId
	JOIN calendar_calendar_event e on e.eventId = l.toId AND et.name = 'CalendarEvent'
	SET l.toId = e.id;";
// new migrations will update the alerts. Others will have dismissed those in the last 3 months.
$updates['202504070955'][] = "UPDATE IGNORE core_alert a
	JOIN core_entity et ON et.id = a.entityTypeId
	JOIN calendar_calendar_event e on e.eventId = a.entityId AND et.name = 'CalendarEvent'
	SET a.entityId = e.id;";

$updates['202504071345'][] = "ALTER TABLE `calendar_event` CHANGE COLUMN `location` `location` TEXT NULL;";
// replace existing resource into core_participants
$updates['202504150919'][] = 'REPLACE INTO core_principal (id, name, email, type, description, timeZone, entityTypeId, entityId, aclId)
SELECT concat("Calendar:", c.id), c.name, u.email, "resource", IFNULL(c.description, ""), c.timeZone, (select id from core_entity where name="Calendar"),  c.id, c.aclId from calendar_calendar c
  JOIN calendar_resource_group rg ON c.groupId = rg.id
  JOIN core_user u ON u.id = rg.defaultOwnerId 
  WHERE c.groupId IS NOT NULL AND rg.defaultOwnerId IS NOT NULL;';
// set the participant id to the user id if the participant is an existing groupoffice user
$updates["202504150959"][] = "UPDATE calendar_participant p INNER JOIN core_user u ON u.email = p.email COLLATE utf8mb4_unicode_ci AND p.id != u.id SET p.id = u.id WHERE kind = 'individual';";

$updates["202505011057"][] = "DELETE `a`
FROM `calendar_calendar_event` AS `a`, `calendar_calendar_event` AS `b`
WHERE `a`.`id` < `b`.`id`
    AND `a`.`eventId` <=> `b`.`eventId`
    AND `a`.`calendarId` <=> `b`.`calendarId`;";
$updates["202505011057"][] = "ALTER TABLE `calendar_calendar_event` ADD UNIQUE INDEX `event_once_per_calendar` (`eventId` ASC, `calendarId` ASC);";

$updates["202505061137"][] = "ALTER TABLE `calendar_event` ADD INDEX `fk_calendar_event_uid_index` (`uid` ASC);";
$updates["202505071158"][] = "ALTER TABLE `calendar_calendar` ADD COLUMN `webcalUri` VARCHAR(512) NULL DEFAULT NULL AFTER `timeZone`;";

$updates["202506061051"][] = "alter table calendar_calendar_user
    alter column includeInAvailability set default 'none';";


$updates["202506121207"][] = "alter table `calendar_preferences` add column showTooltips	TINYINT(1)  DEFAULT 1 NOT NULL AFTER holidaysAreVisible;";


$updates['202506130832'][] = "CREATE TABLE IF NOT EXISTS calendar_schedule_object (
                                   id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                   principaluri VARBINARY(255),
                                   calendardata MEDIUMBLOB,
                                   uri VARBINARY(200),
                                   lastmodified INT(11) UNSIGNED,
                                   etag VARBINARY(32),
                                   size INT(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$updates["202507221653"][] = "alter table `calendar_preferences` add column weekViewGridSize	INT DEFAULT 8 NOT NULL AFTER weekViewGridSnap;";

$updates["202508111058"][] = "update calendar_event set uri = REPLACE(REPLACE(REPLACE(uri, '/', '_'), '+', '-'), '=', '.');";

$updates["202508191124"][] = "alter table calendar_calendar_user add column syncToDevice tinyint default 1 not null after `timeZone`";

$updates["202508211118"][] = "update ignore calendar_participant p inner join core_user u on u.email = p.email set p.id = u.id;";
$updates["202508211118"][] = "update calendar_preferences set defaultCalendarId=null where defaultCalendarId not in (select id from calendar_calendar);";

$updates["202508211118"][] = "alter table calendar_preferences
    add constraint calendar_preferences_calendar_calendar_id_fk
        foreign key (defaultCalendarId) references calendar_calendar (id)
            on delete set null;";



// TODO: calendar views -> custom filters
