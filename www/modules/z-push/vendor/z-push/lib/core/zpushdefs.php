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
define("SYNC_SYNCHRONIZE","Synchronize");
define("SYNC_REPLIES","Replies");
define("SYNC_ADD","Add");
define("SYNC_MODIFY","Modify");
define("SYNC_REMOVE","Remove");
define("SYNC_FETCH","Fetch");
define("SYNC_SYNCKEY","SyncKey");
define("SYNC_CLIENTENTRYID","ClientEntryId");
define("SYNC_SERVERENTRYID","ServerEntryId");
define("SYNC_STATUS","Status");
define("SYNC_FOLDER","Folder");
define("SYNC_FOLDERTYPE","FolderType");
define("SYNC_VERSION","Version"); // deprecated
define("SYNC_FOLDERID","FolderId");
define("SYNC_GETCHANGES","GetChanges");
define("SYNC_MOREAVAILABLE","MoreAvailable");
define("SYNC_WINDOWSIZE","WindowSize"); //MaxItems before z-push 2
define("SYNC_PERFORM","Perform");
define("SYNC_OPTIONS","Options");
define("SYNC_FILTERTYPE","FilterType");
define("SYNC_TRUNCATION","Truncation"); // 2.5
define("SYNC_RTFTRUNCATION","RtfTruncation"); // 2.5
define("SYNC_CONFLICT","Conflict");
define("SYNC_FOLDERS","Folders");
define("SYNC_DATA","Data");
define("SYNC_DELETESASMOVES","DeletesAsMoves");
define("SYNC_NOTIFYGUID","NotifyGUID");
define("SYNC_SUPPORTED","Supported");
define("SYNC_SOFTDELETE","SoftDelete");
define("SYNC_MIMESUPPORT","MIMESupport");
define("SYNC_MIMETRUNCATION","MIMETruncation");
define("SYNC_NEWMESSAGE","NewMessage");
define("SYNC_WAIT","Wait"); // Since 12.1
define("SYNC_LIMIT","Limit"); // Since 12.1
define("SYNC_PARTIAL","Partial"); // Since 12.1
define("SYNC_CONVERSATIONMODE","ConversationMode"); // Since 14.0
define("SYNC_MAXITEMS","MaxItems"); // Since 14.0
define("SYNC_HEARTBEATINTERVAL","HeartbeatInterval"); // Since 14.0

// Code Page 1: Contacts - POOMCONTACTS, all AS versions
define("SYNC_POOMCONTACTS_ANNIVERSARY","POOMCONTACTS:Anniversary");
define("SYNC_POOMCONTACTS_ASSISTANTNAME","POOMCONTACTS:AssistantName");
define("SYNC_POOMCONTACTS_ASSISTNAMEPHONENUMBER","POOMCONTACTS:AssistnamePhoneNumber");
define("SYNC_POOMCONTACTS_BIRTHDAY","POOMCONTACTS:Birthday");
define("SYNC_POOMCONTACTS_BODY","POOMCONTACTS:Body"); // 2.5, AirSyncBase Body is used since 12.0
define("SYNC_POOMCONTACTS_BODYSIZE","POOMCONTACTS:BodySize"); // 2.5, AirSyncBase is used since version 12.0
define("SYNC_POOMCONTACTS_BODYTRUNCATED","POOMCONTACTS:BodyTruncated"); // 2.5, AirSyncBase is used since version 12.0
define("SYNC_POOMCONTACTS_BUSINESS2PHONENUMBER","POOMCONTACTS:Business2PhoneNumber");
define("SYNC_POOMCONTACTS_BUSINESSCITY","POOMCONTACTS:BusinessCity");
define("SYNC_POOMCONTACTS_BUSINESSCOUNTRY","POOMCONTACTS:BusinessCountry");
define("SYNC_POOMCONTACTS_BUSINESSPOSTALCODE","POOMCONTACTS:BusinessPostalCode");
define("SYNC_POOMCONTACTS_BUSINESSSTATE","POOMCONTACTS:BusinessState");
define("SYNC_POOMCONTACTS_BUSINESSSTREET","POOMCONTACTS:BusinessStreet");
define("SYNC_POOMCONTACTS_BUSINESSFAXNUMBER","POOMCONTACTS:BusinessFaxNumber");
define("SYNC_POOMCONTACTS_BUSINESSPHONENUMBER","POOMCONTACTS:BusinessPhoneNumber");
define("SYNC_POOMCONTACTS_CARPHONENUMBER","POOMCONTACTS:CarPhoneNumber");
define("SYNC_POOMCONTACTS_CATEGORIES","POOMCONTACTS:Categories");
define("SYNC_POOMCONTACTS_CATEGORY","POOMCONTACTS:Category");
define("SYNC_POOMCONTACTS_CHILDREN","POOMCONTACTS:Children");
define("SYNC_POOMCONTACTS_CHILD","POOMCONTACTS:Child");
define("SYNC_POOMCONTACTS_COMPANYNAME","POOMCONTACTS:CompanyName");
define("SYNC_POOMCONTACTS_DEPARTMENT","POOMCONTACTS:Department");
define("SYNC_POOMCONTACTS_EMAIL1ADDRESS","POOMCONTACTS:Email1Address");
define("SYNC_POOMCONTACTS_EMAIL2ADDRESS","POOMCONTACTS:Email2Address");
define("SYNC_POOMCONTACTS_EMAIL3ADDRESS","POOMCONTACTS:Email3Address");
define("SYNC_POOMCONTACTS_FILEAS","POOMCONTACTS:FileAs");
define("SYNC_POOMCONTACTS_FIRSTNAME","POOMCONTACTS:FirstName");
define("SYNC_POOMCONTACTS_HOME2PHONENUMBER","POOMCONTACTS:Home2PhoneNumber");
define("SYNC_POOMCONTACTS_HOMECITY","POOMCONTACTS:HomeCity");
define("SYNC_POOMCONTACTS_HOMECOUNTRY","POOMCONTACTS:HomeCountry");
define("SYNC_POOMCONTACTS_HOMEPOSTALCODE","POOMCONTACTS:HomePostalCode");
define("SYNC_POOMCONTACTS_HOMESTATE","POOMCONTACTS:HomeState");
define("SYNC_POOMCONTACTS_HOMESTREET","POOMCONTACTS:HomeStreet");
define("SYNC_POOMCONTACTS_HOMEFAXNUMBER","POOMCONTACTS:HomeFaxNumber");
define("SYNC_POOMCONTACTS_HOMEPHONENUMBER","POOMCONTACTS:HomePhoneNumber");
define("SYNC_POOMCONTACTS_JOBTITLE","POOMCONTACTS:JobTitle");
define("SYNC_POOMCONTACTS_LASTNAME","POOMCONTACTS:LastName");
define("SYNC_POOMCONTACTS_MIDDLENAME","POOMCONTACTS:MiddleName");
define("SYNC_POOMCONTACTS_MOBILEPHONENUMBER","POOMCONTACTS:MobilePhoneNumber");
define("SYNC_POOMCONTACTS_OFFICELOCATION","POOMCONTACTS:OfficeLocation");
define("SYNC_POOMCONTACTS_OTHERCITY","POOMCONTACTS:OtherCity");
define("SYNC_POOMCONTACTS_OTHERCOUNTRY","POOMCONTACTS:OtherCountry");
define("SYNC_POOMCONTACTS_OTHERPOSTALCODE","POOMCONTACTS:OtherPostalCode");
define("SYNC_POOMCONTACTS_OTHERSTATE","POOMCONTACTS:OtherState");
define("SYNC_POOMCONTACTS_OTHERSTREET","POOMCONTACTS:OtherStreet");
define("SYNC_POOMCONTACTS_PAGERNUMBER","POOMCONTACTS:PagerNumber");
define("SYNC_POOMCONTACTS_RADIOPHONENUMBER","POOMCONTACTS:RadioPhoneNumber");
define("SYNC_POOMCONTACTS_SPOUSE","POOMCONTACTS:Spouse");
define("SYNC_POOMCONTACTS_SUFFIX","POOMCONTACTS:Suffix");
define("SYNC_POOMCONTACTS_TITLE","POOMCONTACTS:Title");
define("SYNC_POOMCONTACTS_WEBPAGE","POOMCONTACTS:WebPage");
define("SYNC_POOMCONTACTS_YOMICOMPANYNAME","POOMCONTACTS:YomiCompanyName");
define("SYNC_POOMCONTACTS_YOMIFIRSTNAME","POOMCONTACTS:YomiFirstName");
define("SYNC_POOMCONTACTS_YOMILASTNAME","POOMCONTACTS:YomiLastName");
define("SYNC_POOMCONTACTS_RTF","POOMCONTACTS:Rtf"); // deprecated
define("SYNC_POOMCONTACTS_PICTURE","POOMCONTACTS:Picture");
define("SYNC_POOMCONTACTS_ALIAS","POOMCONTACTS:Alias"); // Since 14.0
define("SYNC_POOMCONTACTS_WEIGHEDRANK","POOMCONTACTS:WeightedRank"); // Since 14.0

