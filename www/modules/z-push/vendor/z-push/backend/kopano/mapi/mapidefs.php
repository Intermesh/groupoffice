<?php
/*
 * Copyright 2005 - 2016  Zarafa B.V. and its licensors
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
 */

/* Resource types as defined in main.h of the mapi extension */
define('RESOURCE_SESSION'                        ,'MAPI Session');
define('RESOURCE_TABLE'                          ,'MAPI Table');
define('RESOURCE_ROWSET'                         ,'MAPI Rowset');
define('RESOURCE_MSGSTORE'                       ,'MAPI Message Store');
define('RESOURCE_FOLDER'                         ,'MAPI Folder');
define('RESOURCE_MESSAGE'                        ,'MAPI Message');
define('RESOURCE_ATTACHMENT'                     ,'MAPI Attachment');


/* Object type */

define('MAPI_STORE'                              ,0x00000001);    /* Message Store */
define('MAPI_ADDRBOOK'                           ,0x00000002);    /* Address Book */
define('MAPI_FOLDER'                             ,0x00000003);    /* Folder */
define('MAPI_ABCONT'                             ,0x00000004);    /* Address Book Container */
define('MAPI_MESSAGE'                            ,0x00000005);    /* Message */
define('MAPI_MAILUSER'                           ,0x00000006);    /* Individual Recipient */
define('MAPI_ATTACH'                             ,0x00000007);    /* Attachment */
define('MAPI_DISTLIST'                           ,0x00000008);    /* Distribution List Recipient */
define('MAPI_PROFSECT'                           ,0x00000009);    /* Profile Section */
define('MAPI_STATUS'                             ,0x0000000A);    /* Status Object */
define('MAPI_SESSION'                            ,0x0000000B);    /* Session */
define('MAPI_FORMINFO'                           ,0x0000000C);    /* Form Information */

define('MV_FLAG'                                 ,0x1000);
define('MV_INSTANCE'                             ,0x2000);
define('MVI_FLAG'                                ,MV_FLAG | MV_INSTANCE);

define('PT_UNSPECIFIED'                          ,  0);    /* (Reserved for interface use) type doesn't matter to caller */
define('PT_NULL'                                 ,  1);    /* NULL property value */
define('PT_I2'                                   ,  2);    /* Signed 16-bit value */
define('PT_LONG'                                 ,  3);    /* Signed 32-bit value */
define('PT_R4'                                   ,  4);    /* 4-byte floating point */
define('PT_DOUBLE'                               ,  5);    /* Floating point double */
define('PT_CURRENCY'                             ,  6);    /* Signed 64-bit int (decimal w/    4 digits right of decimal pt) */
define('PT_APPTIME'                              ,  7);    /* Application time */
define('PT_ERROR'                                , 10);    /* 32-bit error value */
define('PT_BOOLEAN'                              , 11);    /* 16-bit boolean (non-zero true) */
define('PT_OBJECT'                               , 13);    /* Embedded object in a property */
define('PT_I8'                                   , 20);    /* 8-byte signed integer */
define('PT_STRING8'                              , 30);    /* Null terminated 8-bit character string */
define('PT_UNICODE'                              , 31);    /* Null terminated Unicode string */
define('PT_SYSTIME'                              , 64);    /* FILETIME 64-bit int w/ number of 100ns periods since Jan 1,1601 */
define('PT_CLSID'                                , 72);    /* OLE GUID */
define('PT_BINARY'                               ,258);   /* Uninterpreted (counted byte array) */
/* Changes are likely to these numbers, and to their structures. */

/* Alternate property type names for ease of use */
define('PT_SHORT'                                ,PT_I2);
define('PT_I4'                                   ,PT_LONG);
define('PT_FLOAT'                                ,PT_R4);
define('PT_R8'                                   ,PT_DOUBLE);
define('PT_LONGLONG'                             ,PT_I8);


define('PT_TSTRING'                              ,PT_STRING8);



define('PT_MV_I2'                                ,(MV_FLAG | PT_I2));
define('PT_MV_LONG'                              ,(MV_FLAG | PT_LONG));
define('PT_MV_R4'                                ,(MV_FLAG | PT_R4));
define('PT_MV_DOUBLE'                            ,(MV_FLAG | PT_DOUBLE));
define('PT_MV_CURRENCY'                          ,(MV_FLAG | PT_CURRENCY));
define('PT_MV_APPTIME'                           ,(MV_FLAG | PT_APPTIME));
define('PT_MV_SYSTIME'                           ,(MV_FLAG | PT_SYSTIME));
define('PT_MV_STRING8'                           ,(MV_FLAG | PT_STRING8));
define('PT_MV_BINARY'                            ,(MV_FLAG | PT_BINARY));
define('PT_MV_UNICODE'                           ,(MV_FLAG | PT_UNICODE));
define('PT_MV_CLSID'                             ,(MV_FLAG | PT_CLSID));
define('PT_MV_I8'                                ,(MV_FLAG | PT_I8));

