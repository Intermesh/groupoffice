- Calendar: allow booking into the resource calendars directly
- Core: APCu cache only clears entries prefixed for the groupoffice instance
- Core: Create links from new items in GOUI modules was broken
- Projects2: Fixed problem where employee data could not be fetched by non-admins
- ldapauthenticator: Don't empty the contact organizations if there are none in LDAP.
- core: Don't empty contact avatarId if it's not set on it's user.
- Core: updated german thanks to Daniel from Uni Greifswald!
- Projects3: Upcoming filter included custom status
- Automation: fix foreign keys in update scripts
- projects2: sort on manager possible
- core: CTRL + A to select all in GOUI
- Core: CTRL + A in search fields sometimes selected all grid rows instead of the search text
- Serverclient: Fixed cron error
- Calendar / core: Fixed tooltips that would stick after moving an event
- Calendar: Scan email for invites, don't fail completely if one account fails
- Support / Core: stripping plain text quotes was broken
- Support: Don't import SMIME signature attachments in email messages
- Finance: Add paid stamp to template instead of css
- Bookmarks / Core: Http client only allows requests to global IP ranges
- Email: Removed email count desktop notification

22-06-2027: 26.0.36
- Core: Fixed bug where links would not be attached if creating an item from add button using a GOUI dialog
- Email: Folders are now accessible via a direct URL
- Core: Don't notify desktop with JSON bodies
- Files: Delete by path function for CLI
- Support: tasklist grouping separated for support and tasks
- Core: Style selector in HTML field
- Tasks: when projcets3 not available, do not show project column in grid

16-06-2026: 26.0.35
- Timeregistration: Added customer to export and made columns project and customer sortable
- Notes: Display issue on small (mobile) screens

15-06-2026: 26.0.34
- Support: Set customer to null when user is deleted
- Finance: Missing quotes in content-disposition filename
- Core: fixed problem where you could access controllers of not installed modules
- Files: Attempt to restore old version if moving new file somehow fails
- Timeregistration3: Added customer column to list view

08-06-2026: 26.0.33
- Core: Prefix SSE state counter key with database name so it won't conflict with multiple instances
- Kanban: Collapse sidebar
- pdfeditor: debug exceptions
- reminders: Sanitize reminder input (Fixes XSS security issue)
- onlyoffice: better error message when URL provided by ONlyOffice is different from configured
- reminders: Sanitise reminder input (Fixes XSS security issue)
- Tasks: prevent exception when sorting by project without projects3 being installed
- Comments: flickerless reload
- core: html editor clears style attributes with clear formatting too
- Kanban: smoother rendering and fixed reordering bug when dropping underneath the other cards
- Tasks / Support: make priority sortable (cherry-pick from 6.8)


01-06-2026: 26.0.32
- Core: SSE uses apcu if available to check changes every second. When an entity is modified a state counter is 
  incremented and SSE will check if the change was relevant for the user via DB queries. This keeps to amount of DB 
  queries for SSE to a minimum.
- Core: Fixed summing up values in each loops. Also strip newline straight after template tag
- Calendar: Event category selection dropdown will show category colors.
- Core: OTP field will show numeric keyboard on mobile and filter pasted codes
- Calendar: fixed incorrectly showing the event is in the past when inviting others.
- Core: disable output_buffering for SSE
- Core: Put export operations in a background process and notify when ready
- Projects3: Added expense tracking and invoicing
- Core: Optimized export performance for large datasets
- Support: Fixed sorting on customer
- Core: Title texts can be copy pasted
- Notes / Calendar / Projects3: Collapsible sidebar
- Core: In comments sort mention results and show directly when @ is pressed. Escape cancels popup properly now.
- Finance: Put invoice attachments before template attachments
- Core: Fixed AutocompleteField not submitting when emptied because it didn't know it was modified if empty.
- Files: hide portlets if user has no main access
- Tasks / Support: hide delete button if permissions do not allow deleting a task
- Tasks / Support: hide tasklist context menu items if user has no manage task lists permissions
- Core: Fixed slow browser problem when making a lot of changes to the User (by selecting task lists or note books etc.)
- Support / Supportclient: Focus editor when opening ticket

