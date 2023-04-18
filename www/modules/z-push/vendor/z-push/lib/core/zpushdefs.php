<?php
/***********************************************
* File      :   zpushdefs.php
* Project   :   Z-Push
* Descr     :   Constants' definition file
*
* Created   :   01.10.2007
*
* Copyright 2007 - 2016 Zarafa Deutschland GmbH
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License, version 3,
* as published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* Consult LICENSE file for details
************************************************/

// Code Page 0: AirSync, all AS versions
const SYNC_SYNCHRONIZE = "Synchronize";
const SYNC_REPLIES = "Replies";
const SYNC_ADD = "Add";
const SYNC_MODIFY = "Modify";
const SYNC_REMOVE = "Remove";
const SYNC_FETCH = "Fetch";
const SYNC_SYNCKEY = "SyncKey";
const SYNC_CLIENTENTRYID = "ClientEntryId";
const SYNC_SERVERENTRYID = "ServerEntryId";
const SYNC_STATUS = "Status";
const SYNC_FOLDER = "Folder";
const SYNC_FOLDERTYPE = "FolderType";
const SYNC_VERSION = "Version"; // deprecated
const SYNC_FOLDERID = "FolderId";
const SYNC_GETCHANGES = "GetChanges";
const SYNC_MOREAVAILABLE = "MoreAvailable";
const SYNC_WINDOWSIZE = "WindowSize"; //MaxItems before z-push 2
const SYNC_PERFORM = "Perform";
const SYNC_OPTIONS = "Options";
const SYNC_FILTERTYPE = "FilterType";
const SYNC_TRUNCATION = "Truncation"; // 2.5
const SYNC_RTFTRUNCATION = "RtfTruncation"; // 2.5
const SYNC_CONFLICT = "Conflict";
const SYNC_FOLDERS = "Folders";
const SYNC_DATA = "Data";
const SYNC_DELETESASMOVES = "DeletesAsMoves";
const SYNC_NOTIFYGUID = "NotifyGUID";
const SYNC_SUPPORTED = "Supported";
const SYNC_SOFTDELETE = "SoftDelete";
const SYNC_MIMESUPPORT = "MIMESupport";
const SYNC_MIMETRUNCATION = "MIMETruncation";
const SYNC_NEWMESSAGE = "NewMessage";
const SYNC_WAIT = "Wait"; // Since 12.1
const SYNC_LIMIT = "Limit"; // Since 12.1
const SYNC_PARTIAL = "Partial"; // Since 12.1
const SYNC_CONVERSATIONMODE = "ConversationMode"; // Since 14.0
const SYNC_MAXITEMS = "MaxItems"; // Since 14.0
const SYNC_HEARTBEATINTERVAL = "HeartbeatInterval"; // Since 14.0

// Code Page 1: Contacts - POOMCONTACTS, all AS versions
const SYNC_POOMCONTACTS_ANNIVERSARY = "POOMCONTACTS:Anniversary";
const SYNC_POOMCONTACTS_ASSISTANTNAME = "POOMCONTACTS:AssistantName";
const SYNC_POOMCONTACTS_ASSISTNAMEPHONENUMBER = "POOMCONTACTS:AssistnamePhoneNumber";
const SYNC_POOMCONTACTS_BIRTHDAY = "POOMCONTACTS:Birthday";
const SYNC_POOMCONTACTS_BODY = "POOMCONTACTS:Body"; // 2.5, AirSyncBase Body is used since 12.0
const SYNC_POOMCONTACTS_BODYSIZE = "POOMCONTACTS:BodySize"; // 2.5, AirSyncBase is used since version 12.0
const SYNC_POOMCONTACTS_BODYTRUNCATED = "POOMCONTACTS:BodyTruncated"; // 2.5, AirSyncBase is used since version 12.0
const SYNC_POOMCONTACTS_BUSINESS2PHONENUMBER = "POOMCONTACTS:Business2PhoneNumber";
const SYNC_POOMCONTACTS_BUSINESSCITY = "POOMCONTACTS:BusinessCity";
const SYNC_POOMCONTACTS_BUSINESSCOUNTRY = "POOMCONTACTS:BusinessCountry";
const SYNC_POOMCONTACTS_BUSINESSPOSTALCODE = "POOMCONTACTS:BusinessPostalCode";
const SYNC_POOMCONTACTS_BUSINESSSTATE = "POOMCONTACTS:BusinessState";
const SYNC_POOMCONTACTS_BUSINESSSTREET = "POOMCONTACTS:BusinessStreet";
const SYNC_POOMCONTACTS_BUSINESSFAXNUMBER = "POOMCONTACTS:BusinessFaxNumber";
const SYNC_POOMCONTACTS_BUSINESSPHONENUMBER = "POOMCONTACTS:BusinessPhoneNumber";
const SYNC_POOMCONTACTS_CARPHONENUMBER = "POOMCONTACTS:CarPhoneNumber";
const SYNC_POOMCONTACTS_CATEGORIES = "POOMCONTACTS:Categories";
const SYNC_POOMCONTACTS_CATEGORY = "POOMCONTACTS:Category";
const SYNC_POOMCONTACTS_CHILDREN = "POOMCONTACTS:Children";
const SYNC_POOMCONTACTS_CHILD = "POOMCONTACTS:Child";
const SYNC_POOMCONTACTS_COMPANYNAME = "POOMCONTACTS:CompanyName";
const SYNC_POOMCONTACTS_DEPARTMENT = "POOMCONTACTS:Department";
const SYNC_POOMCONTACTS_EMAIL1ADDRESS = "POOMCONTACTS:Email1Address";
const SYNC_POOMCONTACTS_EMAIL2ADDRESS = "POOMCONTACTS:Email2Address";
const SYNC_POOMCONTACTS_EMAIL3ADDRESS = "POOMCONTACTS:Email3Address";
const SYNC_POOMCONTACTS_FILEAS = "POOMCONTACTS:FileAs";
const SYNC_POOMCONTACTS_FIRSTNAME = "POOMCONTACTS:FirstName";
const SYNC_POOMCONTACTS_HOME2PHONENUMBER = "POOMCONTACTS:Home2PhoneNumber";
const SYNC_POOMCONTACTS_HOMECITY = "POOMCONTACTS:HomeCity";
const SYNC_POOMCONTACTS_HOMECOUNTRY = "POOMCONTACTS:HomeCountry";
const SYNC_POOMCONTACTS_HOMEPOSTALCODE = "POOMCONTACTS:HomePostalCode";
const SYNC_POOMCONTACTS_HOMESTATE = "POOMCONTACTS:HomeState";
const SYNC_POOMCONTACTS_HOMESTREET = "POOMCONTACTS:HomeStreet";
const SYNC_POOMCONTACTS_HOMEFAXNUMBER = "POOMCONTACTS:HomeFaxNumber";
const SYNC_POOMCONTACTS_HOMEPHONENUMBER = "POOMCONTACTS:HomePhoneNumber";
const SYNC_POOMCONTACTS_JOBTITLE = "POOMCONTACTS:JobTitle";
const SYNC_POOMCONTACTS_LASTNAME = "POOMCONTACTS:LastName";
const SYNC_POOMCONTACTS_MIDDLENAME = "POOMCONTACTS:MiddleName";
const SYNC_POOMCONTACTS_MOBILEPHONENUMBER = "POOMCONTACTS:MobilePhoneNumber";
const SYNC_POOMCONTACTS_OFFICELOCATION = "POOMCONTACTS:OfficeLocation";
const SYNC_POOMCONTACTS_OTHERCITY = "POOMCONTACTS:OtherCity";
const SYNC_POOMCONTACTS_OTHERCOUNTRY = "POOMCONTACTS:OtherCountry";
const SYNC_POOMCONTACTS_OTHERPOSTALCODE = "POOMCONTACTS:OtherPostalCode";
const SYNC_POOMCONTACTS_OTHERSTATE = "POOMCONTACTS:OtherState";
const SYNC_POOMCONTACTS_OTHERSTREET = "POOMCONTACTS:OtherStreet";
const SYNC_POOMCONTACTS_PAGERNUMBER = "POOMCONTACTS:PagerNumber";
const SYNC_POOMCONTACTS_RADIOPHONENUMBER = "POOMCONTACTS:RadioPhoneNumber";
const SYNC_POOMCONTACTS_SPOUSE = "POOMCONTACTS:Spouse";
const SYNC_POOMCONTACTS_SUFFIX = "POOMCONTACTS:Suffix";
const SYNC_POOMCONTACTS_TITLE = "POOMCONTACTS:Title";
const SYNC_POOMCONTACTS_WEBPAGE = "POOMCONTACTS:WebPage";
const SYNC_POOMCONTACTS_YOMICOMPANYNAME = "POOMCONTACTS:YomiCompanyName";
const SYNC_POOMCONTACTS_YOMIFIRSTNAME = "POOMCONTACTS:YomiFirstName";
const SYNC_POOMCONTACTS_YOMILASTNAME = "POOMCONTACTS:YomiLastName";
const SYNC_POOMCONTACTS_RTF = "POOMCONTACTS:Rtf"; // deprecated
const SYNC_POOMCONTACTS_PICTURE = "POOMCONTACTS:Picture";
const SYNC_POOMCONTACTS_ALIAS = "POOMCONTACTS:Alias"; // Since 14.0
const SYNC_POOMCONTACTS_WEIGHEDRANK = "POOMCONTACTS:WeightedRank"; // Since 14.0

