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

if (!function_exists("mapi_prop_tag"))
    throw new FatalMisconfigurationException("PHP-MAPI extension is not available");

define('PR_ACKNOWLEDGEMENT_MODE'                      ,mapi_prop_tag(PT_LONG,        0x0001));
define('PR_ALTERNATE_RECIPIENT_ALLOWED'               ,mapi_prop_tag(PT_BOOLEAN,     0x0002));
define('PR_AUTHORIZING_USERS'                         ,mapi_prop_tag(PT_BINARY,      0x0003));
define('PR_AUTO_FORWARD_COMMENT'                      ,mapi_prop_tag(PT_TSTRING,     0x0004));
define('PR_AUTO_FORWARDED'                            ,mapi_prop_tag(PT_BOOLEAN,     0x0005));
define('PR_CONTENT_CONFIDENTIALITY_ALGORITHM_ID'      ,mapi_prop_tag(PT_BINARY,      0x0006));
define('PR_CONTENT_CORRELATOR'                        ,mapi_prop_tag(PT_BINARY,      0x0007));
define('PR_CONTENT_IDENTIFIER'                        ,mapi_prop_tag(PT_TSTRING,     0x0008));
define('PR_CONTENT_LENGTH'                            ,mapi_prop_tag(PT_LONG,        0x0009));
define('PR_CONTENT_RETURN_REQUESTED'                  ,mapi_prop_tag(PT_BOOLEAN,     0x000A));



define('PR_CONVERSATION_KEY'                          ,mapi_prop_tag(PT_BINARY,      0x000B));

define('PR_CONVERSION_EITS'                           ,mapi_prop_tag(PT_BINARY,      0x000C));
define('PR_CONVERSION_WITH_LOSS_PROHIBITED'           ,mapi_prop_tag(PT_BOOLEAN,     0x000D));
define('PR_CONVERTED_EITS'                            ,mapi_prop_tag(PT_BINARY,      0x000E));
define('PR_DEFERRED_DELIVERY_TIME'                    ,mapi_prop_tag(PT_SYSTIME,     0x000F));
define('PR_DEFERRED_SEND_TIME'                        ,mapi_prop_tag(PT_SYSTIME,     0x3FEF));
define('PR_DELIVER_TIME'                              ,mapi_prop_tag(PT_SYSTIME,     0x0010));
define('PR_DISCARD_REASON'                            ,mapi_prop_tag(PT_LONG,        0x0011));
define('PR_DISCLOSURE_OF_RECIPIENTS'                  ,mapi_prop_tag(PT_BOOLEAN,     0x0012));
define('PR_DL_EXPANSION_HISTORY'                      ,mapi_prop_tag(PT_BINARY,      0x0013));
define('PR_DL_EXPANSION_PROHIBITED'                   ,mapi_prop_tag(PT_BOOLEAN,     0x0014));
define('PR_EXPIRY_TIME'                               ,mapi_prop_tag(PT_SYSTIME,     0x0015));
define('PR_IMPLICIT_CONVERSION_PROHIBITED'            ,mapi_prop_tag(PT_BOOLEAN,     0x0016));
define('PR_IMPORTANCE'                                ,mapi_prop_tag(PT_LONG,        0x0017));
define('PR_IPM_ID'                                    ,mapi_prop_tag(PT_BINARY,      0x0018));
define('PR_LATEST_DELIVERY_TIME'                      ,mapi_prop_tag(PT_SYSTIME,     0x0019));
define('PR_MESSAGE_CLASS'                             ,mapi_prop_tag(PT_TSTRING,     0x001A));
define('PR_MESSAGE_DELIVERY_ID'                       ,mapi_prop_tag(PT_BINARY,      0x001B));





define('PR_MESSAGE_SECURITY_LABEL'                    ,mapi_prop_tag(PT_BINARY,      0x001E));
define('PR_OBSOLETED_IPMS'                            ,mapi_prop_tag(PT_BINARY,      0x001F));
define('PR_ORIGINALLY_INTENDED_RECIPIENT_NAME'        ,mapi_prop_tag(PT_BINARY,      0x0020));
define('PR_ORIGINAL_EITS'                             ,mapi_prop_tag(PT_BINARY,      0x0021));
define('PR_ORIGINATOR_CERTIFICATE'                    ,mapi_prop_tag(PT_BINARY,      0x0022));
define('PR_ORIGINATOR_DELIVERY_REPORT_REQUESTED'      ,mapi_prop_tag(PT_BOOLEAN,     0x0023));
define('PR_ORIGINATOR_RETURN_ADDRESS'                 ,mapi_prop_tag(PT_BINARY,      0x0024));

define('PR_PARENT_KEY'                                ,mapi_prop_tag(PT_BINARY,      0x0025));
define('PR_PRIORITY'                                  ,mapi_prop_tag(PT_LONG,        0x0026));

define('PR_ORIGIN_CHECK'                              ,mapi_prop_tag(PT_BINARY,      0x0027));
define('PR_PROOF_OF_SUBMISSION_REQUESTED'             ,mapi_prop_tag(PT_BOOLEAN,     0x0028));
define('PR_READ_RECEIPT_REQUESTED'                    ,mapi_prop_tag(PT_BOOLEAN,     0x0029));
define('PR_RECEIPT_TIME'                              ,mapi_prop_tag(PT_SYSTIME,     0x002A));
define('PR_RECIPIENT_REASSIGNMENT_PROHIBITED'         ,mapi_prop_tag(PT_BOOLEAN,     0x002B));
define('PR_REDIRECTION_HISTORY'                       ,mapi_prop_tag(PT_BINARY,      0x002C));
define('PR_RELATED_IPMS'                              ,mapi_prop_tag(PT_BINARY,      0x002D));
define('PR_ORIGINAL_SENSITIVITY'                      ,mapi_prop_tag(PT_LONG,        0x002E));
define('PR_LANGUAGES'                                 ,mapi_prop_tag(PT_TSTRING,     0x002F));
define('PR_REPLY_TIME'                                ,mapi_prop_tag(PT_SYSTIME,     0x0030));
define('PR_REPORT_TAG'                                ,mapi_prop_tag(PT_BINARY,      0x0031));
define('PR_REPORT_TIME'                               ,mapi_prop_tag(PT_SYSTIME,     0x0032));
define('PR_RETURNED_IPM'                              ,mapi_prop_tag(PT_BOOLEAN,     0x0033));
define('PR_SECURITY'                                  ,mapi_prop_tag(PT_LONG,        0x0034));
define('PR_INCOMPLETE_COPY'                           ,mapi_prop_tag(PT_BOOLEAN,     0x0035));
define('PR_SENSITIVITY'                               ,mapi_prop_tag(PT_LONG,        0x0036));
define('PR_SUBJECT'                                   ,mapi_prop_tag(PT_TSTRING,     0x0037));
define('PR_SUBJECT_IPM'                               ,mapi_prop_tag(PT_BINARY,      0x0038));
define('PR_CLIENT_SUBMIT_TIME'                        ,mapi_prop_tag(PT_SYSTIME,     0x0039));
define('PR_REPORT_NAME'                               ,mapi_prop_tag(PT_TSTRING,     0x003A));
define('PR_SENT_REPRESENTING_SEARCH_KEY'              ,mapi_prop_tag(PT_BINARY,      0x003B));
define('PR_X400_CONTENT_TYPE'                         ,mapi_prop_tag(PT_BINARY,      0x003C));
define('PR_SUBJECT_PREFIX'                            ,mapi_prop_tag(PT_TSTRING,     0x003D));
define('PR_NON_RECEIPT_REASON'                        ,mapi_prop_tag(PT_LONG,        0x003E));
define('PR_RECEIVED_BY_ENTRYID'                       ,mapi_prop_tag(PT_BINARY,      0x003F));
define('PR_RECEIVED_BY_NAME'                          ,mapi_prop_tag(PT_TSTRING,     0x0040));
define('PR_SENT_REPRESENTING_ENTRYID'                 ,mapi_prop_tag(PT_BINARY,      0x0041));
define('PR_SENT_REPRESENTING_NAME'                    ,mapi_prop_tag(PT_TSTRING,     0x0042));
define('PR_RCVD_REPRESENTING_ENTRYID'                 ,mapi_prop_tag(PT_BINARY,      0x0043));
define('PR_RCVD_REPRESENTING_NAME'                    ,mapi_prop_tag(PT_TSTRING,     0x0044));
define('PR_REPORT_ENTRYID'                            ,mapi_prop_tag(PT_BINARY,      0x0045));
define('PR_READ_RECEIPT_ENTRYID'                      ,mapi_prop_tag(PT_BINARY,      0x0046));
define('PR_MESSAGE_SUBMISSION_ID'                     ,mapi_prop_tag(PT_BINARY,      0x0047));
define('PR_PROVIDER_SUBMIT_TIME'                      ,mapi_prop_tag(PT_SYSTIME,     0x0048));
define('PR_ORIGINAL_SUBJECT'                          ,mapi_prop_tag(PT_TSTRING,     0x0049));
define('PR_DISC_VAL'                                  ,mapi_prop_tag(PT_BOOLEAN,     0x004A));
define('PR_ORIG_MESSAGE_CLASS'                        ,mapi_prop_tag(PT_TSTRING,     0x004B));
define('PR_ORIGINAL_AUTHOR_ENTRYID'                   ,mapi_prop_tag(PT_BINARY,      0x004C));
define('PR_ORIGINAL_AUTHOR_NAME'                      ,mapi_prop_tag(PT_TSTRING,     0x004D));
define('PR_ORIGINAL_SUBMIT_TIME'                      ,mapi_prop_tag(PT_SYSTIME,     0x004E));
define('PR_REPLY_RECIPIENT_ENTRIES'                   ,mapi_prop_tag(PT_BINARY,      0x004F));
define('PR_REPLY_RECIPIENT_NAMES'                     ,mapi_prop_tag(PT_TSTRING,     0x0050));