define('PT_MV_TSTRING'                           ,PT_MV_STRING8);
/* bit 0: set if descending, clear if ascending */

define('TABLE_SORT_ASCEND'                       ,(0x00000000));
define('TABLE_SORT_DESCEND'                      ,(0x00000001));
define('TABLE_SORT_COMBINE'                      ,(0x00000002));

/* Bookmarks in Table */
define('BOOKMARK_BEGINNING'                      , 0); /* Before first row */
define('BOOKMARK_CURRENT'                        , 1); /* Before current row */
define('BOOKMARK_END'                            , 2); /* After last row */

define('MAPI_UNICODE'                            ,0x80000000);

/* IMAPIFolder Interface --------------------------------------------------- */
define('CONVENIENT_DEPTH'                        ,0x00000001);
define('SEARCH_RUNNING'                          ,0x00000001);
define('SEARCH_REBUILD'                          ,0x00000002);
define('SEARCH_RECURSIVE'                        ,0x00000004);
define('SEARCH_FOREGROUND'                       ,0x00000008);
define('STOP_SEARCH'                             ,0x00000001);
define('RESTART_SEARCH'                          ,0x00000002);
define('RECURSIVE_SEARCH'                        ,0x00000004);
define('SHALLOW_SEARCH'                          ,0x00000008);
define('FOREGROUND_SEARCH'                       ,0x00000010);
define('BACKGROUND_SEARCH'                       ,0x00000020);

/* IMAPIFolder folder type (enum) */

define('FOLDER_ROOT'                             ,0x00000000);
define('FOLDER_GENERIC'                          ,0x00000001);
define('FOLDER_SEARCH'                           ,0x00000002);

/* CreateMessage */
/****** MAPI_DEFERRED_ERRORS    ((ULONG) 0x00000008) below */
/****** MAPI_ASSOCIATED         ((ULONG) 0x00000040) below */

/* CopyMessages */

define('MESSAGE_MOVE'                            ,0x00000001);
define('MESSAGE_DIALOG'                          ,0x00000002);
/****** MAPI_DECLINE_OK         ((ULONG) 0x00000004) above */

/* CreateFolder */

define('OPEN_IF_EXISTS'                          ,0x00000001);
/****** MAPI_DEFERRED_ERRORS    ((ULONG) 0x00000008) below */
/****** MAPI_UNICODE            ((ULONG) 0x80000000) above */

/* DeleteFolder */

define('DEL_MESSAGES'                            ,0x00000001);
define('FOLDER_DIALOG'                           ,0x00000002);
define('DEL_FOLDERS'                             ,0x00000004);

/* EmptyFolder */
define('DEL_ASSOCIATED'                          ,0x00000008);

/* CopyFolder */

define('FOLDER_MOVE'                             ,0x00000001);
/****** FOLDER_DIALOG           ((ULONG) 0x00000002) above */
/****** MAPI_DECLINE_OK         ((ULONG) 0x00000004) above */
define('COPY_SUBFOLDERS'                         ,0x00000010);
/****** MAPI_UNICODE            ((ULONG) 0x80000000) above */


/* SetReadFlags */

define('SUPPRESS_RECEIPT'                        ,0x00000001);
/****** FOLDER_DIALOG           ((ULONG) 0x00000002) above */
define('CLEAR_READ_FLAG'                         ,0x00000004);
/****** MAPI_DEFERRED_ERRORS    ((ULONG) 0x00000008) below */
define('GENERATE_RECEIPT_ONLY'                   ,0x00000010);
define('CLEAR_RN_PENDING'                        ,0x00000020);
define('CLEAR_NRN_PENDING'                       ,0x00000040);

/* Flags defined in PR_MESSAGE_FLAGS */

define('MSGFLAG_READ'                            ,0x00000001);
define('MSGFLAG_UNMODIFIED'                      ,0x00000002);
define('MSGFLAG_SUBMIT'                          ,0x00000004);
define('MSGFLAG_UNSENT'                          ,0x00000008);
define('MSGFLAG_HASATTACH'                       ,0x00000010);
define('MSGFLAG_FROMME'                          ,0x00000020);
define('MSGFLAG_ASSOCIATED'                      ,0x00000040);
define('MSGFLAG_RESEND'                          ,0x00000080);
define('MSGFLAG_RN_PENDING'                      ,0x00000100);
define('MSGFLAG_NRN_PENDING'                     ,0x00000200);

/* GetMessageStatus */

define('MSGSTATUS_HIGHLIGHTED'                   ,0x00000001);
define('MSGSTATUS_TAGGED'                        ,0x00000002);
define('MSGSTATUS_HIDDEN'                        ,0x00000004);
define('MSGSTATUS_DELMARKED'                     ,0x00000008);

/* Bits for remote message status */