// Code Page 2: Email - POOMMAIL, all AS versions
define("SYNC_POOMMAIL_ATTACHMENT","POOMMAIL:Attachment"); // AirSyncBase Attachments is used since 12.0
define("SYNC_POOMMAIL_ATTACHMENTS","POOMMAIL:Attachments"); // AirSyncBase Attachments is used since 12.0
define("SYNC_POOMMAIL_ATTNAME","POOMMAIL:AttName"); // AirSyncBase Attachments is used since 12.0
define("SYNC_POOMMAIL_ATTSIZE","POOMMAIL:AttSize"); // AirSyncBase Attachments is used since 12.0
define("SYNC_POOMMAIL_ATTOID","POOMMAIL:AttOid"); // AirSyncBase Attachments is used since 12.0
define("SYNC_POOMMAIL_ATTMETHOD","POOMMAIL:AttMethod"); // AirSyncBase Attachments is used since 12.0
define("SYNC_POOMMAIL_ATTREMOVED","POOMMAIL:AttRemoved"); // AirSyncBase Attachments is used since 12.0
define("SYNC_POOMMAIL_BODY","POOMMAIL:Body"); // AirSyncBase Body is used since 12.0
define("SYNC_POOMMAIL_BODYSIZE","POOMMAIL:BodySize"); // AirSyncBase Body is used since 12.0
define("SYNC_POOMMAIL_BODYTRUNCATED","POOMMAIL:BodyTruncated"); // AirSyncBase Body is used since 12.0
define("SYNC_POOMMAIL_DATERECEIVED","POOMMAIL:DateReceived");
define("SYNC_POOMMAIL_DISPLAYNAME","POOMMAIL:DisplayName"); // AirSyncBase Attachments is used since 12.0
define("SYNC_POOMMAIL_DISPLAYTO","POOMMAIL:DisplayTo");
define("SYNC_POOMMAIL_IMPORTANCE","POOMMAIL:Importance");
define("SYNC_POOMMAIL_MESSAGECLASS","POOMMAIL:MessageClass");
define("SYNC_POOMMAIL_SUBJECT","POOMMAIL:Subject");
define("SYNC_POOMMAIL_READ","POOMMAIL:Read");
define("SYNC_POOMMAIL_TO","POOMMAIL:To");
define("SYNC_POOMMAIL_CC","POOMMAIL:Cc");
define("SYNC_POOMMAIL_FROM","POOMMAIL:From");
define("SYNC_POOMMAIL_REPLY_TO","POOMMAIL:Reply-To");
define("SYNC_POOMMAIL_ALLDAYEVENT","POOMMAIL:AllDayEvent");
define("SYNC_POOMMAIL_CATEGORIES","POOMMAIL:Categories"); // Since 14.0
define("SYNC_POOMMAIL_CATEGORY","POOMMAIL:Category"); // Since 14.0
define("SYNC_POOMMAIL_DTSTAMP","POOMMAIL:DtStamp");
define("SYNC_POOMMAIL_ENDTIME","POOMMAIL:EndTime");
define("SYNC_POOMMAIL_INSTANCETYPE","POOMMAIL:InstanceType");
define("SYNC_POOMMAIL_BUSYSTATUS","POOMMAIL:BusyStatus");
define("SYNC_POOMMAIL_LOCATION","POOMMAIL:Location"); // 2.5, 12.0, 12.1, 14.0 and 14.1. Since 16.0 AirSyncBase Location is used.
define("SYNC_POOMMAIL_MEETINGREQUEST","POOMMAIL:MeetingRequest");
define("SYNC_POOMMAIL_ORGANIZER","POOMMAIL:Organizer");
define("SYNC_POOMMAIL_RECURRENCEID","POOMMAIL:RecurrenceId");
define("SYNC_POOMMAIL_REMINDER","POOMMAIL:Reminder");
define("SYNC_POOMMAIL_RESPONSEREQUESTED","POOMMAIL:ResponseRequested");
define("SYNC_POOMMAIL_RECURRENCES","POOMMAIL:Recurrences");
define("SYNC_POOMMAIL_RECURRENCE","POOMMAIL:Recurrence");
define("SYNC_POOMMAIL_TYPE","POOMMAIL:Type");
define("SYNC_POOMMAIL_UNTIL","POOMMAIL:Until");
define("SYNC_POOMMAIL_OCCURRENCES","POOMMAIL:Occurrences");
define("SYNC_POOMMAIL_INTERVAL","POOMMAIL:Interval");
define("SYNC_POOMMAIL_DAYOFWEEK","POOMMAIL:DayOfWeek");
define("SYNC_POOMMAIL_DAYOFMONTH","POOMMAIL:DayOfMonth");
define("SYNC_POOMMAIL_WEEKOFMONTH","POOMMAIL:WeekOfMonth");
define("SYNC_POOMMAIL_MONTHOFYEAR","POOMMAIL:MonthOfYear");
define("SYNC_POOMMAIL_STARTTIME","POOMMAIL:StartTime");
define("SYNC_POOMMAIL_SENSITIVITY","POOMMAIL:Sensitivity");
define("SYNC_POOMMAIL_TIMEZONE","POOMMAIL:TimeZone");
define("SYNC_POOMMAIL_GLOBALOBJID","POOMMAIL:GlobalObjId"); // 2.5, 12.0, 12.1, 14.0 and 14.1. UID of Calendar (Code page 4) is used since 16.0
define("SYNC_POOMMAIL_THREADTOPIC","POOMMAIL:ThreadTopic");
define("SYNC_POOMMAIL_MIMEDATA","POOMMAIL:MIMEData"); // 2.5
define("SYNC_POOMMAIL_MIMETRUNCATED","POOMMAIL:MIMETruncated"); // 2.5
define("SYNC_POOMMAIL_MIMESIZE","POOMMAIL:MIMESize");
define("SYNC_POOMMAIL_INTERNETCPID","POOMMAIL:InternetCPID");
define("SYNC_POOMMAIL_FLAG", "POOMMAIL:Flag"); // Since 12.0
define("SYNC_POOMMAIL_FLAGSTATUS", "POOMMAIL:FlagStatus"); // Since 12.0
define("SYNC_POOMMAIL_CONTENTCLASS", "POOMMAIL:ContentClass"); // Since 12.0
define("SYNC_POOMMAIL_FLAGTYPE", "POOMMAIL:FlagType"); // Since 12.0
define("SYNC_POOMMAIL_COMPLETETIME", "POOMMAIL:CompleteTime"); //Since 12.0
define("SYNC_POOMMAIL_DISALLOWNEWTIMEPROPOSAL", "POOMMAIL:DisallowNewTimeProposal"); // Since 14.0

// Code Page 3: AirNotify - AIRNOTIFY, no longer in use
define("SYNC_AIRNOTIFY_NOTIFY","AirNotify:Notify");
define("SYNC_AIRNOTIFY_NOTIFICATION","AirNotify:Notification");
define("SYNC_AIRNOTIFY_VERSION","AirNotify:Version");
define("SYNC_AIRNOTIFY_LIFETIME","AirNotify:Lifetime");
define("SYNC_AIRNOTIFY_DEVICEINFO","AirNotify:DeviceInfo");
define("SYNC_AIRNOTIFY_ENABLE","AirNotify:Enable");
define("SYNC_AIRNOTIFY_FOLDER","AirNotify:Folder");
define("SYNC_AIRNOTIFY_SERVERENTRYID","AirNotify:ServerEntryId");
define("SYNC_AIRNOTIFY_DEVICEADDRESS","AirNotify:DeviceAddress");
define("SYNC_AIRNOTIFY_VALIDCARRIERPROFILES","AirNotify:ValidCarrierProfiles");
define("SYNC_AIRNOTIFY_CARRIERPROFILE","AirNotify:CarrierProfile");
define("SYNC_AIRNOTIFY_STATUS","AirNotify:Status");
define("SYNC_AIRNOTIFY_REPLIES","AirNotify:Replies");
define("SYNC_AIRNOTIFY_VERSION='1.1'","AirNotify:Version='1.1'");
define("SYNC_AIRNOTIFY_DEVICES","AirNotify:Devices");
define("SYNC_AIRNOTIFY_DEVICE","AirNotify:Device");
define("SYNC_AIRNOTIFY_ID","AirNotify:Id");
define("SYNC_AIRNOTIFY_EXPIRY","AirNotify:Expiry");
define("SYNC_AIRNOTIFY_NOTIFYGUID","AirNotify:NotifyGUID");

// Code Page 4: Calendar - POOMCAL, all AS versions
define("SYNC_POOMCAL_TIMEZONE","POOMCAL:Timezone");
define("SYNC_POOMCAL_ALLDAYEVENT","POOMCAL:AllDayEvent");
define("SYNC_POOMCAL_ATTENDEES","POOMCAL:Attendees");
define("SYNC_POOMCAL_ATTENDEE","POOMCAL:Attendee");
define("SYNC_POOMCAL_EMAIL","POOMCAL:Email");
define("SYNC_POOMCAL_NAME","POOMCAL:Name");
define("SYNC_POOMCAL_BODY","POOMCAL:Body"); // AirSyncBase Body is used since 12.0
define("SYNC_POOMCAL_BODYTRUNCATED","POOMCAL:BodyTruncated"); // AirSyncBase Body is used since 12.0
define("SYNC_POOMCAL_BUSYSTATUS","POOMCAL:BusyStatus");
define("SYNC_POOMCAL_CATEGORIES","POOMCAL:Categories");
define("SYNC_POOMCAL_CATEGORY","POOMCAL:Category");
define("SYNC_POOMCAL_RTF","POOMCAL:Rtf"); // deprecated
define("SYNC_POOMCAL_DTSTAMP","POOMCAL:DtStamp");
define("SYNC_POOMCAL_ENDTIME","POOMCAL:EndTime");
define("SYNC_POOMCAL_EXCEPTION","POOMCAL:Exception");
define("SYNC_POOMCAL_EXCEPTIONS","POOMCAL:Exceptions");
define("SYNC_POOMCAL_DELETED","POOMCAL:Deleted");
define("SYNC_POOMCAL_EXCEPTIONSTARTTIME","POOMCAL:ExceptionStartTime"); // 2.5, 12.0, 12.1, 14.0 and 14.1.
define("SYNC_POOMCAL_LOCATION","POOMCAL:Location"); // 2.5, 12.0, 12.1, 14.0 and 14.1. Since 16.0 AirSyncBase Location is used.
define("SYNC_POOMCAL_MEETINGSTATUS","POOMCAL:MeetingStatus");
define("SYNC_POOMCAL_ORGANIZEREMAIL","POOMCAL:OrganizerEmail");
define("SYNC_POOMCAL_ORGANIZERNAME","POOMCAL:OrganizerName");
define("SYNC_POOMCAL_RECURRENCE","POOMCAL:Recurrence");
define("SYNC_POOMCAL_TYPE","POOMCAL:Type");
define("SYNC_POOMCAL_UNTIL","POOMCAL:Until");
define("SYNC_POOMCAL_OCCURRENCES","POOMCAL:Occurrences");
define("SYNC_POOMCAL_INTERVAL","POOMCAL:Interval");
define("SYNC_POOMCAL_DAYOFWEEK","POOMCAL:DayOfWeek");
define("SYNC_POOMCAL_DAYOFMONTH","POOMCAL:DayOfMonth");
define("SYNC_POOMCAL_WEEKOFMONTH","POOMCAL:WeekOfMonth");
define("SYNC_POOMCAL_MONTHOFYEAR","POOMCAL:MonthOfYear");
define("SYNC_POOMCAL_REMINDER","POOMCAL:Reminder");
define("SYNC_POOMCAL_SENSITIVITY","POOMCAL:Sensitivity");
define("SYNC_POOMCAL_SUBJECT","POOMCAL:Subject");
define("SYNC_POOMCAL_STARTTIME","POOMCAL:StartTime");
define("SYNC_POOMCAL_UID","POOMCAL:UID");
define("SYNC_POOMCAL_ATTENDEESTATUS","POOMCAL:Attendee_Status"); // Since 12.0
define("SYNC_POOMCAL_ATTENDEETYPE","POOMCAL:Attendee_Type"); // Since 12.0
define("SYNC_POOMCAL_ATTACHMENT","POOMCAL:Attachment"); // Not defined / deprecated
define("SYNC_POOMCAL_ATTACHMENTS","POOMCAL:Attachments"); // Not defined / deprecated
define("SYNC_POOMCAL_ATTNAME","POOMCAL:AttName"); // Not defined / deprecated
define("SYNC_POOMCAL_ATTSIZE","POOMCAL:AttSize"); // Not defined / deprecated
define("SYNC_POOMCAL_ATTOID","POOMCAL:AttOid"); // Not defined / deprecated
define("SYNC_POOMCAL_ATTMETHOD","POOMCAL:AttMethod"); // Not defined / deprecated
define("SYNC_POOMCAL_ATTREMOVED","POOMCAL:AttRemoved"); // Not defined / deprecated
define("SYNC_POOMCAL_DISPLAYNAME","POOMCAL:DisplayName"); // Not defined / deprecated
define("SYNC_POOMCAL_DISALLOWNEWTIMEPROPOSAL","POOMCAL:DisallowNewTimeProposal"); // Since 14.0
define("SYNC_POOMCAL_RESPONSEREQUESTED","POOMCAL:ResponseRequested"); // Since 14.0
define("SYNC_POOMCAL_APPOINTMENTREPLYTIME","POOMCAL:AppointmentReplyTime"); // Since 14.0
define("SYNC_POOMCAL_RESPONSETYPE","POOMCAL:ResponseType"); // Since 14.0
define("SYNC_POOMCAL_CALENDARTYPE","POOMCAL:CalendarType"); // Since 14.0
define("SYNC_POOMCAL_ISLEAPMONTH","POOMCAL:IsLeapMonth"); // Since 14.0
define("SYNC_POOMCAL_FIRSTDAYOFWEEK","POOMCAL:FirstDayOfWeek"); // Since 14.1
define("SYNC_POOMCAL_ONLINEMEETINGCONFLINK","POOMCAL:OnlineMeetingConfLink"); // Since 14.1
define("SYNC_POOMCAL_ONLINEMEETINGEXTERNALLINK","POOMCAL:OnlineMeetingExternalLink"); // Since 14.0