// Code Page 2: Email - POOMMAIL, all AS versions
const SYNC_POOMMAIL_ATTACHMENT = "POOMMAIL:Attachment"; // AirSyncBase Attachments is used since 12.0
const SYNC_POOMMAIL_ATTACHMENTS = "POOMMAIL:Attachments"; // AirSyncBase Attachments is used since 12.0
const SYNC_POOMMAIL_ATTNAME = "POOMMAIL:AttName"; // AirSyncBase Attachments is used since 12.0
const SYNC_POOMMAIL_ATTSIZE = "POOMMAIL:AttSize"; // AirSyncBase Attachments is used since 12.0
const SYNC_POOMMAIL_ATTOID = "POOMMAIL:AttOid"; // AirSyncBase Attachments is used since 12.0
const SYNC_POOMMAIL_ATTMETHOD = "POOMMAIL:AttMethod"; // AirSyncBase Attachments is used since 12.0
const SYNC_POOMMAIL_ATTREMOVED = "POOMMAIL:AttRemoved"; // AirSyncBase Attachments is used since 12.0
const SYNC_POOMMAIL_BODY = "POOMMAIL:Body"; // AirSyncBase Body is used since 12.0
const SYNC_POOMMAIL_BODYSIZE = "POOMMAIL:BodySize"; // AirSyncBase Body is used since 12.0
const SYNC_POOMMAIL_BODYTRUNCATED = "POOMMAIL:BodyTruncated"; // AirSyncBase Body is used since 12.0
const SYNC_POOMMAIL_DATERECEIVED = "POOMMAIL:DateReceived";
const SYNC_POOMMAIL_DISPLAYNAME = "POOMMAIL:DisplayName"; // AirSyncBase Attachments is used since 12.0
const SYNC_POOMMAIL_DISPLAYTO = "POOMMAIL:DisplayTo";
const SYNC_POOMMAIL_IMPORTANCE = "POOMMAIL:Importance";
const SYNC_POOMMAIL_MESSAGECLASS = "POOMMAIL:MessageClass";
const SYNC_POOMMAIL_SUBJECT = "POOMMAIL:Subject";
const SYNC_POOMMAIL_READ = "POOMMAIL:Read";
const SYNC_POOMMAIL_TO = "POOMMAIL:To";
const SYNC_POOMMAIL_CC = "POOMMAIL:Cc";
const SYNC_POOMMAIL_FROM = "POOMMAIL:From";
const SYNC_POOMMAIL_REPLY_TO = "POOMMAIL:Reply-To";
const SYNC_POOMMAIL_ALLDAYEVENT = "POOMMAIL:AllDayEvent";
const SYNC_POOMMAIL_CATEGORIES = "POOMMAIL:Categories"; // Since 14.0
const SYNC_POOMMAIL_CATEGORY = "POOMMAIL:Category"; // Since 14.0
const SYNC_POOMMAIL_DTSTAMP = "POOMMAIL:DtStamp";
const SYNC_POOMMAIL_ENDTIME = "POOMMAIL:EndTime";
const SYNC_POOMMAIL_INSTANCETYPE = "POOMMAIL:InstanceType";
const SYNC_POOMMAIL_BUSYSTATUS = "POOMMAIL:BusyStatus";
const SYNC_POOMMAIL_LOCATION = "POOMMAIL:Location"; // 2.5, 12.0, 12.1, 14.0 and 14.1. Since 16.0 AirSyncBase Location is used.
const SYNC_POOMMAIL_MEETINGREQUEST = "POOMMAIL:MeetingRequest";
const SYNC_POOMMAIL_ORGANIZER = "POOMMAIL:Organizer";
const SYNC_POOMMAIL_RECURRENCEID = "POOMMAIL:RecurrenceId";
const SYNC_POOMMAIL_REMINDER = "POOMMAIL:Reminder";
const SYNC_POOMMAIL_RESPONSEREQUESTED = "POOMMAIL:ResponseRequested";
const SYNC_POOMMAIL_RECURRENCES = "POOMMAIL:Recurrences";
const SYNC_POOMMAIL_RECURRENCE = "POOMMAIL:Recurrence";
const SYNC_POOMMAIL_TYPE = "POOMMAIL:Type";
const SYNC_POOMMAIL_UNTIL = "POOMMAIL:Until";
const SYNC_POOMMAIL_OCCURRENCES = "POOMMAIL:Occurrences";
const SYNC_POOMMAIL_INTERVAL = "POOMMAIL:Interval";
const SYNC_POOMMAIL_DAYOFWEEK = "POOMMAIL:DayOfWeek";
const SYNC_POOMMAIL_DAYOFMONTH = "POOMMAIL:DayOfMonth";
const SYNC_POOMMAIL_WEEKOFMONTH = "POOMMAIL:WeekOfMonth";
const SYNC_POOMMAIL_MONTHOFYEAR = "POOMMAIL:MonthOfYear";
const SYNC_POOMMAIL_STARTTIME = "POOMMAIL:StartTime";
const SYNC_POOMMAIL_SENSITIVITY = "POOMMAIL:Sensitivity";
const SYNC_POOMMAIL_TIMEZONE = "POOMMAIL:TimeZone";
const SYNC_POOMMAIL_GLOBALOBJID = "POOMMAIL:GlobalObjId"; // 2.5, 12.0, 12.1, 14.0 and 14.1. UID of Calendar (Code page 4) is used since 16.0
const SYNC_POOMMAIL_THREADTOPIC = "POOMMAIL:ThreadTopic";
const SYNC_POOMMAIL_MIMEDATA = "POOMMAIL:MIMEData"; // 2.5
const SYNC_POOMMAIL_MIMETRUNCATED = "POOMMAIL:MIMETruncated"; // 2.5
const SYNC_POOMMAIL_MIMESIZE = "POOMMAIL:MIMESize";
const SYNC_POOMMAIL_INTERNETCPID = "POOMMAIL:InternetCPID";
const SYNC_POOMMAIL_FLAG = "POOMMAIL:Flag"; // Since 12.0
const SYNC_POOMMAIL_FLAGSTATUS = "POOMMAIL:FlagStatus"; // Since 12.0
const SYNC_POOMMAIL_CONTENTCLASS = "POOMMAIL:ContentClass"; // Since 12.0
const SYNC_POOMMAIL_FLAGTYPE = "POOMMAIL:FlagType"; // Since 12.0
const SYNC_POOMMAIL_COMPLETETIME = "POOMMAIL:CompleteTime"; //Since 12.0
const SYNC_POOMMAIL_DISALLOWNEWTIMEPROPOSAL = "POOMMAIL:DisallowNewTimeProposal"; // Since 14.0

// Code Page 3: AirNotify - AIRNOTIFY, no longer in use
const SYNC_AIRNOTIFY_NOTIFY = "AirNotify:Notify";
const SYNC_AIRNOTIFY_NOTIFICATION = "AirNotify:Notification";
const SYNC_AIRNOTIFY_VERSION = "AirNotify:Version";
const SYNC_AIRNOTIFY_LIFETIME = "AirNotify:Lifetime";
const SYNC_AIRNOTIFY_DEVICEINFO = "AirNotify:DeviceInfo";
const SYNC_AIRNOTIFY_ENABLE = "AirNotify:Enable";
const SYNC_AIRNOTIFY_FOLDER = "AirNotify:Folder";
const SYNC_AIRNOTIFY_SERVERENTRYID = "AirNotify:ServerEntryId";
const SYNC_AIRNOTIFY_DEVICEADDRESS = "AirNotify:DeviceAddress";
const SYNC_AIRNOTIFY_VALIDCARRIERPROFILES = "AirNotify:ValidCarrierProfiles";
const SYNC_AIRNOTIFY_CARRIERPROFILE = "AirNotify:CarrierProfile";
const SYNC_AIRNOTIFY_STATUS = "AirNotify:Status";
const SYNC_AIRNOTIFY_REPLIES = "AirNotify:Replies";
define("SYNC_AIRNOTIFY_VERSION='1.1'","AirNotify:Version='1.1'");
const SYNC_AIRNOTIFY_DEVICES = "AirNotify:Devices";
const SYNC_AIRNOTIFY_DEVICE = "AirNotify:Device";
const SYNC_AIRNOTIFY_ID = "AirNotify:Id";
const SYNC_AIRNOTIFY_EXPIRY = "AirNotify:Expiry";
const SYNC_AIRNOTIFY_NOTIFYGUID = "AirNotify:NotifyGUID";

// Code Page 4: Calendar - POOMCAL, all AS versions
const SYNC_POOMCAL_TIMEZONE = "POOMCAL:Timezone";
const SYNC_POOMCAL_ALLDAYEVENT = "POOMCAL:AllDayEvent";
const SYNC_POOMCAL_ATTENDEES = "POOMCAL:Attendees";
const SYNC_POOMCAL_ATTENDEE = "POOMCAL:Attendee";
const SYNC_POOMCAL_EMAIL = "POOMCAL:Email";
const SYNC_POOMCAL_NAME = "POOMCAL:Name";
const SYNC_POOMCAL_BODY = "POOMCAL:Body"; // AirSyncBase Body is used since 12.0
const SYNC_POOMCAL_BODYTRUNCATED = "POOMCAL:BodyTruncated"; // AirSyncBase Body is used since 12.0
const SYNC_POOMCAL_BUSYSTATUS = "POOMCAL:BusyStatus";
const SYNC_POOMCAL_CATEGORIES = "POOMCAL:Categories";
const SYNC_POOMCAL_CATEGORY = "POOMCAL:Category";
const SYNC_POOMCAL_RTF = "POOMCAL:Rtf"; // deprecated
const SYNC_POOMCAL_DTSTAMP = "POOMCAL:DtStamp";
const SYNC_POOMCAL_ENDTIME = "POOMCAL:EndTime";
const SYNC_POOMCAL_EXCEPTION = "POOMCAL:Exception";
const SYNC_POOMCAL_EXCEPTIONS = "POOMCAL:Exceptions";
const SYNC_POOMCAL_DELETED = "POOMCAL:Deleted";
const SYNC_POOMCAL_EXCEPTIONSTARTTIME = "POOMCAL:ExceptionStartTime"; // 2.5, 12.0, 12.1, 14.0 and 14.1.
const SYNC_POOMCAL_LOCATION = "POOMCAL:Location"; // 2.5, 12.0, 12.1, 14.0 and 14.1. Since 16.0 AirSyncBase Location is used.
const SYNC_POOMCAL_MEETINGSTATUS = "POOMCAL:MeetingStatus";
const SYNC_POOMCAL_ORGANIZEREMAIL = "POOMCAL:OrganizerEmail";
const SYNC_POOMCAL_ORGANIZERNAME = "POOMCAL:OrganizerName";
const SYNC_POOMCAL_RECURRENCE = "POOMCAL:Recurrence";
const SYNC_POOMCAL_TYPE = "POOMCAL:Type";
const SYNC_POOMCAL_UNTIL = "POOMCAL:Until";
const SYNC_POOMCAL_OCCURRENCES = "POOMCAL:Occurrences";
const SYNC_POOMCAL_INTERVAL = "POOMCAL:Interval";
const SYNC_POOMCAL_DAYOFWEEK = "POOMCAL:DayOfWeek";
const SYNC_POOMCAL_DAYOFMONTH = "POOMCAL:DayOfMonth";
const SYNC_POOMCAL_WEEKOFMONTH = "POOMCAL:WeekOfMonth";
const SYNC_POOMCAL_MONTHOFYEAR = "POOMCAL:MonthOfYear";
const SYNC_POOMCAL_REMINDER = "POOMCAL:Reminder";
const SYNC_POOMCAL_SENSITIVITY = "POOMCAL:Sensitivity";
const SYNC_POOMCAL_SUBJECT = "POOMCAL:Subject";
const SYNC_POOMCAL_STARTTIME = "POOMCAL:StartTime";
const SYNC_POOMCAL_UID = "POOMCAL:UID";
const SYNC_POOMCAL_ATTENDEESTATUS = "POOMCAL:Attendee_Status"; // Since 12.0
const SYNC_POOMCAL_ATTENDEETYPE = "POOMCAL:Attendee_Type"; // Since 12.0
const SYNC_POOMCAL_ATTACHMENT = "POOMCAL:Attachment"; // Not defined / deprecated
const SYNC_POOMCAL_ATTACHMENTS = "POOMCAL:Attachments"; // Not defined / deprecated
const SYNC_POOMCAL_ATTNAME = "POOMCAL:AttName"; // Not defined / deprecated
const SYNC_POOMCAL_ATTSIZE = "POOMCAL:AttSize"; // Not defined / deprecated
const SYNC_POOMCAL_ATTOID = "POOMCAL:AttOid"; // Not defined / deprecated
const SYNC_POOMCAL_ATTMETHOD = "POOMCAL:AttMethod"; // Not defined / deprecated
const SYNC_POOMCAL_ATTREMOVED = "POOMCAL:AttRemoved"; // Not defined / deprecated
const SYNC_POOMCAL_DISPLAYNAME = "POOMCAL:DisplayName"; // Not defined / deprecated
const SYNC_POOMCAL_DISALLOWNEWTIMEPROPOSAL = "POOMCAL:DisallowNewTimeProposal"; // Since 14.0
const SYNC_POOMCAL_RESPONSEREQUESTED = "POOMCAL:ResponseRequested"; // Since 14.0
const SYNC_POOMCAL_APPOINTMENTREPLYTIME = "POOMCAL:AppointmentReplyTime"; // Since 14.0
const SYNC_POOMCAL_RESPONSETYPE = "POOMCAL:ResponseType"; // Since 14.0
const SYNC_POOMCAL_CALENDARTYPE = "POOMCAL:CalendarType"; // Since 14.0
const SYNC_POOMCAL_ISLEAPMONTH = "POOMCAL:IsLeapMonth"; // Since 14.0
const SYNC_POOMCAL_FIRSTDAYOFWEEK = "POOMCAL:FirstDayOfWeek"; // Since 14.1
const SYNC_POOMCAL_ONLINEMEETINGCONFLINK = "POOMCAL:OnlineMeetingConfLink"; // Since 14.1
const SYNC_POOMCAL_ONLINEMEETINGEXTERNALLINK = "POOMCAL:OnlineMeetingExternalLink"; // Since 14.0

