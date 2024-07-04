CREATE TABLE IF NOT EXISTS  calendar_resource_group (
	id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name           VARCHAR(200) NULL,
	description    MEDIUMTEXT NULL,
	defaultOwnerId INT NOT NULL,
	createdBy 		 INT NULL,
	CONSTRAINT calendar_resource_group_core_user_id_fk FOREIGN KEY (defaultOwnerId)
		REFERENCES core_user (id),
	CONSTRAINT `fk_calendar_resource_group_core_user_creator` FOREIGN KEY (createdBy)
		REFERENCES `core_user` (id) ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `calendar_resource_group_admin` (
	`groupId` int UNSIGNED NOT NULL,
	`userId` int(11) NOT NULL,
	PRIMARY KEY (`groupId`,`userId`),
	CONSTRAINT `fk_calendar_resource_group_admin_resource_group1`
		FOREIGN KEY (`groupId`)
			REFERENCES `calendar_resource_group` (`id`)
			ON DELETE CASCADE
			ON UPDATE NO ACTION,
	CONSTRAINT `fk_calendar_resource_group_admin_core_user`
		FOREIGN KEY (`userId`)
			REFERENCES `core_user` (`id`)
			ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `calendar_calendar` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(80) NOT NULL,
	`description` TEXT NULL,
	`defaultColor` VARCHAR(21) NOT NULL, # lightgoldenrodyellow
	`timeZone` VARCHAR(45) NULL,
	`groupId` INT UNSIGNED NULL,
	`aclId` INT NOT NULL,
	`createdBy` INT NULL,
	`ownerId` INT NULL,
	`highestItemModSeq` VARCHAR(32) NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	INDEX `fk_calendar_calendar_calendar_resource_group_idx` (`groupId` ASC),
	CONSTRAINT `fk_calendar_calendar_calendar_resource_group`
		FOREIGN KEY (`groupId`)
			REFERENCES `calendar_resource_group` (`id`)
			ON DELETE RESTRICT,
	CONSTRAINT `fk_calendar_calendar_core_acl1`
		FOREIGN KEY (`aclId`)
			REFERENCES `core_acl` (`id`),
	CONSTRAINT `fk_calendar_calendar_core_user_creator`
		FOREIGN KEY (`createdBy`)
			REFERENCES `core_user` (`id`)
			ON DELETE SET NULL,
	CONSTRAINT `fk_calendar_calendar_core_user_owner`
		FOREIGN KEY (`ownerId`)
			REFERENCES `core_user` (`id`)
			ON DELETE CASCADE
) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `calendar_calendar_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_calendar_user` (
	`id` INT UNSIGNED NOT NULL,
	`userId` INT NOT NULL,
	`isSubscribed` TINYINT(1) NOT NULL DEFAULT 0,
	`isVisible` TINYINT(1) NOT NULL DEFAULT 0,
	`color` VARCHAR(21) NOT NULL,
	`sortOrder` INT NOT NULL DEFAULT 0,
	`timeZone` VARCHAR(45) NULL,
	`includeInAvailability` ENUM('all', 'attending', 'none') NOT NULL,
	`modSeq` INT NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`, `userId`),
	CONSTRAINT `fk_calendar_calendar_user_calendar_calendar1`
		FOREIGN KEY (`id`)
			REFERENCES `calendar_calendar` (`id`)
			ON DELETE CASCADE
			ON UPDATE NO ACTION,
	CONSTRAINT `fk_calendar_calendar_user_core_user1`
		FOREIGN KEY (`userId`)
			REFERENCES `core_user` (`id`)
			ON DELETE CASCADE
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `calendar_default_alert`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_default_alert` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`offset` VARCHAR(20) NULL,
	`relativeTo` ENUM('start', 'end') NOT NULL DEFAULT 'start',
	`when` DATE NULL,
	`calendarId` INT UNSIGNED NOT NULL,
	`userId` INT NOT NULL,
	PRIMARY KEY (`id`, `calendarId`, `userId`),
	INDEX `fk_calendar_default_alert_calendar_calendar1_idx` (`calendarId` ASC, `userId` ASC),
	CONSTRAINT `fk_calendar_default_alert_calendar_calendar1`
		FOREIGN KEY (`calendarId`, `userId`)
			REFERENCES `calendar_calendar_user` (`id` , `userId`)
			ON DELETE CASCADE
			ON UPDATE CASCADE)
	ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `calendar_default_alert_with_time`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_default_alert_with_time` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`offset` VARCHAR(20) NULL,
	`relativeTo` ENUM('start', 'end') NOT NULL DEFAULT 'start',
	`when` DATETIME NULL,
	`calendarId` INT UNSIGNED NOT NULL,
	`userId` INT NOT NULL,
	PRIMARY KEY (`id`, `calendarId`, `userId`),
	INDEX `fk_calendar_default_alert_with_time_calendar_calendar1_idx` (`calendarId` ASC, `userId` ASC),
	CONSTRAINT `fk_calendar_default_alert_with_time_calendar_calendar1`
		FOREIGN KEY (`calendarId` , `userId`)
			REFERENCES `calendar_calendar_user` (`id` , `userId`)
			ON DELETE CASCADE
			ON UPDATE CASCADE)
	ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `calendar_event` (
	`eventId` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`prodId` VARCHAR(100) NOT NULL DEFAULT 'Unknown',
	`uid` VARCHAR(255) NOT NULL,
	`sequence` INT UNSIGNED NOT NULL DEFAULT 1,
	`title` VARCHAR(255) NOT NULL,
	`description` TEXT NULL,
	`location` VARCHAR(255) NOT NULL DEFAULT '',
	`locale` VARCHAR(6) NULL,
	`showWithoutTime` TINYINT(1) NOT NULL DEFAULT 0,
	`start` DATETIME NOT NULL COMMENT '@dbType=localdatetime',
	`timeZone` VARCHAR(45) NULL,
	`duration` VARCHAR(32) NOT NULL,
	`priority` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	`privacy` ENUM('public', 'private', 'secret') NOT NULL DEFAULT 'public',
	`status` ENUM('confirmed', 'cancelled', 'tentative') NOT NULL DEFAULT 'confirmed',
	`recurrenceRule` TEXT NULL DEFAULT NULL,
	`lastOccurrence` DATETIME NULL DEFAULT NULL COMMENT '@dbType=localdatetime',
	`createdAt` DATETIME NULL,
	`modifiedAt` DATETIME NULL,
	`createdBy` INT NULL,
	`modifiedBy` INT NULL,
	`isOrigin` TINYINT(1) NOT NULL DEFAULT 1,
	`replyTo` VARCHAR(100),
	`requestStatus` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`eventId`),
	INDEX `calendar_event_lastOccurrence_index` (`lastOccurrence` ASC),
	INDEX `calendar_event_start_index` (`start` ASC),
	INDEX `fk_calendar_event_core_user1_idx` (`createdBy` ASC),
	INDEX `fk_calendar_event_core_user2_idx` (`modifiedBy` ASC),
	CONSTRAINT `fk_calendar_event_core_user1`
		FOREIGN KEY (`createdBy`)
			REFERENCES `core_user` (`id`)
			ON DELETE SET NULL,
	CONSTRAINT `fk_calendar_event_core_user2`
		FOREIGN KEY (`modifiedBy`)
			REFERENCES `core_user` (`id`)
			ON DELETE SET NULL)
	ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `calendar_calendar_event` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`eventId` INT UNSIGNED NOT NULL,
	`calendarId` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `fk_calendar_calendar_event_calendar_event1_idx` (`eventId` ASC),
	INDEX `fk_calendar_calendar_event_calendar_calendar1_idx` (`calendarId` ASC),
	CONSTRAINT `fk_calendar_calendar_event_calendar_event1`
		FOREIGN KEY (`eventId`)
			REFERENCES `calendar_event` (`eventId`)
			ON DELETE RESTRICT
			ON UPDATE NO ACTION,
	CONSTRAINT `fk_calendar_calendar_event_calendar_calendar1`
		FOREIGN KEY (`calendarId`)
			REFERENCES `calendar_calendar` (`id`)
			ON DELETE CASCADE
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `calendar_participant`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_participant` (
	`id` VARCHAR(128) NOT NULL,
	`eventId` INT UNSIGNED NOT NULL,
	`name` VARCHAR(100) NULL,
	`email` VARCHAR(128) NOT NULL,
	`kind` ENUM('individual', 'group', 'location', 'resource') NOT NULL,
	`rolesMask` INT NOT NULL DEFAULT 0,
	`language` VARCHAR(20),
	`participationStatus` ENUM('needs-action', 'tentative', 'accepted', 'declined', 'delegated') NULL DEFAULT 'needs-action',
	`scheduleAgent` ENUM('server', 'client', 'none') DEFAULT 'server',
	`expectReply` TINYINT(1) NOT NULL DEFAULT 0,
	`scheduleUpdated` DATETIME NULL,
	`scheduleStatus` varchar(255) DEFAULT NULL,
	`scheduleSecret` CHAR(16) COLLATE ascii_bin NULL,
	PRIMARY KEY (`id`, `eventId`),
	INDEX `fk_participant_calendar_event1_idx` (`eventId` ASC),
	CONSTRAINT `fk_participant_calendar_event1`
		FOREIGN KEY (`eventId`)
			REFERENCES `calendar_event` (`eventId`)
			ON DELETE CASCADE
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `calendar_event_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_event_user` (
	`eventId` INT UNSIGNED NOT NULL,
	`userId` INT NOT NULL,
	`freeBusyStatus` ENUM('free', 'busy') NULL DEFAULT 'busy',
	`color` VARCHAR(21) NULL DEFAULT NULL,
	`useDefaultAlerts` TINYINT(1) NULL DEFAULT 1,
	`veventBlobId` BINARY(40) NULL,
	`modSeq` INT NOT NULL DEFAULT 0,
	PRIMARY KEY (`eventId`, `userId`),
	INDEX `fk_calendar_event_user_calendar_event1_idx` (`eventId` ASC),
	INDEX `fk_calendar_event_user_core_user1_idx` (`userId` ASC),
	INDEX `fk_calendar_event_user_core_blob1_idx` (`veventBlobId` ASC),
	CONSTRAINT `fk_calendar_event_user_calendar_event1`
		FOREIGN KEY (`eventId`)
			REFERENCES `calendar_event` (`eventId`)
			ON DELETE CASCADE,
	CONSTRAINT `fk_calendar_event_user_core_user1`
		FOREIGN KEY (`userId`)
			REFERENCES `core_user` (`id`)
			ON DELETE CASCADE,
	CONSTRAINT `fk_calendar_event_user_core_blob1`
		FOREIGN KEY (`veventBlobId`)
			REFERENCES `core_blob` (`id`)
			ON DELETE RESTRICT
			ON UPDATE NO ACTION
) ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `calendar_event_alert`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_event_alert` (
	`id` INT UNSIGNED NOT NULL,
	`offset` VARCHAR(20) NULL,
	`relativeTo` ENUM('start', 'end') NULL DEFAULT 'start',
	`when` DATETIME NULL,
	`eventId` INT UNSIGNED NOT NULL,
	`userId` INT NOT NULL,
	PRIMARY KEY (`id`, `eventId`, `userId`),
	INDEX `fk_calendar_event_alert_calendar_event_user1_idx` (`eventId` ASC, `userId` ASC),
	CONSTRAINT `fk_calendar_event_alert_calendar_event_user1`
		FOREIGN KEY (`eventId` , `userId`)
			REFERENCES `calendar_event_user` (`eventId` , `userId`)
			ON DELETE cascade
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;
-- -----------------------------------------------------
-- Table `calendar_recurrence_override`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_recurrence_override` (
	`fk` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`recurrenceId` DATETIME NOT NULL COMMENT '@dbType=localdatetime',
	`patch` MEDIUMTEXT NOT NULL DEFAULT '{}',
	PRIMARY KEY (`fk`, `recurrenceId`),
	INDEX `fk_recurrence_override_calendar_event1_idx` (`fk` ASC),
	CONSTRAINT `fk_recurrence_override_calendar_event1`
		FOREIGN KEY (`fk`)
			REFERENCES `calendar_event` (`eventId`)
			ON DELETE CASCADE
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `calendar_event_related`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_event_related` (
	`type` INT NOT NULL,
	`uid` VARCHAR(100) NOT NULL,
	`eventId` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`type`, `uid`),
	INDEX `fk_calendar_event_related_calendar_event1_idx` (`eventId` ASC),
	CONSTRAINT `fk_calendar_event_related_calendar_event1`
		FOREIGN KEY (`eventId`)
			REFERENCES `calendar_event` (`eventId`)
			ON DELETE CASCADE
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `calendar_event_link`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_event_link` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`eventId` INT UNSIGNED NOT NULL,
	`href` VARCHAR(100) NULL,
	`title` VARCHAR(200) NULL,
	`contentType` VARCHAR(129) NOT NULL,
	`size` INT NOT NULL,
	`rel` VARCHAR(50) NOT NULL,
	`blobId` BINARY(40) NULL,
	PRIMARY KEY (`id`, `eventId`),
	INDEX `fk_event_attachment_calendar_event1_idx` (`eventId` ASC),
	INDEX `fk_calendar_event_link_core_blob1_idx` (`blobId` ASC),
	CONSTRAINT `fk_event_attachment_calendar_event1`
		FOREIGN KEY (`eventId`)
			REFERENCES `calendar_event` (`eventId`)
			ON DELETE CASCADE
			ON UPDATE NO ACTION,
	CONSTRAINT `fk_calendar_event_link_core_blob1`
		FOREIGN KEY (`blobId`)
			REFERENCES `core_blob` (`id`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `calendar_event_location`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_event_location` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`eventId` INT UNSIGNED NOT NULL,
	`name` VARCHAR(100) NOT NULL,
	`description` TEXT NULL,
	`relativeTo` INT NULL,
	`latitude` DECIMAL(10,8) NULL,
	`longitude` DECIMAL(11,8) NULL,
	PRIMARY KEY (`id`, `eventId`),
	INDEX `fk_event_location_calendar_event1_idx` (`eventId` ASC),
	CONSTRAINT `fk_event_location_calendar_event1`
		FOREIGN KEY (`eventId`)
			REFERENCES `calendar_event` (`eventId`)
			ON DELETE CASCADE
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `calendar_category` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`color` VARCHAR(21) NULL DEFAULT NULL,
	`ownerId` INT(11) NULL,
	`calendarId` INT(11) UNSIGNED NULL,
	PRIMARY KEY (`id`),
	INDEX `user_id` (`ownerId` ASC),
	constraint calendar_category_ibfk_1
		foreign key (ownerId) references core_user (id)
			on delete set null,
	constraint calendar_category_calendar_ibfk_9
		foreign key (calendarId) references calendar_calendar (id)
			on delete cascade)
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `calendar_event_category` (
	`eventId` INT(11) UNSIGNED NOT NULL,
	`categoryId` INT(11) UNSIGNED NOT NULL,
	PRIMARY KEY (`eventId`, `categoryId`),
	INDEX `calendar_task_category_ibfk_2` (`categoryId` ASC),
	CONSTRAINT `calendar_task_category_ibfk_1`
		FOREIGN KEY (`eventId`)
			REFERENCES `calendar_event` (`eventId`)
			ON DELETE CASCADE,
	CONSTRAINT `calendar_event_category_ibfk_2`
		FOREIGN KEY (`categoryId`)
			REFERENCES `calendar_category` (`id`)
			ON DELETE CASCADE)
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS calendar_preferences (
	userId                INT NOT NULL PRIMARY KEY,
	weekViewGridSnap      INT NULL,
	defaultDuration       VARCHAR(32) NULL,
	autoUpdateInvitations TINYINT(1) DEFAULT 0 NOT NULL,
	autoAddInvitations    TINYINT(1) DEFAULT 0 NOT NULL,
	markReadAndFileAutoAdd TINYINT(1) DEFAULT 0 NOT NULL,
	markReadAndFileAutoUpdate TINYINT(1) DEFAULT 0 NOT NULL,
	lastProcessed					VARCHAR(11) DEFAULT '' NOT NULL,
	lastProcessedUid		  BIGINT DEFAULT 1 NOT NULL,
	showDeclined          TINYINT(1) DEFAULT 1 NOT NULL,
	showWeekNumbers				TINYINT(1) DEFAULT 1 NOT NULL,
	birthdaysAreVisible   TINYINT(1) DEFAULT 0 NOT NULL,
	tasksAreVisible       TINYINT(1) DEFAULT 0 NOT NULL,
	holidaysAreVisible    TINYINT(1)  DEFAULT 0 NOT NULL,
	defaultCalendarId     INT UNSIGNED NULL,
	startView             ENUM ('week', 'month', 'year', 'list') DEFAULT 'month' NULL,
	CONSTRAINT calendar_preferences_core_user_id_fk FOREIGN KEY (userId)
		REFERENCES core_user (id) ON DELETE CASCADE
) COLLATE = utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `calendar_event_custom_fields` (
	`id` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `fk_calendar_event_cf1` FOREIGN KEY (`id`) REFERENCES `calendar_event` (`eventId`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `calendar_calendar_custom_fields` (
	`id` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `fk_calendar_cf1` FOREIGN KEY (`id`) REFERENCES `calendar_calendar` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- missing: (fields, show_not_as_busy)
INSERT INTO calendar_resource_group
	(id, name,description, defaultOwnerId, createdBy) SELECT
   id, name, '',ga.user_id, g.user_id FROM cal_groups g JOIN cal_group_admins ga ON ga.group_id = g.id
   WHERE g.id != 1
GROUP by g.id;

-- concat 18  random hex colors and select 1 with calendar.id % 18 * hexlength if null
INSERT INTO calendar_calendar
	(id,		name, description, defaultColor, timeZone, groupId, aclId,createdBy,ownerId) SELECT
   cal.id, name, comment,IFNULL(cu.color, SUBSTRING('#CDAD00#E74C3C#9B59B6#8E44AD#2980B9#3498DB#1ABC9C#16A085#27AE60#2ECC71#F1C40F#F39C12#E67E22#D35400#95A5A6#34495E#808B96#1652a1', (cal.id MOD 18) * 7 + 2 ,6)), null, 	IF(group_id=1,null,group_id), acl_id, 1,	cal.user_id FROM
  cal_calendars cal LEFT JOIN cal_calendar_user_colors cu ON cal.id = cu.calendar_id GROUP BY cal.id;

-- subscribe to own calendar
INSERT IGNORE INTO calendar_calendar_user
(id, userId, isSubscribed, isVisible, color, sortOrder, timeZone, includeInAvailability, modSeq) SELECT
	cal.id, user_id, 1, 1, IFNULL(background,SUBSTRING('#CDAD00#E74C3C#9B59B6#8E44AD#2980B9#3498DB#1ABC9C#16A085#27AE60#2ECC71#F1C40F#F39C12#E67E22#D35400#95A5A6#34495E#808B96#1652a1', (cal.id MOD 18) * 7 + 2 ,6)), 0, null, 'all', 1 FROM cal_calendars cal JOIN core_user u ON cal.user_id = u.id where group_id=1 ;

-- subscribe every user who added a color to 1 of the visible calendars
INSERT IGNORE INTO calendar_calendar_user
	(id, userId, isSubscribed, isVisible, color, sortOrder, timeZone, includeInAvailability, modSeq) SELECT
 	 id, cu.user_id, 1, 0, color, 1, null, IF(cal.user_id=0, 'attending', 'all'), 1 FROM
 	cal_calendar_user_colors cu JOIN cal_calendars cal ON cal.id = cu.calendar_id;


INSERT IGNORE INTO calendar_default_alert_with_time
  (`offset`, relativeTo, calendarId, userId) SELECT
  CONCAT('PT',reminder,'S'), 'start', calendar_id, user_id FROM cal_settings WHERE calendar_id != '0' AND reminder IS NOT NULL;

INSERT INTO calendar_category
	(id, name, color, ownerId, calendarId) SELECT
	 id, name, color, null, calendar_id FROM cal_categories;

-- insert instance that belongs to the organizer
INSERT INTO calendar_event
	(eventId, prodId, uid, sequence, title, description, location, showWithoutTime, start, timeZone, duration, priority,
	 privacy,status,recurrenceRule,lastOccurrence,createdAt,modifiedAt, createdBy, modifiedBy, isOrigin, replyTo, requestStatus) SELECT
	id, 'Group-Office', uuid, 1, name, description, location, all_day_event, FROM_UNIXTIME(start_time), timezone, CONCAT('PT',end_time-start_time,'S'), 0,
	IF(private=1, 'private', 'public'), LOWER(status), IF(rrule='',null,rrule), FROM_UNIXTIME(end_time), FROM_UNIXTIME(ctime), FROM_UNIXTIME(mtime), user_id, muser_id, 1, '',''
FROM cal_events WHERE exception_for_event_id = 0 AND is_organizer = 1 GROUP BY uuid;

-- insert the events that have no organizer
INSERT INTO calendar_event
(eventId, prodId, uid, sequence, title, description, location, showWithoutTime, start, timeZone, duration, priority,
 privacy,status,recurrenceRule,lastOccurrence,createdAt,modifiedAt, createdBy, modifiedBy, isOrigin, replyTo, requestStatus) SELECT
 id, 'Group-Office', uuid, 1, name, description, location, all_day_event, FROM_UNIXTIME(start_time), timezone, CONCAT('PT',end_time-start_time,'S'), 0,
 IF(private=1, 'private', 'public'), LOWER(status), IF(rrule='',null,rrule), FROM_UNIXTIME(end_time), FROM_UNIXTIME(ctime), FROM_UNIXTIME(mtime), user_id, muser_id, 0, '',''
FROM cal_events WHERE exception_for_event_id = 0 GROUP BY uuid HAVING SUM(is_organizer) = 0;

INSERT INTO calendar_calendar_event
(id, eventId, calendarId) SELECT null, id, calendar_id FROM cal_events WHERE exception_for_event_id = 0;

-- TODO: should we bother with past events?
INSERT INTO calendar_event_alert
	(id, `offset`, relativeTo, eventId, userId) SELECT
	1, CONCAT('PT',reminder,'S'), 'start', id, user_id FROM cal_events WHERE exception_for_event_id = 0 AND reminder IS NOT NULL;

INSERT INTO calendar_event_category
	(eventId, categoryId) SELECT
	id, category_id FROM cal_events old JOIN calendar_event new ON new.eventId = old.id where category_id > 0 GROUP BY new.uid, old.category_id ;

INSERT INTO calendar_event_custom_fields SELECT * FROM cal_events_custom_fields;

-- calendar_event_location and calendar_event_related are unused for now.

INSERT INTO calendar_event_user
	(eventId, userId, freeBusyStatus, color, useDefaultAlerts, veventBlobId, modSeq) SELECT
	e.id, e.user_id, if(e.busy=1,'busy', 'free'), e.background, IF(e.reminder=0, 1, 0), null, 1 FROM cal_events e
		JOIN calendar_event ce ON ce.eventId = e.id JOIN core_user u ON e.user_id = u.id  WHERE e.exception_for_event_id = 0;
-- skip per-user properties of events without organizer

INSERT INTO calendar_participant
	(id, eventId, name, email, kind, rolesMask, participationStatus, scheduleAgent, expectReply, scheduleUpdated) SELECT
	id, event_id, name, email, 'individual', IF(role='REQ-PARTICIPANT',2,0)+is_organizer, LOWER(p.status),'server',1,FROM_UNIXTIME(IF(last_modified='',0, last_modified)) FROM cal_participants p JOIN calendar_event ce ON ce.eventId = event_id;


-- TODO: Is RolesMask right for resource participants?
INSERT INTO calendar_participant
(id, eventId, name, email, kind, rolesMask, participationStatus, scheduleAgent, expectReply, scheduleUpdated) SELECT
CONCAT('Calendar:',c.id), r.id as eventId, c.name,u.email , 'individual', 0 ,IF(e.status = 'CONFIRMED', 'accepted', LOWER(e.status)), 'server',1,FROM_UNIXTIME(IF(e.mtime='',0, e.mtime))FROM cal_events e
	 JOIN cal_calendars c ON e.calendar_id = c.id
	 JOIN core_user u ON c.user_id = u.id
	 JOIN cal_events r ON e.resource_event_id = r.id
WHERE c.group_id != 1 AND e.resource_event_id IS NOT NULL;

INSERT IGNORE INTO calendar_recurrence_override
	(fk, recurrenceId, patch) SELECT
	event_id,FROM_UNIXTIME(time), '{"excluded":true}' FROM cal_exceptions e JOIN calendar_event ce ON ce.eventId = e.event_id WHERE exception_event_id=0;

INSERT INTO calendar_preferences (userId, weekViewGridSnap, defaultCalendarId) SELECT user_id, 15, calendar_id FROM cal_settings s JOIN core_user u ON u.id = s.user_id;