define('PR_RECEIVED_BY_SEARCH_KEY'                    ,mapi_prop_tag(PT_BINARY,      0x0051));
define('PR_RCVD_REPRESENTING_SEARCH_KEY'              ,mapi_prop_tag(PT_BINARY,      0x0052));
define('PR_READ_RECEIPT_SEARCH_KEY'                   ,mapi_prop_tag(PT_BINARY,      0x0053));
define('PR_REPORT_SEARCH_KEY'                         ,mapi_prop_tag(PT_BINARY,      0x0054));
define('PR_ORIGINAL_DELIVERY_TIME'                    ,mapi_prop_tag(PT_SYSTIME,     0x0055));
define('PR_ORIGINAL_AUTHOR_SEARCH_KEY'                ,mapi_prop_tag(PT_BINARY,      0x0056));

define('PR_MESSAGE_TO_ME'                             ,mapi_prop_tag(PT_BOOLEAN,     0x0057));
define('PR_MESSAGE_CC_ME'                             ,mapi_prop_tag(PT_BOOLEAN,     0x0058));
define('PR_MESSAGE_RECIP_ME'                          ,mapi_prop_tag(PT_BOOLEAN,     0x0059));

define('PR_ORIGINAL_SENDER_NAME'                      ,mapi_prop_tag(PT_TSTRING,     0x005A));
define('PR_ORIGINAL_SENDER_ENTRYID'                   ,mapi_prop_tag(PT_BINARY,      0x005B));
define('PR_ORIGINAL_SENDER_SEARCH_KEY'                ,mapi_prop_tag(PT_BINARY,      0x005C));
define('PR_ORIGINAL_SENT_REPRESENTING_NAME'           ,mapi_prop_tag(PT_TSTRING,     0x005D));
define('PR_ORIGINAL_SENT_REPRESENTING_ENTRYID'        ,mapi_prop_tag(PT_BINARY,      0x005E));
define('PR_ORIGINAL_SENT_REPRESENTING_SEARCH_KEY'     ,mapi_prop_tag(PT_BINARY,      0x005F));

define('PR_START_DATE'                                ,mapi_prop_tag(PT_SYSTIME,     0x0060));
define('PR_END_DATE'                                  ,mapi_prop_tag(PT_SYSTIME,     0x0061));
define('PR_OWNER_APPT_ID'                             ,mapi_prop_tag(PT_LONG,        0x0062));
define('PR_RESPONSE_REQUESTED'                        ,mapi_prop_tag(PT_BOOLEAN,     0x0063));

define('PR_SENT_REPRESENTING_ADDRTYPE'                ,mapi_prop_tag(PT_TSTRING,     0x0064));
define('PR_SENT_REPRESENTING_EMAIL_ADDRESS'           ,mapi_prop_tag(PT_TSTRING,     0x0065));

define('PR_ORIGINAL_SENDER_ADDRTYPE'                  ,mapi_prop_tag(PT_TSTRING,     0x0066));
define('PR_ORIGINAL_SENDER_EMAIL_ADDRESS'             ,mapi_prop_tag(PT_TSTRING,     0x0067));

define('PR_ORIGINAL_SENT_REPRESENTING_ADDRTYPE'       ,mapi_prop_tag(PT_TSTRING,     0x0068));
define('PR_ORIGINAL_SENT_REPRESENTING_EMAIL_ADDRESS'  ,mapi_prop_tag(PT_TSTRING,     0x0069));

define('PR_CONVERSATION_TOPIC'                        ,mapi_prop_tag(PT_TSTRING,     0x0070));
define('PR_CONVERSATION_INDEX'                        ,mapi_prop_tag(PT_BINARY,      0x0071));

define('PR_ORIGINAL_DISPLAY_BCC'                      ,mapi_prop_tag(PT_TSTRING,     0x0072));
define('PR_ORIGINAL_DISPLAY_CC'                       ,mapi_prop_tag(PT_TSTRING,     0x0073));
define('PR_ORIGINAL_DISPLAY_TO'                       ,mapi_prop_tag(PT_TSTRING,     0x0074));

define('PR_RECEIVED_BY_ADDRTYPE'                      ,mapi_prop_tag(PT_TSTRING,     0x0075));
define('PR_RECEIVED_BY_EMAIL_ADDRESS'                 ,mapi_prop_tag(PT_TSTRING,     0x0076));

define('PR_RCVD_REPRESENTING_ADDRTYPE'                ,mapi_prop_tag(PT_TSTRING,     0x0077));
define('PR_RCVD_REPRESENTING_EMAIL_ADDRESS'           ,mapi_prop_tag(PT_TSTRING,     0x0078));

define('PR_ORIGINAL_AUTHOR_ADDRTYPE'                  ,mapi_prop_tag(PT_TSTRING,     0x0079));
define('PR_ORIGINAL_AUTHOR_EMAIL_ADDRESS'             ,mapi_prop_tag(PT_TSTRING,     0x007A));

define('PR_ORIGINALLY_INTENDED_RECIP_ADDRTYPE'        ,mapi_prop_tag(PT_TSTRING,     0x007B));
define('PR_ORIGINALLY_INTENDED_RECIP_EMAIL_ADDRESS'   ,mapi_prop_tag(PT_TSTRING,     0x007C));

define('PR_TRANSPORT_MESSAGE_HEADERS'                 ,mapi_prop_tag(PT_TSTRING,     0x007D));

define('PR_DELEGATION'                                ,mapi_prop_tag(PT_BINARY,      0x007E));

define('PR_TNEF_CORRELATION_KEY'                      ,mapi_prop_tag(PT_BINARY,      0x007F));

define('PR_MDN_DISPOSITION_TYPE'                      ,mapi_prop_tag(PT_STRING8,     0x0080));
define('PR_MDN_DISPOSITION_SENDINGMODE'               ,mapi_prop_tag(PT_STRING8,     0x0081));

define('PR_USER_ENTRYID'                              ,mapi_prop_tag(PT_BINARY,      0x6619));
define('PR_USER_NAME'                                 ,mapi_prop_tag(PT_STRING8,     0x661A));
define('PR_MAILBOX_OWNER_ENTRYID'                     ,mapi_prop_tag(PT_BINARY,      0x661B));
define('PR_MAILBOX_OWNER_NAME'                        ,mapi_prop_tag(PT_STRING8,     0x661C));

define('PR_HIERARCHY_SYNCHRONIZER'                    ,mapi_prop_tag(PT_OBJECT,      0x662C));
define('PR_CONTENTS_SYNCHRONIZER'                     ,mapi_prop_tag(PT_OBJECT,      0x662D));
define('PR_COLLECTOR'                                 ,mapi_prop_tag(PT_OBJECT,      0x662E));

define('PR_SMTP_ADDRESS'                              ,mapi_prop_tag(PT_TSTRING,     0x39FE));

/*
 *  Message content properties
 */

define('PR_BODY'                                      ,mapi_prop_tag(PT_TSTRING,     0x1000));
define('PR_HTML'                                      ,mapi_prop_tag(PT_BINARY,      0x1013));
define('PR_REPORT_TEXT'                               ,mapi_prop_tag(PT_TSTRING,     0x1001));
define('PR_ORIGINATOR_AND_DL_EXPANSION_HISTORY'       ,mapi_prop_tag(PT_BINARY,      0x1002));
define('PR_REPORTING_DL_NAME'                         ,mapi_prop_tag(PT_BINARY,      0x1003));
define('PR_REPORTING_MTA_CERTIFICATE'                 ,mapi_prop_tag(PT_BINARY,      0x1004));

/*  Removed 'PR_REPORT_ORIGIN_AUTHENTICATION_CHECK with DCR 3865, use 'PR_ORIGIN_CHECK */

define('PR_RTF_SYNC_BODY_CRC'                         ,mapi_prop_tag(PT_LONG,        0x1006));
define('PR_RTF_SYNC_BODY_COUNT'                       ,mapi_prop_tag(PT_LONG,        0x1007));
define('PR_RTF_SYNC_BODY_TAG'                         ,mapi_prop_tag(PT_TSTRING,     0x1008));
define('PR_RTF_COMPRESSED'                            ,mapi_prop_tag(PT_BINARY,      0x1009));
define('PR_RTF_SYNC_PREFIX_COUNT'                     ,mapi_prop_tag(PT_LONG,        0x1010));
define('PR_RTF_SYNC_TRAILING_COUNT'                   ,mapi_prop_tag(PT_LONG,        0x1011));
define('PR_ORIGINALLY_INTENDED_RECIP_ENTRYID'         ,mapi_prop_tag(PT_BINARY,      0x1012));
define('PR_NATIVE_BODY_INFO'                          ,mapi_prop_tag(PT_LONG,        0x1016));

define('PR_CONFLICT_ITEMS'                            ,mapi_prop_tag(PT_MV_BINARY,   0x1098));

/*
 *  Reserved 0x1100-0x1200
 */


/*
 *  Message recipient properties
 */

define('PR_CONTENT_INTEGRITY_CHECK'                   ,mapi_prop_tag(PT_BINARY,      0x0C00));
define('PR_EXPLICIT_CONVERSION'                       ,mapi_prop_tag(PT_LONG,        0x0C01));
define('PR_IPM_RETURN_REQUESTED'                      ,mapi_prop_tag(PT_BOOLEAN,     0x0C02));
define('PR_MESSAGE_TOKEN'                             ,mapi_prop_tag(PT_BINARY,      0x0C03));
define('PR_NDR_REASON_CODE'                           ,mapi_prop_tag(PT_LONG,        0x0C04));
define('PR_NDR_DIAG_CODE'                             ,mapi_prop_tag(PT_LONG,        0x0C05));
define('PR_NON_RECEIPT_NOTIFICATION_REQUESTED'        ,mapi_prop_tag(PT_BOOLEAN,     0x0C06));
define('PR_DELIVERY_POINT'                            ,mapi_prop_tag(PT_LONG,        0x0C07));