define('MSGSTATUS_REMOTE_DOWNLOAD'               ,0x00001000);
define('MSGSTATUS_REMOTE_DELETE'                 ,0x00002000);

/* SaveContentsSort */

define('RECURSIVE_SORT'                          ,0x00000002);

/* PR_STATUS property */

define('FLDSTATUS_HIGHLIGHTED'                   ,0x00000001);
define('FLDSTATUS_TAGGED'                        ,0x00000002);
define('FLDSTATUS_HIDDEN'                        ,0x00000004);
define('FLDSTATUS_DELMARKED'                     ,0x00000008);


/* IMAPIStatus Interface --------------------------------------------------- */

/* Values for PR_RESOURCE_TYPE, _METHODS, _FLAGS */

define('MAPI_STORE_PROVIDER'                     , 33);    /* Message Store */
define('MAPI_AB'                                 , 34);    /* Address Book */
define('MAPI_AB_PROVIDER'                        , 35);    /* Address Book Provider */
define('MAPI_TRANSPORT_PROVIDER'                 , 36);    /* Transport Provider */
define('MAPI_SPOOLER'                            , 37);    /* Message Spooler */
define('MAPI_PROFILE_PROVIDER'                   , 38);    /* Profile Provider */
define('MAPI_SUBSYSTEM'                          , 39);    /* Overall Subsystem Status */
define('MAPI_HOOK_PROVIDER'                      , 40);    /* Spooler Hook */
define('STATUS_VALIDATE_STATE'                   ,0x00000001);
define('STATUS_SETTINGS_DIALOG'                  ,0x00000002);
define('STATUS_CHANGE_PASSWORD'                  ,0x00000004);
define('STATUS_FLUSH_QUEUES'                     ,0x00000008);

define('STATUS_DEFAULT_OUTBOUND'                 ,0x00000001);
define('STATUS_DEFAULT_STORE'                    ,0x00000002);
define('STATUS_PRIMARY_IDENTITY'                 ,0x00000004);
define('STATUS_SIMPLE_STORE'                     ,0x00000008);
define('STATUS_XP_PREFER_LAST'                   ,0x00000010);
define('STATUS_NO_PRIMARY_IDENTITY'              ,0x00000020);
define('STATUS_NO_DEFAULT_STORE'                 ,0x00000040);
define('STATUS_TEMP_SECTION'                     ,0x00000080);
define('STATUS_OWN_STORE'                        ,0x00000100);
define('STATUS_NEED_IPM_TREE'                    ,0x00000800);
define('STATUS_PRIMARY_STORE'                    ,0x00001000);
define('STATUS_SECONDARY_STORE'                  ,0x00002000);


/* ------------ */
/* Random flags */

/* Flag for deferred error */
define('MAPI_DEFERRED_ERRORS'                    ,0x00000008);

/* Flag for creating and using Folder Associated Information Messages */
define('MAPI_ASSOCIATED'                         ,0x00000040);

/* Flags for OpenMessageStore() */

define('MDB_NO_DIALOG'                           ,0x00000001);
define('MDB_WRITE'                               ,0x00000004);
/****** MAPI_DEFERRED_ERRORS    ((ULONG) 0x00000008) above */
/****** MAPI_BEST_ACCESS        ((ULONG) 0x00000010) above */
define('MDB_TEMPORARY'                           ,0x00000020);
define('MDB_NO_MAIL'                             ,0x00000080);

/* Flags for OpenAddressBook */

define('AB_NO_DIALOG'                            ,0x00000001);

/* ((ULONG) 0x00000001 is not a valid flag on ModifyRecipients. */
define('MODRECIP_ADD'                            ,0x00000002);
define('MODRECIP_MODIFY'                         ,0x00000004);
define('MODRECIP_REMOVE'                         ,0x00000008);


define('MAPI_ORIG'                               ,0);          /* Recipient is message originator          */
define('MAPI_TO'                                 ,1);          /* Recipient is a primary recipient         */
define('MAPI_CC'                                 ,2);          /* Recipient is a copy recipient            */
define('MAPI_BCC'                                ,3);          /* Recipient is blind copy recipient        */


/* IAttach Interface ------------------------------------------------------- */

/* IAttach attachment methods: PR_ATTACH_METHOD values */

define('NO_ATTACHMENT'                           ,0x00000000);
define('ATTACH_BY_VALUE'                         ,0x00000001);
define('ATTACH_BY_REFERENCE'                     ,0x00000002);
define('ATTACH_BY_REF_RESOLVE'                   ,0x00000003);
define('ATTACH_BY_REF_ONLY'                      ,0x00000004);
define('ATTACH_EMBEDDED_MSG'                     ,0x00000005);
define('ATTACH_OLE'                              ,0x00000006);

