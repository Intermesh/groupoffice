- Maildomains: fix display of active column in aliases table #1482
- Core: increase max size of email template subject field

20-04-2026: 26.0.25
- Calendar: Event location field allows free text input
- Core: Fixed holidays error in billing because of type and case problem in generate() function
- Calendar: update category list according to selected calendar in event window
- Email: Remove old code which had an authenticated stored XSS vulnerability
- Core: Validate user ID param when saving settings
- Calendar: Fix error when in account dialog when you don't have permissions to the calendar.
- Emailfavorites: Fix error when you don't have permissions
- Tasks: On migration subscribe to tasklists owned by the user
- Support: Don't process @mention on migration
- OTP: explicitly log username and IP upon wrong authentication
- Email: Do not show 'save to personal folder' button if no access to files module
- Forms: increase maximum size of field length

14-04-2026: 26.0.24
- Support: Mail sending was broken if SMIME was not configured
- Notes: Fixed note dialog on mobiles
- ActiveSync: Missing MSTZ function

13-04-2026: 26.0.23
- Support: Threading issue when someone sent more than one email within the import interval (5 mins).
- Files: Better validator for folder names for Windows compatibility (cherry-pick from 6.8)
- Updated Japanase translations. Arigato HIRA Shuichi
- Updated Czech translations. Thanks to Mareg
- Calendar: Only process invitations for enabled users and don't fail if one user fails
- Core: Cleanup some old entities
- Files: Move to trash could accidentally remove selected tree folder instead of selection in the thumbnail view
- Forms: set maximum length validation for label and hint fields

09-04-2026: 26.0.22
- Update GOUI version

09-04-2026: 26.0.21
- Calendar: State mismatch for this and future will cancel the creation
- Comments / Core: Fixed some upgrade issues where table collation was incorrect
- Savemailas: Fixed save as note
- Tasks / Support: Set default task list when creating a new task/ticket from other modules
- Notes: Open note when creating a new note
- Workflow: make permissions depend on mayManage
- Core: log error in apache log when token invalid
- Files: fix 'Move to trash' in tree context menu
- Billing: Fixed type error in billing when duplicating orders


02-04-2026: 26.0.20
- imapauth: Fixed login problem for new users

31-03-2026: 26.0.19
- Core: Fixed login problem with history and permission check on acl ownerr
- Files: Trashed folders do no longer show in the tree

30-03-2026: 26.0.18
- Files: Fix ACL error when moving folder with subfolders as a normal user
- Studio: fix bug in generating ActItemEntityCombo 
- MS Teams: Fix for MS bug when curl uses http2 and ALPN
- Savemailas: Tasks in email list function always queried all linked e-mails
- Core: Reset entity store when opening account settings to avoid lots of changes requests
- Core: Set 'to' if you are an admin in core/Notify/mail controller
 
 
24-03-2026: 26.0.17
- Addressbook: Fixed SQL Injection vulnerability in contact filters
- Core: Fixed: Typed property SmtpAccount::$id must not be accessed before initialization #1462

20-03-2026: 26.0.16
- Files: fix file browser button for older modules
- Core: Convert dates to user timezone in spreadsheet export

16-03-2026: 26.0.15
- Oauth2client: Workaround MS bug where it returns 404 if http2 is used with alpn. This happened on Debian Trixie.

13-03-2026: 26.0.14
- Core: Added LT translations.
- Files: fix error when cleaning out the trash and a file or folder does not exist anymore in the database.
- Core / CalDAV: Compare dates with date time format for database. This fixes an issue with CalDAV where the datetime object is different in timezone but this has no effect in the database. Therefore the event wrongfully thought the start time was changed and the participant status was reset.
- CalDAV: etag was always immediately changed for new events leading to a resync after a first accept
- Core: Fixed bug where SSE and z-push would constantly clear the cached database scheme
- Files: Fixed issue where a sub folder could appear twice in the shares tree

09-03-2026: 26.0.13
- Core: Tooltip was not removed if the target's parent was removed
- Core: Replaced unsafe MaestroError/php-heic-to-jpg lib with imagick PHP extension to support HEIF image files.
- Core: PHP 8.5 compatibility issues
- Address book: Fixed contact sort order when showing names as  "lastname, firstname". Fixes #1219
- Core: Also treat / and \ as white space in searching. Fixes Search in note title, is not contains string #1340
- Core / Sync: fix database exception when saving new user
- Projects3: Webdav support for projects3