define('PR_ORIGINATOR_NON_DELIVERY_REPORT_REQUESTED'  ,mapi_prop_tag(PT_BOOLEAN,     0x0C08));
define('PR_ORIGINATOR_REQUESTED_ALTERNATE_RECIPIENT'  ,mapi_prop_tag(PT_BINARY,      0x0C09));
define('PR_PHYSICAL_DELIVERY_BUREAU_FAX_DELIVERY'     ,mapi_prop_tag(PT_BOOLEAN,     0x0C0A));
define('PR_PHYSICAL_DELIVERY_MODE'                    ,mapi_prop_tag(PT_LONG,        0x0C0B));
define('PR_PHYSICAL_DELIVERY_REPORT_REQUEST'          ,mapi_prop_tag(PT_LONG,        0x0C0C));
define('PR_PHYSICAL_FORWARDING_ADDRESS'               ,mapi_prop_tag(PT_BINARY,      0x0C0D));
define('PR_PHYSICAL_FORWARDING_ADDRESS_REQUESTED'     ,mapi_prop_tag(PT_BOOLEAN,     0x0C0E));
define('PR_PHYSICAL_FORWARDING_PROHIBITED'            ,mapi_prop_tag(PT_BOOLEAN,     0x0C0F));
define('PR_PHYSICAL_RENDITION_ATTRIBUTES'             ,mapi_prop_tag(PT_BINARY,      0x0C10));
define('PR_PROOF_OF_DELIVERY'                         ,mapi_prop_tag(PT_BINARY,      0x0C11));
define('PR_PROOF_OF_DELIVERY_REQUESTED'               ,mapi_prop_tag(PT_BOOLEAN,     0x0C12));
define('PR_RECIPIENT_CERTIFICATE'                     ,mapi_prop_tag(PT_BINARY,      0x0C13));
define('PR_RECIPIENT_NUMBER_FOR_ADVICE'               ,mapi_prop_tag(PT_TSTRING,     0x0C14));
define('PR_RECIPIENT_TYPE'                            ,mapi_prop_tag(PT_LONG,        0x0C15));
define('PR_REGISTERED_MAIL_TYPE'                      ,mapi_prop_tag(PT_LONG,        0x0C16));
define('PR_REPLY_REQUESTED'                           ,mapi_prop_tag(PT_BOOLEAN,     0x0C17));
define('PR_REQUESTED_DELIVERY_METHOD'                 ,mapi_prop_tag(PT_LONG,        0x0C18));
define('PR_SENDER_ENTRYID'                            ,mapi_prop_tag(PT_BINARY,      0x0C19));
define('PR_SENDER_NAME'                               ,mapi_prop_tag(PT_TSTRING,     0x0C1A));
define('PR_SUPPLEMENTARY_INFO'                        ,mapi_prop_tag(PT_TSTRING,     0x0C1B));
define('PR_TYPE_OF_MTS_USER'                          ,mapi_prop_tag(PT_LONG,        0x0C1C));
define('PR_SENDER_SEARCH_KEY'                         ,mapi_prop_tag(PT_BINARY,      0x0C1D));
define('PR_SENDER_ADDRTYPE'                           ,mapi_prop_tag(PT_TSTRING,     0x0C1E));
define('PR_SENDER_EMAIL_ADDRESS'                      ,mapi_prop_tag(PT_TSTRING,     0x0C1F));

/*
 *  Message non-transmittable properties
 */

/*
 * The two tags, 'PR_MESSAGE_RECIPIENTS and 'PR_MESSAGE_ATTACHMENTS,
 * are to be used in the exclude list passed to
 * IMessage::CopyTo when the caller wants either the recipients or attachments
 * of the message to not get copied.  It is also used in the ProblemArray
 * return from IMessage::CopyTo when an error is encountered copying them
 */

define('PR_CURRENT_VERSION'                           ,mapi_prop_tag(PT_I8,          0x0E00));
define('PR_DELETE_AFTER_SUBMIT'                       ,mapi_prop_tag(PT_BOOLEAN,     0x0E01));
define('PR_DISPLAY_BCC'                               ,mapi_prop_tag(PT_TSTRING,     0x0E02));
define('PR_DISPLAY_CC'                                ,mapi_prop_tag(PT_TSTRING,     0x0E03));
define('PR_DISPLAY_TO'                                ,mapi_prop_tag(PT_TSTRING,     0x0E04));
define('PR_PARENT_DISPLAY'                            ,mapi_prop_tag(PT_TSTRING,     0x0E05));
define('PR_MESSAGE_DELIVERY_TIME'                     ,mapi_prop_tag(PT_SYSTIME,     0x0E06));
define('PR_MESSAGE_FLAGS'                             ,mapi_prop_tag(PT_LONG,        0x0E07));
define('PR_MESSAGE_SIZE'                              ,mapi_prop_tag(PT_LONG,        0x0E08));
define('PR_MESSAGE_SIZE_EXTENDED'                     ,mapi_prop_tag(PT_LONGLONG,    0x0E08));
define('PR_PARENT_ENTRYID'                            ,mapi_prop_tag(PT_BINARY,      0x0E09));
define('PR_SENTMAIL_ENTRYID'                          ,mapi_prop_tag(PT_BINARY,      0x0E0A));
define('PR_CORRELATE'                                 ,mapi_prop_tag(PT_BOOLEAN,     0x0E0C));
define('PR_CORRELATE_MTSID'                           ,mapi_prop_tag(PT_BINARY,      0x0E0D));
define('PR_DISCRETE_VALUES'                           ,mapi_prop_tag(PT_BOOLEAN,     0x0E0E));
define('PR_RESPONSIBILITY'                            ,mapi_prop_tag(PT_BOOLEAN,     0x0E0F));
define('PR_SPOOLER_STATUS'                            ,mapi_prop_tag(PT_LONG,        0x0E10));
define('PR_TRANSPORT_STATUS'                          ,mapi_prop_tag(PT_LONG,        0x0E11));
define('PR_MESSAGE_RECIPIENTS'                        ,mapi_prop_tag(PT_OBJECT,      0x0E12));
define('PR_MESSAGE_ATTACHMENTS'                       ,mapi_prop_tag(PT_OBJECT,      0x0E13));
define('PR_SUBMIT_FLAGS'                              ,mapi_prop_tag(PT_LONG,        0x0E14));
define('PR_RECIPIENT_STATUS'                          ,mapi_prop_tag(PT_LONG,        0x0E15));
define('PR_TRANSPORT_KEY'                             ,mapi_prop_tag(PT_LONG,        0x0E16));
define('PR_MSG_STATUS'                                ,mapi_prop_tag(PT_LONG,        0x0E17));
define('PR_MESSAGE_DOWNLOAD_TIME'                     ,mapi_prop_tag(PT_LONG,        0x0E18));
define('PR_CREATION_VERSION'                          ,mapi_prop_tag(PT_I8,          0x0E19));
define('PR_MODIFY_VERSION'                            ,mapi_prop_tag(PT_I8,          0x0E1A));
define('PR_HASATTACH'                                 ,mapi_prop_tag(PT_BOOLEAN,     0x0E1B));
define('PR_BODY_CRC'                                  ,mapi_prop_tag(PT_LONG,        0x0E1C));
define('PR_NORMALIZED_SUBJECT'                        ,mapi_prop_tag(PT_TSTRING,     0x0E1D));
define('PR_RTF_IN_SYNC'                               ,mapi_prop_tag(PT_BOOLEAN,     0x0E1F));
define('PR_ATTACH_SIZE'                               ,mapi_prop_tag(PT_LONG,        0x0E20));
define('PR_ATTACH_NUM'                                ,mapi_prop_tag(PT_LONG,        0x0E21));
define('PR_PREPROCESS'                                ,mapi_prop_tag(PT_BOOLEAN,     0x0E22));

/* 'PR_ORIGINAL_DISPLAY_TO, _CC, and _BCC moved to transmittible range 03/09/95 */

define('PR_ORIGINATING_MTA_CERTIFICATE'               ,mapi_prop_tag(PT_BINARY,      0x0E25));
define('PR_PROOF_OF_SUBMISSION'                       ,mapi_prop_tag(PT_BINARY,      0x0E26));

define('PR_TODO_ITEM_FLAGS'                           ,mapi_prop_tag(PT_LONG,        0x0E2B));

/*
 * The range of non-message and non-recipient property IDs (0x3000 - 0x3FFF)); is
 * further broken down into ranges to make assigning new property IDs easier.
 *
 *  From    To      Kind of property
 *  --------------------------------
 *  3000    32FF    MAPI_defined common property
 *  3200    33FF    MAPI_defined form property
 *  3400    35FF    MAPI_defined message store property
 *  3600    36FF    MAPI_defined Folder or AB Container property
 *  3700    38FF    MAPI_defined attachment property
 *  3900    39FF    MAPI_defined address book property
 *  3A00    3BFF    MAPI_defined mailuser property
 *  3C00    3CFF    MAPI_defined DistList property
 *  3D00    3DFF    MAPI_defined Profile Section property
 *  3E00    3EFF    MAPI_defined Status property
 *  3F00    3FFF    MAPI_defined display table property
 */

/*
 *  Properties common to numerous MAPI objects.
 *
 *  Those properties that can appear on messages are in the
 *  non-transmittable range for messages. They start at the high
 *  end of that range and work down.
 *
 *  Properties that never appear on messages are defined in the common
 *  property range (see above));.
 */

/*
 * properties that are common to multiple objects (including message objects));
 * -- these ids are in the non-transmittable range
 */