// Code Page 5: Move, all AS versions
define("SYNC_MOVE_MOVES","Move:Moves");
define("SYNC_MOVE_MOVE","Move:Move");
define("SYNC_MOVE_SRCMSGID","Move:SrcMsgId");
define("SYNC_MOVE_SRCFLDID","Move:SrcFldId");
define("SYNC_MOVE_DSTFLDID","Move:DstFldId");
define("SYNC_MOVE_RESPONSE","Move:Response");
define("SYNC_MOVE_STATUS","Move:Status");
define("SYNC_MOVE_DSTMSGID","Move:DstMsgId");

// Code Page 6: GetItemEstimate, all AS versions
define("SYNC_GETITEMESTIMATE_GETITEMESTIMATE","GetItemEstimate:GetItemEstimate");
define("SYNC_GETITEMESTIMATE_VERSION","GetItemEstimate:Version"); // deprecated
define("SYNC_GETITEMESTIMATE_FOLDERS","GetItemEstimate:Folders");
define("SYNC_GETITEMESTIMATE_FOLDER","GetItemEstimate:Folder");
define("SYNC_GETITEMESTIMATE_FOLDERTYPE","GetItemEstimate:FolderType"); // AirSync Class(SYNC_FOLDERTYPE) is used since AS 14.0
define("SYNC_GETITEMESTIMATE_FOLDERID","GetItemEstimate:FolderId");
define("SYNC_GETITEMESTIMATE_DATETIME","GetItemEstimate:DateTime"); // deprecated
define("SYNC_GETITEMESTIMATE_ESTIMATE","GetItemEstimate:Estimate");
define("SYNC_GETITEMESTIMATE_RESPONSE","GetItemEstimate:Response");
define("SYNC_GETITEMESTIMATE_STATUS","GetItemEstimate:Status");

// Code Page 7: FolderHierarchy, all AS versions
define("SYNC_FOLDERHIERARCHY_FOLDERS","FolderHierarchy:Folders"); // 2.5, 12.0 and 12.1
define("SYNC_FOLDERHIERARCHY_FOLDER","FolderHierarchy:Folder"); // 2.5, 12.0 and 12.1
define("SYNC_FOLDERHIERARCHY_DISPLAYNAME","FolderHierarchy:DisplayName");
define("SYNC_FOLDERHIERARCHY_SERVERENTRYID","FolderHierarchy:ServerEntryId");
define("SYNC_FOLDERHIERARCHY_PARENTID","FolderHierarchy:ParentId");
define("SYNC_FOLDERHIERARCHY_TYPE","FolderHierarchy:Type");
define("SYNC_FOLDERHIERARCHY_RESPONSE","FolderHierarchy:Response"); // deprecated
define("SYNC_FOLDERHIERARCHY_STATUS","FolderHierarchy:Status");
define("SYNC_FOLDERHIERARCHY_CONTENTCLASS","FolderHierarchy:ContentClass"); // deprecated
define("SYNC_FOLDERHIERARCHY_CHANGES","FolderHierarchy:Changes");
define("SYNC_FOLDERHIERARCHY_ADD","FolderHierarchy:Add");
define("SYNC_FOLDERHIERARCHY_REMOVE","FolderHierarchy:Remove");
define("SYNC_FOLDERHIERARCHY_UPDATE","FolderHierarchy:Update");
define("SYNC_FOLDERHIERARCHY_SYNCKEY","FolderHierarchy:SyncKey");
define("SYNC_FOLDERHIERARCHY_FOLDERCREATE","FolderHierarchy:FolderCreate");
define("SYNC_FOLDERHIERARCHY_FOLDERDELETE","FolderHierarchy:FolderDelete");
define("SYNC_FOLDERHIERARCHY_FOLDERUPDATE","FolderHierarchy:FolderUpdate");
define("SYNC_FOLDERHIERARCHY_FOLDERSYNC","FolderHierarchy:FolderSync");
define("SYNC_FOLDERHIERARCHY_COUNT","FolderHierarchy:Count");
define("SYNC_FOLDERHIERARCHY_VERSION","FolderHierarchy:Version"); // Not defined / deprecated
// only for internal use - never to be streamed to the mobile
define("SYNC_FOLDERHIERARCHY_IGNORE_STORE","FolderHierarchy:IgnoreStore");
define("SYNC_FOLDERHIERARCHY_IGNORE_NOBCKENDFLD","FolderHierarchy:IgnoreNoBackendFolder");
define("SYNC_FOLDERHIERARCHY_IGNORE_BACKENDID","FolderHierarchy:IgnoreBackendId");
define("SYNC_FOLDERHIERARCHY_IGNORE_FLAGS","FolderHierarchy:IgnoreFlags");
define("SYNC_FOLDERHIERARCHY_IGNORE_TYPEREAL","FolderHierarchy:TypeReal");

// Code Page 8: MeetingResponse, all AS versions
define("SYNC_MEETINGRESPONSE_CALENDARID","MeetingResponse:CalendarId");
define("SYNC_MEETINGRESPONSE_FOLDERID","MeetingResponse:FolderId");
define("SYNC_MEETINGRESPONSE_MEETINGRESPONSE","MeetingResponse:MeetingResponse");
define("SYNC_MEETINGRESPONSE_REQUESTID","MeetingResponse:RequestId");
define("SYNC_MEETINGRESPONSE_REQUEST","MeetingResponse:Request");
define("SYNC_MEETINGRESPONSE_RESULT","MeetingResponse:Result");
define("SYNC_MEETINGRESPONSE_STATUS","MeetingResponse:Status");
define("SYNC_MEETINGRESPONSE_USERRESPONSE","MeetingResponse:UserResponse");
define("SYNC_MEETINGRESPONSE_VERSION","MeetingResponse:Version"); // Not defined / deprecated
define("SYNC_MEETINGRESPONSE_INSTANCEID","MeetingResponse:InstanceId"); // Since AS 14.1

// Code Page 9: Tasks - POOMTASKS, all AS versions
define("SYNC_POOMTASKS_BODY","POOMTASKS:Body"); // AirSyncBase Body is used since 12.0
define("SYNC_POOMTASKS_BODYSIZE","POOMTASKS:BodySize"); // AirSyncBase Body is used since 12.0
define("SYNC_POOMTASKS_BODYTRUNCATED","POOMTASKS:BodyTruncated"); // AirSyncBase Body is used since 12.0
define("SYNC_POOMTASKS_CATEGORIES","POOMTASKS:Categories");
define("SYNC_POOMTASKS_CATEGORY","POOMTASKS:Category");
define("SYNC_POOMTASKS_COMPLETE","POOMTASKS:Complete");
define("SYNC_POOMTASKS_DATECOMPLETED","POOMTASKS:DateCompleted");
define("SYNC_POOMTASKS_DUEDATE","POOMTASKS:DueDate");
define("SYNC_POOMTASKS_UTCDUEDATE","POOMTASKS:UtcDueDate");
define("SYNC_POOMTASKS_IMPORTANCE","POOMTASKS:Importance");
define("SYNC_POOMTASKS_RECURRENCE","POOMTASKS:Recurrence");
define("SYNC_POOMTASKS_TYPE","POOMTASKS:Type");
define("SYNC_POOMTASKS_START","POOMTASKS:Start");
define("SYNC_POOMTASKS_UNTIL","POOMTASKS:Until");
define("SYNC_POOMTASKS_OCCURRENCES","POOMTASKS:Occurrences");
define("SYNC_POOMTASKS_INTERVAL","POOMTASKS:Interval");
define("SYNC_POOMTASKS_DAYOFWEEK","POOMTASKS:DayOfWeek");
define("SYNC_POOMTASKS_DAYOFMONTH","POOMTASKS:DayOfMonth");
define("SYNC_POOMTASKS_WEEKOFMONTH","POOMTASKS:WeekOfMonth");
define("SYNC_POOMTASKS_MONTHOFYEAR","POOMTASKS:MonthOfYear");
define("SYNC_POOMTASKS_REGENERATE","POOMTASKS:Regenerate");
define("SYNC_POOMTASKS_DEADOCCUR","POOMTASKS:DeadOccur");
define("SYNC_POOMTASKS_REMINDERSET","POOMTASKS:ReminderSet");
define("SYNC_POOMTASKS_REMINDERTIME","POOMTASKS:ReminderTime");
define("SYNC_POOMTASKS_SENSITIVITY","POOMTASKS:Sensitivity");
define("SYNC_POOMTASKS_STARTDATE","POOMTASKS:StartDate");
define("SYNC_POOMTASKS_UTCSTARTDATE","POOMTASKS:UtcStartDate");
define("SYNC_POOMTASKS_SUBJECT","POOMTASKS:Subject");
define("SYNC_POOMTASKS_RTF","POOMTASKS:Rtf");
define("SYNC_POOMTASKS_ORDINALDATE","POOMTASKS:OrdinalDate"); // Since 12.0
define("SYNC_POOMTASKS_SUBORDINALDATE","POOMTASKS:SubOrdinalDate"); // Since 12.0
define("SYNC_POOMTASKS_CALENDARTYPE","POOMTASKS:CalendarType"); // Since 14.0
define("SYNC_POOMTASKS_ISLEAPMONTH","POOMTASKS:IsLeapMonth"); // Since 14.0
define("SYNC_POOMTASKS_FIRSTDAYOFWEEK","POOMTASKS:FirstDayOfWeek"); // Since 14.0