// Code Page 5: Move, all AS versions
const SYNC_MOVE_MOVES = "Move:Moves";
const SYNC_MOVE_MOVE = "Move:Move";
const SYNC_MOVE_SRCMSGID = "Move:SrcMsgId";
const SYNC_MOVE_SRCFLDID = "Move:SrcFldId";
const SYNC_MOVE_DSTFLDID = "Move:DstFldId";
const SYNC_MOVE_RESPONSE = "Move:Response";
const SYNC_MOVE_STATUS = "Move:Status";
const SYNC_MOVE_DSTMSGID = "Move:DstMsgId";

// Code Page 6: GetItemEstimate, all AS versions
const SYNC_GETITEMESTIMATE_GETITEMESTIMATE = "GetItemEstimate:GetItemEstimate";
const SYNC_GETITEMESTIMATE_VERSION = "GetItemEstimate:Version"; // deprecated
const SYNC_GETITEMESTIMATE_FOLDERS = "GetItemEstimate:Folders";
const SYNC_GETITEMESTIMATE_FOLDER = "GetItemEstimate:Folder";
const SYNC_GETITEMESTIMATE_FOLDERTYPE = "GetItemEstimate:FolderType"; // AirSync Class(SYNC_FOLDERTYPE) is used since AS 14.0
const SYNC_GETITEMESTIMATE_FOLDERID = "GetItemEstimate:FolderId";
const SYNC_GETITEMESTIMATE_DATETIME = "GetItemEstimate:DateTime"; // deprecated
const SYNC_GETITEMESTIMATE_ESTIMATE = "GetItemEstimate:Estimate";
const SYNC_GETITEMESTIMATE_RESPONSE = "GetItemEstimate:Response";
const SYNC_GETITEMESTIMATE_STATUS = "GetItemEstimate:Status";

// Code Page 7: FolderHierarchy, all AS versions
const SYNC_FOLDERHIERARCHY_FOLDERS = "FolderHierarchy:Folders"; // 2.5, 12.0 and 12.1
const SYNC_FOLDERHIERARCHY_FOLDER = "FolderHierarchy:Folder"; // 2.5, 12.0 and 12.1
const SYNC_FOLDERHIERARCHY_DISPLAYNAME = "FolderHierarchy:DisplayName";
const SYNC_FOLDERHIERARCHY_SERVERENTRYID = "FolderHierarchy:ServerEntryId";
const SYNC_FOLDERHIERARCHY_PARENTID = "FolderHierarchy:ParentId";
const SYNC_FOLDERHIERARCHY_TYPE = "FolderHierarchy:Type";
const SYNC_FOLDERHIERARCHY_RESPONSE = "FolderHierarchy:Response"; // deprecated
const SYNC_FOLDERHIERARCHY_STATUS = "FolderHierarchy:Status";
const SYNC_FOLDERHIERARCHY_CONTENTCLASS = "FolderHierarchy:ContentClass"; // deprecated
const SYNC_FOLDERHIERARCHY_CHANGES = "FolderHierarchy:Changes";
const SYNC_FOLDERHIERARCHY_ADD = "FolderHierarchy:Add";
const SYNC_FOLDERHIERARCHY_REMOVE = "FolderHierarchy:Remove";
const SYNC_FOLDERHIERARCHY_UPDATE = "FolderHierarchy:Update";
const SYNC_FOLDERHIERARCHY_SYNCKEY = "FolderHierarchy:SyncKey";
const SYNC_FOLDERHIERARCHY_FOLDERCREATE = "FolderHierarchy:FolderCreate";
const SYNC_FOLDERHIERARCHY_FOLDERDELETE = "FolderHierarchy:FolderDelete";
const SYNC_FOLDERHIERARCHY_FOLDERUPDATE = "FolderHierarchy:FolderUpdate";
const SYNC_FOLDERHIERARCHY_FOLDERSYNC = "FolderHierarchy:FolderSync";
const SYNC_FOLDERHIERARCHY_COUNT = "FolderHierarchy:Count";
const SYNC_FOLDERHIERARCHY_VERSION = "FolderHierarchy:Version"; // Not defined / deprecated
// only for internal use - never to be streamed to the mobile
const SYNC_FOLDERHIERARCHY_IGNORE_STORE = "FolderHierarchy:IgnoreStore";
const SYNC_FOLDERHIERARCHY_IGNORE_NOBCKENDFLD = "FolderHierarchy:IgnoreNoBackendFolder";
const SYNC_FOLDERHIERARCHY_IGNORE_BACKENDID = "FolderHierarchy:IgnoreBackendId";
const SYNC_FOLDERHIERARCHY_IGNORE_FLAGS = "FolderHierarchy:IgnoreFlags";
const SYNC_FOLDERHIERARCHY_IGNORE_TYPEREAL = "FolderHierarchy:TypeReal";

// Code Page 8: MeetingResponse, all AS versions
const SYNC_MEETINGRESPONSE_CALENDARID = "MeetingResponse:CalendarId";
const SYNC_MEETINGRESPONSE_FOLDERID = "MeetingResponse:FolderId";
const SYNC_MEETINGRESPONSE_MEETINGRESPONSE = "MeetingResponse:MeetingResponse";
const SYNC_MEETINGRESPONSE_REQUESTID = "MeetingResponse:RequestId";
const SYNC_MEETINGRESPONSE_REQUEST = "MeetingResponse:Request";
const SYNC_MEETINGRESPONSE_RESULT = "MeetingResponse:Result";
const SYNC_MEETINGRESPONSE_STATUS = "MeetingResponse:Status";
const SYNC_MEETINGRESPONSE_USERRESPONSE = "MeetingResponse:UserResponse";
const SYNC_MEETINGRESPONSE_VERSION = "MeetingResponse:Version"; // Not defined / deprecated
const SYNC_MEETINGRESPONSE_INSTANCEID = "MeetingResponse:InstanceId"; // Since AS 14.1

// Code Page 9: Tasks - POOMTASKS, all AS versions
const SYNC_POOMTASKS_BODY = "POOMTASKS:Body"; // AirSyncBase Body is used since 12.0
const SYNC_POOMTASKS_BODYSIZE = "POOMTASKS:BodySize"; // AirSyncBase Body is used since 12.0
const SYNC_POOMTASKS_BODYTRUNCATED = "POOMTASKS:BodyTruncated"; // AirSyncBase Body is used since 12.0
const SYNC_POOMTASKS_CATEGORIES = "POOMTASKS:Categories";
const SYNC_POOMTASKS_CATEGORY = "POOMTASKS:Category";
const SYNC_POOMTASKS_COMPLETE = "POOMTASKS:Complete";
const SYNC_POOMTASKS_DATECOMPLETED = "POOMTASKS:DateCompleted";
const SYNC_POOMTASKS_DUEDATE = "POOMTASKS:DueDate";
const SYNC_POOMTASKS_UTCDUEDATE = "POOMTASKS:UtcDueDate";
const SYNC_POOMTASKS_IMPORTANCE = "POOMTASKS:Importance";
const SYNC_POOMTASKS_RECURRENCE = "POOMTASKS:Recurrence";
const SYNC_POOMTASKS_TYPE = "POOMTASKS:Type";
const SYNC_POOMTASKS_START = "POOMTASKS:Start";
const SYNC_POOMTASKS_UNTIL = "POOMTASKS:Until";
const SYNC_POOMTASKS_OCCURRENCES = "POOMTASKS:Occurrences";
const SYNC_POOMTASKS_INTERVAL = "POOMTASKS:Interval";
const SYNC_POOMTASKS_DAYOFWEEK = "POOMTASKS:DayOfWeek";
const SYNC_POOMTASKS_DAYOFMONTH = "POOMTASKS:DayOfMonth";
const SYNC_POOMTASKS_WEEKOFMONTH = "POOMTASKS:WeekOfMonth";
const SYNC_POOMTASKS_MONTHOFYEAR = "POOMTASKS:MonthOfYear";
const SYNC_POOMTASKS_REGENERATE = "POOMTASKS:Regenerate";
const SYNC_POOMTASKS_DEADOCCUR = "POOMTASKS:DeadOccur";
const SYNC_POOMTASKS_REMINDERSET = "POOMTASKS:ReminderSet";
const SYNC_POOMTASKS_REMINDERTIME = "POOMTASKS:ReminderTime";
const SYNC_POOMTASKS_SENSITIVITY = "POOMTASKS:Sensitivity";
const SYNC_POOMTASKS_STARTDATE = "POOMTASKS:StartDate";
const SYNC_POOMTASKS_UTCSTARTDATE = "POOMTASKS:UtcStartDate";
const SYNC_POOMTASKS_SUBJECT = "POOMTASKS:Subject";
const SYNC_POOMTASKS_RTF = "POOMTASKS:Rtf";
const SYNC_POOMTASKS_ORDINALDATE = "POOMTASKS:OrdinalDate"; // Since 12.0
const SYNC_POOMTASKS_SUBORDINALDATE = "POOMTASKS:SubOrdinalDate"; // Since 12.0
const SYNC_POOMTASKS_CALENDARTYPE = "POOMTASKS:CalendarType"; // Since 14.0
const SYNC_POOMTASKS_ISLEAPMONTH = "POOMTASKS:IsLeapMonth"; // Since 14.0
const SYNC_POOMTASKS_FIRSTDAYOFWEEK = "POOMTASKS:FirstDayOfWeek"; // Since 14.0