define('PR_ENTRYID'                                   ,mapi_prop_tag(PT_BINARY,      0x0FFF));
define('PR_OBJECT_TYPE'                               ,mapi_prop_tag(PT_LONG,        0x0FFE));
define('PR_ICON'                                      ,mapi_prop_tag(PT_BINARY,      0x0FFD));
define('PR_MINI_ICON'                                 ,mapi_prop_tag(PT_BINARY,      0x0FFC));
define('PR_STORE_ENTRYID'                             ,mapi_prop_tag(PT_BINARY,      0x0FFB));
define('PR_STORE_RECORD_KEY'                          ,mapi_prop_tag(PT_BINARY,      0x0FFA));
define('PR_RECORD_KEY'                                ,mapi_prop_tag(PT_BINARY,      0x0FF9));
define('PR_MAPPING_SIGNATURE'                         ,mapi_prop_tag(PT_BINARY,      0x0FF8));
define('PR_ACCESS_LEVEL'                              ,mapi_prop_tag(PT_LONG,        0x0FF7));
define('PR_INSTANCE_KEY'                              ,mapi_prop_tag(PT_BINARY,      0x0FF6));
define('PR_ROW_TYPE'                                  ,mapi_prop_tag(PT_LONG,        0x0FF5));
define('PR_ACCESS'                                    ,mapi_prop_tag(PT_LONG,        0x0FF4));

/*
 * properties that are common to multiple objects (usually not including message objects));
 * -- these ids are in the transmittable range
 */

define('PR_ROWID'                                     ,mapi_prop_tag(PT_LONG,        0x3000));
define('PR_DISPLAY_NAME'                              ,mapi_prop_tag(PT_TSTRING,     0x3001));
define('PR_DISPLAY_NAME_W'                            ,mapi_prop_tag(PT_UNICODE,     0x3001));
define('PR_ADDRTYPE'                                  ,mapi_prop_tag(PT_TSTRING,     0x3002));
define('PR_EMAIL_ADDRESS'                             ,mapi_prop_tag(PT_TSTRING,     0x3003));
define('PR_COMMENT'                                   ,mapi_prop_tag(PT_TSTRING,     0x3004));
define('PR_DEPTH'                                     ,mapi_prop_tag(PT_LONG,        0x3005));
define('PR_PROVIDER_DISPLAY'                          ,mapi_prop_tag(PT_TSTRING,     0x3006));
define('PR_CREATION_TIME'                             ,mapi_prop_tag(PT_SYSTIME,     0x3007));
define('PR_LAST_MODIFICATION_TIME'                    ,mapi_prop_tag(PT_SYSTIME,     0x3008));
define('PR_RESOURCE_FLAGS'                            ,mapi_prop_tag(PT_LONG,        0x3009));
define('PR_PROVIDER_DLL_NAME'                         ,mapi_prop_tag(PT_TSTRING,     0x300A));
define('PR_SEARCH_KEY'                                ,mapi_prop_tag(PT_BINARY,      0x300B));
define('PR_PROVIDER_UID'                              ,mapi_prop_tag(PT_BINARY,      0x300C));
define('PR_PROVIDER_ORDINAL'                          ,mapi_prop_tag(PT_LONG,        0x300D));

/*
 *  MAPI Form properties
 */
define('PR_FORM_VERSION'                              ,mapi_prop_tag(PT_TSTRING,     0x3301));
define('PR_FORM_CLSID'                                ,mapi_prop_tag(PT_CLSID,       0x3302));
define('PR_FORM_CONTACT_NAME'                         ,mapi_prop_tag(PT_TSTRING,     0x3303));
define('PR_FORM_CATEGORY'                             ,mapi_prop_tag(PT_TSTRING,     0x3304));
define('PR_FORM_CATEGORY_SUB'                         ,mapi_prop_tag(PT_TSTRING,     0x3305));
define('PR_FORM_HOST_MAP'                             ,mapi_prop_tag(PT_MV_LONG,     0x3306));
define('PR_FORM_HIDDEN'                               ,mapi_prop_tag(PT_BOOLEAN,     0x3307));
define('PR_FORM_DESIGNER_NAME'                        ,mapi_prop_tag(PT_TSTRING,     0x3308));
define('PR_FORM_DESIGNER_GUID'                        ,mapi_prop_tag(PT_CLSID,       0x3309));
define('PR_FORM_MESSAGE_BEHAVIOR'                     ,mapi_prop_tag(PT_LONG,        0x330A));

/*
 *  Message store properties
 */

define('PR_DEFAULT_STORE'                             ,mapi_prop_tag(PT_BOOLEAN,     0x3400));
define('PR_STORE_SUPPORT_MASK'                        ,mapi_prop_tag(PT_LONG,        0x340D));
define('PR_STORE_STATE'                               ,mapi_prop_tag(PT_LONG,        0x340E));

define('PR_IPM_SUBTREE_SEARCH_KEY'                    ,mapi_prop_tag(PT_BINARY,      0x3410));
define('PR_IPM_OUTBOX_SEARCH_KEY'                     ,mapi_prop_tag(PT_BINARY,      0x3411));
define('PR_IPM_WASTEBASKET_SEARCH_KEY'                ,mapi_prop_tag(PT_BINARY,      0x3412));
define('PR_IPM_SENTMAIL_SEARCH_KEY'                   ,mapi_prop_tag(PT_BINARY,      0x3413));
define('PR_MDB_PROVIDER'                              ,mapi_prop_tag(PT_BINARY,      0x3414));
define('PR_RECEIVE_FOLDER_SETTINGS'                   ,mapi_prop_tag(PT_OBJECT,      0x3415));

define('PR_VALID_FOLDER_MASK'                         ,mapi_prop_tag(PT_LONG,        0x35DF));
define('PR_IPM_SUBTREE_ENTRYID'                       ,mapi_prop_tag(PT_BINARY,      0x35E0));

define('PR_IPM_OUTBOX_ENTRYID'                        ,mapi_prop_tag(PT_BINARY,      0x35E2));
define('PR_IPM_WASTEBASKET_ENTRYID'                   ,mapi_prop_tag(PT_BINARY,      0x35E3));
define('PR_IPM_SENTMAIL_ENTRYID'                      ,mapi_prop_tag(PT_BINARY,      0x35E4));
define('PR_VIEWS_ENTRYID'                             ,mapi_prop_tag(PT_BINARY,      0x35E5));
define('PR_COMMON_VIEWS_ENTRYID'                      ,mapi_prop_tag(PT_BINARY,      0x35E6));
define('PR_FINDER_ENTRYID'                            ,mapi_prop_tag(PT_BINARY,      0x35E7));
define('PR_IPM_FAVORITES_ENTRYID'                     ,mapi_prop_tag(PT_BINARY,      0x6630));
define('PR_IPM_PUBLIC_FOLDERS_ENTRYID'                ,mapi_prop_tag(PT_BINARY,      0x6631));


/* Proptags 0x35E8-0x35FF reserved for folders "guaranteed" by 'PR_VALID_FOLDER_MASK */


/*
 *  Folder and AB Container properties
 */

define('PR_CONTAINER_FLAGS'                           ,mapi_prop_tag(PT_LONG,        0x3600));
define('PR_FOLDER_TYPE'                               ,mapi_prop_tag(PT_LONG,        0x3601));
define('PR_CONTENT_COUNT'                             ,mapi_prop_tag(PT_LONG,        0x3602));
define('PR_CONTENT_UNREAD'                            ,mapi_prop_tag(PT_LONG,        0x3603));
define('PR_CREATE_TEMPLATES'                          ,mapi_prop_tag(PT_OBJECT,      0x3604));
define('PR_DETAILS_TABLE'                             ,mapi_prop_tag(PT_OBJECT,      0x3605));
define('PR_SEARCH'                                    ,mapi_prop_tag(PT_OBJECT,      0x3607));
define('PR_SELECTABLE'                                ,mapi_prop_tag(PT_BOOLEAN,     0x3609));
define('PR_SUBFOLDERS'                                ,mapi_prop_tag(PT_BOOLEAN,     0x360A));
define('PR_STATUS'                                    ,mapi_prop_tag(PT_LONG,        0x360B));
define('PR_ANR'                                       ,mapi_prop_tag(PT_TSTRING,     0x360C));
define('PR_CONTENTS_SORT_ORDER'                       ,mapi_prop_tag(PT_MV_LONG,     0x360D));
define('PR_CONTAINER_HIERARCHY'                       ,mapi_prop_tag(PT_OBJECT,      0x360E));
define('PR_CONTAINER_CONTENTS'                        ,mapi_prop_tag(PT_OBJECT,      0x360F));
define('PR_FOLDER_ASSOCIATED_CONTENTS'                ,mapi_prop_tag(PT_OBJECT,      0x3610));
define('PR_DEF_CREATE_DL'                             ,mapi_prop_tag(PT_BINARY,      0x3611));
define('PR_DEF_CREATE_MAILUSER'                       ,mapi_prop_tag(PT_BINARY,      0x3612));
define('PR_CONTAINER_CLASS'                           ,mapi_prop_tag(PT_TSTRING,     0x3613));
define('PR_CONTAINER_MODIFY_VERSION'                  ,mapi_prop_tag(PT_I8,          0x3614));
define('PR_AB_PROVIDER_ID'                            ,mapi_prop_tag(PT_BINARY,      0x3615));
define('PR_DEFAULT_VIEW_ENTRYID'                      ,mapi_prop_tag(PT_BINARY,      0x3616));
define('PR_ASSOC_CONTENT_COUNT'                       ,mapi_prop_tag(PT_LONG,        0x3617));
define('PR_EXTENDED_FOLDER_FLAGS'                     ,mapi_prop_tag(PT_BINARY,      0x36DA));

define('PR_RIGHTS'                                    ,mapi_prop_tag(PT_LONG,        0x6639));

/* Reserved 0x36C0-0x36FF */

/*
 *  Attachment properties
 */

