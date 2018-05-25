<?php
class BackendGoConfig {

	const GOBACKENDVERSION = 406;
	
	const CONTACTBACKENDID = 'c';
	const TASKSBACKENDID = 't';
	const NOTESBACKENDID = 'n';
	const CALENDARBACKENDID = 'a';
	const MAILBACKENDID = 'm';

	const TASKSBACKENDFOLDER = "GroupOfficeTasks"; // TODO: This maybe needs to be "c/GroupOfficeTasks" NEED TO TEST!!
	const CONTACTBACKENDFOLDER = "GroupOfficeContacts"; // TODO: This maybe needs to be "c/GroupOfficeContacts" NEED TO TEST!!
	const CALENDARBACKENDFOLDER = "GroupOfficeCalendar"; // TODO: This maybe needs to be "c/GroupOfficeCalendar" NEED TO TEST!!
	const NOTESBACKENDFOLDER = "GroupOfficeNotes"; // TODO: This maybe needs to be "c/GroupOfficeNotes" NEED TO TEST!!
	const MAILBACKENDFOLDER = "GroupOfficeMail"; // TODO: This maybe needs to be "c/GroupOfficeMail" NEED TO TEST!!

	public static $ContactBackendConfig = array();
	public static $TaskbackendConfig = array();
	public static $NoteBackendConfig = array();
	public static $CalendarBackendConfig = array();
	public static $MailBackendConfig = array();

	public static function GetBackendGoConfig() {
		return array(
			'backends' => array(
				self::CONTACTBACKENDID => array('name' => 'goContact','config' => self::$ContactBackendConfig),
				self::TASKSBACKENDID => array('name' => 'goTask','config' => self::$TaskbackendConfig),
				self::NOTESBACKENDID => array('name' => 'goNote','config' => self::$NoteBackendConfig),
				self::CALENDARBACKENDID => array('name' => 'goCalendar','config' => self::$CalendarBackendConfig),
				self::MAILBACKENDID => array('name' => 'goMail','config' => self::$MailBackendConfig),
			),
			'delimiter' => '/',
			'folderbackend' => array(
				SYNC_FOLDER_TYPE_INBOX => self::MAILBACKENDID,
				SYNC_FOLDER_TYPE_DRAFTS => self::MAILBACKENDID,
				SYNC_FOLDER_TYPE_WASTEBASKET => self::MAILBACKENDID,
				SYNC_FOLDER_TYPE_SENTMAIL => self::MAILBACKENDID,
				SYNC_FOLDER_TYPE_OUTBOX => self::MAILBACKENDID,
				SYNC_FOLDER_TYPE_TASK => self::TASKSBACKENDID,
				SYNC_FOLDER_TYPE_APPOINTMENT => self::CALENDARBACKENDID,
				SYNC_FOLDER_TYPE_CONTACT => self::CONTACTBACKENDID,
				SYNC_FOLDER_TYPE_NOTE => self::NOTESBACKENDID,
//			SYNC_FOLDER_TYPE_JOURNAL => 'z',
//      SYNC_FOLDER_TYPE_OTHER => 'i',
				SYNC_FOLDER_TYPE_USER_MAIL => self::MAILBACKENDID,
//				SYNC_FOLDER_TYPE_USER_APPOINTMENT => self::CALENDARBACKENDID,
//				SYNC_FOLDER_TYPE_USER_CONTACT => self::CONTACTBACKENDID,
//				SYNC_FOLDER_TYPE_USER_TASK => self::TASKSBACKENDID,
//      SYNC_FOLDER_TYPE_USER_JOURNAL => 'z',
//				SYNC_FOLDER_TYPE_USER_NOTE => self::NOTESBACKENDID,
//      SYNC_FOLDER_TYPE_UNKNOWN => 'z',
			),
			'rootcreatefolderbackend' => self::MAILBACKENDID,
		);
	}
}