21-05-2026: 26.0.31
- Support: Migrate categories to support module
- Multi instance: Remove studio package creation as php fpm on debian is hardened and doesn't allow file creation in /usr/share/groupoffice
- Notes: Fixed overflow problem
- Core: Combobox list in GOUI misaligned
- Core: Fixed header bug in range download causing Firefox not to play mp4 files
- Core: Fixed template bug. html entity decode before evaluating expressions
- Core: Fixed template parser failing to handle some if statements with variables containing math operators
- Projects3: Added project templates
- Filesearch: Fixed results not being filtered when using it as an admin

18-05-2026: 26.0.30
- Calendar: show icons in monthview
- Calendar: removed extra hr in event context menu
- Calendar: Event locations in overview are clickable "Web" links when they start with http(s)
- Alerts: recurring events would show with the start date of the series instead of the occurrence
- Core: Upload error fix in detail panels
- Calendar: Fixed typeerror when importing ICS. Issue #1509
- Calendar: Fixed jumping calendar list when working with a read only calendar
- Comments: set comment editor in modal mode to prevent focus stealing
- Supportclient: wider default width for center panel
- Tasks: paginate tasklist grid at 50
- Billing: Fixed some orders changing to status paid when migrating
- Finance: Add shipping and invoice address to detail panel

13-05-2026: 26.0.29
- Supportclient: Fix toggle west panel 
- Supportclient: set minimum permission level for support lists to custom 'Submit as customer' level
- Supportclient: fix display of grouping in support lists
- Files: fix DE translations, clean duplicate keys, make sure that some strings are actually being translated"

12-05-2026: 26.0.28
- Core: TemplateExpressionEvaluator in wrong place leading to incorrect PDF templates
- Finance: fixed bug in status history logging

11-05-2026: 26.0.27
- Calendar: Added location in calendar views. (if any)
- Support: Separate Support categories from task categories
- Contracts: Enable exports for all users
- Notes / Projects3: Add button UI consitency with other modules
- Email: Delete attachments when there's only one
- ldapauthenticator: Handle DbException in syncing LDAP users and groups
- Core: Let GC delete ACL for folders
- Tasks: Don't set syncToDevice when subscribing to task lists
- Support: Wrong label for created by resulting in duplicate Customer column
- Support: An extra day was added to modified at
- Core: Fixed race condition in combobox loading a related entity record
- Finance: Migrate billing module statuses and status history
- Finance: New status history feature
- Savemailas: Disable saving file into Group-Office if no main access to files module 
- Finance: Added 'credit note' book type
- Email: Fixed bug where adding an email to an existing contact didn't save
- Comments: fix error in comments list with edited comments
- Core: Replace eval() with an expression evalutator. Fixes RCE - GroupOffice TemplateParser eval() via Contact/labels

04-05-2026: 26.0.26
- Maildomains: fix display of active column in aliases table #1482
- Core: increase max size of email template subject field
- Leavedays: Holidays type error fixed
- Fixed: ACL bug related to 4519e840 #1485
- Savemailas: Fixed eventwindow not opening a second time from save mail as
- Finance: Fixed drag and drop in finance between groups
- Core: Fixed infinite loop when installation is disabled
- Supportclient: Show ticket ID in title in east panel
- Support: When migrating find principal by email if not a user
- Core: SQL build error when using bind parameters in nested subqueries. (Found in address book age filter)
- Core: Updated German translation and fixed some typos.
- Multi instance: Disconnect Mysql connection when checking if instance is installed.
- Core: Fixed: Installer breaks on first load: htmlentities($_POST['username']) not null-safe in install/install.php #1496
- Core: Hints for colors in System Settings -> Appearance
- Bookmarks: Permissions were not implemented correctly. Fixes Unhandled Promise Rejection: [object Object] when trying to delete a bookmark. #1498
- Forms: export sets labels as column names for fields

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