define('PR_ATTACHMENT_X400_PARAMETERS'                ,mapi_prop_tag(PT_BINARY,      0x3700));
define('PR_ATTACH_DATA_OBJ'                           ,mapi_prop_tag(PT_OBJECT,      0x3701));
define('PR_ATTACH_DATA_BIN'                           ,mapi_prop_tag(PT_BINARY,      0x3701));
define('PR_ATTACH_CONTENT_ID'                         ,mapi_prop_tag(PT_STRING8,     0x3712));
define('PR_ATTACH_CONTENT_ID_W'                       ,mapi_prop_tag(PT_UNICODE,     0x3712));
define('PR_ATTACH_CONTENT_LOCATION'                   ,mapi_prop_tag(PT_STRING8,     0x3713));
define('PR_ATTACH_ENCODING'                           ,mapi_prop_tag(PT_BINARY,      0x3702));
define('PR_ATTACH_EXTENSION'                          ,mapi_prop_tag(PT_TSTRING,     0x3703));
define('PR_ATTACH_FILENAME'                           ,mapi_prop_tag(PT_TSTRING,     0x3704));
define('PR_ATTACH_METHOD'                             ,mapi_prop_tag(PT_LONG,        0x3705));
define('PR_ATTACH_LONG_FILENAME'                      ,mapi_prop_tag(PT_TSTRING,     0x3707));
define('PR_ATTACH_PATHNAME'                           ,mapi_prop_tag(PT_TSTRING,     0x3708));
define('PR_ATTACH_RENDERING'                          ,mapi_prop_tag(PT_BINARY,      0x3709));
define('PR_ATTACH_TAG'                                ,mapi_prop_tag(PT_BINARY,      0x370A));
define('PR_RENDERING_POSITION'                        ,mapi_prop_tag(PT_LONG,        0x370B));
define('PR_ATTACH_TRANSPORT_NAME'                     ,mapi_prop_tag(PT_TSTRING,     0x370C));
define('PR_ATTACH_LONG_PATHNAME'                      ,mapi_prop_tag(PT_TSTRING,     0x370D));
define('PR_ATTACH_MIME_TAG'                           ,mapi_prop_tag(PT_TSTRING,     0x370E));
define('PR_ATTACH_MIME_TAG_W'                         ,mapi_prop_tag(PT_UNICODE,     0x370E));
define('PR_ATTACH_ADDITIONAL_INFO'                    ,mapi_prop_tag(PT_BINARY,      0x370F));
define('PR_ATTACHMENT_FLAGS'                          ,mapi_prop_tag(PT_LONG,        0x7FFD));
define('PR_ATTACHMENT_HIDDEN'                         ,mapi_prop_tag(PT_BOOLEAN,     0x7FFE));
define('PR_ATTACHMENT_LINKID'                         ,mapi_prop_tag(PT_LONG,        0x7FFA));
define('PR_ATTACH_FLAGS'                              ,mapi_prop_tag(PT_LONG,        0x3714));
define('PR_EXCEPTION_STARTTIME'                       ,mapi_prop_tag(PT_SYSTIME,     0x7FFB));
define('PR_EXCEPTION_ENDTIME'                         ,mapi_prop_tag(PT_SYSTIME,     0x7FFC));

/*
 *  AB Object properties
 */

define('PR_DISPLAY_TYPE'                              ,mapi_prop_tag(PT_LONG,        0x3900));
define('PR_DISPLAY_TYPE_EX'                           ,mapi_prop_tag(PT_LONG,        0x3905));
define('PR_TEMPLATEID'                                ,mapi_prop_tag(PT_BINARY,      0x3902));
define('PR_PRIMARY_CAPABILITY'                        ,mapi_prop_tag(PT_BINARY,      0x3904));


/*
 *  Mail user properties
 */
define('PR_7BIT_DISPLAY_NAME'                         ,mapi_prop_tag(PT_STRING8,     0x39FF));
define('PR_ACCOUNT'                                   ,mapi_prop_tag(PT_TSTRING,     0x3A00));
define('PR_ALTERNATE_RECIPIENT'                       ,mapi_prop_tag(PT_BINARY,      0x3A01));
define('PR_CALLBACK_TELEPHONE_NUMBER'                 ,mapi_prop_tag(PT_TSTRING,     0x3A02));
define('PR_CONVERSION_PROHIBITED'                     ,mapi_prop_tag(PT_BOOLEAN,     0x3A03));
define('PR_DISCLOSE_RECIPIENTS'                       ,mapi_prop_tag(PT_BOOLEAN,     0x3A04));
define('PR_GENERATION'                                ,mapi_prop_tag(PT_TSTRING,     0x3A05));
define('PR_GIVEN_NAME'                                ,mapi_prop_tag(PT_TSTRING,     0x3A06));
define('PR_GOVERNMENT_ID_NUMBER'                      ,mapi_prop_tag(PT_TSTRING,     0x3A07));
define('PR_BUSINESS_TELEPHONE_NUMBER'                 ,mapi_prop_tag(PT_TSTRING,     0x3A08));
define('PR_OFFICE_TELEPHONE_NUMBER'                   ,PR_BUSINESS_TELEPHONE_NUMBER);
define('PR_HOME_TELEPHONE_NUMBER'                     ,mapi_prop_tag(PT_TSTRING,     0x3A09));
define('PR_INITIALS'                                  ,mapi_prop_tag(PT_TSTRING,     0x3A0A));
define('PR_KEYWORD'                                   ,mapi_prop_tag(PT_TSTRING,     0x3A0B));
define('PR_LANGUAGE'                                  ,mapi_prop_tag(PT_TSTRING,     0x3A0C));
define('PR_LOCATION'                                  ,mapi_prop_tag(PT_TSTRING,     0x3A0D));
define('PR_MAIL_PERMISSION'                           ,mapi_prop_tag(PT_BOOLEAN,     0x3A0E));
define('PR_MHS_COMMON_NAME'                           ,mapi_prop_tag(PT_TSTRING,     0x3A0F));
define('PR_ORGANIZATIONAL_ID_NUMBER'                  ,mapi_prop_tag(PT_TSTRING,     0x3A10));
define('PR_SURNAME'                                   ,mapi_prop_tag(PT_TSTRING,     0x3A11));
define('PR_ORIGINAL_ENTRYID'                          ,mapi_prop_tag(PT_BINARY,      0x3A12));
define('PR_ORIGINAL_DISPLAY_NAME'                     ,mapi_prop_tag(PT_TSTRING,     0x3A13));
define('PR_ORIGINAL_SEARCH_KEY'                       ,mapi_prop_tag(PT_BINARY,      0x3A14));
define('PR_POSTAL_ADDRESS'                            ,mapi_prop_tag(PT_TSTRING,     0x3A15));
define('PR_COMPANY_NAME'                              ,mapi_prop_tag(PT_TSTRING,     0x3A16));
define('PR_TITLE'                                     ,mapi_prop_tag(PT_TSTRING,     0x3A17));
define('PR_DEPARTMENT_NAME'                           ,mapi_prop_tag(PT_TSTRING,     0x3A18));
define('PR_OFFICE_LOCATION'                           ,mapi_prop_tag(PT_TSTRING,     0x3A19));
define('PR_PRIMARY_TELEPHONE_NUMBER'                  ,mapi_prop_tag(PT_TSTRING,     0x3A1A));
define('PR_BUSINESS2_TELEPHONE_NUMBER'                ,mapi_prop_tag(PT_TSTRING,     0x3A1B));
define('PR_OFFICE2_TELEPHONE_NUMBER'                  ,PR_BUSINESS2_TELEPHONE_NUMBER);
define('PR_MOBILE_TELEPHONE_NUMBER'                   ,mapi_prop_tag(PT_TSTRING,     0x3A1C));
define('PR_CELLULAR_TELEPHONE_NUMBER'                 ,PR_MOBILE_TELEPHONE_NUMBER);
define('PR_RADIO_TELEPHONE_NUMBER'                    ,mapi_prop_tag(PT_TSTRING,     0x3A1D));
define('PR_CAR_TELEPHONE_NUMBER'                      ,mapi_prop_tag(PT_TSTRING,     0x3A1E));
define('PR_OTHER_TELEPHONE_NUMBER'                    ,mapi_prop_tag(PT_TSTRING,     0x3A1F));
define('PR_TRANSMITABLE_DISPLAY_NAME'                 ,mapi_prop_tag(PT_TSTRING,     0x3A20));
define('PR_PAGER_TELEPHONE_NUMBER'                    ,mapi_prop_tag(PT_TSTRING,     0x3A21));
define('PR_BEEPER_TELEPHONE_NUMBER'                   ,PR_PAGER_TELEPHONE_NUMBER);
define('PR_USER_CERTIFICATE'                          ,mapi_prop_tag(PT_BINARY,      0x3A22));
define('PR_PRIMARY_FAX_NUMBER'                        ,mapi_prop_tag(PT_TSTRING,     0x3A23));
define('PR_BUSINESS_FAX_NUMBER'                       ,mapi_prop_tag(PT_TSTRING,     0x3A24));
define('PR_HOME_FAX_NUMBER'                           ,mapi_prop_tag(PT_TSTRING,     0x3A25));
define('PR_COUNTRY'                                   ,mapi_prop_tag(PT_TSTRING,     0x3A26));
define('PR_BUSINESS_ADDRESS_COUNTRY'                  ,PR_COUNTRY);

define('PR_FLAG_STATUS'                               ,mapi_prop_tag(PT_LONG,        0x1090));
define('PR_FLAG_COMPLETE_TIME'                        ,mapi_prop_tag(PT_SYSTIME,     0x1091));
define('PR_FLAG_ICON'                                 ,mapi_prop_tag(PT_LONG,        0x1095));
define('PR_BLOCK_STATUS'                              ,mapi_prop_tag(PT_LONG,        0x1096));

define('PR_LOCALITY'                                  ,mapi_prop_tag(PT_TSTRING,     0x3A27));
define('PR_BUSINESS_ADDRESS_CITY'                     ,PR_LOCALITY);

define('PR_STATE_OR_PROVINCE'                         ,mapi_prop_tag(PT_TSTRING,     0x3A28));
define('PR_BUSINESS_ADDRESS_STATE_OR_PROVINCE'        ,PR_STATE_OR_PROVINCE);