// Code Page 10: ResolveRecipients, all AS versions
define("SYNC_RESOLVERECIPIENTS_RESOLVERECIPIENTS","ResolveRecipients:ResolveRecipients");
define("SYNC_RESOLVERECIPIENTS_RESPONSE","ResolveRecipients:Response");
define("SYNC_RESOLVERECIPIENTS_STATUS","ResolveRecipients:Status");
define("SYNC_RESOLVERECIPIENTS_TYPE","ResolveRecipients:Type");
define("SYNC_RESOLVERECIPIENTS_RECIPIENT","ResolveRecipients:Recipient");
define("SYNC_RESOLVERECIPIENTS_DISPLAYNAME","ResolveRecipients:DisplayName");
define("SYNC_RESOLVERECIPIENTS_EMAILADDRESS","ResolveRecipients:EmailAddress");
define("SYNC_RESOLVERECIPIENTS_CERTIFICATES","ResolveRecipients:Certificates");
define("SYNC_RESOLVERECIPIENTS_CERTIFICATE","ResolveRecipients:Certificate");
define("SYNC_RESOLVERECIPIENTS_MINICERTIFICATE","ResolveRecipients:MiniCertificate");
define("SYNC_RESOLVERECIPIENTS_OPTIONS","ResolveRecipients:Options");
define("SYNC_RESOLVERECIPIENTS_TO","ResolveRecipients:To");
define("SYNC_RESOLVERECIPIENTS_CERTIFICATERETRIEVAL","ResolveRecipients:CertificateRetrieval");
define("SYNC_RESOLVERECIPIENTS_RECIPIENTCOUNT","ResolveRecipients:RecipientCount");
define("SYNC_RESOLVERECIPIENTS_MAXCERTIFICATES","ResolveRecipients:MaxCertificates");
define("SYNC_RESOLVERECIPIENTS_MAXAMBIGUOUSRECIPIENTS","ResolveRecipients:MaxAmbiguousRecipients");
define("SYNC_RESOLVERECIPIENTS_CERTIFICATECOUNT","ResolveRecipients:CertificateCount");
define("SYNC_RESOLVERECIPIENTS_AVAILABILITY","ResolveRecipients:Availability"); // Since 14.0
define("SYNC_RESOLVERECIPIENTS_STARTTIME","ResolveRecipients:StartTime"); // Since 14.0
define("SYNC_RESOLVERECIPIENTS_ENDTIME","ResolveRecipients:EndTime"); // Since 14.0
define("SYNC_RESOLVERECIPIENTS_MERGEDFREEBUSY","ResolveRecipients:MergedFreeBusy"); // Since 14.0
define("SYNC_RESOLVERECIPIENTS_PICTURE","ResolveRecipients:Picture"); // Since 14.1
define("SYNC_RESOLVERECIPIENTS_MAXSIZE","ResolveRecipients:MaxSize"); // Since 14.1
define("SYNC_RESOLVERECIPIENTS_DATA","ResolveRecipients:Data"); // Since 14.1
define("SYNC_RESOLVERECIPIENTS_MAXPICTURES","ResolveRecipients:MaxPictures"); // Since 14.1

// Code Page 11: ValidateCert, all AS versions
define("SYNC_VALIDATECERT_VALIDATECERT","ValidateCert:ValidateCert");
define("SYNC_VALIDATECERT_CERTIFICATES","ValidateCert:Certificates");
define("SYNC_VALIDATECERT_CERTIFICATE","ValidateCert:Certificate");
define("SYNC_VALIDATECERT_CERTIFICATECHAIN","ValidateCert:CertificateChain");
define("SYNC_VALIDATECERT_CHECKCRL","ValidateCert:CheckCRL");
define("SYNC_VALIDATECERT_STATUS","ValidateCert:Status");

// Code Page 12: Contacts2 - POOMCONTACTS2, all AS versions
define("SYNC_POOMCONTACTS2_CUSTOMERID","POOMCONTACTS2:CustomerId");
define("SYNC_POOMCONTACTS2_GOVERNMENTID","POOMCONTACTS2:GovernmentId");
define("SYNC_POOMCONTACTS2_IMADDRESS","POOMCONTACTS2:IMAddress");
define("SYNC_POOMCONTACTS2_IMADDRESS2","POOMCONTACTS2:IMAddress2");
define("SYNC_POOMCONTACTS2_IMADDRESS3","POOMCONTACTS2:IMAddress3");
define("SYNC_POOMCONTACTS2_MANAGERNAME","POOMCONTACTS2:ManagerName");
define("SYNC_POOMCONTACTS2_COMPANYMAINPHONE","POOMCONTACTS2:CompanyMainPhone");
define("SYNC_POOMCONTACTS2_ACCOUNTNAME","POOMCONTACTS2:AccountName");
define("SYNC_POOMCONTACTS2_NICKNAME","POOMCONTACTS2:NickName");
define("SYNC_POOMCONTACTS2_MMS","POOMCONTACTS2:MMS");

// Code Page 13: Ping, all AS versions
define("SYNC_PING_PING","Ping:Ping");
define("SYNC_PING_STATUS","Ping:Status");
define("SYNC_PING_LIFETIME", "Ping:LifeTime");
define("SYNC_PING_FOLDERS", "Ping:Folders");
define("SYNC_PING_FOLDER", "Ping:Folder");
define("SYNC_PING_SERVERENTRYID", "Ping:ServerEntryId");
define("SYNC_PING_FOLDERTYPE", "Ping:FolderType");
define("SYNC_PING_MAXFOLDERS", "Ping:MaxFolders");
define("SYNC_PING_VERSION", "Ping:Version"); // not defined / deprecated