// Code Page 10: ResolveRecipients, all AS versions
const SYNC_RESOLVERECIPIENTS_RESOLVERECIPIENTS = "ResolveRecipients:ResolveRecipients";
const SYNC_RESOLVERECIPIENTS_RESPONSE = "ResolveRecipients:Response";
const SYNC_RESOLVERECIPIENTS_STATUS = "ResolveRecipients:Status";
const SYNC_RESOLVERECIPIENTS_TYPE = "ResolveRecipients:Type";
const SYNC_RESOLVERECIPIENTS_RECIPIENT = "ResolveRecipients:Recipient";
const SYNC_RESOLVERECIPIENTS_DISPLAYNAME = "ResolveRecipients:DisplayName";
const SYNC_RESOLVERECIPIENTS_EMAILADDRESS = "ResolveRecipients:EmailAddress";
const SYNC_RESOLVERECIPIENTS_CERTIFICATES = "ResolveRecipients:Certificates";
const SYNC_RESOLVERECIPIENTS_CERTIFICATE = "ResolveRecipients:Certificate";
const SYNC_RESOLVERECIPIENTS_MINICERTIFICATE = "ResolveRecipients:MiniCertificate";
const SYNC_RESOLVERECIPIENTS_OPTIONS = "ResolveRecipients:Options";
const SYNC_RESOLVERECIPIENTS_TO = "ResolveRecipients:To";
const SYNC_RESOLVERECIPIENTS_CERTIFICATERETRIEVAL = "ResolveRecipients:CertificateRetrieval";
const SYNC_RESOLVERECIPIENTS_RECIPIENTCOUNT = "ResolveRecipients:RecipientCount";
const SYNC_RESOLVERECIPIENTS_MAXCERTIFICATES = "ResolveRecipients:MaxCertificates";
const SYNC_RESOLVERECIPIENTS_MAXAMBIGUOUSRECIPIENTS = "ResolveRecipients:MaxAmbiguousRecipients";
const SYNC_RESOLVERECIPIENTS_CERTIFICATECOUNT = "ResolveRecipients:CertificateCount";
const SYNC_RESOLVERECIPIENTS_AVAILABILITY = "ResolveRecipients:Availability"; // Since 14.0
const SYNC_RESOLVERECIPIENTS_STARTTIME = "ResolveRecipients:StartTime"; // Since 14.0
const SYNC_RESOLVERECIPIENTS_ENDTIME = "ResolveRecipients:EndTime"; // Since 14.0
const SYNC_RESOLVERECIPIENTS_MERGEDFREEBUSY = "ResolveRecipients:MergedFreeBusy"; // Since 14.0
const SYNC_RESOLVERECIPIENTS_PICTURE = "ResolveRecipients:Picture"; // Since 14.1
const SYNC_RESOLVERECIPIENTS_MAXSIZE = "ResolveRecipients:MaxSize"; // Since 14.1
const SYNC_RESOLVERECIPIENTS_DATA = "ResolveRecipients:Data"; // Since 14.1
const SYNC_RESOLVERECIPIENTS_MAXPICTURES = "ResolveRecipients:MaxPictures"; // Since 14.1

// Code Page 11: ValidateCert, all AS versions
const SYNC_VALIDATECERT_VALIDATECERT = "ValidateCert:ValidateCert";
const SYNC_VALIDATECERT_CERTIFICATES = "ValidateCert:Certificates";
const SYNC_VALIDATECERT_CERTIFICATE = "ValidateCert:Certificate";
const SYNC_VALIDATECERT_CERTIFICATECHAIN = "ValidateCert:CertificateChain";
const SYNC_VALIDATECERT_CHECKCRL = "ValidateCert:CheckCRL";
const SYNC_VALIDATECERT_STATUS = "ValidateCert:Status";

// Code Page 12: Contacts2 - POOMCONTACTS2, all AS versions
const SYNC_POOMCONTACTS2_CUSTOMERID = "POOMCONTACTS2:CustomerId";
const SYNC_POOMCONTACTS2_GOVERNMENTID = "POOMCONTACTS2:GovernmentId";
const SYNC_POOMCONTACTS2_IMADDRESS = "POOMCONTACTS2:IMAddress";
const SYNC_POOMCONTACTS2_IMADDRESS2 = "POOMCONTACTS2:IMAddress2";
const SYNC_POOMCONTACTS2_IMADDRESS3 = "POOMCONTACTS2:IMAddress3";
const SYNC_POOMCONTACTS2_MANAGERNAME = "POOMCONTACTS2:ManagerName";
const SYNC_POOMCONTACTS2_COMPANYMAINPHONE = "POOMCONTACTS2:CompanyMainPhone";
const SYNC_POOMCONTACTS2_ACCOUNTNAME = "POOMCONTACTS2:AccountName";
const SYNC_POOMCONTACTS2_NICKNAME = "POOMCONTACTS2:NickName";
const SYNC_POOMCONTACTS2_MMS = "POOMCONTACTS2:MMS";

// Code Page 13: Ping, all AS versions
const SYNC_PING_PING = "Ping:Ping";
const SYNC_PING_STATUS = "Ping:Status";
const SYNC_PING_LIFETIME = "Ping:LifeTime";
const SYNC_PING_FOLDERS = "Ping:Folders";
const SYNC_PING_FOLDER = "Ping:Folder";
const SYNC_PING_SERVERENTRYID = "Ping:ServerEntryId";
const SYNC_PING_FOLDERTYPE = "Ping:FolderType";
const SYNC_PING_MAXFOLDERS = "Ping:MaxFolders";
const SYNC_PING_VERSION = "Ping:Version"; // not defined / deprecated

// Code Page 14: Provision, all AS versions
const SYNC_PROVISION_PROVISION = "Provision:Provision";
const SYNC_PROVISION_POLICIES = "Provision:Policies";
const SYNC_PROVISION_POLICY = "Provision:Policy";
const SYNC_PROVISION_POLICYTYPE = "Provision:PolicyType";
const SYNC_PROVISION_POLICYKEY = "Provision:PolicyKey";
const SYNC_PROVISION_DATA = "Provision:Data";
const SYNC_PROVISION_STATUS = "Provision:Status";
const SYNC_PROVISION_REMOTEWIPE = "Provision:RemoteWipe";
const SYNC_PROVISION_EASPROVISIONDOC = "Provision:EASProvisionDoc"; // Since AS 12.0
const SYNC_PROVISION_DEVPWENABLED = "Provision:DevicePasswordEnabled"; // Since AS 12.0
const SYNC_PROVISION_ALPHANUMPWREQ = "Provision:AlphanumericDevicePasswordRequired"; // Since AS 12.0
const SYNC_PROVISION_DEVENCENABLED = "Provision:DeviceEncryptionEnabled"; // Since AS 12.1
const SYNC_PROVISION_REQSTORAGECARDENC = "Provision:RequireStorageCardEncryption"; // Since AS 12.1
const SYNC_PROVISION_PWRECOVERYENABLED = "Provision:PasswordRecoveryEnabled"; // Since AS 12.0
const SYNC_PROVISION_DOCBROWSEENABLED = "Provision:DocumentBrowseEnabled"; // deprecated
const SYNC_PROVISION_ATTENABLED = "Provision:AttachmentsEnabled"; // Since AS 12.0
const SYNC_PROVISION_MINDEVPWLENGTH = "Provision:MinDevicePasswordLength"; // Since AS 12.0
const SYNC_PROVISION_MAXINACTTIMEDEVLOCK = "Provision:MaxInactivityTimeDeviceLock"; // Since AS 12.0
const SYNC_PROVISION_MAXDEVPWFAILEDATTEMPTS = "Provision:MaxDevicePasswordFailedAttempts"; // Since AS 12.0
const SYNC_PROVISION_MAXATTSIZE = "Provision:MaxAttachmentSize"; // Since AS 12.0
const SYNC_PROVISION_ALLOWSIMPLEDEVPW = "Provision:AllowSimpleDevicePassword"; // Since AS 12.0
const SYNC_PROVISION_DEVPWEXPIRATION = "Provision:DevicePasswordExpiration"; // Since AS 12.0
const SYNC_PROVISION_DEVPWHISTORY = "Provision:DevicePasswordHistory"; // Since AS 12.0
const SYNC_PROVISION_ALLOWSTORAGECARD = "Provision:AllowStorageCard"; // Since AS 12.1
const SYNC_PROVISION_ALLOWCAM = "Provision:AllowCamera"; // Since AS 12.1
const SYNC_PROVISION_REQDEVENC = "Provision:RequireDeviceEncryption"; // Since AS 12.1
const SYNC_PROVISION_ALLOWUNSIGNEDAPPS = "Provision:AllowUnsignedApplications"; // Since AS 12.1
const SYNC_PROVISION_ALLOWUNSIGNEDINSTALLATIONPACKAGES = "Provision:AllowUnsignedInstallationPackages"; // Since AS 12.1
const SYNC_PROVISION_MINDEVPWCOMPLEXCHARS = "Provision:MinDevicePasswordComplexCharacters"; // Since AS 12.1
const SYNC_PROVISION_ALLOWWIFI = "Provision:AllowWiFi"; // Since AS 12.1
const SYNC_PROVISION_ALLOWTEXTMESSAGING = "Provision:AllowTextMessaging"; // Since AS 12.1
const SYNC_PROVISION_ALLOWPOPIMAPEMAIL = "Provision:AllowPOPIMAPEmail"; // Since AS 12.1
const SYNC_PROVISION_ALLOWBLUETOOTH = "Provision:AllowBluetooth"; // Since AS 12.1
const SYNC_PROVISION_ALLOWIRDA = "Provision:AllowIrDA"; // Since AS 12.1
const SYNC_PROVISION_REQMANUALSYNCWHENROAM = "Provision:RequireManualSyncWhenRoaming"; // Since AS 12.1
const SYNC_PROVISION_ALLOWDESKTOPSYNC = "Provision:AllowDesktopSync"; // Since AS 12.1
const SYNC_PROVISION_MAXCALAGEFILTER = "Provision:MaxCalendarAgeFilter"; // Since AS 12.1
const SYNC_PROVISION_ALLOWHTMLEMAIL = "Provision:AllowHTMLEmail"; // Since AS 12.1
const SYNC_PROVISION_MAXEMAILAGEFILTER = "Provision:MaxEmailAgeFilter"; // Since AS 12.1
const SYNC_PROVISION_MAXEMAILBODYTRUNCSIZE = "Provision:MaxEmailBodyTruncationSize"; // Since AS 12.1
const SYNC_PROVISION_MAXEMAILHTMLBODYTRUNCSIZE = "Provision:MaxEmailHTMLBodyTruncationSize"; // Since AS 12.1
const SYNC_PROVISION_REQSIGNEDSMIMEMESSAGES = "Provision:RequireSignedSMIMEMessages"; // Since AS 12.1
const SYNC_PROVISION_REQENCSMIMEMESSAGES = "Provision:RequireEncryptedSMIMEMessages"; // Since AS 12.1
const SYNC_PROVISION_REQSIGNEDSMIMEALGORITHM = "Provision:RequireSignedSMIMEAlgorithm"; // Since AS 12.1
const SYNC_PROVISION_REQENCSMIMEALGORITHM = "Provision:RequireEncryptionSMIMEAlgorithm"; // Since AS 12.1
const SYNC_PROVISION_ALLOWSMIMEENCALGORITHNEG = "Provision:AllowSMIMEEncryptionAlgorithmNegotiation"; // Since AS 12.1
const SYNC_PROVISION_ALLOWSMIMESOFTCERTS = "Provision:AllowSMIMESoftCerts"; // Since AS 12.1
const SYNC_PROVISION_ALLOWBROWSER = "Provision:AllowBrowser"; // Since AS 12.1
const SYNC_PROVISION_ALLOWCONSUMEREMAIL = "Provision:AllowConsumerEmail"; // Since AS 12.1
const SYNC_PROVISION_ALLOWREMOTEDESKTOP = "Provision:AllowRemoteDesktop"; // Since AS 12.1
const SYNC_PROVISION_ALLOWINTERNETSHARING = "Provision:AllowInternetSharing"; // Since AS 12.1
const SYNC_PROVISION_UNAPPROVEDINROMAPPLIST = "Provision:UnapprovedInROMApplicationList"; // Since AS 12.1
const SYNC_PROVISION_APPNAME = "Provision:ApplicationName"; // Since AS 12.1
const SYNC_PROVISION_APPROVEDAPPLIST = "Provision:ApprovedApplicationList"; // Since AS 12.1
const SYNC_PROVISION_HASH = "Provision:Hash"; // Since AS 12.1
// only for internal use - never to be streamed to the mobile
const SYNC_PROVISION_POLICYNAME = "Provision:PolicyName";