define('PR_STREET_ADDRESS'                            ,mapi_prop_tag(PT_TSTRING,     0x3A29));
define('PR_BUSINESS_ADDRESS_STREET'                   ,PR_STREET_ADDRESS);

define('PR_POSTAL_CODE'                               ,mapi_prop_tag(PT_TSTRING,     0x3A2A));
define('PR_BUSINESS_ADDRESS_POSTAL_CODE'              ,PR_POSTAL_CODE);


define('PR_POST_OFFICE_BOX'                           ,mapi_prop_tag(PT_TSTRING,     0x3A2B));
define('PR_BUSINESS_ADDRESS_POST_OFFICE_BOX'          ,PR_POST_OFFICE_BOX);


define('PR_TELEX_NUMBER'                              ,mapi_prop_tag(PT_TSTRING,     0x3A2C));
define('PR_ISDN_NUMBER'                               ,mapi_prop_tag(PT_TSTRING,     0x3A2D));
define('PR_ASSISTANT_TELEPHONE_NUMBER'                ,mapi_prop_tag(PT_TSTRING,     0x3A2E));
define('PR_HOME2_TELEPHONE_NUMBER'                    ,mapi_prop_tag(PT_TSTRING,     0x3A2F));
define('PR_ASSISTANT'                                 ,mapi_prop_tag(PT_TSTRING,     0x3A30));
define('PR_SEND_RICH_INFO'                            ,mapi_prop_tag(PT_BOOLEAN,     0x3A40));
define('PR_WEDDING_ANNIVERSARY'                       ,mapi_prop_tag(PT_SYSTIME,     0x3A41));
define('PR_BIRTHDAY'                                  ,mapi_prop_tag(PT_SYSTIME,     0x3A42));


define('PR_HOBBIES'                                   ,mapi_prop_tag(PT_TSTRING,     0x3A43));

define('PR_MIDDLE_NAME'                               ,mapi_prop_tag(PT_TSTRING,     0x3A44));

define('PR_DISPLAY_NAME_PREFIX'                       ,mapi_prop_tag(PT_TSTRING,     0x3A45));

define('PR_PROFESSION'                                ,mapi_prop_tag(PT_TSTRING,     0x3A46));

define('PR_PREFERRED_BY_NAME'                         ,mapi_prop_tag(PT_TSTRING,     0x3A47));

define('PR_SPOUSE_NAME'                               ,mapi_prop_tag(PT_TSTRING,     0x3A48));

define('PR_COMPUTER_NETWORK_NAME'                     ,mapi_prop_tag(PT_TSTRING,     0x3A49));

define('PR_CUSTOMER_ID'                               ,mapi_prop_tag(PT_TSTRING,     0x3A4A));

define('PR_TTYTDD_PHONE_NUMBER'                       ,mapi_prop_tag(PT_TSTRING,     0x3A4B));

define('PR_FTP_SITE'                                  ,mapi_prop_tag(PT_TSTRING,     0x3A4C));

define('PR_GENDER'                                    ,mapi_prop_tag(PT_SHORT,       0x3A4D));

define('PR_MANAGER_NAME'                              ,mapi_prop_tag(PT_TSTRING,     0x3A4E));

define('PR_NICKNAME'                                  ,mapi_prop_tag(PT_TSTRING,     0x3A4F));

define('PR_PERSONAL_HOME_PAGE'                        ,mapi_prop_tag(PT_TSTRING,     0x3A50));


define('PR_BUSINESS_HOME_PAGE'                        ,mapi_prop_tag(PT_TSTRING,     0x3A51));

define('PR_CONTACT_VERSION'                           ,mapi_prop_tag(PT_CLSID,       0x3A52));
define('PR_CONTACT_ENTRYIDS'                          ,mapi_prop_tag(PT_MV_BINARY,   0x3A53));

define('PR_CONTACT_ADDRTYPES'                         ,mapi_prop_tag(PT_MV_TSTRING,  0x3A54));

define('PR_CONTACT_DEFAULT_ADDRESS_INDEX'             ,mapi_prop_tag(PT_LONG,        0x3A55));

define('PR_CONTACT_EMAIL_ADDRESSES'                   ,mapi_prop_tag(PT_MV_TSTRING,  0x3A56));
define('PR_ATTACHMENT_CONTACTPHOTO'                   ,mapi_prop_tag(PT_BOOLEAN,     0x7FFF));


define('PR_COMPANY_MAIN_PHONE_NUMBER'                 ,mapi_prop_tag(PT_TSTRING,     0x3A57));

define('PR_CHILDRENS_NAMES'                           ,mapi_prop_tag(PT_MV_TSTRING,  0x3A58));



define('PR_HOME_ADDRESS_CITY'                         ,mapi_prop_tag(PT_TSTRING,     0x3A59));

define('PR_HOME_ADDRESS_COUNTRY'                      ,mapi_prop_tag(PT_TSTRING,     0x3A5A));

define('PR_HOME_ADDRESS_POSTAL_CODE'                  ,mapi_prop_tag(PT_TSTRING,     0x3A5B));

define('PR_HOME_ADDRESS_STATE_OR_PROVINCE'            ,mapi_prop_tag(PT_TSTRING,     0x3A5C));

define('PR_HOME_ADDRESS_STREET'                       ,mapi_prop_tag(PT_TSTRING,     0x3A5D));

define('PR_HOME_ADDRESS_POST_OFFICE_BOX'              ,mapi_prop_tag(PT_TSTRING,     0x3A5E));

define('PR_OTHER_ADDRESS_CITY'                        ,mapi_prop_tag(PT_TSTRING,     0x3A5F));

define('PR_OTHER_ADDRESS_COUNTRY'                     ,mapi_prop_tag(PT_TSTRING,     0x3A60));

define('PR_OTHER_ADDRESS_POSTAL_CODE'                 ,mapi_prop_tag(PT_TSTRING,     0x3A61));

define('PR_OTHER_ADDRESS_STATE_OR_PROVINCE'           ,mapi_prop_tag(PT_TSTRING,     0x3A62));

define('PR_OTHER_ADDRESS_STREET'                      ,mapi_prop_tag(PT_TSTRING,     0x3A63));

define('PR_OTHER_ADDRESS_POST_OFFICE_BOX'             ,mapi_prop_tag(PT_TSTRING,     0x3A64));

define('PR_USER_X509_CERTIFICATE'                     ,mapi_prop_tag(PT_MV_BINARY,   0x3A70));

/*
 *  Profile section properties
 */

define('PR_STORE_PROVIDERS'                           ,mapi_prop_tag(PT_BINARY,      0x3D00));
define('PR_AB_PROVIDERS'                              ,mapi_prop_tag(PT_BINARY,      0x3D01));
define('PR_TRANSPORT_PROVIDERS'                       ,mapi_prop_tag(PT_BINARY,      0x3D02));

define('PR_DEFAULT_PROFILE'                           ,mapi_prop_tag(PT_BOOLEAN,     0x3D04));
define('PR_AB_SEARCH_PATH'                            ,mapi_prop_tag(PT_MV_BINARY,   0x3D05));
define('PR_AB_DEFAULT_DIR'                            ,mapi_prop_tag(PT_BINARY,      0x3D06));
define('PR_AB_DEFAULT_PAB'                            ,mapi_prop_tag(PT_BINARY,      0x3D07));

define('PR_FILTERING_HOOKS'                           ,mapi_prop_tag(PT_BINARY,      0x3D08));
define('PR_SERVICE_NAME'                              ,mapi_prop_tag(PT_TSTRING,     0x3D09));
define('PR_SERVICE_DLL_NAME'                          ,mapi_prop_tag(PT_TSTRING,     0x3D0A));
define('PR_SERVICE_ENTRY_NAME'                        ,mapi_prop_tag(PT_STRING8,     0x3D0B));
define('PR_SERVICE_UID'                               ,mapi_prop_tag(PT_BINARY,      0x3D0C));
define('PR_SERVICE_EXTRA_UIDS'                        ,mapi_prop_tag(PT_BINARY,      0x3D0D));
define('PR_SERVICES'                                  ,mapi_prop_tag(PT_BINARY,      0x3D0E));
define('PR_SERVICE_SUPPORT_FILES'                     ,mapi_prop_tag(PT_MV_TSTRING,  0x3D0F));
define('PR_SERVICE_DELETE_FILES'                      ,mapi_prop_tag(PT_MV_TSTRING,  0x3D10));
define('PR_AB_SEARCH_PATH_UPDATE'                     ,mapi_prop_tag(PT_BINARY,      0x3D11));
define('PR_PROFILE_NAME'                              ,mapi_prop_tag(PT_TSTRING,     0x3D12));

/*
 *  Status object properties
 */

define('PR_IDENTITY_DISPLAY'                          ,mapi_prop_tag(PT_TSTRING,     0x3E00));
define('PR_IDENTITY_ENTRYID'                          ,mapi_prop_tag(PT_BINARY,      0x3E01));
define('PR_RESOURCE_METHODS'                          ,mapi_prop_tag(PT_LONG,        0x3E02));
define('PR_RESOURCE_TYPE'                             ,mapi_prop_tag(PT_LONG,        0x3E03));
define('PR_STATUS_CODE'                               ,mapi_prop_tag(PT_LONG,        0x3E04));
define('PR_IDENTITY_SEARCH_KEY'                       ,mapi_prop_tag(PT_BINARY,      0x3E05));
define('PR_OWN_STORE_ENTRYID'                         ,mapi_prop_tag(PT_BINARY,      0x3E06));
define('PR_RESOURCE_PATH'                             ,mapi_prop_tag(PT_TSTRING,     0x3E07));
define('PR_STATUS_STRING'                             ,mapi_prop_tag(PT_TSTRING,     0x3E08));
define('PR_X400_DEFERRED_DELIVERY_CANCEL'             ,mapi_prop_tag(PT_BOOLEAN,     0x3E09));
define('PR_HEADER_FOLDER_ENTRYID'                     ,mapi_prop_tag(PT_BINARY,      0x3E0A));
define('PR_REMOTE_PROGRESS'                           ,mapi_prop_tag(PT_LONG,        0x3E0B));
define('PR_REMOTE_PROGRESS_TEXT'                      ,mapi_prop_tag(PT_TSTRING,     0x3E0C));
define('PR_REMOTE_VALIDATE_OK'                        ,mapi_prop_tag(PT_BOOLEAN,     0x3E0D));