/* OpenProperty  - ulFlags */
define('MAPI_MODIFY'                             ,0x00000001);
define('MAPI_CREATE'                             ,0x00000002);
define('STREAM_APPEND'                           ,0x00000004);
/****** MAPI_DEFERRED_ERRORS    ((ULONG) 0x00000008) below */


/* PR_PRIORITY values */
define('PRIO_URGENT'                             , 1);
define('PRIO_NORMAL'                             , 0);
define('PRIO_NONURGENT'                          ,-1);

/* PR_SENSITIVITY values */
define('SENSITIVITY_NONE'                        ,0x00000000);
define('SENSITIVITY_PERSONAL'                    ,0x00000001);
define('SENSITIVITY_PRIVATE'                     ,0x00000002);
define('SENSITIVITY_COMPANY_CONFIDENTIAL'        ,0x00000003);

/* PR_IMPORTANCE values */
define('IMPORTANCE_LOW'                          ,0);
define('IMPORTANCE_NORMAL'                       ,1);
define('IMPORTANCE_HIGH'                         ,2);

/* Stream interace values */
define('STREAM_SEEK_SET'                         ,0);
define('STREAM_SEEK_CUR'                         ,1);
define('STREAM_SEEK_END'                         ,2);

define('SHOW_SOFT_DELETES'                       ,0x00000002);
define('DELETE_HARD_DELETE'                      ,0x00000010);

/*
 *    The following flags are used to indicate to the client what access
 *    level is permissible in the object. They appear in PR_ACCESS in
 *    message and folder objects as well as in contents and associated
 *    contents tables
 */
define('MAPI_ACCESS_MODIFY'                      ,0x00000001);
define('MAPI_ACCESS_READ'                        ,0x00000002);
define('MAPI_ACCESS_DELETE'                      ,0x00000004);
define('MAPI_ACCESS_CREATE_HIERARCHY'            ,0x00000008);
define('MAPI_ACCESS_CREATE_CONTENTS'             ,0x00000010);
define('MAPI_ACCESS_CREATE_ASSOCIATED'           ,0x00000020);

define('MAPI_SEND_NO_RICH_INFO'                  ,0x00010000);

/* flags for PR_STORE_SUPPORT_MASK */
define('STORE_ANSI_OK'                           ,0x00020000); // The message store supports properties containing ANSI (8-bit) characters.
define('STORE_ATTACH_OK'                         ,0x00000020); // The message store supports attachments (OLE or non-OLE) to messages.
define('STORE_CATEGORIZE_OK'                     ,0x00000400); // The message store supports categorized views of tables.
define('STORE_CREATE_OK'                         ,0x00000010); // The message store supports creation of new messages.
define('STORE_ENTRYID_UNIQUE'                    ,0x00000001); // Entry identifiers for the objects in the message store are unique, that is, never reused during the life of the store.
define('STORE_HTML_OK'                           ,0x00010000); // The message store supports Hypertext Markup Language (HTML) messages, stored in the PR_BODY_HTML property. Note that STORE_HTML_OK is not defined in versions of MAPIDEFS.H included with Microsoft� Exchange 2000 Server and earlier. If your development environment uses a MAPIDEFS.H file that does not include STORE_HTML_OK, use the value 0x00010000 instead.
define('STORE_LOCALSTORE'                        ,0x00080000); // This flag is reserved and should not be used.
define('STORE_MODIFY_OK'                         ,0x00000008); // The message store supports modification of its existing messages.
define('STORE_MV_PROPS_OK'                       ,0x00000200); // The message store supports multivalued properties, guarantees the stability of value order in a multivalued property throughout a save operation, and supports instantiation of multivalued properties in tables.
define('STORE_NOTIFY_OK'                         ,0x00000100); // The message store supports notifications.
define('STORE_OLE_OK'                            ,0x00000040); // The message store supports OLE attachments. The OLE data is accessible through an IStorage interface, such as that available through the PR_ATTACH_DATA_OBJ property.
define('STORE_PUBLIC_FOLDERS'                    ,0x00004000); // The folders in this store are public (multi-user), not private (possibly multi-instance but not multi-user).
define('STORE_READONLY'                          ,0x00000002); // All interfaces for the message store have a read-only access level.
define('STORE_RESTRICTION_OK'                    ,0x00001000); // The message store supports restrictions.
define('STORE_RTF_OK'                            ,0x00000800); // The message store supports Rich Text Format (RTF) messages, usually stored compressed, and the store itself keeps PR_BODY and PR_RTF_COMPRESSED synchronized.
define('STORE_SEARCH_OK'                         ,0x00000004); // The message store supports search-results folders.
define('STORE_SORT_OK'                           ,0x00002000); // The message store supports sorting views of tables.
define('STORE_SUBMIT_OK'                         ,0x00000080); // The message store supports marking a message for submission.
define('STORE_UNCOMPRESSED_RTF'                  ,0x00008000); // The message store supports storage of Rich Text Format (RTF) messages in uncompressed form. An uncompressed RTF stream is identified by the value dwMagicUncompressedRTF in the stream header. The dwMagicUncompressedRTF value is defined in the RTFLIB.H file.
define('STORE_UNICODE_OK'                        ,0x00040000); // The message store supports properties containing Unicode characters.