// Code Page 15: Search, all AS versions
const SYNC_SEARCH_SEARCH = "Search:Search";
const SYNC_SEARCH_STORE = "Search:Store";
const SYNC_SEARCH_NAME = "Search:Name";
const SYNC_SEARCH_QUERY = "Search:Query";
const SYNC_SEARCH_OPTIONS = "Search:Options";
const SYNC_SEARCH_RANGE = "Search:Range";
const SYNC_SEARCH_STATUS = "Search:Status";
const SYNC_SEARCH_RESPONSE = "Search:Response";
const SYNC_SEARCH_RESULT = "Search:Result";
const SYNC_SEARCH_PROPERTIES = "Search:Properties";
const SYNC_SEARCH_TOTAL = "Search:Total";
const SYNC_SEARCH_EQUALTO = "Search:EqualTo"; // Since AS 12.0
const SYNC_SEARCH_VALUE = "Search:Value"; // Since AS 12.0
const SYNC_SEARCH_AND = "Search:And"; // Since AS 12.0
const SYNC_SEARCH_OR = "Search:Or"; // Since AS 12.0
const SYNC_SEARCH_FREETEXT = "Search:FreeText"; // Since AS 12.0
const SYNC_SEARCH_DEEPTRAVERSAL = "Search:DeepTraversal"; // Since AS 12.0
const SYNC_SEARCH_LONGID = "Search:LongId"; // Since AS 12.0
const SYNC_SEARCH_REBUILDRESULTS = "Search:RebuildResults"; // Since AS 12.0
const SYNC_SEARCH_LESSTHAN = "Search:LessThan"; // Since AS 12.0
const SYNC_SEARCH_GREATERTHAN = "Search:GreaterThan"; // Since AS 12.0
const SYNC_SEARCH_SCHEMA = "Search:Schema"; // Since AS 12.0
const SYNC_SEARCH_SUPPORTED = "Search:Supported"; // Since AS 12.0
const SYNC_SEARCH_USERNAME = "Search:UserName"; // Since 12.1
const SYNC_SEARCH_PASSWORD = "Search:Password"; // Since 12.1
const SYNC_SEARCH_CONVERSATIONID = "Search:ConversationId"; // Since 14.0
const SYNC_SEARCH_PICTURE = "Search:Picture"; // Since 14.1
const SYNC_SEARCH_MAXSIZE = "Search:MaxSize"; // Since 14.1
const SYNC_SEARCH_MAXPICTURES = "Search:MaxPictures"; // Since 14.1

// Code Page 16: GAL, all AS versions
const SYNC_GAL_DISPLAYNAME = "GAL:DisplayName";
const SYNC_GAL_PHONE = "GAL:Phone";
const SYNC_GAL_OFFICE = "GAL:Office";
const SYNC_GAL_TITLE = "GAL:Title";
const SYNC_GAL_COMPANY = "GAL:Company";
const SYNC_GAL_ALIAS = "GAL:Alias";
const SYNC_GAL_FIRSTNAME = "GAL:FirstName";
const SYNC_GAL_LASTNAME = "GAL:LastName";
const SYNC_GAL_HOMEPHONE = "GAL:HomePhone";
const SYNC_GAL_MOBILEPHONE = "GAL:MobilePhone";
const SYNC_GAL_EMAILADDRESS = "GAL:EmailAddress";
const SYNC_GAL_PICTURE = "GAL:Picture"; // Since 14.1
const SYNC_GAL_STATUS = "GAL:Status"; // Since 14.1
const SYNC_GAL_DATA = "GAL:Data"; // Since 14.1

// Code Page 17: AirSyncBase, Since 12.0
const SYNC_AIRSYNCBASE_BODYPREFERENCE = "AirSyncBase:BodyPreference";
const SYNC_AIRSYNCBASE_TYPE = "AirSyncBase:Type";
const SYNC_AIRSYNCBASE_TRUNCATIONSIZE = "AirSyncBase:TruncationSize";
const SYNC_AIRSYNCBASE_ALLORNONE = "AirSyncBase:AllOrNone";
const SYNC_AIRSYNCBASE_BODY = "AirSyncBase:Body";
const SYNC_AIRSYNCBASE_DATA = "AirSyncBase:Data";
const SYNC_AIRSYNCBASE_ESTIMATEDDATASIZE = "AirSyncBase:EstimatedDataSize";
const SYNC_AIRSYNCBASE_TRUNCATED = "AirSyncBase:Truncated";
const SYNC_AIRSYNCBASE_ATTACHMENTS = "AirSyncBase:Attachments";
const SYNC_AIRSYNCBASE_ATTACHMENT = "AirSyncBase:Attachment";
const SYNC_AIRSYNCBASE_DISPLAYNAME = "AirSyncBase:DisplayName";
const SYNC_AIRSYNCBASE_FILEREFERENCE = "AirSyncBase:FileReference";
const SYNC_AIRSYNCBASE_METHOD = "AirSyncBase:Method";
const SYNC_AIRSYNCBASE_CONTENTID = "AirSyncBase:ContentId";
const SYNC_AIRSYNCBASE_CONTENTLOCATION = "AirSyncBase:ContentLocation";
const SYNC_AIRSYNCBASE_ISINLINE = "AirSyncBase:IsInline";
const SYNC_AIRSYNCBASE_NATIVEBODYTYPE = "AirSyncBase:NativeBodyType";
const SYNC_AIRSYNCBASE_CONTENTTYPE = "AirSyncBase:ContentType";
const SYNC_AIRSYNCBASE_PREVIEW = "AirSyncBase:Preview"; // Since 14.0
const SYNC_AIRSYNCBASE_BODYPARTPREFERENCE = "AirSyncBase:BodyPartPreference"; //Since 14.1
const SYNC_AIRSYNCBASE_BODYPART = "AirSyncBase:BodyPart"; // Since 14.1
const SYNC_AIRSYNCBASE_STATUS = "AirSyncBase:Status"; //Since 14.1

// Code Page 18: Settings, Since 12.0
const SYNC_SETTINGS_SETTINGS = "Settings:Settings";
const SYNC_SETTINGS_STATUS = "Settings:Status";
const SYNC_SETTINGS_GET = "Settings:Get";
const SYNC_SETTINGS_SET = "Settings:Set";
const SYNC_SETTINGS_OOF = "Settings:Oof";
const SYNC_SETTINGS_OOFSTATE = "Settings:OofState";
const SYNC_SETTINGS_STARTTIME = "Settings:StartTime";
const SYNC_SETTINGS_ENDTIME = "Settings:EndTime";
const SYNC_SETTINGS_OOFMESSAGE = "Settings:OofMessage";
const SYNC_SETTINGS_APPLIESTOINTERVAL = "Settings:AppliesToInternal";
const SYNC_SETTINGS_APPLIESTOEXTERNALKNOWN = "Settings:AppliesToExternalKnown";
const SYNC_SETTINGS_APPLIESTOEXTERNALUNKNOWN = "Settings:AppliesToExternalUnknown";
const SYNC_SETTINGS_ENABLED = "Settings:Enabled";
const SYNC_SETTINGS_REPLYMESSAGE = "Settings:ReplyMessage";
const SYNC_SETTINGS_BODYTYPE = "Settings:BodyType";
const SYNC_SETTINGS_DEVICEPW = "Settings:DevicePassword";
const SYNC_SETTINGS_PW = "Settings:Password";
const SYNC_SETTINGS_DEVICEINFORMATION = "Settings:DeviceInformation";
const SYNC_SETTINGS_MODEL = "Settings:Model";
const SYNC_SETTINGS_IMEI = "Settings:IMEI";
const SYNC_SETTINGS_FRIENDLYNAME = "Settings:FriendlyName";
const SYNC_SETTINGS_OS = "Settings:OS";
const SYNC_SETTINGS_OSLANGUAGE = "Settings:OSLanguage";
const SYNC_SETTINGS_PHONENUMBER = "Settings:PhoneNumber";
const SYNC_SETTINGS_USERINFORMATION = "Settings:UserInformation";
const SYNC_SETTINGS_EMAILADDRESSES = "Settings:EmailAddresses";
const SYNC_SETTINGS_SMPTADDRESS = "Settings:SmtpAddress";
const SYNC_SETTINGS_USERAGENT = "Settings:UserAgent"; // Since 12.1
const SYNC_SETTINGS_ENABLEOUTBOUNDSMS = "Settings:EnableOutboundSMS"; // Since 14.0
const SYNC_SETTINGS_MOBILEOPERATOR = "Settings:MobileOperator"; // Since 14.0
const SYNC_SETTINGS_PRIMARYSMTPADDRESS = "Settings:PrimarySmtpAddress"; // Since 14.1
const SYNC_SETTINGS_ACCOUNTS = "Settings:Accounts"; // Since 14.1
const SYNC_SETTINGS_ACCOUNT = "Settings:Account"; // Since 14.1
const SYNC_SETTINGS_ACCOUNTID = "Settings:AccountId"; // Since 14.1
const SYNC_SETTINGS_ACCOUNTNAME = "Settings:AccountName"; // Since 14.1
const SYNC_SETTINGS_USERDISPLAYNAME = "Settings:UserDisplayName"; // Since 14.1
const SYNC_SETTINGS_SENDDISABLED = "Settings:SendDisabled"; // Since 14.1
const SYNC_SETTINGS_RIGHTSMANAGEMENTINFORMATION = "Settings:RightsManagementInformation"; // Since 14.1
// only for internal use - never to be streamed to the mobile
const SYNC_SETTINGS_PROP_STATUS = "Settings:PropertyStatus";

//Code Page 19: DocumentLibrary, Since 12.0
const SYNC_DOCUMENTLIBRARY_LINKID = "DocumentLibrary:LinkId";
const SYNC_DOCUMENTLIBRARY_DISPLAYNAME = "DocumentLibrary:DisplayName";
const SYNC_DOCUMENTLIBRARY_ISFOLDER = "DocumentLibrary:IsFolder";
const SYNC_DOCUMENTLIBRARY_CREATIONDATE = "DocumentLibrary:CreationDate";
const SYNC_DOCUMENTLIBRARY_LASTMODIFIEDDATE = "DocumentLibrary:LastModifiedDate";
const SYNC_DOCUMENTLIBRARY_ISHIDDEN = "DocumentLibrary:IsHidden";
const SYNC_DOCUMENTLIBRARY_CONTENTLENGTH = "DocumentLibrary:ContentLength";
const SYNC_DOCUMENTLIBRARY_CONTENTTYPE = "DocumentLibrary:ContentType";