/*
 * Display table properties
 */

define('PR_CONTROL_FLAGS'                             ,mapi_prop_tag(PT_LONG,        0x3F00));
define('PR_CONTROL_STRUCTURE'                         ,mapi_prop_tag(PT_BINARY,      0x3F01));
define('PR_CONTROL_TYPE'                              ,mapi_prop_tag(PT_LONG,        0x3F02));
define('PR_DELTAX'                                    ,mapi_prop_tag(PT_LONG,        0x3F03));
define('PR_DELTAY'                                    ,mapi_prop_tag(PT_LONG,        0x3F04));
define('PR_XPOS'                                      ,mapi_prop_tag(PT_LONG,        0x3F05));
define('PR_YPOS'                                      ,mapi_prop_tag(PT_LONG,        0x3F06));
define('PR_CONTROL_ID'                                ,mapi_prop_tag(PT_BINARY,      0x3F07));
define('PR_INITIAL_DETAILS_PANE'                      ,mapi_prop_tag(PT_LONG,        0x3F08));

/*
 * Secure property id range
 */

define('PROP_ID_SECURE_MIN'                           ,0x67F0);
define('PROP_ID_SECURE_MAX'                           ,0x67FF);

/*
 * Extra properties
 */

define('PR_IPM_APPOINTMENT_ENTRYID'                   ,mapi_prop_tag(PT_BINARY,      0x36D0));
define('PR_IPM_CONTACT_ENTRYID'                       ,mapi_prop_tag(PT_BINARY,      0x36D1));
define('PR_IPM_JOURNAL_ENTRYID'                       ,mapi_prop_tag(PT_BINARY,      0x36D2));
define('PR_IPM_NOTE_ENTRYID'                          ,mapi_prop_tag(PT_BINARY,      0x36D3));
define('PR_IPM_TASK_ENTRYID'                          ,mapi_prop_tag(PT_BINARY,      0x36D4));
define('PR_IPM_DRAFTS_ENTRYID'                        ,mapi_prop_tag(PT_BINARY,      0x36D7));
/*
PR_ADDITIONAL_REN_ENTRYIDS:
    This is a multivalued property which contains entry IDs for certain special folders.
    The first 5 (0-4) entries in this multivalued property are as follows:
        0 - Conflicts folder
        1 - Sync Issues folder
        2 - Local Failures folder
        3 - Server Failures folder
        4 - Junk E-mail Folder
        5 - sfSpamTagDontUse (unknown what this is, disable olk spam stuff?)
*/
define('PR_ADDITIONAL_REN_ENTRYIDS'                   ,mapi_prop_tag(PT_MV_BINARY,   0x36D8));
define('PR_FREEBUSY_ENTRYIDS'                         ,mapi_prop_tag(PT_MV_BINARY,   0x36E4));
define('PR_REM_ONLINE_ENTRYID'                        ,mapi_prop_tag(PT_BINARY,      0x36D5));
define('PR_REM_OFFLINE_ENTRYID'                       ,mapi_prop_tag(PT_BINARY,      0x36D6));
define('PR_FREEBUSY_COUNT_MONTHS'                     ,mapi_prop_tag(PT_LONG,        0x6869));
/*
PR_IPM_OL2007_ENTRYIDS:
    This is a single binary property containing the entryids for:
    - 'Rss feeds' folder
    - The searchfolder 'Tracked mail processing'
    - The searchfolder 'To-do list'

    However, it is encoded something like the following:

    01803200 (type: rss feeds ?)
    0100
    2E00
    00000000B774162F0098C84182DE9E4358E4249D01000B41FF66083D464EA7E34D6026C9B143000000006DDA0000 (entryid)
    04803200 (type: tracked mail processing ?)
    0100
    2E00
    00000000B774162F0098C84182DE9E4358E4249D01000B41FF66083D464EA7E34D6026C9B143000000006DDB0000 (entryid)
    02803200 (type: todo list ?)
    0100
    2E00
    00000000B774162F0098C84182DE9E4358E4249D01000B41FF66083D464EA7E34D6026C9B143000000006DE40000 (entryid)
    00000000 (terminator?)

    It may also only contain the rss feeds entryid, and then have the 00000000 terminator directly after the entryid:

    01803200 (type: rss feeds ?)
    0100
    2E00
    00000000B774162F0098C84182DE9E4358E4249D01000B41FF66083D464EA7E34D6026C9B143000000006DDA0000 (entryid)
    00000000 (terminator?)
*/
define('PR_IPM_OL2007_ENTRYIDS'                       ,mapi_prop_tag(PT_BINARY,      0x36D9));
// Note: PR_IPM_OL2007_ENTRYIDS is the same property as PR_ADDITIONAL_REN_ENTRYIDS_EX, but Microsoft
// seems to use the latter hence we will also use that to not confuse developers that want to Google it.
define('PR_ADDITIONAL_REN_ENTRYIDS_EX'                ,mapi_prop_tag(PT_BINARY,      0x36D9));



/*
 * Don't know where to put these
 */

define('PR_ICON_INDEX'                                ,mapi_prop_tag(PT_LONG,        0x1080));
define('PR_LAST_VERB_EXECUTED'                        ,mapi_prop_tag(PT_LONG,        0x1081));
define('PR_LAST_VERB_EXECUTION_TIME'                  ,mapi_prop_tag(PT_SYSTIME,     0x1082));
define('PR_INTERNET_CPID'                             ,mapi_prop_tag(PT_LONG,        0x3FDE));
define('PR_RECIPIENT_ENTRYID'                         ,mapi_prop_tag(PT_BINARY,      0x5FF7));
define('PR_SEND_INTERNET_ENCODING'                    ,mapi_prop_tag(PT_LONG,        0x3FDE));
define('PR_RECIPIENT_DISPLAY_NAME'                    ,mapi_prop_tag(PT_STRING8,     0x5FF6));
define('PR_RECIPIENT_TRACKSTATUS'                     ,mapi_prop_tag(PT_LONG,        0x5FFF));
define('PR_RECIPIENT_FLAGS'                           ,mapi_prop_tag(PT_LONG,        0x5FFD));
define('PR_RECIPIENT_TRACKSTATUS_TIME'                ,mapi_prop_tag(PT_SYSTIME,     0x5FFB));

define('PR_EC_OUTOFOFFICE'                            ,mapi_prop_tag(PT_BOOLEAN,     0x6760));
define('PR_EC_OUTOFOFFICE_MSG'                        ,mapi_prop_tag(PT_STRING8,     0x6761));
define('PR_EC_OUTOFOFFICE_SUBJECT'                    ,mapi_prop_tag(PT_STRING8,     0x6762));
define('PR_EC_OUTOFOFFICE_FROM'                       ,mapi_prop_tag(PT_SYSTIME,     0x6763));
define('PR_EC_OUTOFOFFICE_UNTIL'                      ,mapi_prop_tag(PT_SYSTIME,     0x6764));

/* quota support */
define('PR_QUOTA_WARNING_THRESHOLD'                   ,mapi_prop_tag(PT_LONG,        0x6721));
define('PR_QUOTA_SEND_THRESHOLD'                      ,mapi_prop_tag(PT_LONG,        0x6722));
define('PR_QUOTA_RECEIVE_THRESHOLD'                   ,mapi_prop_tag(PT_LONG,        0x6723));

/* storage for the settings for the webaccess 6.xx */
define('PR_EC_WEBACCESS_SETTINGS'                     ,mapi_prop_tag(PT_STRING8,     0x6770));
define('PR_EC_RECIPIENT_HISTORY'                      ,mapi_prop_tag(PT_STRING8,     0x6771));

/* storage for the settings for the webaccess 7.xx */
define('PR_EC_WEBACCESS_SETTINGS_JSON'                ,mapi_prop_tag(PT_STRING8,     0x6772));
define('PR_EC_RECIPIENT_HISTORY_JSON'                 ,mapi_prop_tag(PT_STRING8,     0x6773));

/* The peristent settings are settings that will not be touched when the settings are reset */
define('PR_EC_WEBAPP_PERSISTENT_SETTINGS_JSON'        ,mapi_prop_tag(PT_STRING8,     0x6774));

/* statistics properties */
define('PR_EC_STATSTABLE_SYSTEM'                      ,mapi_prop_tag(PT_OBJECT,      0x6730));
define('PR_EC_STATSTABLE_SESSIONS'                    ,mapi_prop_tag(PT_OBJECT,      0x6731));
define('PR_EC_STATSTABLE_USERS'                       ,mapi_prop_tag(PT_OBJECT,      0x6732));
define('PR_EC_STATSTABLE_COMPANY'                     ,mapi_prop_tag(PT_OBJECT,      0x6733));

define('PR_EC_STATS_SYSTEM_DESCRIPTION'               ,mapi_prop_tag(PT_STRING8,     0x6740));
define('PR_EC_STATS_SYSTEM_VALUE'                     ,mapi_prop_tag(PT_STRING8,     0x6741));
define('PR_EC_STATS_SESSION_ID'                       ,mapi_prop_tag(PT_LONG,        0x6742));
define('PR_EC_STATS_SESSION_IPADDRESS'                ,mapi_prop_tag(PT_STRING8,     0x6743));
define('PR_EC_STATS_SESSION_IDLETIME'                 ,mapi_prop_tag(PT_LONG,        0x6744));
define('PR_EC_STATS_SESSION_CAPABILITY'               ,mapi_prop_tag(PT_LONG,        0x6745));
define('PR_EC_STATS_SESSION_LOCKED'                   ,mapi_prop_tag(PT_BOOLEAN,     0x6746));
define('PR_EC_STATS_SESSION_BUSYSTATES'               ,mapi_prop_tag(PT_MV_STRING8,  0x6747));
define('PR_EC_COMPANY_NAME'                           ,mapi_prop_tag(PT_STRING8,     0x6748));