/*  PR_DISPLAY_TYPEs                 */
/*  For address book contents tables */
define('DT_MAILUSER'                             ,0x00000000);
define('DT_DISTLIST'                             ,0x00000001);
define('DT_FORUM'                                ,0x00000002);
define('DT_AGENT'                                ,0x00000003);
define('DT_ORGANIZATION'                         ,0x00000004);
define('DT_PRIVATE_DISTLIST'                     ,0x00000005);
define('DT_REMOTE_MAILUSER'                      ,0x00000006);

/* For address book hierarchy tables */
define('DT_MODIFIABLE'                           ,0x00010000);
define('DT_GLOBAL'                               ,0x00020000);
define('DT_LOCAL'                                ,0x00030000);
define('DT_WAN'                                  ,0x00040000);
define('DT_NOT_SPECIFIC'                         ,0x00050000);

/* For folder hierarchy tables */
define('DT_FOLDER'                               ,0x01000000);
define('DT_FOLDER_LINK'                          ,0x02000000);
define('DT_FOLDER_SPECIAL'                       ,0x04000000);

/* PR_DISPLAY_TYPE_EX values */
define('DT_ROOM'                                 ,0x00000007);
define('DT_EQUIPMENT'                            ,0x00000008);
define('DT_SEC_DISTLIST'                         ,0x00000009);

/* PR_DISPLAY_TYPE_EX flags */
define('DTE_FLAG_REMOTE_VALID'                   ,0x80000000);
define('DTE_FLAG_ACL_CAPABLE'                    ,0x40000000); /* on for DT_MAILUSER and DT_SEC_DISTLIST */
define('DTE_MASK_REMOTE'                         ,0x0000FF00);
define('DTE_MASK_LOCAL'                          ,0x000000FF);

/* OlResponseStatus */
define('olResponseNone'                          ,0);
define('olResponseOrganized'                     ,1);
define('olResponseTentative'                     ,2);
define('olResponseAccepted'                      ,3);
define('olResponseDeclined'                      ,4);
define('olResponseNotResponded'                  ,5);

/* OlRecipientTrackStatus to set PR_RECIPIENT_TRACKSTATUS in recipient table
 * Value of the recipient trackstatus are same as OlResponseStatus but
 * recipient trackstatus doesn't have olResponseOrganized and olResponseNotResponded
 * and olResponseNone has different interpretation with PR_RECIPIENT_TRACKSTATUS
 * so to avoid confusions we have defined new constants.
*/
define('olRecipientTrackStatusNone'              ,0);
define('olRecipientTrackStatusTentative'         ,2);
define('olRecipientTrackStatusAccepted'          ,3);
define('olRecipientTrackStatusDeclined'          ,4);

/* OlMeetingStatus */
define('olNonMeeting'                            ,0);
define('olMeeting'                               ,1);
define('olMeetingReceived'                       ,3);
define('olMeetingCanceled'                       ,5);
define('olMeetingReceivedAndCanceled'            ,7);

/*    OlMeetingResponse */
define('olMeetingTentative'                      ,2);
define('olMeetingAccepted'                       ,3);
define('olMeetingDeclined'                       ,4);

/* OL Attendee type */
define('olAttendeeRequired'                      ,1);
define('olAttendeeOptional'                      ,2);
define('olAttendeeResource'                      ,3);

/* task status */
define('olTaskNotStarted'                        ,0);
define('olTaskInProgress'                        ,1);
define('olTaskComplete'                          ,2);
define('olTaskWaiting'                           ,3);
define('olTaskDeferred'                          ,4);

/* restrictions */
define('RES_AND'                                 ,0);
define('RES_OR'                                  ,1);
define('RES_NOT'                                 ,2);
define('RES_CONTENT'                             ,3);
define('RES_PROPERTY'                            ,4);
define('RES_COMPAREPROPS'                        ,5);
define('RES_BITMASK'                             ,6);
define('RES_SIZE'                                ,7);
define('RES_EXIST'                               ,8);
define('RES_SUBRESTRICTION'                      ,9);
define('RES_COMMENT'                             ,10);

/* restriction compares */
define('RELOP_LT'                                ,0);
define('RELOP_LE'                                ,1);
define('RELOP_GT'                                ,2);
define('RELOP_GE'                                ,3);
define('RELOP_EQ'                                ,4);
define('RELOP_NE'                                ,5);
define('RELOP_RE'                                ,6);

/* string 'fuzzylevel' */
define('FL_FULLSTRING'                           ,0x00000000);
define('FL_SUBSTRING'                            ,0x00000001);
define('FL_PREFIX'                               ,0x00000002);
define('FL_IGNORECASE'                           ,0x00010000);
define('FL_IGNORENONSPACE'                       ,0x00020000);
define('FL_LOOSE'                                ,0x00040000);