//Code Page 20: ItemOperations, Since 12.0
const SYNC_ITEMOPERATIONS_ITEMOPERATIONS = "ItemOperations:ItemOperations";
const SYNC_ITEMOPERATIONS_FETCH = "ItemOperations:Fetch";
const SYNC_ITEMOPERATIONS_STORE = "ItemOperations:Store";
const SYNC_ITEMOPERATIONS_OPTIONS = "ItemOperations:Options";
const SYNC_ITEMOPERATIONS_RANGE = "ItemOperations:Range";
const SYNC_ITEMOPERATIONS_TOTAL = "ItemOperations:Total";
const SYNC_ITEMOPERATIONS_PROPERTIES = "ItemOperations:Properties";
const SYNC_ITEMOPERATIONS_DATA = "ItemOperations:Data";
const SYNC_ITEMOPERATIONS_STATUS = "ItemOperations:Status";
const SYNC_ITEMOPERATIONS_RESPONSE = "ItemOperations:Response";
const SYNC_ITEMOPERATIONS_VERSIONS = "ItemOperations:Version";
const SYNC_ITEMOPERATIONS_SCHEMA = "ItemOperations:Schema";
const SYNC_ITEMOPERATIONS_PART = "ItemOperations:Part";
const SYNC_ITEMOPERATIONS_EMPTYFOLDERCONTENTS = "ItemOperations:EmptyFolderContents";
const SYNC_ITEMOPERATIONS_DELETESUBFOLDERS = "ItemOperations:DeleteSubFolders";
const SYNC_ITEMOPERATIONS_USERNAME = "ItemOperations:UserName"; // Since 12.1
const SYNC_ITEMOPERATIONS_PASSWORD = "ItemOperations:Password"; // Since 12.1
const SYNC_ITEMOPERATIONS_MOVE = "ItemOperations:Move"; // Since 14.0
const SYNC_ITEMOPERATIONS_DSTFLDID = "ItemOperations:DstFldId"; // Since 14.0
const SYNC_ITEMOPERATIONS_CONVERSATIONID = "ItemOperations:ConversationId"; // Since 14.0
const SYNC_ITEMOPERATIONS_MOVEALWAYS = "ItemOperations:MoveAlways"; // Since 14.0

// Code Page 21: ComposeMail, Since 14.0
const SYNC_COMPOSEMAIL_SENDMAIL = "ComposeMail:SendMail";
const SYNC_COMPOSEMAIL_SMARTFORWARD = "ComposeMail:SmartForward";
const SYNC_COMPOSEMAIL_SMARTREPLY = "ComposeMail:SmartReply";
const SYNC_COMPOSEMAIL_SAVEINSENTITEMS = "ComposeMail:SaveInSentItems";
const SYNC_COMPOSEMAIL_REPLACEMIME = "ComposeMail:ReplaceMime";
const SYNC_COMPOSEMAIL_TYPE = "ComposeMail:Type"; // not used
const SYNC_COMPOSEMAIL_SOURCE = "ComposeMail:Source";
const SYNC_COMPOSEMAIL_FOLDERID = "ComposeMail:FolderId";
const SYNC_COMPOSEMAIL_ITEMID = "ComposeMail:ItemId";
const SYNC_COMPOSEMAIL_LONGID = "ComposeMail:LongId";
const SYNC_COMPOSEMAIL_INSTANCEID = "ComposeMail:InstanceId";
const SYNC_COMPOSEMAIL_MIME = "ComposeMail:MIME";
const SYNC_COMPOSEMAIL_CLIENTID = "ComposeMail:ClientId";
const SYNC_COMPOSEMAIL_STATUS = "ComposeMail:Status";
const SYNC_COMPOSEMAIL_ACCOUNTID = "ComposeMail:AccountId"; // Since 14.1
// only for internal use - never to be streamed to the mobile
const SYNC_COMPOSEMAIL_REPLYFLAG = "ComposeMail:ReplyFlag";
const SYNC_COMPOSEMAIL_FORWARDFLAG = "ComposeMail:ForwardFlag";

// Code Page 22: Email2 - POOMMAIL2, Since 14.0
const SYNC_POOMMAIL2_UMCALLERID = "POOMMAIL2:UmCallerId";
const SYNC_POOMMAIL2_UMUSERNOTES = "POOMMAIL2:UmUserNotes";
const SYNC_POOMMAIL2_UMATTDURATION = "POOMMAIL2:UmAttDuration";
const SYNC_POOMMAIL2_UMATTORDER = "POOMMAIL2:UmAttOrder";
const SYNC_POOMMAIL2_CONVERSATIONID = "POOMMAIL2:ConversationId";
const SYNC_POOMMAIL2_CONVERSATIONINDEX = "POOMMAIL2:ConversationIndex";
const SYNC_POOMMAIL2_LASTVERBEXECUTED = "POOMMAIL2:LastVerbExecuted";
const SYNC_POOMMAIL2_LASTVERBEXECUTIONTIME = "POOMMAIL2:LastVerbExecutionTime";
const SYNC_POOMMAIL2_RECEIVEDASBCC = "POOMMAIL2:ReceivedAsBcc";
const SYNC_POOMMAIL2_SENDER = "POOMMAIL2:Sender";
const SYNC_POOMMAIL2_CALENDARTYPE = "POOMMAIL2:CalendarType";
const SYNC_POOMMAIL2_ISLEAPMONTH = "POOMMAIL2:IsLeapMonth";
const SYNC_POOMMAIL2_ACCOUNTID = "POOMMAIL2:AccountId"; // Since 14.1
const SYNC_POOMMAIL2_FIRSTDAYOFWEEK = "POOMMAIL2:FirstDayOfWeek"; // Since 14.1
const SYNC_POOMMAIL2_MEETINGMESSAGETYPE = "POOMMAIL2:MeetingMessageType"; // Since 14.1

// Code Page 23: Notes, Since 14.0
const SYNC_NOTES_SUBJECT = "Notes:Subject";
const SYNC_NOTES_MESSAGECLASS = "Notes:MessageClass";
const SYNC_NOTES_LASTMODIFIEDDATE = "Notes:LastModifiedDate";
const SYNC_NOTES_CATEGORIES = "Notes:Categories";
const SYNC_NOTES_CATEGORY = "Notes:Category";
// only for internal use - never to be streamed to the mobile
const SYNC_NOTES_IGNORE_COLOR = "Notes:IgnoreColor";

// Code Page 24: RightsManagement, Since 14.1
const SYNC_RIGHTSMANAGEMENT_SUPPORT = "RightsManagement:RightsManagementSupport";
const SYNC_RIGHTSMANAGEMENT_TEMPLATES = "RightsManagement:RightsManagementTemplates";
const SYNC_RIGHTSMANAGEMENT_TEMPLATE = "RightsManagement:RightsManagementTemplate";
const SYNC_RIGHTSMANAGEMENT_LICENSE = "RightsManagement:RightsManagementLicense";
const SYNC_RIGHTSMANAGEMENT_EDITALLOWED = "RightsManagement:EditAllowed";
const SYNC_RIGHTSMANAGEMENT_REPLYALLOWED = "RightsManagement:ReplyAllowed";
const SYNC_RIGHTSMANAGEMENT_REPLYALLALLOWED = "RightsManagement:ReplyAllAllowed";
const SYNC_RIGHTSMANAGEMENT_FORWARDALLOWED = "RightsManagement:ForwardAllowed";
const SYNC_RIGHTSMANAGEMENT_MODIFYRECIPIENTSALLOWED = "RightsManagement:ModifyRecipientsAllowed";
const SYNC_RIGHTSMANAGEMENT_EXTRACTALLOWED = "RightsManagement:ExtractAllowed";
const SYNC_RIGHTSMANAGEMENT_PRINTALLOWED = "RightsManagement:PrintAllowed";
const SYNC_RIGHTSMANAGEMENT_EXPORTALLOWED = "RightsManagement:ExportAllowed";
const SYNC_RIGHTSMANAGEMENT_PROGRAMMATICACCESSALLOWED = "RightsManagement:ProgrammaticAccessAllowed";
const SYNC_RIGHTSMANAGEMENT_OWNER = "RightsManagement:Owner";
const SYNC_RIGHTSMANAGEMENT_CONTENTEXPIRYDATE = "RightsManagement:ContentExpiryDate";
const SYNC_RIGHTSMANAGEMENT_TEMPLATEID = "RightsManagement:TemplateID";
const SYNC_RIGHTSMANAGEMENT_TEMPLATENAME = "RightsManagement:TemplateName";
const SYNC_RIGHTSMANAGEMENT_TEMPLATEDESCRIPTION = "RightsManagement:TemplateDescription";
const SYNC_RIGHTSMANAGEMENT_CONTENTOWNER = "RightsManagement:ContentOwner";
const SYNC_RIGHTSMANAGEMENT_REMOVERIGHTSMGNTPROTECTION = "RightsManagement:RemoveRightsManagementProtection";

// Other constants
const SYNC_FOLDER_TYPE_OTHER = 1;
const SYNC_FOLDER_TYPE_INBOX = 2;
const SYNC_FOLDER_TYPE_DRAFTS = 3;
const SYNC_FOLDER_TYPE_WASTEBASKET = 4;
const SYNC_FOLDER_TYPE_SENTMAIL = 5;
const SYNC_FOLDER_TYPE_OUTBOX = 6;
const SYNC_FOLDER_TYPE_TASK = 7;
const SYNC_FOLDER_TYPE_APPOINTMENT = 8;
const SYNC_FOLDER_TYPE_CONTACT = 9;
const SYNC_FOLDER_TYPE_NOTE = 10;
const SYNC_FOLDER_TYPE_JOURNAL = 11;
const SYNC_FOLDER_TYPE_USER_MAIL = 12;
const SYNC_FOLDER_TYPE_USER_APPOINTMENT = 13;
const SYNC_FOLDER_TYPE_USER_CONTACT = 14;
const SYNC_FOLDER_TYPE_USER_TASK = 15;
const SYNC_FOLDER_TYPE_USER_JOURNAL = 16;
const SYNC_FOLDER_TYPE_USER_NOTE = 17;
const SYNC_FOLDER_TYPE_UNKNOWN = 18;
const SYNC_FOLDER_TYPE_RECIPIENT_CACHE = 19;
const SYNC_FOLDER_TYPE_DUMMY = 999999;

const SYNC_CONFLICT_OVERWRITE_SERVER = 0;
const SYNC_CONFLICT_OVERWRITE_PIM = 1;

const SYNC_FILTERTYPE_ALL = 0;
const SYNC_FILTERTYPE_1DAY = 1;
const SYNC_FILTERTYPE_3DAYS = 2;
const SYNC_FILTERTYPE_1WEEK = 3;
const SYNC_FILTERTYPE_2WEEKS = 4;
const SYNC_FILTERTYPE_1MONTH = 5;
const SYNC_FILTERTYPE_3MONTHS = 6;
const SYNC_FILTERTYPE_6MONTHS = 7;
const SYNC_FILTERTYPE_INCOMPLETETASKS = 8;

const SYNC_TRUNCATION_HEADERS = 0;
const SYNC_TRUNCATION_512B = 1;
const SYNC_TRUNCATION_1K = 2;
const SYNC_TRUNCATION_2K = 3;
const SYNC_TRUNCATION_5K = 4;
const SYNC_TRUNCATION_10K = 5;
const SYNC_TRUNCATION_20K = 6;
const SYNC_TRUNCATION_50K = 7;
const SYNC_TRUNCATION_100K = 8;
const SYNC_TRUNCATION_ALL = 9;

const SYNC_PROVISION_STATUS_SUCCESS = 1;
const SYNC_PROVISION_STATUS_PROTERROR = 2;
const SYNC_PROVISION_STATUS_SERVERERROR = 3;
const SYNC_PROVISION_STATUS_DEVEXTMANAGED = 4;