/* user features */
define('PR_EC_ENABLED_FEATURES'                       ,mapi_prop_tag(PT_MV_TSTRING,  0x67B3));
define('PR_EC_DISABLED_FEATURES'                      ,mapi_prop_tag(PT_MV_TSTRING,  0x67B4));

/* WA properties */
define('PR_EC_WA_ATTACHMENT_HIDDEN_OVERRIDE'          ,mapi_prop_tag(PT_BOOLEAN,     0x67E0));
define('PR_EC_WA_ATTACHMENT_ID'                       ,mapi_prop_tag(PT_STRING8,     0x67E1));

// edkmdb, rules properties
#define pidSpecialMin                                   0x6670
define('PR_RULE_ID'                                   ,mapi_prop_tag(PT_I8,          0x6674)); // only lower 32bits are used.
define('PR_RULE_IDS'                                  ,mapi_prop_tag(PT_BINARY,      0x6675));
define('PR_RULE_SEQUENCE'                             ,mapi_prop_tag(PT_LONG,        0x6676));
define('PR_RULE_STATE'                                ,mapi_prop_tag(PT_LONG,        0x6677));
define('PR_RULE_USER_FLAGS'                           ,mapi_prop_tag(PT_LONG,        0x6678));
define('PR_RULE_CONDITION'                            ,mapi_prop_tag(PT_SRESTRICTION,0x6679));
define('PR_RULE_ACTIONS'                              ,mapi_prop_tag(PT_ACTIONS,     0x6680));
define('PR_RULE_PROVIDER'                             ,mapi_prop_tag(PT_STRING8,     0x6681));
define('PR_RULE_NAME'                                 ,mapi_prop_tag(PT_TSTRING,     0x6682));
define('PR_RULE_LEVEL'                                ,mapi_prop_tag(PT_LONG,        0x6683));
define('PR_RULE_PROVIDER_DATA'                        ,mapi_prop_tag(PT_BINARY,      0x6684));

// edkmdb, ICS properties
define('PR_SOURCE_KEY'                                ,mapi_prop_tag(PT_BINARY,      0x65E0));
define('PR_PARENT_SOURCE_KEY'                         ,mapi_prop_tag(PT_BINARY,      0x65E1));
define('PR_CHANGE_KEY'                                ,mapi_prop_tag(PT_BINARY,      0x65E2));
define('PR_PREDECESSOR_CHANGE_LIST'                   ,mapi_prop_tag(PT_BINARY,      0x65E3));


define('PR_PROCESS_MEETING_REQUESTS'                  ,mapi_prop_tag(PT_BOOLEAN,     0x686D));
define('PR_DECLINE_RECURRING_MEETING_REQUESTS'        ,mapi_prop_tag(PT_BOOLEAN,     0x686E));
define('PR_DECLINE_CONFLICTING_MEETING_REQUESTS'      ,mapi_prop_tag(PT_BOOLEAN,     0x686F));


define('PR_PROPOSEDNEWTIME'                           ,mapi_prop_tag(PT_BOOLEAN,     0x5FE1));
define('PR_PROPOSENEWTIME_START'                      ,mapi_prop_tag(PT_SYSTIME,     0x5FE3));
define('PR_PROPOSENEWTIME_END'                        ,mapi_prop_tag(PT_SYSTIME,     0x5FE4));

// property for sort the recoverable items.
define('PR_DELETED_ON'                                ,mapi_prop_tag(PT_SYSTIME,     0x668F));

define('PR_PROCESSED'                                 ,mapi_prop_tag(PT_BOOLEAN,     0x7D01));

// Delegates properties
define('PR_DELEGATES_SEE_PRIVATE'                     ,mapi_prop_tag(PT_MV_LONG,     0x686B));
define('PR_SCHDINFO_DELEGATE_ENTRYIDS'                ,mapi_prop_tag(PT_MV_BINARY,   0x6845));
define('PR_SCHDINFO_DELEGATE_NAMES'                   ,mapi_prop_tag(PT_MV_STRING8,  0x6844));
define('PR_DELEGATED_BY_RULE'                         ,mapi_prop_tag(PT_BOOLEAN,     0x3FE3));

// properties required in Reply mail.
define('PR_INTERNET_REFERENCES'                       ,mapi_prop_tag(PT_STRING8,     0x1039));
define('PR_IN_REPLY_TO_ID'                            ,mapi_prop_tag(PT_STRING8,     0x1042));
define('PR_INTERNET_MESSAGE_ID'                       ,mapi_prop_tag(PT_STRING8,     0x1035));

// for hidden folders
define('PR_ATTR_HIDDEN'                               ,mapi_prop_tag(PT_BOOLEAN,     0x10F4));

/**
 * Addressbook detail properties.
 * It is not defined by MAPI, but to keep in sync with the interface of outlook we have to use these
 * properties. Outlook actually uses these properties for it's addressbook details.
 */
define('PR_HOME2_TELEPHONE_NUMBER_MV'                 ,mapi_prop_tag(PT_MV_TSTRING,  0x3A2F));
define('PR_BUSINESS2_TELEPHONE_NUMBER_MV'             ,mapi_prop_tag(PT_MV_TSTRING,  0x3A1B));
define('PR_EMS_AB_PROXY_ADDRESSES'                    ,mapi_prop_tag(PT_TSTRING,     0x800F));
define('PR_EMS_AB_PROXY_ADDRESSES_MV'                 ,mapi_prop_tag(PT_MV_TSTRING,  0x800F));
define('PR_EMS_AB_MANAGER'                            ,mapi_prop_tag(PT_BINARY,      0x8005));
define('PR_EMS_AB_REPORTS'                            ,mapi_prop_tag(PT_BINARY,      0x800E));
define('PR_EMS_AB_REPORTS_MV'                         ,mapi_prop_tag(PT_MV_BINARY,   0x800E));
define('PR_EMS_AB_IS_MEMBER_OF_DL'                    ,mapi_prop_tag(PT_MV_BINARY,   0x8008));
define('PR_EMS_AB_OWNER'                              ,mapi_prop_tag(PT_BINARY,      0x800C));
define('PR_EMS_AB_ROOM_CAPACITY'                      ,mapi_prop_tag(PT_LONG,        0x0807));
define('PR_EMS_AB_TAGGED_X509_CERT'                   ,mapi_prop_tag(PT_MV_BINARY,   0x8C6A));
define('PR_EMS_AB_THUMBNAIL_PHOTO'                    ,mapi_prop_tag(PT_BINARY,      0x8C9E));

define('PR_EC_ARCHIVE_SERVERS'                        ,mapi_prop_tag(PT_MV_TSTRING,  0x67c4));

/* kopano contacts provider properties */
define('PR_ZC_CONTACT_STORE_ENTRYIDS'                 ,mapi_prop_tag(PT_MV_BINARY,   0x6711));
define('PR_ZC_CONTACT_FOLDER_ENTRYIDS'                ,mapi_prop_tag(PT_MV_BINARY,   0x6712));
define('PR_ZC_CONTACT_FOLDER_NAMES'                   ,mapi_prop_tag(PT_MV_TSTRING,  0x6713));

/* kopano specific properties for optimization of imap functionality */
define('PR_EC_IMAP_EMAIL'                             ,mapi_prop_tag(PT_BINARY,      0x678C)); //the complete rfc822 email
define('PR_EC_IMAP_EMAIL_SIZE'                        ,mapi_prop_tag(PT_LONG,        0x678D));
define('PR_EC_IMAP_BODY'                              ,mapi_prop_tag(PT_STRING8,     0x678E)); //simplified bodystructure (mostly unused by clients)
define('PR_EC_IMAP_BODYSTRUCTURE'                     ,mapi_prop_tag(PT_STRING8,     0x678F)); //extended bodystructure (often used by clients)

/* Folder properties for unread counters */
define('PR_LOCAL_COMMIT_TIME_MAX'                     ,mapi_prop_tag(PT_SYSTIME,     0x670A));
define('PR_DELETED_MSG_COUNT'                         ,mapi_prop_tag(PT_LONG,        0x6640));

/* Favorites folder properties*/
define('PR_WLINK_ENTRYID'                             ,mapi_prop_tag(PT_BINARY,      0x684C));
define('PR_WLINK_FLAGS'                               ,mapi_prop_tag(PT_LONG,        0x684A));
define('PR_WLINK_ORDINAL'                             ,mapi_prop_tag(PT_BINARY,      0x684B));
define('PR_WLINK_STORE_ENTRYID'                       ,mapi_prop_tag(PT_BINARY,      0x684E));
define('PR_WLINK_TYPE'                                ,mapi_prop_tag(PT_LONG,        0x6849));
define('PR_WLINK_SECTION'                             ,mapi_prop_tag(PT_LONG,        0x6852));
define('PR_WLINK_RECKEY'                              ,mapi_prop_tag(PT_BINARY,      0x684D));
define('PR_WB_SF_ID'                                  ,mapi_prop_tag(PT_BINARY,      0x6842));

/* Search folder properties */
define('PR_EC_SUGGESTION'                             ,mapi_prop_tag(PT_TSTRING,     0x6707));

define('PR_EC_BODY_FILTERED'                          ,mapi_prop_tag(PT_BINARY, 0x6791));
define('PR_PROPOSEDNEWTIME_START'                     ,PR_PROPOSENEWTIME_START);
define('PR_PROPOSEDNEWTIME_END'                       ,PR_PROPOSENEWTIME_END);