/* bitmask restriction types */
define('BMR_EQZ'                                 ,0x00000000);
define('BMR_NEZ'                                 ,0x00000001);

/* array index values of restrictions -- same values are used in php-ext/main.cpp::PHPArraytoSRestriction() */
define('VALUE'                                   ,0);        // propval
define('RELOP'                                   ,1);        // compare method
define('FUZZYLEVEL'                              ,2);        // string search flags
define('CB'                                      ,3);        // size restriction
define('ULTYPE'                                  ,4);        // bit mask restriction type BMR_xxx
define('ULMASK'                                  ,5);        // bitmask
define('ULPROPTAG'                               ,6);        // property
define('ULPROPTAG1'                              ,7);        // RES_COMPAREPROPS 1st property
define('ULPROPTAG2'                              ,8);        // RES_COMPAREPROPS 2nd property
define('PROPS'                                   ,9);        // RES_COMMENT properties
define('RESTRICTION'                             ,10);       // RES_COMMENT and RES_SUBRESTRICTION restriction

/* GUID's for PR_MDB_PROVIDER */
define("ZARAFA_SERVICE_GUID"                     ,makeGuid("{3C253DCA-D227-443C-94FE-425FAB958C19}"));    // default store
define("ZARAFA_STORE_PUBLIC_GUID"                ,makeGuid("{D47F4A09-D3BD-493C-B2FC-3C90BBCB48D4}"));    // public store
define("ZARAFA_STORE_DELEGATE_GUID"              ,makeGuid("{7C7C1085-BC6D-4E53-9DAB-8A53F8DEF808}"));    // other store
define('ZARAFA_STORE_ARCHIVER_GUID'              ,makeGuid("{BC8953AD-2E3F-4172-9404-896FF459870F}"));    // archive store

/* global profile section guid */
define('pbGlobalProfileSectionGuid'              ,makeGuid("{C8B0DB13-05AA-1A10-9BB0-00AA002FC45A}"));

/* Zarafa Contacts provider GUID */
define('ZARAFA_CONTACTS_GUID'                    ,makeGuid("{30047F72-92E3-DA4F-B86A-E52A7FE46571}"));

/* Permissions */

// Get permission type
define('ACCESS_TYPE_DENIED'                      ,1);
define('ACCESS_TYPE_GRANT'                       ,2);
define('ACCESS_TYPE_BOTH'                        ,3);

define('ecRightsNone'                            ,0x00000000);
define('ecRightsReadAny'                         ,0x00000001);
define('ecRightsCreate'                          ,0x00000002);
define('ecRightsEditOwned'                       ,0x00000008);
define('ecRightsDeleteOwned'                     ,0x00000010);
define('ecRightsEditAny'                         ,0x00000020);
define('ecRightsDeleteAny'                       ,0x00000040);
define('ecRightsCreateSubfolder'                 ,0x00000080);
define('ecRightsFolderAccess'                    ,0x00000100);
//define('ecrightsContact'                       ,0x00000200);
define('ecRightsFolderVisible'                   ,0x00000400);

define('ecRightsAll'                             ,ecRightsReadAny | ecRightsCreate | ecRightsEditOwned | ecRightsDeleteOwned | ecRightsEditAny | ecRightsDeleteAny | ecRightsCreateSubfolder | ecRightsFolderAccess | ecRightsFolderVisible);
define('ecRightsFullControl'                     ,ecRightsReadAny | ecRightsCreate | ecRightsEditOwned | ecRightsDeleteOwned | ecRightsEditAny | ecRightsDeleteAny | ecRightsCreateSubfolder | ecRightsFolderVisible);
define('ecRightsDefault'                         ,ecRightsNone | ecRightsFolderVisible);
define('ecRightsDefaultPublic'                   ,ecRightsReadAny | ecRightsFolderVisible);
define('ecRightsAdmin'                           ,0x00001000);
define('ecRightsAllMask'                         ,0x000015FB);

// Right change indication
define('RIGHT_NORMAL'                            ,0x00);
define('RIGHT_NEW'                               ,0x01);
define('RIGHT_MODIFY'                            ,0x02);
define('RIGHT_DELETED'                           ,0x04);
define('RIGHT_AUTOUPDATE_DENIED'                 ,0x08);

// IExchangeModifyTable: defines for rules
define('ROWLIST_REPLACE'                         ,0x0001);
define('ROW_ADD'                                 ,0x0001);
define('ROW_MODIFY'                              ,0x0002);
define('ROW_REMOVE'                              ,0x0004);
define('ROW_EMPTY'                               ,(ROW_ADD|ROW_REMOVE));