const SYNC_PROVISION_POLICYSTATUS_SUCCESS = 1;
const SYNC_PROVISION_POLICYSTATUS_NOPOLICY = 2;
const SYNC_PROVISION_POLICYSTATUS_UNKNOWNVALUE = 3;
const SYNC_PROVISION_POLICYSTATUS_CORRUPTED = 4;
const SYNC_PROVISION_POLICYSTATUS_POLKEYMISM = 5;

const SYNC_PROVISION_RWSTATUS_NA = 0;
const SYNC_PROVISION_RWSTATUS_OK = 1;
const SYNC_PROVISION_RWSTATUS_PENDING = 2;
const SYNC_PROVISION_RWSTATUS_REQUESTED = 4;
const SYNC_PROVISION_RWSTATUS_WIPED = 8;

const SYNC_STATUS_SUCCESS = 1;
const SYNC_STATUS_INVALIDSYNCKEY = 3;
const SYNC_STATUS_PROTOCOLLERROR = 4;
const SYNC_STATUS_SERVERERROR = 5;
const SYNC_STATUS_CLIENTSERVERCONVERSATIONERROR = 6;
const SYNC_STATUS_CONFLICTCLIENTSERVEROBJECT = 7;
const SYNC_STATUS_OBJECTNOTFOUND = 8;
const SYNC_STATUS_SYNCCANNOTBECOMPLETED = 9;
const SYNC_STATUS_FOLDERHIERARCHYCHANGED = 12;
const SYNC_STATUS_SYNCREQUESTINCOMPLETE = 13;
const SYNC_STATUS_INVALIDWAITORHBVALUE = 14;
const SYNC_STATUS_SYNCREQUESTINVALID = 15;
const SYNC_STATUS_RETRY = 16;

const SYNC_FSSTATUS_SUCCESS = 1;
const SYNC_FSSTATUS_FOLDEREXISTS = 2;
const SYNC_FSSTATUS_SYSTEMFOLDER = 3;
const SYNC_FSSTATUS_FOLDERDOESNOTEXIST = 4;
const SYNC_FSSTATUS_PARENTNOTFOUND = 5;
const SYNC_FSSTATUS_SERVERERROR = 6;
const SYNC_FSSTATUS_REQUESTTIMEOUT = 8;
const SYNC_FSSTATUS_SYNCKEYERROR = 9;
const SYNC_FSSTATUS_MAILFORMEDREQ = 10;
const SYNC_FSSTATUS_UNKNOWNERROR = 11;
const SYNC_FSSTATUS_CODEUNKNOWN = 12;

const SYNC_GETITEMESTSTATUS_SUCCESS = 1;
const SYNC_GETITEMESTSTATUS_COLLECTIONINVALID = 2;
const SYNC_GETITEMESTSTATUS_SYNCSTATENOTPRIMED = 3;
const SYNC_GETITEMESTSTATUS_SYNCKKEYINVALID = 4;

const SYNC_ITEMOPERATIONSSTATUS_SUCCESS = 1;
const SYNC_ITEMOPERATIONSSTATUS_PROTERROR = 2;
const SYNC_ITEMOPERATIONSSTATUS_SERVERERROR = 3;
const SYNC_ITEMOPERATIONSSTATUS_DL_BADURI = 4;
const SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED = 5;
const SYNC_ITEMOPERATIONSSTATUS_DL_NOTFOUND = 6;
const SYNC_ITEMOPERATIONSSTATUS_DL_CONNFAILED = 7;
const SYNC_ITEMOPERATIONSSTATUS_DL_BYTERANGEINVALID = 8;
const SYNC_ITEMOPERATIONSSTATUS_DL_STOREUNKNOWN = 9;
const SYNC_ITEMOPERATIONSSTATUS_DL_EMPTYFILE = 10;
const SYNC_ITEMOPERATIONSSTATUS_DL_TOOLARGE = 11;
const SYNC_ITEMOPERATIONSSTATUS_DL_IOFAILURE = 12;
const SYNC_ITEMOPERATIONSSTATUS_CONVERSIONFAILED = 14;
const SYNC_ITEMOPERATIONSSTATUS_INVALIDATT = 15;
const SYNC_ITEMOPERATIONSSTATUS_BLOCKED = 16;
const SYNC_ITEMOPERATIONSSTATUS_EMPTYFOLDER = 17;
const SYNC_ITEMOPERATIONSSTATUS_CREDSREQUIRED = 18;
const SYNC_ITEMOPERATIONSSTATUS_PROTOCOLERROR = 155;
const SYNC_ITEMOPERATIONSSTATUS_UNSUPPORTEDACTION = 156;

const SYNC_MEETRESPSTATUS_SUCCESS = 1;
const SYNC_MEETRESPSTATUS_INVALIDMEETREQ = 2;
const SYNC_MEETRESPSTATUS_MAILBOXERROR = 3;
const SYNC_MEETRESPSTATUS_SERVERERROR = 4;

const SYNC_MOVEITEMSSTATUS_INVALIDSOURCEID = 1;
const SYNC_MOVEITEMSSTATUS_INVALIDDESTID = 2;
const SYNC_MOVEITEMSSTATUS_SUCCESS = 3;
const SYNC_MOVEITEMSSTATUS_SAMESOURCEANDDEST = 4;
const SYNC_MOVEITEMSSTATUS_CANNOTMOVE = 5;
const SYNC_MOVEITEMSSTATUS_SOURCEORDESTLOCKED = 7;

const SYNC_PINGSTATUS_HBEXPIRED = 1;
const SYNC_PINGSTATUS_CHANGES = 2;
const SYNC_PINGSTATUS_FAILINGPARAMS = 3;
const SYNC_PINGSTATUS_SYNTAXERROR = 4;
const SYNC_PINGSTATUS_HBOUTOFRANGE = 5;
const SYNC_PINGSTATUS_TOOMUCHFOLDERS = 6;
const SYNC_PINGSTATUS_FOLDERHIERSYNCREQUIRED = 7;
const SYNC_PINGSTATUS_SERVERERROR = 8;

const SYNC_RESOLVERECIPSSTATUS_SUCCESS = 1;
const SYNC_RESOLVERECIPSSTATUS_PROTOCOLERROR = 5;
const SYNC_RESOLVERECIPSSTATUS_SERVERERROR = 6;
const SYNC_RESOLVERECIPSSTATUS_RESPONSE_SUCCESS = 1;
const SYNC_RESOLVERECIPSSTATUS_RESPONSE_AMBRECIP = 2;
const SYNC_RESOLVERECIPSSTATUS_RESPONSE_AMBRECIPPARTIAL = 3;
const SYNC_RESOLVERECIPSSTATUS_RESPONSE_UNRESOLVEDRECIP = 4;
const SYNC_RESOLVERECIPSSTATUS_CERTIFICATES_SUCCESS = 1;
const SYNC_RESOLVERECIPSSTATUS_CERTIFICATES_NOVALIDCERT = 7;
const SYNC_RESOLVERECIPSSTATUS_CERTIFICATES_CERTLIMIT = 8;
const SYNC_RESOLVERECIPSSTATUS_AVAILABILITY_SUCCESS = 1;
const SYNC_RESOLVERECIPSSTATUS_AVAILABILITY_MORETHAN100 = 160;
const SYNC_RESOLVERECIPSSTATUS_AVAILABILITY_MORETHAN20 = 161;
const SYNC_RESOLVERECIPSSTATUS_AVAILABILITY_REISSUE = 162;
const SYNC_RESOLVERECIPSSTATUS_AVAILABILITY_FAILED = 163;
const SYNC_RESOLVERECIPSSTATUS_PICTURE_SUCCESS = 1;
const SYNC_RESOLVERECIPSSTATUS_PICTURE_NOFOTO = 173;
const SYNC_RESOLVERECIPSSTATUS_PICTURE_MAXSIZEEXCEEDED = 174;
const SYNC_RESOLVERECIPSSTATUS_PICTURE_MAXPICTURESEXCEEDED = 175;

const SYNC_SEARCHSTATUS_SUCCESS = 1;
const SYNC_SEARCHSTATUS_SERVERERROR = 3;
const SYNC_SEARCHSTATUS_STORE_SUCCESS = 1;
const SYNC_SEARCHSTATUS_STORE_REQINVALID = 2;
const SYNC_SEARCHSTATUS_STORE_SERVERERROR = 3;
const SYNC_SEARCHSTATUS_STORE_BADLINK = 4;
const SYNC_SEARCHSTATUS_STORE_ACCESSDENIED = 5;
const SYNC_SEARCHSTATUS_STORE_NOTFOUND = 6;
const SYNC_SEARCHSTATUS_STORE_CONNECTIONFAILED = 7;
const SYNC_SEARCHSTATUS_STORE_TOOCOMPLEX = 8;
const SYNC_SEARCHSTATUS_STORE_TIMEDOUT = 10;
const SYNC_SEARCHSTATUS_STORE_FOLDERSYNCREQ = 11;
const SYNC_SEARCHSTATUS_STORE_ENDOFRETRANGE = 12;
const SYNC_SEARCHSTATUS_STORE_ACCESSBLOCKED = 13;
const SYNC_SEARCHSTATUS_STORE_CREDENTIALSREQ = 14;
const SYNC_SEARCHSTATUS_PICTURE_SUCCESS = 1;
const SYNC_SEARCHSTATUS_PICTURE_NOFOTO = 173;
const SYNC_SEARCHSTATUS_PICTURE_MAXSIZEEXCEEDED = 174;
const SYNC_SEARCHSTATUS_PICTURE_MAXPICTURESEXCEEDED = 175;

const SYNC_SETTINGSSTATUS_SUCCESS = 1;
const SYNC_SETTINGSSTATUS_PROTOCOLLERROR = 2;
const SYNC_SETTINGSSTATUS_DEVINFO_SUCCESS = 1;
const SYNC_SETTINGSSTATUS_DEVINFO_PROTOCOLLERROR = 2;
const SYNC_SETTINGSSTATUS_DEVIPASS_SUCCESS = 1;
const SYNC_SETTINGSSTATUS_DEVIPASS_PROTOCOLLERROR = 2;
const SYNC_SETTINGSSTATUS_DEVIPASS_INVALIDARGS = 3;
const SYNC_SETTINGSSTATUS_DEVIPASS_DENIED = 7;
const SYNC_SETTINGSSTATUS_USERINFO_SUCCESS = 1;
const SYNC_SETTINGSSTATUS_USERINFO_PROTOCOLLERROR = 2;

const SYNC_SETTINGSOOF_DISABLED = 0;
const SYNC_SETTINGSOOF_GLOBAL = 1;
const SYNC_SETTINGSOOF_TIMEBASED = 2;

const SYNC_MIMETRUNCATION_ALL = 0;
const SYNC_MIMETRUNCATION_4096 = 1;
const SYNC_MIMETRUNCATION_5120 = 2;
const SYNC_MIMETRUNCATION_7168 = 3;
const SYNC_MIMETRUNCATION_10240 = 4;
const SYNC_MIMETRUNCATION_20480 = 5;
const SYNC_MIMETRUNCATION_51200 = 6;
const SYNC_MIMETRUNCATION_102400 = 7;
const SYNC_MIMETRUNCATION_COMPLETE = 8;

const SYNC_MIMESUPPORT_NEVER = 0;
const SYNC_MIMESUPPORT_SMIME = 1;
const SYNC_MIMESUPPORT_ALWAYS = 2;