// Code Page 14: Provision, all AS versions
define("SYNC_PROVISION_PROVISION", "Provision:Provision");
define("SYNC_PROVISION_POLICIES", "Provision:Policies");
define("SYNC_PROVISION_POLICY", "Provision:Policy");
define("SYNC_PROVISION_POLICYTYPE", "Provision:PolicyType");
define("SYNC_PROVISION_POLICYKEY", "Provision:PolicyKey");
define("SYNC_PROVISION_DATA", "Provision:Data");
define("SYNC_PROVISION_STATUS", "Provision:Status");
define("SYNC_PROVISION_REMOTEWIPE", "Provision:RemoteWipe");
define("SYNC_PROVISION_EASPROVISIONDOC", "Provision:EASProvisionDoc"); // Since AS 12.0
define("SYNC_PROVISION_DEVPWENABLED", "Provision:DevicePasswordEnabled"); // Since AS 12.0
define("SYNC_PROVISION_ALPHANUMPWREQ", "Provision:AlphanumericDevicePasswordRequired"); // Since AS 12.0
define("SYNC_PROVISION_DEVENCENABLED", "Provision:DeviceEncryptionEnabled"); // Since AS 12.1
define("SYNC_PROVISION_REQSTORAGECARDENC", "Provision:RequireStorageCardEncryption"); // Since AS 12.1
define("SYNC_PROVISION_PWRECOVERYENABLED", "Provision:PasswordRecoveryEnabled"); // Since AS 12.0
define("SYNC_PROVISION_DOCBROWSEENABLED", "Provision:DocumentBrowseEnabled"); // deprecated
define("SYNC_PROVISION_ATTENABLED", "Provision:AttachmentsEnabled"); // Since AS 12.0
define("SYNC_PROVISION_MINDEVPWLENGTH", "Provision:MinDevicePasswordLength"); // Since AS 12.0
define("SYNC_PROVISION_MAXINACTTIMEDEVLOCK", "Provision:MaxInactivityTimeDeviceLock"); // Since AS 12.0
define("SYNC_PROVISION_MAXDEVPWFAILEDATTEMPTS", "Provision:MaxDevicePasswordFailedAttempts"); // Since AS 12.0
define("SYNC_PROVISION_MAXATTSIZE", "Provision:MaxAttachmentSize"); // Since AS 12.0
define("SYNC_PROVISION_ALLOWSIMPLEDEVPW", "Provision:AllowSimpleDevicePassword"); // Since AS 12.0
define("SYNC_PROVISION_DEVPWEXPIRATION", "Provision:DevicePasswordExpiration"); // Since AS 12.0
define("SYNC_PROVISION_DEVPWHISTORY", "Provision:DevicePasswordHistory"); // Since AS 12.0
define("SYNC_PROVISION_ALLOWSTORAGECARD", "Provision:AllowStorageCard"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWCAM", "Provision:AllowCamera"); // Since AS 12.1
define("SYNC_PROVISION_REQDEVENC", "Provision:RequireDeviceEncryption"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWUNSIGNEDAPPS", "Provision:AllowUnsignedApplications"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWUNSIGNEDINSTALLATIONPACKAGES", "Provision:AllowUnsignedInstallationPackages"); // Since AS 12.1
define("SYNC_PROVISION_MINDEVPWCOMPLEXCHARS", "Provision:MinDevicePasswordComplexCharacters"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWWIFI", "Provision:AllowWiFi"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWTEXTMESSAGING", "Provision:AllowTextMessaging"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWPOPIMAPEMAIL", "Provision:AllowPOPIMAPEmail"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWBLUETOOTH", "Provision:AllowBluetooth"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWIRDA", "Provision:AllowIrDA"); // Since AS 12.1
define("SYNC_PROVISION_REQMANUALSYNCWHENROAM", "Provision:RequireManualSyncWhenRoaming"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWDESKTOPSYNC", "Provision:AllowDesktopSync"); // Since AS 12.1
define("SYNC_PROVISION_MAXCALAGEFILTER", "Provision:MaxCalendarAgeFilter"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWHTMLEMAIL", "Provision:AllowHTMLEmail"); // Since AS 12.1
define("SYNC_PROVISION_MAXEMAILAGEFILTER", "Provision:MaxEmailAgeFilter"); // Since AS 12.1
define("SYNC_PROVISION_MAXEMAILBODYTRUNCSIZE", "Provision:MaxEmailBodyTruncationSize"); // Since AS 12.1
define("SYNC_PROVISION_MAXEMAILHTMLBODYTRUNCSIZE", "Provision:MaxEmailHTMLBodyTruncationSize"); // Since AS 12.1
define("SYNC_PROVISION_REQSIGNEDSMIMEMESSAGES", "Provision:RequireSignedSMIMEMessages"); // Since AS 12.1
define("SYNC_PROVISION_REQENCSMIMEMESSAGES", "Provision:RequireEncryptedSMIMEMessages"); // Since AS 12.1
define("SYNC_PROVISION_REQSIGNEDSMIMEALGORITHM", "Provision:RequireSignedSMIMEAlgorithm"); // Since AS 12.1
define("SYNC_PROVISION_REQENCSMIMEALGORITHM", "Provision:RequireEncryptionSMIMEAlgorithm"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWSMIMEENCALGORITHNEG", "Provision:AllowSMIMEEncryptionAlgorithmNegotiation"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWSMIMESOFTCERTS", "Provision:AllowSMIMESoftCerts"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWBROWSER", "Provision:AllowBrowser"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWCONSUMEREMAIL", "Provision:AllowConsumerEmail"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWREMOTEDESKTOP", "Provision:AllowRemoteDesktop"); // Since AS 12.1
define("SYNC_PROVISION_ALLOWINTERNETSHARING", "Provision:AllowInternetSharing"); // Since AS 12.1
define("SYNC_PROVISION_UNAPPROVEDINROMAPPLIST", "Provision:UnapprovedInROMApplicationList"); // Since AS 12.1
define("SYNC_PROVISION_APPNAME", "Provision:ApplicationName"); // Since AS 12.1
define("SYNC_PROVISION_APPROVEDAPPLIST", "Provision:ApprovedApplicationList"); // Since AS 12.1
define("SYNC_PROVISION_HASH", "Provision:Hash"); // Since AS 12.1
// only for internal use - never to be streamed to the mobile
define("SYNC_PROVISION_POLICYNAME", "Provision:PolicyName");

// Code Page 15: Search, all AS versions
define("SYNC_SEARCH_SEARCH", "Search:Search");
define("SYNC_SEARCH_STORE", "Search:Store");
define("SYNC_SEARCH_NAME", "Search:Name");
define("SYNC_SEARCH_QUERY", "Search:Query");
define("SYNC_SEARCH_OPTIONS", "Search:Options");
define("SYNC_SEARCH_RANGE", "Search:Range");
define("SYNC_SEARCH_STATUS", "Search:Status");
define("SYNC_SEARCH_RESPONSE", "Search:Response");
define("SYNC_SEARCH_RESULT", "Search:Result");
define("SYNC_SEARCH_PROPERTIES", "Search:Properties");
define("SYNC_SEARCH_TOTAL", "Search:Total");
define("SYNC_SEARCH_EQUALTO", "Search:EqualTo"); // Since AS 12.0
define("SYNC_SEARCH_VALUE", "Search:Value"); // Since AS 12.0
define("SYNC_SEARCH_AND", "Search:And"); // Since AS 12.0
define("SYNC_SEARCH_OR", "Search:Or"); // Since AS 12.0
define("SYNC_SEARCH_FREETEXT", "Search:FreeText"); // Since AS 12.0
define("SYNC_SEARCH_DEEPTRAVERSAL", "Search:DeepTraversal"); // Since AS 12.0
define("SYNC_SEARCH_LONGID", "Search:LongId"); // Since AS 12.0
define("SYNC_SEARCH_REBUILDRESULTS", "Search:RebuildResults"); // Since AS 12.0
define("SYNC_SEARCH_LESSTHAN", "Search:LessThan"); // Since AS 12.0
define("SYNC_SEARCH_GREATERTHAN", "Search:GreaterThan"); // Since AS 12.0
define("SYNC_SEARCH_SCHEMA", "Search:Schema"); // Since AS 12.0
define("SYNC_SEARCH_SUPPORTED", "Search:Supported"); // Since AS 12.0
define("SYNC_SEARCH_USERNAME", "Search:UserName"); // Since 12.1
define("SYNC_SEARCH_PASSWORD", "Search:Password"); // Since 12.1
define("SYNC_SEARCH_CONVERSATIONID", "Search:ConversationId"); // Since 14.0
define("SYNC_SEARCH_PICTURE","Search:Picture"); // Since 14.1
define("SYNC_SEARCH_MAXSIZE","Search:MaxSize"); // Since 14.1
define("SYNC_SEARCH_MAXPICTURES","Search:MaxPictures"); // Since 14.1

// Code Page 16: GAL, all AS versions
define("SYNC_GAL_DISPLAYNAME", "GAL:DisplayName");
define("SYNC_GAL_PHONE", "GAL:Phone");
define("SYNC_GAL_OFFICE", "GAL:Office");
define("SYNC_GAL_TITLE", "GAL:Title");
define("SYNC_GAL_COMPANY", "GAL:Company");
define("SYNC_GAL_ALIAS", "GAL:Alias");
define("SYNC_GAL_FIRSTNAME", "GAL:FirstName");
define("SYNC_GAL_LASTNAME", "GAL:LastName");
define("SYNC_GAL_HOMEPHONE", "GAL:HomePhone");
define("SYNC_GAL_MOBILEPHONE", "GAL:MobilePhone");
define("SYNC_GAL_EMAILADDRESS", "GAL:EmailAddress");
define("SYNC_GAL_PICTURE","GAL:Picture"); // Since 14.1
define("SYNC_GAL_STATUS","GAL:Status"); // Since 14.1
define("SYNC_GAL_DATA","GAL:Data"); // Since 14.1

// Code Page 17: AirSyncBase, Since 12.0
define("SYNC_AIRSYNCBASE_BODYPREFERENCE", "AirSyncBase:BodyPreference");
define("SYNC_AIRSYNCBASE_TYPE", "AirSyncBase:Type");
define("SYNC_AIRSYNCBASE_TRUNCATIONSIZE", "AirSyncBase:TruncationSize");
define("SYNC_AIRSYNCBASE_ALLORNONE", "AirSyncBase:AllOrNone");
define("SYNC_AIRSYNCBASE_BODY", "AirSyncBase:Body");
define("SYNC_AIRSYNCBASE_DATA", "AirSyncBase:Data");
define("SYNC_AIRSYNCBASE_ESTIMATEDDATASIZE", "AirSyncBase:EstimatedDataSize");
define("SYNC_AIRSYNCBASE_TRUNCATED", "AirSyncBase:Truncated");
define("SYNC_AIRSYNCBASE_ATTACHMENTS", "AirSyncBase:Attachments");
define("SYNC_AIRSYNCBASE_ATTACHMENT", "AirSyncBase:Attachment");
define("SYNC_AIRSYNCBASE_DISPLAYNAME", "AirSyncBase:DisplayName");
define("SYNC_AIRSYNCBASE_FILEREFERENCE", "AirSyncBase:FileReference");
define("SYNC_AIRSYNCBASE_METHOD", "AirSyncBase:Method");
define("SYNC_AIRSYNCBASE_CONTENTID", "AirSyncBase:ContentId");
define("SYNC_AIRSYNCBASE_CONTENTLOCATION", "AirSyncBase:ContentLocation");
define("SYNC_AIRSYNCBASE_ISINLINE", "AirSyncBase:IsInline");
define("SYNC_AIRSYNCBASE_NATIVEBODYTYPE", "AirSyncBase:NativeBodyType");
define("SYNC_AIRSYNCBASE_CONTENTTYPE", "AirSyncBase:ContentType");
define("SYNC_AIRSYNCBASE_PREVIEW", "AirSyncBase:Preview"); // Since 14.0
define("SYNC_AIRSYNCBASE_BODYPARTPREFERENCE", "AirSyncBase:BodyPartPreference"); //Since 14.1
define("SYNC_AIRSYNCBASE_BODYPART", "AirSyncBase:BodyPart"); // Since 14.1
define("SYNC_AIRSYNCBASE_STATUS", "AirSyncBase:Status"); //Since 14.1

// Code Page 18: Settings, Since 12.0
define("SYNC_SETTINGS_SETTINGS", "Settings:Settings");
define("SYNC_SETTINGS_STATUS", "Settings:Status");
define("SYNC_SETTINGS_GET", "Settings:Get");
define("SYNC_SETTINGS_SET", "Settings:Set");
define("SYNC_SETTINGS_OOF", "Settings:Oof");
define("SYNC_SETTINGS_OOFSTATE", "Settings:OofState");
define("SYNC_SETTINGS_STARTTIME", "Settings:StartTime");
define("SYNC_SETTINGS_ENDTIME", "Settings:EndTime");
define("SYNC_SETTINGS_OOFMESSAGE", "Settings:OofMessage");
define("SYNC_SETTINGS_APPLIESTOINTERVAL", "Settings:AppliesToInternal");
define("SYNC_SETTINGS_APPLIESTOEXTERNALKNOWN", "Settings:AppliesToExternalKnown");
define("SYNC_SETTINGS_APPLIESTOEXTERNALUNKNOWN", "Settings:AppliesToExternalUnknown");
define("SYNC_SETTINGS_ENABLED", "Settings:Enabled");
define("SYNC_SETTINGS_REPLYMESSAGE", "Settings:ReplyMessage");
define("SYNC_SETTINGS_BODYTYPE", "Settings:BodyType");
define("SYNC_SETTINGS_DEVICEPW", "Settings:DevicePassword");
define("SYNC_SETTINGS_PW", "Settings:Password");
define("SYNC_SETTINGS_DEVICEINFORMATION", "Settings:DeviceInformation");
define("SYNC_SETTINGS_MODEL", "Settings:Model");
define("SYNC_SETTINGS_IMEI", "Settings:IMEI");
define("SYNC_SETTINGS_FRIENDLYNAME", "Settings:FriendlyName");
define("SYNC_SETTINGS_OS", "Settings:OS");
define("SYNC_SETTINGS_OSLANGUAGE", "Settings:OSLanguage");
define("SYNC_SETTINGS_PHONENUMBER", "Settings:PhoneNumber");
define("SYNC_SETTINGS_USERINFORMATION", "Settings:UserInformation");
define("SYNC_SETTINGS_EMAILADDRESSES", "Settings:EmailAddresses");
define("SYNC_SETTINGS_SMPTADDRESS", "Settings:SmtpAddress");
define("SYNC_SETTINGS_USERAGENT", "Settings:UserAgent"); // Since 12.1
define("SYNC_SETTINGS_ENABLEOUTBOUNDSMS", "Settings:EnableOutboundSMS"); // Since 14.0
define("SYNC_SETTINGS_MOBILEOPERATOR", "Settings:MobileOperator"); // Since 14.0
define("SYNC_SETTINGS_PRIMARYSMTPADDRESS", "Settings:PrimarySmtpAddress"); // Since 14.1
define("SYNC_SETTINGS_ACCOUNTS", "Settings:Accounts"); // Since 14.1
define("SYNC_SETTINGS_ACCOUNT", "Settings:Account"); // Since 14.1
define("SYNC_SETTINGS_ACCOUNTID", "Settings:AccountId"); // Since 14.1
define("SYNC_SETTINGS_ACCOUNTNAME", "Settings:AccountName"); // Since 14.1
define("SYNC_SETTINGS_USERDISPLAYNAME", "Settings:UserDisplayName"); // Since 14.1
define("SYNC_SETTINGS_SENDDISABLED", "Settings:SendDisabled"); // Since 14.1
define("SYNC_SETTINGS_RIGHTSMANAGEMENTINFORMATION", "Settings:RightsManagementInformation"); // Since 14.1
// only for internal use - never to be streamed to the mobile
define("SYNC_SETTINGS_PROP_STATUS", "Settings:PropertyStatus");

//Code Page 19: DocumentLibrary, Since 12.0
define("SYNC_DOCUMENTLIBRARY_LINKID", "DocumentLibrary:LinkId");
define("SYNC_DOCUMENTLIBRARY_DISPLAYNAME", "DocumentLibrary:DisplayName");
define("SYNC_DOCUMENTLIBRARY_ISFOLDER", "DocumentLibrary:IsFolder");
define("SYNC_DOCUMENTLIBRARY_CREATIONDATE", "DocumentLibrary:CreationDate");
define("SYNC_DOCUMENTLIBRARY_LASTMODIFIEDDATE", "DocumentLibrary:LastModifiedDate");
define("SYNC_DOCUMENTLIBRARY_ISHIDDEN", "DocumentLibrary:IsHidden");
define("SYNC_DOCUMENTLIBRARY_CONTENTLENGTH", "DocumentLibrary:ContentLength");
define("SYNC_DOCUMENTLIBRARY_CONTENTTYPE", "DocumentLibrary:ContentType");

//Code Page 20: ItemOperations, Since 12.0
define("SYNC_ITEMOPERATIONS_ITEMOPERATIONS", "ItemOperations:ItemOperations");
define("SYNC_ITEMOPERATIONS_FETCH", "ItemOperations:Fetch");
define("SYNC_ITEMOPERATIONS_STORE", "ItemOperations:Store");
define("SYNC_ITEMOPERATIONS_OPTIONS", "ItemOperations:Options");
define("SYNC_ITEMOPERATIONS_RANGE", "ItemOperations:Range");
define("SYNC_ITEMOPERATIONS_TOTAL", "ItemOperations:Total");
define("SYNC_ITEMOPERATIONS_PROPERTIES", "ItemOperations:Properties");
define("SYNC_ITEMOPERATIONS_DATA", "ItemOperations:Data");
define("SYNC_ITEMOPERATIONS_STATUS", "ItemOperations:Status");
define("SYNC_ITEMOPERATIONS_RESPONSE", "ItemOperations:Response");
define("SYNC_ITEMOPERATIONS_VERSIONS", "ItemOperations:Version");
define("SYNC_ITEMOPERATIONS_SCHEMA", "ItemOperations:Schema");
define("SYNC_ITEMOPERATIONS_PART", "ItemOperations:Part");
define("SYNC_ITEMOPERATIONS_EMPTYFOLDERCONTENTS", "ItemOperations:EmptyFolderContents");
define("SYNC_ITEMOPERATIONS_DELETESUBFOLDERS", "ItemOperations:DeleteSubFolders");
define("SYNC_ITEMOPERATIONS_USERNAME", "ItemOperations:UserName"); // Since 12.1
define("SYNC_ITEMOPERATIONS_PASSWORD", "ItemOperations:Password"); // Since 12.1
define("SYNC_ITEMOPERATIONS_MOVE", "ItemOperations:Move"); // Since 14.0
define("SYNC_ITEMOPERATIONS_DSTFLDID", "ItemOperations:DstFldId"); // Since 14.0
define("SYNC_ITEMOPERATIONS_CONVERSATIONID", "ItemOperations:ConversationId"); // Since 14.0
define("SYNC_ITEMOPERATIONS_MOVEALWAYS", "ItemOperations:MoveAlways"); // Since 14.0

// Code Page 21: ComposeMail, Since 14.0
define("SYNC_COMPOSEMAIL_SENDMAIL", "ComposeMail:SendMail");
define("SYNC_COMPOSEMAIL_SMARTFORWARD", "ComposeMail:SmartForward");
define("SYNC_COMPOSEMAIL_SMARTREPLY", "ComposeMail:SmartReply");
define("SYNC_COMPOSEMAIL_SAVEINSENTITEMS", "ComposeMail:SaveInSentItems");
define("SYNC_COMPOSEMAIL_REPLACEMIME", "ComposeMail:ReplaceMime");
define("SYNC_COMPOSEMAIL_TYPE", "ComposeMail:Type"); // not used
define("SYNC_COMPOSEMAIL_SOURCE", "ComposeMail:Source");
define("SYNC_COMPOSEMAIL_FOLDERID", "ComposeMail:FolderId");
define("SYNC_COMPOSEMAIL_ITEMID", "ComposeMail:ItemId");
define("SYNC_COMPOSEMAIL_LONGID", "ComposeMail:LongId");
define("SYNC_COMPOSEMAIL_INSTANCEID", "ComposeMail:InstanceId");
define("SYNC_COMPOSEMAIL_MIME", "ComposeMail:MIME");
define("SYNC_COMPOSEMAIL_CLIENTID", "ComposeMail:ClientId");
define("SYNC_COMPOSEMAIL_STATUS", "ComposeMail:Status");
define("SYNC_COMPOSEMAIL_ACCOUNTID", "ComposeMail:AccountId"); // Since 14.1
// only for internal use - never to be streamed to the mobile
define("SYNC_COMPOSEMAIL_REPLYFLAG","ComposeMail:ReplyFlag");
define("SYNC_COMPOSEMAIL_FORWARDFLAG","ComposeMail:ForwardFlag");

// Code Page 22: Email2 - POOMMAIL2, Since 14.0
define("SYNC_POOMMAIL2_UMCALLERID", "POOMMAIL2:UmCallerId");
define("SYNC_POOMMAIL2_UMUSERNOTES", "POOMMAIL2:UmUserNotes");
define("SYNC_POOMMAIL2_UMATTDURATION", "POOMMAIL2:UmAttDuration");
define("SYNC_POOMMAIL2_UMATTORDER", "POOMMAIL2:UmAttOrder");
define("SYNC_POOMMAIL2_CONVERSATIONID", "POOMMAIL2:ConversationId");
define("SYNC_POOMMAIL2_CONVERSATIONINDEX", "POOMMAIL2:ConversationIndex");
define("SYNC_POOMMAIL2_LASTVERBEXECUTED", "POOMMAIL2:LastVerbExecuted");
define("SYNC_POOMMAIL2_LASTVERBEXECUTIONTIME", "POOMMAIL2:LastVerbExecutionTime");
define("SYNC_POOMMAIL2_RECEIVEDASBCC", "POOMMAIL2:ReceivedAsBcc");
define("SYNC_POOMMAIL2_SENDER", "POOMMAIL2:Sender");
define("SYNC_POOMMAIL2_CALENDARTYPE", "POOMMAIL2:CalendarType");
define("SYNC_POOMMAIL2_ISLEAPMONTH", "POOMMAIL2:IsLeapMonth");
define("SYNC_POOMMAIL2_ACCOUNTID", "POOMMAIL2:AccountId"); // Since 14.1
define("SYNC_POOMMAIL2_FIRSTDAYOFWEEK", "POOMMAIL2:FirstDayOfWeek"); // Since 14.1
define("SYNC_POOMMAIL2_MEETINGMESSAGETYPE", "POOMMAIL2:MeetingMessageType"); // Since 14.1

// Code Page 23: Notes, Since 14.0
define("SYNC_NOTES_SUBJECT", "Notes:Subject");
define("SYNC_NOTES_MESSAGECLASS", "Notes:MessageClass");
define("SYNC_NOTES_LASTMODIFIEDDATE", "Notes:LastModifiedDate");
define("SYNC_NOTES_CATEGORIES", "Notes:Categories");
define("SYNC_NOTES_CATEGORY", "Notes:Category");
// only for internal use - never to be streamed to the mobile
define("SYNC_NOTES_IGNORE_COLOR","Notes:IgnoreColor");

// Code Page 24: RightsManagement, Since 14.1
define("SYNC_RIGHTSMANAGEMENT_SUPPORT", "RightsManagement:RightsManagementSupport");
define("SYNC_RIGHTSMANAGEMENT_TEMPLATES", "RightsManagement:RightsManagementTemplates");
define("SYNC_RIGHTSMANAGEMENT_TEMPLATE", "RightsManagement:RightsManagementTemplate");
define("SYNC_RIGHTSMANAGEMENT_LICENSE", "RightsManagement:RightsManagementLicense");
define("SYNC_RIGHTSMANAGEMENT_EDITALLOWED", "RightsManagement:EditAllowed");
define("SYNC_RIGHTSMANAGEMENT_REPLYALLOWED", "RightsManagement:ReplyAllowed");
define("SYNC_RIGHTSMANAGEMENT_REPLYALLALLOWED", "RightsManagement:ReplyAllAllowed");
define("SYNC_RIGHTSMANAGEMENT_FORWARDALLOWED", "RightsManagement:ForwardAllowed");
define("SYNC_RIGHTSMANAGEMENT_MODIFYRECIPIENTSALLOWED", "RightsManagement:ModifyRecipientsAllowed");
define("SYNC_RIGHTSMANAGEMENT_EXTRACTALLOWED", "RightsManagement:ExtractAllowed");
define("SYNC_RIGHTSMANAGEMENT_PRINTALLOWED", "RightsManagement:PrintAllowed");
define("SYNC_RIGHTSMANAGEMENT_EXPORTALLOWED", "RightsManagement:ExportAllowed");
define("SYNC_RIGHTSMANAGEMENT_PROGRAMMATICACCESSALLOWED", "RightsManagement:ProgrammaticAccessAllowed");
define("SYNC_RIGHTSMANAGEMENT_OWNER", "RightsManagement:Owner");
define("SYNC_RIGHTSMANAGEMENT_CONTENTEXPIRYDATE", "RightsManagement:ContentExpiryDate");
define("SYNC_RIGHTSMANAGEMENT_TEMPLATEID", "RightsManagement:TemplateID");
define("SYNC_RIGHTSMANAGEMENT_TEMPLATENAME", "RightsManagement:TemplateName");
define("SYNC_RIGHTSMANAGEMENT_TEMPLATEDESCRIPTION", "RightsManagement:TemplateDescription");
define("SYNC_RIGHTSMANAGEMENT_CONTENTOWNER", "RightsManagement:ContentOwner");
define("SYNC_RIGHTSMANAGEMENT_REMOVERIGHTSMGNTPROTECTION", "RightsManagement:RemoveRightsManagementProtection");

// Other constants
define("SYNC_FOLDER_TYPE_OTHER", 1);
define("SYNC_FOLDER_TYPE_INBOX", 2);
define("SYNC_FOLDER_TYPE_DRAFTS", 3);
define("SYNC_FOLDER_TYPE_WASTEBASKET", 4);
define("SYNC_FOLDER_TYPE_SENTMAIL", 5);
define("SYNC_FOLDER_TYPE_OUTBOX", 6);
define("SYNC_FOLDER_TYPE_TASK", 7);
define("SYNC_FOLDER_TYPE_APPOINTMENT", 8);
define("SYNC_FOLDER_TYPE_CONTACT", 9);
define("SYNC_FOLDER_TYPE_NOTE", 10);
define("SYNC_FOLDER_TYPE_JOURNAL", 11);
define("SYNC_FOLDER_TYPE_USER_MAIL", 12);
define("SYNC_FOLDER_TYPE_USER_APPOINTMENT", 13);
define("SYNC_FOLDER_TYPE_USER_CONTACT", 14);
define("SYNC_FOLDER_TYPE_USER_TASK", 15);
define("SYNC_FOLDER_TYPE_USER_JOURNAL", 16);
define("SYNC_FOLDER_TYPE_USER_NOTE", 17);
define("SYNC_FOLDER_TYPE_UNKNOWN", 18);
define("SYNC_FOLDER_TYPE_RECIPIENT_CACHE", 19);
define("SYNC_FOLDER_TYPE_DUMMY", 999999);

define("SYNC_CONFLICT_OVERWRITE_SERVER", 0);
define("SYNC_CONFLICT_OVERWRITE_PIM", 1);

define("SYNC_FILTERTYPE_ALL", 0);
define("SYNC_FILTERTYPE_1DAY", 1);
define("SYNC_FILTERTYPE_3DAYS", 2);
define("SYNC_FILTERTYPE_1WEEK", 3);
define("SYNC_FILTERTYPE_2WEEKS", 4);
define("SYNC_FILTERTYPE_1MONTH", 5);
define("SYNC_FILTERTYPE_3MONTHS", 6);
define("SYNC_FILTERTYPE_6MONTHS", 7);
define("SYNC_FILTERTYPE_INCOMPLETETASKS", 8);

define("SYNC_TRUNCATION_HEADERS", 0);
define("SYNC_TRUNCATION_512B", 1);
define("SYNC_TRUNCATION_1K", 2);
define("SYNC_TRUNCATION_2K", 3);
define("SYNC_TRUNCATION_5K", 4);
define("SYNC_TRUNCATION_10K", 5);
define("SYNC_TRUNCATION_20K", 6);
define("SYNC_TRUNCATION_50K", 7);
define("SYNC_TRUNCATION_100K", 8);
define("SYNC_TRUNCATION_ALL", 9);

define("SYNC_PROVISION_STATUS_SUCCESS", 1);
define("SYNC_PROVISION_STATUS_PROTERROR", 2);
define("SYNC_PROVISION_STATUS_SERVERERROR", 3);
define("SYNC_PROVISION_STATUS_DEVEXTMANAGED", 4);

define("SYNC_PROVISION_POLICYSTATUS_SUCCESS", 1);
define("SYNC_PROVISION_POLICYSTATUS_NOPOLICY", 2);
define("SYNC_PROVISION_POLICYSTATUS_UNKNOWNVALUE", 3);
define("SYNC_PROVISION_POLICYSTATUS_CORRUPTED", 4);
define("SYNC_PROVISION_POLICYSTATUS_POLKEYMISM", 5);

define("SYNC_PROVISION_RWSTATUS_NA", 0);
define("SYNC_PROVISION_RWSTATUS_OK", 1);
define("SYNC_PROVISION_RWSTATUS_PENDING", 2);
define("SYNC_PROVISION_RWSTATUS_REQUESTED", 4);
define("SYNC_PROVISION_RWSTATUS_WIPED", 8);

define("SYNC_STATUS_SUCCESS", 1);
define("SYNC_STATUS_INVALIDSYNCKEY", 3);
define("SYNC_STATUS_PROTOCOLLERROR", 4);
define("SYNC_STATUS_SERVERERROR", 5);
define("SYNC_STATUS_CLIENTSERVERCONVERSATIONERROR", 6);
define("SYNC_STATUS_CONFLICTCLIENTSERVEROBJECT", 7);
define("SYNC_STATUS_OBJECTNOTFOUND", 8);
define("SYNC_STATUS_SYNCCANNOTBECOMPLETED", 9);
define("SYNC_STATUS_FOLDERHIERARCHYCHANGED", 12);
define("SYNC_STATUS_SYNCREQUESTINCOMPLETE", 13);
define("SYNC_STATUS_INVALIDWAITORHBVALUE", 14);
define("SYNC_STATUS_SYNCREQUESTINVALID", 15);
define("SYNC_STATUS_RETRY", 16);

define("SYNC_FSSTATUS_SUCCESS", 1);
define("SYNC_FSSTATUS_FOLDEREXISTS", 2);
define("SYNC_FSSTATUS_SYSTEMFOLDER", 3);
define("SYNC_FSSTATUS_FOLDERDOESNOTEXIST", 4);
define("SYNC_FSSTATUS_PARENTNOTFOUND", 5);
define("SYNC_FSSTATUS_SERVERERROR", 6);
define("SYNC_FSSTATUS_REQUESTTIMEOUT", 8);
define("SYNC_FSSTATUS_SYNCKEYERROR", 9);
define("SYNC_FSSTATUS_MAILFORMEDREQ", 10);
define("SYNC_FSSTATUS_UNKNOWNERROR", 11);
define("SYNC_FSSTATUS_CODEUNKNOWN", 12);

define("SYNC_GETITEMESTSTATUS_SUCCESS", 1);
define("SYNC_GETITEMESTSTATUS_COLLECTIONINVALID", 2);
define("SYNC_GETITEMESTSTATUS_SYNCSTATENOTPRIMED", 3);
define("SYNC_GETITEMESTSTATUS_SYNCKKEYINVALID", 4);

define("SYNC_ITEMOPERATIONSSTATUS_SUCCESS", 1);
define("SYNC_ITEMOPERATIONSSTATUS_PROTERROR", 2);
define("SYNC_ITEMOPERATIONSSTATUS_SERVERERROR", 3);
define("SYNC_ITEMOPERATIONSSTATUS_DL_BADURI", 4);
define("SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED", 5);
define("SYNC_ITEMOPERATIONSSTATUS_DL_NOTFOUND", 6);
define("SYNC_ITEMOPERATIONSSTATUS_DL_CONNFAILED", 7);
define("SYNC_ITEMOPERATIONSSTATUS_DL_BYTERANGEINVALID", 8);
define("SYNC_ITEMOPERATIONSSTATUS_DL_STOREUNKNOWN", 9);
define("SYNC_ITEMOPERATIONSSTATUS_DL_EMPTYFILE", 10);
define("SYNC_ITEMOPERATIONSSTATUS_DL_TOOLARGE", 11);
define("SYNC_ITEMOPERATIONSSTATUS_DL_IOFAILURE", 12);
define("SYNC_ITEMOPERATIONSSTATUS_CONVERSIONFAILED", 14);
define("SYNC_ITEMOPERATIONSSTATUS_INVALIDATT", 15);
define("SYNC_ITEMOPERATIONSSTATUS_BLOCKED", 16);
define("SYNC_ITEMOPERATIONSSTATUS_EMPTYFOLDER", 17);
define("SYNC_ITEMOPERATIONSSTATUS_CREDSREQUIRED", 18);
define("SYNC_ITEMOPERATIONSSTATUS_PROTOCOLERROR", 155);
define("SYNC_ITEMOPERATIONSSTATUS_UNSUPPORTEDACTION", 156);

define("SYNC_MEETRESPSTATUS_SUCCESS", 1);
define("SYNC_MEETRESPSTATUS_INVALIDMEETREQ", 2);
define("SYNC_MEETRESPSTATUS_MAILBOXERROR", 3);
define("SYNC_MEETRESPSTATUS_SERVERERROR", 4);

define("SYNC_MOVEITEMSSTATUS_INVALIDSOURCEID", 1);
define("SYNC_MOVEITEMSSTATUS_INVALIDDESTID", 2);
define("SYNC_MOVEITEMSSTATUS_SUCCESS", 3);
define("SYNC_MOVEITEMSSTATUS_SAMESOURCEANDDEST", 4);
define("SYNC_MOVEITEMSSTATUS_CANNOTMOVE", 5);
define("SYNC_MOVEITEMSSTATUS_SOURCEORDESTLOCKED", 7);

define("SYNC_PINGSTATUS_HBEXPIRED", 1);
define("SYNC_PINGSTATUS_CHANGES", 2);
define("SYNC_PINGSTATUS_FAILINGPARAMS", 3);
define("SYNC_PINGSTATUS_SYNTAXERROR", 4);
define("SYNC_PINGSTATUS_HBOUTOFRANGE", 5);
define("SYNC_PINGSTATUS_TOOMUCHFOLDERS", 6);
define("SYNC_PINGSTATUS_FOLDERHIERSYNCREQUIRED", 7);
define("SYNC_PINGSTATUS_SERVERERROR", 8);

define("SYNC_RESOLVERECIPSSTATUS_SUCCESS", 1);
define("SYNC_RESOLVERECIPSSTATUS_PROTOCOLERROR", 5);
define("SYNC_RESOLVERECIPSSTATUS_SERVERERROR", 6);
define("SYNC_RESOLVERECIPSSTATUS_RESPONSE_SUCCESS", 1);
define("SYNC_RESOLVERECIPSSTATUS_RESPONSE_AMBRECIP", 2);
define("SYNC_RESOLVERECIPSSTATUS_RESPONSE_AMBRECIPPARTIAL", 3);
define("SYNC_RESOLVERECIPSSTATUS_RESPONSE_UNRESOLVEDRECIP", 4);
define("SYNC_RESOLVERECIPSSTATUS_CERTIFICATES_SUCCESS", 1);
define("SYNC_RESOLVERECIPSSTATUS_CERTIFICATES_NOVALIDCERT", 7);
define("SYNC_RESOLVERECIPSSTATUS_CERTIFICATES_CERTLIMIT", 8);
define("SYNC_RESOLVERECIPSSTATUS_AVAILABILITY_SUCCESS", 1);
define("SYNC_RESOLVERECIPSSTATUS_AVAILABILITY_MORETHAN100", 160);
define("SYNC_RESOLVERECIPSSTATUS_AVAILABILITY_MORETHAN20", 161);
define("SYNC_RESOLVERECIPSSTATUS_AVAILABILITY_REISSUE", 162);
define("SYNC_RESOLVERECIPSSTATUS_AVAILABILITY_FAILED", 163);
define("SYNC_RESOLVERECIPSSTATUS_PICTURE_SUCCESS", 1);
define("SYNC_RESOLVERECIPSSTATUS_PICTURE_NOFOTO", 173);
define("SYNC_RESOLVERECIPSSTATUS_PICTURE_MAXSIZEEXCEEDED", 174);
define("SYNC_RESOLVERECIPSSTATUS_PICTURE_MAXPICTURESEXCEEDED", 175);

define("SYNC_SEARCHSTATUS_SUCCESS", 1);
define("SYNC_SEARCHSTATUS_SERVERERROR", 3);
define("SYNC_SEARCHSTATUS_STORE_SUCCESS", 1);
define("SYNC_SEARCHSTATUS_STORE_REQINVALID", 2);
define("SYNC_SEARCHSTATUS_STORE_SERVERERROR", 3);
define("SYNC_SEARCHSTATUS_STORE_BADLINK", 4);
define("SYNC_SEARCHSTATUS_STORE_ACCESSDENIED", 5);
define("SYNC_SEARCHSTATUS_STORE_NOTFOUND", 6);
define("SYNC_SEARCHSTATUS_STORE_CONNECTIONFAILED", 7);
define("SYNC_SEARCHSTATUS_STORE_TOOCOMPLEX", 8);
define("SYNC_SEARCHSTATUS_STORE_TIMEDOUT", 10);
define("SYNC_SEARCHSTATUS_STORE_FOLDERSYNCREQ", 11);
define("SYNC_SEARCHSTATUS_STORE_ENDOFRETRANGE", 12);
define("SYNC_SEARCHSTATUS_STORE_ACCESSBLOCKED", 13);
define("SYNC_SEARCHSTATUS_STORE_CREDENTIALSREQ", 14);
define("SYNC_SEARCHSTATUS_PICTURE_SUCCESS", 1);
define("SYNC_SEARCHSTATUS_PICTURE_NOFOTO", 173);
define("SYNC_SEARCHSTATUS_PICTURE_MAXSIZEEXCEEDED", 174);
define("SYNC_SEARCHSTATUS_PICTURE_MAXPICTURESEXCEEDED", 175);

define("SYNC_SETTINGSSTATUS_SUCCESS", 1);
define("SYNC_SETTINGSSTATUS_PROTOCOLLERROR", 2);
define("SYNC_SETTINGSSTATUS_DEVINFO_SUCCESS", 1);
define("SYNC_SETTINGSSTATUS_DEVINFO_PROTOCOLLERROR", 2);
define("SYNC_SETTINGSSTATUS_DEVIPASS_SUCCESS", 1);
define("SYNC_SETTINGSSTATUS_DEVIPASS_PROTOCOLLERROR", 2);
define("SYNC_SETTINGSSTATUS_DEVIPASS_INVALIDARGS", 3);
define("SYNC_SETTINGSSTATUS_DEVIPASS_DENIED", 7);
define("SYNC_SETTINGSSTATUS_USERINFO_SUCCESS", 1);
define("SYNC_SETTINGSSTATUS_USERINFO_PROTOCOLLERROR", 2);

define("SYNC_SETTINGSOOF_DISABLED", 0);
define("SYNC_SETTINGSOOF_GLOBAL", 1);
define("SYNC_SETTINGSOOF_TIMEBASED", 2);

define("SYNC_MIMETRUNCATION_ALL", 0);
define("SYNC_MIMETRUNCATION_4096", 1);
define("SYNC_MIMETRUNCATION_5120", 2);
define("SYNC_MIMETRUNCATION_7168", 3);
define("SYNC_MIMETRUNCATION_10240", 4);
define("SYNC_MIMETRUNCATION_20480", 5);
define("SYNC_MIMETRUNCATION_51200", 6);
define("SYNC_MIMETRUNCATION_102400", 7);
define("SYNC_MIMETRUNCATION_COMPLETE", 8);

define("SYNC_MIMESUPPORT_NEVER", 0);
define("SYNC_MIMESUPPORT_SMIME", 1);
define("SYNC_MIMESUPPORT_ALWAYS", 2);

define("SYNC_VALIDATECERTSTATUS_SUCCESS", 1);
define("SYNC_VALIDATECERTSTATUS_PROTOCOLLERROR", 2);
define("SYNC_VALIDATECERTSTATUS_CANTVALIDATESIG", 3);
define("SYNC_VALIDATECERTSTATUS_DIGIDUNTRUSTED", 4);
define("SYNC_VALIDATECERTSTATUS_CERTCHAINNOTCORRECT", 5);
define("SYNC_VALIDATECERTSTATUS_DIGIDNOTVALIDFORSIGN", 6);
define("SYNC_VALIDATECERTSTATUS_DIGIDNOTVALID", 7);
define("SYNC_VALIDATECERTSTATUS_INVALIDCHAINCERTSTIME", 8);
define("SYNC_VALIDATECERTSTATUS_DIGIDUSEDINCORRECTLY", 9);
define("SYNC_VALIDATECERTSTATUS_INCORRECTDIGIDINFO", 10);
define("SYNC_VALIDATECERTSTATUS_INCORRECTUSEOFDIGIDINCHAIN", 11);
define("SYNC_VALIDATECERTSTATUS_DIGIDDOESNOTMATCHEMAIL", 12);
define("SYNC_VALIDATECERTSTATUS_DIGIDREVOKED", 13);
define("SYNC_VALIDATECERTSTATUS_DIGIDSERVERUNAVAILABLE", 14);
define("SYNC_VALIDATECERTSTATUS_DIGIDINCHAINREVOKED", 15);
define("SYNC_VALIDATECERTSTATUS_DIGIDREVSTATUSUNVALIDATED", 16);
define("SYNC_VALIDATECERTSTATUS_SERVERERROR", 17);

define("SYNC_COMMONSTATUS_SUCCESS", 1);
define("SYNC_COMMONSTATUS_INVALIDCONTENT", 101);
define("SYNC_COMMONSTATUS_INVALIDWBXML", 102);
define("SYNC_COMMONSTATUS_INVALIDXML", 103);
define("SYNC_COMMONSTATUS_INVALIDDATETIME", 104);
define("SYNC_COMMONSTATUS_INVALIDCOMBINATIONOFIDS", 105);
define("SYNC_COMMONSTATUS_INVALIDIDS", 106);
define("SYNC_COMMONSTATUS_INVALIDMIME", 107);
define("SYNC_COMMONSTATUS_DEVIDMISSINGORINVALID", 108);
define("SYNC_COMMONSTATUS_DEVTYPEMISSINGORINVALID", 109);
define("SYNC_COMMONSTATUS_SERVERERROR", 110);
define("SYNC_COMMONSTATUS_SERVERERRORRETRYLATER", 111);
define("SYNC_COMMONSTATUS_ADACCESSDENIED", 112);
define("SYNC_COMMONSTATUS_MAILBOXQUOTAEXCEEDED", 113);
define("SYNC_COMMONSTATUS_MAILBOXSERVEROFFLINE", 114);
define("SYNC_COMMONSTATUS_SENDQUOTAEXCEEDED", 115);
define("SYNC_COMMONSTATUS_MESSRECIPUNRESOLVED", 116);
define("SYNC_COMMONSTATUS_MESSREPLYNOTALLOWED", 117);
define("SYNC_COMMONSTATUS_MESSPREVSENT", 118);
define("SYNC_COMMONSTATUS_MESSHASNORECIP", 119);
define("SYNC_COMMONSTATUS_MAILSUBMISSIONFAILED", 120);
define("SYNC_COMMONSTATUS_MESSREPLYFAILED", 121);
define("SYNC_COMMONSTATUS_ATTTOOLARGE", 122);
define("SYNC_COMMONSTATUS_USERHASNOMAILBOX", 123);
define("SYNC_COMMONSTATUS_USERCANTBEANONYMOUS", 124);
define("SYNC_COMMONSTATUS_USERPRINCIPALNOTFOUND", 125);
define("SYNC_COMMONSTATUS_USERDISABLEDFORSYNC", 126);
define("SYNC_COMMONSTATUS_USERONNEWMAILBOXCANTSYNC", 127);
define("SYNC_COMMONSTATUS_USERONLEGACYMAILBOXCANTSYNC", 128);
define("SYNC_COMMONSTATUS_DEVICEBLOCKEDFORUSER", 129);
define("SYNC_COMMONSTATUS_ACCESSDENIED", 130);
define("SYNC_COMMONSTATUS_ACCOUNTDISABLED", 131);
define("SYNC_COMMONSTATUS_SYNCSTATENOTFOUND", 132);
define("SYNC_COMMONSTATUS_SYNCSTATELOCKED", 133);
define("SYNC_COMMONSTATUS_SYNCSTATECORRUPT", 134);
define("SYNC_COMMONSTATUS_SYNCSTATEEXISTS", 135);
define("SYNC_COMMONSTATUS_SYNCSTATEVERSIONINVALID", 136);
define("SYNC_COMMONSTATUS_COMMANDONOTSUPPORTED", 137);
define("SYNC_COMMONSTATUS_VERSIONNOTSUPPORTED", 138);
define("SYNC_COMMONSTATUS_DEVNOTFULLYPROVISIONABLE", 139);
define("SYNC_COMMONSTATUS_REMWIPEREQUESTED", 140);
define("SYNC_COMMONSTATUS_LEGACYDEVONSTRICTPOLICY", 141);
define("SYNC_COMMONSTATUS_DEVICENOTPROVISIONED", 142);
define("SYNC_COMMONSTATUS_POLICYREFRESH", 143);
define("SYNC_COMMONSTATUS_INVALIDPOLICYKEY", 144);
define("SYNC_COMMONSTATUS_EXTMANDEVICESNOTALLOWED", 145);
define("SYNC_COMMONSTATUS_NORECURRINCAL", 146);
define("SYNC_COMMONSTATUS_UNEXPECTEDITEMCLASS", 147);
define("SYNC_COMMONSTATUS_REMSERVERHASNOSSL", 148);
define("SYNC_COMMONSTATUS_INVALIDSTOREDREQ", 149);
define("SYNC_COMMONSTATUS_ITEMNOTFOUND", 150);
define("SYNC_COMMONSTATUS_TOOMANYFOLDERS", 151);
define("SYNC_COMMONSTATUS_NOFOLDERSFOUND", 152);
define("SYNC_COMMONSTATUS_ITEMLOSTAFTERMOVE", 153);
define("SYNC_COMMONSTATUS_FAILUREINMOVE", 154);
define("SYNC_COMMONSTATUS_NONPERSISTANTMOVEDISALLOWED", 155);
define("SYNC_COMMONSTATUS_MOVEINVALIDDESTFOLDER", 156);
define("SYNC_COMMONSTATUS_INVALIDACCOUNTID", 166);
define("SYNC_COMMONSTATUS_ACCOUNTSENDDISABLED", 167);
define("SYNC_COMMONSTATUS_IRMFEATUREDISABLED", 168);
define("SYNC_COMMONSTATUS_IRMTRANSIENTERROR", 169);
define("SYNC_COMMONSTATUS_IRMPERMANENTERROR", 170);
define("SYNC_COMMONSTATUS_IRMINVALIDTEMPLATEID", 171);
define("SYNC_COMMONSTATUS_IRMOPERATIONNOTPERMITTED", 172);
define("SYNC_COMMONSTATUS_NOPICTURE", 173);
define("SYNC_COMMONSTATUS_PICTURETOOLARGE", 174);
define("SYNC_COMMONSTATUS_PICTURELIMITREACHED", 175);
define("SYNC_COMMONSTATUS_BODYPARTCONVERSATIONTOOLARGE", 176);
define("SYNC_COMMONSTATUS_MAXDEVICESREACHED", 177);

define("HTTP_CODE_200", 200);
define("HTTP_CODE_400", 400);
define("HTTP_CODE_401", 401);
define("HTTP_CODE_449", 449);
define("HTTP_CODE_500", 500);
define("HTTP_CODE_503", 503);

define("WINDOW_SIZE_MAX", 512);

//logging defs
define("LOGLEVEL_OFF", 0);
define("LOGLEVEL_FATAL", 1);
define("LOGLEVEL_ERROR", 2);
define("LOGLEVEL_WARN", 4);
define("LOGLEVEL_INFO", 8);
define("LOGLEVEL_DEBUG", 16);
define("LOGLEVEL_WBXML", 32);
define("LOGLEVEL_DEVICEID", 64);
define("LOGLEVEL_WBXMLSTACK", 128);

define("LOGLEVEL_ALL", LOGLEVEL_FATAL | LOGLEVEL_ERROR | LOGLEVEL_WARN | LOGLEVEL_INFO | LOGLEVEL_DEBUG | LOGLEVEL_WBXML);

define("BACKEND_DISCARD_DATA", 1);

define("SYNC_BODYPREFERENCE_UNDEFINED", 0);
define("SYNC_BODYPREFERENCE_PLAIN", 1);
define("SYNC_BODYPREFERENCE_HTML", 2);
define("SYNC_BODYPREFERENCE_RTF", 3);
define("SYNC_BODYPREFERENCE_MIME", 4);

define("SYNC_FLAGSTATUS_CLEAR", 0);
define("SYNC_FLAGSTATUS_COMPLETE", 1);
define("SYNC_FLAGSTATUS_ACTIVE", 2);

define("DEFAULT_EMAIL_CONTENTCLASS", "urn:content-classes:message");
define("DEFAULT_CALENDAR_CONTENTCLASS", "urn:content-classes:calendarmessage");

define("SYNC_MAIL_LASTVERB_UNKNOWN", 0);
define("SYNC_MAIL_LASTVERB_REPLYSENDER", 1);
define("SYNC_MAIL_LASTVERB_REPLYALL", 2);
define("SYNC_MAIL_LASTVERB_FORWARD", 3);

define("INTERNET_CPID_WINDOWS1252", 1252);
define("INTERNET_CPID_UTF8", 65001);

define("MAPI_E_NOT_ENOUGH_MEMORY_32BIT", -2147024882);
define("MAPI_E_NOT_ENOUGH_MEMORY_64BIT", 2147942414);

define("SYNC_SETTINGSOOF_BODYTYPE_HTML", "HTML");
define("SYNC_SETTINGSOOF_BODYTYPE_TEXT", "TEXT");

define("SYNC_FILEAS_FIRSTLAST", 1);
define("SYNC_FILEAS_LASTFIRST", 2);
define("SYNC_FILEAS_COMPANYONLY", 3);
define("SYNC_FILEAS_COMPANYLAST", 4);
define("SYNC_FILEAS_COMPANYFIRST", 5);
define("SYNC_FILEAS_LASTCOMPANY", 6);
define("SYNC_FILEAS_FIRSTCOMPANY", 7);

define ("SYNC_RESOLVERECIPIENTS_TYPE_GAL", 1);
define ("SYNC_RESOLVERECIPIENTS_TYPE_CONTACT", 2);

define("SYNC_RESOLVERECIPIENTS_CERTRETRIEVE_NO", 1);
define("SYNC_RESOLVERECIPIENTS_CERTRETRIEVE_FULL", 2);
define("SYNC_RESOLVERECIPIENTS_CERTRETRIEVE_MINI", 3);

define("NOTEIVERB_REPLYTOSENDER", 102);
define("NOTEIVERB_REPLYTOALL", 103);
define("NOTEIVERB_FORWARD", 104);

define("AS_REPLYTOSENDER", 1);
define("AS_REPLYTOALL", 2);
define("AS_FORWARD", 3);

define('AUTODISCOVER_LOGIN_EMAIL', 0);
define('AUTODISCOVER_LOGIN_NO_DOT', 1);
define('AUTODISCOVER_LOGIN_F_NO_DOT_LAST', 2);
define('AUTODISCOVER_LOGIN_F_DOT_LAST', 3);