// new property types
define('PT_SRESTRICTION'                         ,0x00FD);
define('PT_ACTIONS'                              ,0x00FE);
// unused, I believe
define('PT_FILE_HANDLE'                          ,0x0103);
define('PT_FILE_EA'                              ,0x0104);
define('PT_VIRTUAL'                              ,0x0105);

// rules state
define('ST_DISABLED'                             ,0x0000);
define('ST_ENABLED'                              ,0x0001);
define('ST_ERROR'                                ,0x0002);
define('ST_ONLY_WHEN_OOF'                        ,0x0004);
define('ST_KEEP_OOF_HIST'                        ,0x0008);
define('ST_EXIT_LEVEL'                           ,0x0010);
define('ST_SKIP_IF_SCL_IS_SAFE'                  ,0x0020);
define('ST_RULE_PARSE_ERROR'                     ,0x0040);
define('ST_CLEAR_OOF_HIST'                       ,0x80000000);

// action types
define('OP_MOVE'                                 ,1);
define('OP_COPY'                                 ,2);
define('OP_REPLY'                                ,3);
define('OP_OOF_REPLY'                            ,4);
define('OP_DEFER_ACTION'                         ,5);
define('OP_BOUNCE'                               ,6);
define('OP_FORWARD'                              ,7);
define('OP_DELEGATE'                             ,8);
define('OP_TAG'                                  ,9);
define('OP_DELETE'                               ,10);
define('OP_MARK_AS_READ'                         ,11);

// for OP_REPLY
define('DO_NOT_SEND_TO_ORIGINATOR'               ,1);
define('STOCK_REPLY_TEMPLATE'                    ,2);

// for OP_FORWARD
define('FWD_PRESERVE_SENDER'                     ,1);
define('FWD_DO_NOT_MUNGE_MSG'                    ,2);
define('FWD_AS_ATTACHMENT'                       ,4);

// scBounceCodevalues
define('BOUNCE_MESSAGE_SIZE_TOO_LARGE'           ,13);
define('BOUNCE_FORMS_MISMATCH'                   ,31);
define('BOUNCE_ACCESS_DENIED'                    ,38);

// Free/busystatus
define('fbFree'                                  ,0);
define('fbTentative'                             ,1);
define('fbBusy'                                  ,2);
define('fbOutOfOffice'                           ,3);
define('fbWorkingElsewhere'                      ,4);
define('fbNoData'                                ,4);

/* ICS flags */

// For Synchronize()
define('SYNC_UNICODE'                            ,0x01);
define('SYNC_NO_DELETIONS'                       ,0x02);
define('SYNC_NO_SOFT_DELETIONS'                  ,0x04);
define('SYNC_READ_STATE'                         ,0x08);
define('SYNC_ASSOCIATED'                         ,0x10);
define('SYNC_NORMAL'                             ,0x20);
define('SYNC_NO_CONFLICTS'                       ,0x40);
define('SYNC_ONLY_SPECIFIED_PROPS'               ,0x80);
define('SYNC_NO_FOREIGN_KEYS'                    ,0x100);
define('SYNC_LIMITED_IMESSAGE'                   ,0x200);
define('SYNC_CATCHUP'                            ,0x400);
define('SYNC_NEW_MESSAGE'                        ,0x800);         // only applicable to ImportMessageChange()
define('SYNC_MSG_SELECTIVE'                      ,0x1000);        // Used internally.      Will reject if used by clients.
define('SYNC_BEST_BODY'                          ,0x2000);
define('SYNC_IGNORE_SPECIFIED_ON_ASSOCIATED'     ,0x4000);
define('SYNC_PROGRESS_MODE'                      ,0x8000);        // AirMapi progress mode
define('SYNC_FXRECOVERMODE'                      ,0x10000);
define('SYNC_DEFER_CONFIG'                       ,0x20000);
define('SYNC_FORCE_UNICODE'                      ,0x40000);       // Forces server to return Unicode properties
define('SYNC_STATE_READONLY'                     ,0x80000);       // Server will not update the states in the DB, setting up exporter with this flag states are read only

define('EMS_AB_ADDRESS_LOOKUP'                   ,0x00000001);    // Flag for resolvename to resolve only exact matches

define('TBL_BATCH'                               ,0x00000002);    // Batch multiple table commands

/* Flags for recipients in exceptions */
define('recipSendable'                           ,0x00000001);    // sendable attendee.
define('recipOrganizer'                          ,0x00000002);    // meeting organizer
define('recipExceptionalResponse'                ,0x00000010);    // attendee gave a response for the exception
define('recipExceptionalDeleted'                 ,0x00000020);    // recipientRow exists, but it is treated as if the corresponding recipient is deleted from meeting
define('recipOriginal'                           ,0x00000100);    // recipient is an original Attendee
define('recipReserved'                           ,0x00000200);