const SYNC_VALIDATECERTSTATUS_SUCCESS = 1;
const SYNC_VALIDATECERTSTATUS_PROTOCOLLERROR = 2;
const SYNC_VALIDATECERTSTATUS_CANTVALIDATESIG = 3;
const SYNC_VALIDATECERTSTATUS_DIGIDUNTRUSTED = 4;
const SYNC_VALIDATECERTSTATUS_CERTCHAINNOTCORRECT = 5;
const SYNC_VALIDATECERTSTATUS_DIGIDNOTVALIDFORSIGN = 6;
const SYNC_VALIDATECERTSTATUS_DIGIDNOTVALID = 7;
const SYNC_VALIDATECERTSTATUS_INVALIDCHAINCERTSTIME = 8;
const SYNC_VALIDATECERTSTATUS_DIGIDUSEDINCORRECTLY = 9;
const SYNC_VALIDATECERTSTATUS_INCORRECTDIGIDINFO = 10;
const SYNC_VALIDATECERTSTATUS_INCORRECTUSEOFDIGIDINCHAIN = 11;
const SYNC_VALIDATECERTSTATUS_DIGIDDOESNOTMATCHEMAIL = 12;
const SYNC_VALIDATECERTSTATUS_DIGIDREVOKED = 13;
const SYNC_VALIDATECERTSTATUS_DIGIDSERVERUNAVAILABLE = 14;
const SYNC_VALIDATECERTSTATUS_DIGIDINCHAINREVOKED = 15;
const SYNC_VALIDATECERTSTATUS_DIGIDREVSTATUSUNVALIDATED = 16;
const SYNC_VALIDATECERTSTATUS_SERVERERROR = 17;

const SYNC_COMMONSTATUS_SUCCESS = 1;
const SYNC_COMMONSTATUS_INVALIDCONTENT = 101;
const SYNC_COMMONSTATUS_INVALIDWBXML = 102;
const SYNC_COMMONSTATUS_INVALIDXML = 103;
const SYNC_COMMONSTATUS_INVALIDDATETIME = 104;
const SYNC_COMMONSTATUS_INVALIDCOMBINATIONOFIDS = 105;
const SYNC_COMMONSTATUS_INVALIDIDS = 106;
const SYNC_COMMONSTATUS_INVALIDMIME = 107;
const SYNC_COMMONSTATUS_DEVIDMISSINGORINVALID = 108;
const SYNC_COMMONSTATUS_DEVTYPEMISSINGORINVALID = 109;
const SYNC_COMMONSTATUS_SERVERERROR = 110;
const SYNC_COMMONSTATUS_SERVERERRORRETRYLATER = 111;
const SYNC_COMMONSTATUS_ADACCESSDENIED = 112;
const SYNC_COMMONSTATUS_MAILBOXQUOTAEXCEEDED = 113;
const SYNC_COMMONSTATUS_MAILBOXSERVEROFFLINE = 114;
const SYNC_COMMONSTATUS_SENDQUOTAEXCEEDED = 115;
const SYNC_COMMONSTATUS_MESSRECIPUNRESOLVED = 116;
const SYNC_COMMONSTATUS_MESSREPLYNOTALLOWED = 117;
const SYNC_COMMONSTATUS_MESSPREVSENT = 118;
const SYNC_COMMONSTATUS_MESSHASNORECIP = 119;
const SYNC_COMMONSTATUS_MAILSUBMISSIONFAILED = 120;
const SYNC_COMMONSTATUS_MESSREPLYFAILED = 121;
const SYNC_COMMONSTATUS_ATTTOOLARGE = 122;
const SYNC_COMMONSTATUS_USERHASNOMAILBOX = 123;
const SYNC_COMMONSTATUS_USERCANTBEANONYMOUS = 124;
const SYNC_COMMONSTATUS_USERPRINCIPALNOTFOUND = 125;
const SYNC_COMMONSTATUS_USERDISABLEDFORSYNC = 126;
const SYNC_COMMONSTATUS_USERONNEWMAILBOXCANTSYNC = 127;
const SYNC_COMMONSTATUS_USERONLEGACYMAILBOXCANTSYNC = 128;
const SYNC_COMMONSTATUS_DEVICEBLOCKEDFORUSER = 129;
const SYNC_COMMONSTATUS_ACCESSDENIED = 130;
const SYNC_COMMONSTATUS_ACCOUNTDISABLED = 131;
const SYNC_COMMONSTATUS_SYNCSTATENOTFOUND = 132;
const SYNC_COMMONSTATUS_SYNCSTATELOCKED = 133;
const SYNC_COMMONSTATUS_SYNCSTATECORRUPT = 134;
const SYNC_COMMONSTATUS_SYNCSTATEEXISTS = 135;
const SYNC_COMMONSTATUS_SYNCSTATEVERSIONINVALID = 136;
const SYNC_COMMONSTATUS_COMMANDONOTSUPPORTED = 137;
const SYNC_COMMONSTATUS_VERSIONNOTSUPPORTED = 138;
const SYNC_COMMONSTATUS_DEVNOTFULLYPROVISIONABLE = 139;
const SYNC_COMMONSTATUS_REMWIPEREQUESTED = 140;
const SYNC_COMMONSTATUS_LEGACYDEVONSTRICTPOLICY = 141;
const SYNC_COMMONSTATUS_DEVICENOTPROVISIONED = 142;
const SYNC_COMMONSTATUS_POLICYREFRESH = 143;
const SYNC_COMMONSTATUS_INVALIDPOLICYKEY = 144;
const SYNC_COMMONSTATUS_EXTMANDEVICESNOTALLOWED = 145;
const SYNC_COMMONSTATUS_NORECURRINCAL = 146;
const SYNC_COMMONSTATUS_UNEXPECTEDITEMCLASS = 147;
const SYNC_COMMONSTATUS_REMSERVERHASNOSSL = 148;
const SYNC_COMMONSTATUS_INVALIDSTOREDREQ = 149;
const SYNC_COMMONSTATUS_ITEMNOTFOUND = 150;
const SYNC_COMMONSTATUS_TOOMANYFOLDERS = 151;
const SYNC_COMMONSTATUS_NOFOLDERSFOUND = 152;
const SYNC_COMMONSTATUS_ITEMLOSTAFTERMOVE = 153;
const SYNC_COMMONSTATUS_FAILUREINMOVE = 154;
const SYNC_COMMONSTATUS_NONPERSISTANTMOVEDISALLOWED = 155;
const SYNC_COMMONSTATUS_MOVEINVALIDDESTFOLDER = 156;
const SYNC_COMMONSTATUS_INVALIDACCOUNTID = 166;
const SYNC_COMMONSTATUS_ACCOUNTSENDDISABLED = 167;
const SYNC_COMMONSTATUS_IRMFEATUREDISABLED = 168;
const SYNC_COMMONSTATUS_IRMTRANSIENTERROR = 169;
const SYNC_COMMONSTATUS_IRMPERMANENTERROR = 170;
const SYNC_COMMONSTATUS_IRMINVALIDTEMPLATEID = 171;
const SYNC_COMMONSTATUS_IRMOPERATIONNOTPERMITTED = 172;
const SYNC_COMMONSTATUS_NOPICTURE = 173;
const SYNC_COMMONSTATUS_PICTURETOOLARGE = 174;
const SYNC_COMMONSTATUS_PICTURELIMITREACHED = 175;
const SYNC_COMMONSTATUS_BODYPARTCONVERSATIONTOOLARGE = 176;
const SYNC_COMMONSTATUS_MAXDEVICESREACHED = 177;

const HTTP_CODE_200 = 200;
const HTTP_CODE_400 = 400;
const HTTP_CODE_401 = 401;
const HTTP_CODE_449 = 449;
const HTTP_CODE_500 = 500;
const HTTP_CODE_503 = 503;

const WINDOW_SIZE_MAX = 512;

//logging defs
const LOGLEVEL_OFF = 0;
const LOGLEVEL_FATAL = 1;
const LOGLEVEL_ERROR = 2;
const LOGLEVEL_WARN = 4;
const LOGLEVEL_INFO = 8;
const LOGLEVEL_DEBUG = 16;
const LOGLEVEL_WBXML = 32;
const LOGLEVEL_DEVICEID = 64;
const LOGLEVEL_WBXMLSTACK = 128;

const LOGLEVEL_ALL = LOGLEVEL_FATAL | LOGLEVEL_ERROR | LOGLEVEL_WARN | LOGLEVEL_INFO | LOGLEVEL_DEBUG | LOGLEVEL_WBXML;

const BACKEND_DISCARD_DATA = 1;

const SYNC_BODYPREFERENCE_UNDEFINED = 0;
const SYNC_BODYPREFERENCE_PLAIN = 1;
const SYNC_BODYPREFERENCE_HTML = 2;
const SYNC_BODYPREFERENCE_RTF = 3;
const SYNC_BODYPREFERENCE_MIME = 4;

const SYNC_FLAGSTATUS_CLEAR = 0;
const SYNC_FLAGSTATUS_COMPLETE = 1;
const SYNC_FLAGSTATUS_ACTIVE = 2;

const DEFAULT_EMAIL_CONTENTCLASS = "urn:content-classes:message";
const DEFAULT_CALENDAR_CONTENTCLASS = "urn:content-classes:calendarmessage";

const SYNC_MAIL_LASTVERB_UNKNOWN = 0;
const SYNC_MAIL_LASTVERB_REPLYSENDER = 1;
const SYNC_MAIL_LASTVERB_REPLYALL = 2;
const SYNC_MAIL_LASTVERB_FORWARD = 3;

const INTERNET_CPID_WINDOWS1252 = 1252;
const INTERNET_CPID_UTF8 = 65001;

const MAPI_E_NOT_ENOUGH_MEMORY_32BIT = -2147024882;
const MAPI_E_NOT_ENOUGH_MEMORY_64BIT = 2147942414;

const SYNC_SETTINGSOOF_BODYTYPE_HTML = "HTML";
const SYNC_SETTINGSOOF_BODYTYPE_TEXT = "TEXT";

const SYNC_FILEAS_FIRSTLAST = 1;
const SYNC_FILEAS_LASTFIRST = 2;
const SYNC_FILEAS_COMPANYONLY = 3;
const SYNC_FILEAS_COMPANYLAST = 4;
const SYNC_FILEAS_COMPANYFIRST = 5;
const SYNC_FILEAS_LASTCOMPANY = 6;
const SYNC_FILEAS_FIRSTCOMPANY = 7;

const SYNC_RESOLVERECIPIENTS_TYPE_GAL = 1;
const SYNC_RESOLVERECIPIENTS_TYPE_CONTACT = 2;

const SYNC_RESOLVERECIPIENTS_CERTRETRIEVE_NO = 1;
const SYNC_RESOLVERECIPIENTS_CERTRETRIEVE_FULL = 2;
const SYNC_RESOLVERECIPIENTS_CERTRETRIEVE_MINI = 3;

const NOTEIVERB_REPLYTOSENDER = 102;
const NOTEIVERB_REPLYTOALL = 103;
const NOTEIVERB_FORWARD = 104;

const AS_REPLYTOSENDER = 1;
const AS_REPLYTOALL = 2;
const AS_FORWARD = 3;

const AUTODISCOVER_LOGIN_EMAIL = 0;
const AUTODISCOVER_LOGIN_NO_DOT = 1;
const AUTODISCOVER_LOGIN_F_NO_DOT_LAST = 2;
const AUTODISCOVER_LOGIN_F_DOT_LAST = 3;