06-03-2026: 26.0.12
- LDAP: Fixed LDAP authentication not work #1444
- Core: Fixed encoding issue after moving grid columns
- Calendar: Fixed issue: No email when a participant confirm or decline #1437
- Core: Fixed Authorization bypass: IDOR in SmtpAccount/test, Calendar downloadIcs, DavAccount/sync + missing admin checks #1447
- Core: Fixed Authenticated Remote Code Execution via PHP Insecure Deserialization in `AbstractSettingsCollection`
- Core: Fixed Self XSS in GroupOffice Installer License Page (install/license.php)

02-03-2026: 26.0.11
- Comments: Fixed comment routing from search
- Support: Search in comments too

28-02-2026: 26.0.10
- Tasks: Fixed subscribe to task lists
- Comments: Images didn't load in edit dialog
- Comments: Fixed markup for lists
- History: Fixed invalid formatting of changed details
- Core: Fixed xss vulnerability in external function handling
- Core: Fixed xss in installer

24-02-2026: 26.0.9
- Core: Fixed RCE vulnerability.

24-02-2026: 26.0.8
- Core: Allow archiving if you have "mayChangeUsers" permission
- Support: fixed "Save e-mail as ticket" function
- pdfeditor: Fixed GO 26.0.7 internal PDF-editor refuses saving #1428. It assumed GO is installed in the root.
- Core: Fixed comments not reloading when edited
- wopi: add clipboard-read and clipboard-write permissions
- Contracts: remove redundant customer field
- ActiveSync: Fixed: Issues with Z-push and v16.0 instanceid handling. Fixes Error in Z-Push/src/lib/request/sync.php at line 559: Class "GSync" not found #1426
- Calendar: Fixed display issue of read only events in the calendar
- Core: Fixed SQL injection vulnerability in deprecated code that's removed now

18-02-2026: 26.0.7
- Core: Check if export is not empty.
- LDAPAuthenticator enable and optionally enforce TOTP workflow (cherry-pick from 6.8)
- Support: Implemented quote collapsing in support
- Assistant: When file is locked warn about it and download it read only.
- Calendar: Added start page widget
- Core: A bookmark with "Behave as module" could mess up sort order
- Oauth: Correct wrong username in email account
- 
09-02-2026: 26.0.6
- jitsimeet: Add sub to jwt
- davclient: Add option to disable SSL validation
- MS Teams: meeting gets decription with meeting id, passcode and meeting options link. Teams event is also set with subject and meeting time
- Finance: Migrate from billing didn't copy frontpage text to greeting and sometimes address was missing
- Finance: New book option to make items read only after they have been sent
- Core: Fixed inserting links with Safari in email composer

02-02-2026: 26.0.5
- Core: fixed Security vulnerability Remote Code Execution (RCE)
- Core: Only allow http(s) protocols in httpclient for security reasons. Fixes SSRF and File Read in WOPI service discovery.
- Core: Remove debug and info from client with ctrl+f7 for security reasons
- Core: removed old code and restricted User access to admins or users with "mayChangeUser" permissions
- Files: Remove url in link in FilePanel.js to avoid confusion with other links
- Finance: FinanceStatus::$color can be null
- Email: Fixed  RCE - Command Injection via TNEF Attachment Handler

 29-01-2026: 26.0.4
- calendar: Fixed create link field in event dialog
- Core: Cron jobs no output buffering and debug memory
- Core: New config options for mail debugging (mailerDebugHost and mailerDebugPort)
- Core: Exit with error immediately when config file can't be loaded. It hung endlessly on require.
- Core: New config options for mail debugging (mailerDebugHost and mailerDebugPort)
- Calendar: Fixed calendar load problem if a relation with id="0" was fetched. In this case modifiedBy was '0' after]
  migrating an ancient 4.0 version.
- Wopi: Removed references to business package. Fixes 26.0.3 module store complains about missing module files #1409
- Business: cascade managers when user is deleted
- Calendar: Add participant on blur
- Core: updated Italian translation. Thanks Alexander!
- Kanban: was missing
- PDF Editor: New module for annotating PDF files
- Email favorites: New module to bookmark email folders
- Core: Only lock set operations for the same entity as it blocked serverclient requests to the same group-office instance.
- Email: Fixed dropzone flickering when dropping files

20-01-2026: 26.0.3
- wopi: Was still not available without a license

19-01-2026: 26.0.2
Initial release: https://www.group-office.com/blog/2026/01/groupoffice-26.0-released