/* Flags which indicates type of Meeting Object */
define('mtgEmpty'                                ,0x00000000);    // Unspecified.
define('mtgRequest'                              ,0x00000001);    // Initial meeting request.
define('mtgFull'                                 ,0x00010000);    // Full update.
define('mtgInfo'                                 ,0x00020000);    // Informational update.
define('mtgOutOfDate'                            ,0x00080000);    // A newer Meeting Request object or Meeting Update object was received after this one.
define('mtgDelegatorCopy'                        ,0x00100000);    // This is set on the delegator's copy when a delegate will handle meeting-related objects.

define('MAPI_ONE_OFF_UNICODE'                    ,0x8000);        // the flag that defines whether the embedded strings are Unicode in one off entryids.
define('MAPI_ONE_OFF_NO_RICH_INFO'               ,0x0001);        // the flag that specifies whether the recipient gets TNEF or not.

/* Mask flags for mapi_msgstore_advise */
define('fnevCriticalError'                       ,0x00000001);
define('fnevNewMail'                             ,0x00000002);
define('fnevObjectCreated'                       ,0x00000004);
define('fnevObjectDeleted'                       ,0x00000008);
define('fnevObjectModified'                      ,0x00000010);
define('fnevObjectMoved'                         ,0x00000020);
define('fnevObjectCopied'                        ,0x00000040);
define('fnevSearchComplete'                      ,0x00000080);
define('fnevTableModified'                       ,0x00000100);
define('fnevStatusObjectModified'                ,0x00000200);
define('fnevReservedForMapi'                     ,0x40000000);
define('fnevExtended'                            ,0x80000000);

/* PersistBlockType values PR_IPM_OL2007_ENTRYIDS / PR_ADDITIONAL_REN_ENTRYIDS_EX PersistIDs*/
define('PERSIST_SENTINEL'                        ,0x0000); // Indicates that the PersistData structure is the last one contained in the PidTagAdditionalRenEntryIdsEx property
define('RSF_PID_RSS_SUBSCRIPTION'                ,0x8001); // Indicates that the structure contains data for the RSS Feeds folder
define('RSF_PID_SEND_AND_TRACK'                  ,0x8002); // Indicates that the structure contains data for the Tracked Mail Processing folder
define('RSF_PID_TODO_SEARCH'                     ,0x8004); // Indicates that the structure contains data for the To-Do folder
define('RSF_PID_CONV_ACTIONS'                    ,0x8006); // Indicates that the structure contains data for the Conversation Action Settings folder
define('RSF_PID_COMBINED_ACTIONS'                ,0x8007); // This value is reserved.
define('RSF_PID_SUGGESTED_CONTACTS'              ,0x8008); // Indicates that the structure contains data for the Suggested Contacts folder.
define('RSF_PID_CONTACT_SEARCH'                  ,0x8009); // Indicates that the structure contains data for the Contacts Search folder.
define('RSF_PID_BUDDYLIST_PDLS'                  ,0x800A); // Indicates that the structure contains data for the IM Contacts List folder.
define('RSF_PID_BUDDYLIST_CONTACTS'              ,0x800B); // Indicates that the structure contains data for the Quick Contacts folder.

/* PersistElementType Values ElementIDs for persist data of PR_IPM_OL2007_ENTRYIDS / PR_ADDITIONAL_REN_ENTRYIDS_EX */
define('ELEMENT_SENTINEL'                        ,0x0000); // 0 bytes Indicates that the PersistElement structure is the last one contained in the DataElements field of the PersistData structure.
define('RSF_ELID_ENTRYID'                        ,0x0001); // variable Indicates that the ElementData field contains the entry ID of the special folder
                                                           // that is of the type indicated by the value of the PersistID field of the PersistData structure.
define('RSF_ELID_HEADER'                         ,0x0002); // 4 bytes Indicates that the ElementData field contains a 4-byte header value equal to 0x00000000.

define('STGM_DIRECT'                             ,0x00000000);
define('STGM_TRANSACTED'                         ,0x00010000);
define('STGM_SIMPLE'                             ,0x08000000);
define('STGM_READ'                               ,0x00000000);
define('STGM_WRITE'                              ,0x00000001);
define('STGM_READWRITE'                          ,0x00000002);
define('STGM_SHARE_DENY_NONE'                    ,0x00000040);
define('STGM_SHARE_DENY_READ'                    ,0x00000030);
define('STGM_SHARE_DENY_WRITE'                   ,0x00000020);
define('STGM_SHARE_EXCLUSIVE'                    ,0x00000010);
define('STGM_PRIORITY'                           ,0x00040000);
define('STGM_DELETEONRELEASE'                    ,0x04000000);
define('STGM_NOSCRATCH'                          ,0x00100000);
define('STGM_CREATE'                             ,0x00001000);
define('STGM_CONVERT'                            ,0x00020000);
define('STGM_FAILIFTHERE'                        ,0x00000000);
define('STGM_NOSNAPSHOT'                         ,0x00200000);
define('STGM_DIRECT_SWMR'                        ,0x00400000);