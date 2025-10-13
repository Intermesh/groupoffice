- Address book: Add zipcode and street in grid
- Calendar: Enable syncToDevice by default only for calendars you own / create.
- Email: Save messages to computer in this format: YYYY-MM-DD_HHMM_MAILADDRESS_SUBJECT.eml so you can sort them easily
- Support: grid column name title => subject and created by => customer
- Calendar: Fix new event from item dialog not loading editable dialog
- Calendar: Add info option to context menu to show read only view with links and comments. It's also printable.
- Calendar: Show counter on link button in edit dialog so it's visible that there are links
- Calendar: Cancellation of occurrences in a recurring series showed as strike-through instead of removed.
- Files: direct route to folder will open actual folder instead of parent

06-10-2025: 25.0.57
- Calendartimetracking: set required modules
- Core: More PL translations and corrections
- Finance/mollie: Setup SEPA mandates manually
- Calendar: Improve meeting selection clarity by changing button text and hide body buttons inside group-office 
  (except for resources).
- Files: heic support in image viewer

29-09-2025: 25.0.56
- Calendar: Find participant by e-mail so that scheduling works better when you invite a contact that's also a user
- E-mail: Missing "None" label for in email composer templates menu. Related to issue #1343
- Calendar: Reply sometimes wasn't processed because of a case sensitive match
- Calendar: Add sender as participant when saving email as appointment 
- Project3: Save email as project fixed
- Core: Fix render bug in Ext multiselect fields #1345
- Calendar: Hide cancelled events
- Core: PL translations. Again: many thanks Krzysztof!

22-09-2025: 25.0.55
- Calendar: Added Views (selection of calendars in a choisen period with 1-click)
- Calendar: If the same event occurs in multiple selected calendars the week view will show them stacked.
- wopi: allow co-editing when file is locked
- onlyoffice: allow co-editing when file is locked
- Address book: replace deprecated maps: uri with geo: uri
- Calendar: Add Polish translations #1343 - Thanks Krzysztof!

15-09-2025: 25.0.54
- Calendar: cron job could add duplicate event because it didn't find the event already created by other means like 
  ActiveSync or the e-mail module.
- Calendar: Handle missing sequence
- Core: Fixed duplicate user groups issue
- Core: fix deprecated error in ActiveRecord Excel Import/Export (#1336)
- Support: (cherry pick from 6.8) more helpful email message upon failed IMAP import
- OnlyOffice: fix JWT error message
- OnlyOffice: fix stylesheet

09-09-2025: 25.0.53
- Core: Don't log cannotcalulatechanges exception as it's not a problem
- Calendar: Fix jistimeet JWT Authentication
- Core: Fixed handling of outlook invites in z-push / ActiveSync
- Email: Fixed issue: Sieve working with Docker-Mailserver? #1338
- ActiveSync: Send message MIME without processing if smime signed. Fixes: Users cannot maintain S/mime certificates / 
  Sending Smime from IOS broken #1337
- ActiveSync: Fixed Duplicate Entries via ZPush #1333


09-09-2025: 25.0.52
- Calendar: Fix Typed property go\\modules\\community\\calendar\\model\\Holiday::$region must not be accessed before
  initialization
- Calendar: Fix reply not being processed by gmail because prodid must be set to Group-Office's.
- Calendar: Make sure calendar store is loaded when handling invitations. Otherwise the status buttons 
  didn't appear or you could change status for the wrong participant
- Calendar: Favor the personal item over the shared ones when only displaying meetings merged
- Calendar: Respect freebusy permissions

08-09-2025: 25.0.51
- Calendar: Added fallback to user timezone if the timezone can't be determined
- Caldav: Sort personal calendar on top as Sabredav picks the first in the list for invites.
- Projects: Fixed issue where files and comments wouldn't display on project
- Calendar: Merge multiple regional holidays into one
- Newsletters: fix error in smtp workflow
- Files: make trash sortable by name, path, deletion date, deletedBy user
- Calendar: Background of unaccepted events not white but with opacity
- Calendar: Only show one calendar item of a meeting to avoid a very crowded view
- Time registration: Fix task list refresh when switching projects
- Caldav: When scheduling via caldav the event could end up in the wrong calendar if the invited
  e-mail matched another e-mail address using wild cards. eg. "bar@foo.com" could find
  "foobar@foo.com" because it searched using %bar@foo.com%.
- Calendar: Fixed: go\modules\community\calendar\model\Scheduler::handle(): Argument #1 ($event) must be of type 
  go\modules\community\calendar\model\CalendarEvent, go\modules\community\calendar\model\RecurrenceOverride given, 
  called in /usr/local/share/src/www/go/modules/community/calendar/model/Scheduler.php on line 46
- Calendar: reuse mailer for invites and allow 180s timeout. Improve error message ion screen if timeout occurs


02-09-2025: 25.0.50
- ActiveSync: Fixed Error in modules/z-push/backend/go/TaskStore.php at line 237: Typed property 
  go\modules\community\tasks\model\Task::$start must not be accessed before initialization
- Tasks: Added "Sync to device" option for ActiveSync and CalDAV sync

01-09-2025: 25.0.49
- ActiveSync: Fix error in scheduling request via mail and z-push. Fixes z-push sync email iOS more than one month
  #1324
- CalDAV: Optimized calendar query performance

01-09-2025: 25.0.48
- Calendar: Map cutype room to location. Fixes database error when cutype was 'room' in an invitation.
- Calendar: Sometimes events lost duration and suddenly spanned 2 days in the view.
- Caldav: Fixed sync issue with participants without name in recurring events
- Caldav: Be more forgiving with broken events. Omit broken event instead of complete failure. 
- Calendar: Required ActiveSync database columns are not created, no sync possible - after 25.0.41 #1327
- Calendar: Fixed copy & paste on the same day

28-08-2025 25.0.47
- Core: App::USER_MAILER event so calendar can send scheduling mails with user from address
- Core: Contacts showed in group member grid
- Core: Hide disabled users from Principal queries by default
- Core: Jumping scroll position on scroll load in autocomplete fields
- Core: Safer way to execute custom field functions

25-08-2025: 25.0.46
- Core: Cache busting for GOUI modules
- Calendar: Fixed holidays appeared every year
- Calendar: New users gets personal calendar if he has permissions for the calendar module
- Projects2: Custom column is sortable
- Projects2: Include sub projects in project report
- Calendar: Fix incorrect participants patch after migration
- Email: Don't match <link@domain.com> as xss threat
- Core/Support: Fixed incremental HTML encoding issue
- Serverclient: Fixed creating mailbox if first attempt of creating user failed with validation error
- DAV: Don't list all principals for privacy reasons
- Leavedays: fix manager check in user panel
- Core: Fixed duplicate request bugs on initital load of UI

19-08-2025: 25.0.45
- Calendar: Visible category filter depend on visible calendars instead of subscribed calendar.

19-08-2025: 25.0.44
- Core: Updated German translations. Danke Christopher K.
- Newsletters: prevent unhelpful Javascript error when a newsletter neither has a smtp or email account ID
- Calendar: fixed showing edit dialog when the user has writeall permission to the calendar but the calendar owner / current user is not the organizer.
- Calendar: Show regional holidays
- Calendar: New checkbox "Sync to device" on calendar edit dialog. To prevent syncing all subscribed calendars.
- Calendar: Affecting availability is now a checkbox. Events in shared calendars will only affect availability if the user is a participant. 

17-08-2025: 25.0.43
- Calendar: Fixed notifications that come from the cronjob and fixed that the cronjob and mail reading reprocesses invites every time.
- Calendar: Update time in now indicator
- Newsletters / Core: Speed up newsletter sending and saving of models with large "map" properties
- Core: Show unavailable modules so they can be removed
- Core: Convert mail addresses too in html editor
- Calendar: Added "Home" button
- Core: Use TEXT values for custom fields with lengths greater than 255 characters

12-08-2025: 25.0.42
- Calendar: bug with 12pm times becoming 12 hr later
- Calendar: Fixed some problems when GO timezone didn't match system timezone
- Calendar: 0pm -> 12pm
- Calendar: Some hyperlinks could be malformed
- Caldav: Fixed scheduling issue with recurring events
- Calendar: Private event shows as private in z-push 
- ActiveSync: avoid generating an organizer when there are no attendees

12-08-2025: 25.0.41
- Core: restored submodules groupoffice-core and z-push

11-08-2025: 25.0.40
- Calendar: Only subscribe personal calendars in new calendar migration to avoid an excessive list of calendars
- Calendar: Sanitize invalid URI's
- Newsletters: Fixed Error in /usr/local/share/src/www/go/core/acl/model/AclOwnerEntity.php at line 218: Typed property go\core\acl\model\AclOwnerEntity::$aclId must not be accessed before initialization
  when sending with an account from the e-mail module
- CalDAV: Sanitize event URI's in database.
- Calendar: Use correct date format in links
- Calendar: Print shows wrong week when week starts on sunday
- Calendar: Load correct calendars for user in system settings
- Calendar: 12am -> 12pm
- Calendar: updated time indicator every minute


08-08-2025: 25.0.39
- Addressbook / Carddav: Fixed: CardDAV / vcard: Improve compatibility by changing type=mobile to type=cell #1283
- Caldav: Fixed caldav not found error

07-08-2025: 25.0.38
- Maildomains: Fixed domains with no mailboxes not showing
- Calendar: Added confirm dialog to delete event
- Core: Fixed external function handlers like mailto: links
- Tasks: expand description, date and alert panels
- Files: Raise max filename size to 260 characters
- Calendar: Allow same date with full day
- ActiveSync: Fixed empty reminder issue
- ActiveSync: Sends scheduling messages now
- ActiveSync: Improved exception handling
- Timeregistration2: Fixed timer button

04-08-2025: 25.0.37
- Calendar: added customfields for calendar entity.
- GOUI: when SelectField was set to null the option item was not selected.
- GOUI: DateTime object will respect the users language and first day of the week.
- Calendar: ics export filename will be calendar+date+title instead of uid
- Finance: Payment should be verified / checked by default
- Core: Improved setting link on image in htmleditor
- ldapauthenticator: Fixed ErrorException in /usr/share/groupoffice/go/modules/community/ldapauthenticator/cli/controller/Sync.php at line 443: Trying to access array offset on value of type null
- caldav: Fixed: ErrorException in /usr/share/groupoffice/go/modules/community/calendar/model/CalDAVBackend.php at line 131: Undefined array key "color"
- caldav: Fixed: InvalidArgumentException in /usr/share/groupoffice/vendor/sabre/dav/lib/CalDAV/CalendarObject.php at line 58: The objectData argument must contain an 'uri' property
- maildomains: Fixed install error on mysql Database exception: SQLSTATE[42000]: Syntax error or access violation: 1101 BLOB, TEXT, GEOMETRY or JSON column 'publicKey' can't have a default value

14-07-2025: 25.0.36
- OTP: dialog would not popup when OTP setup is required.
- OTP: Code would not verify during setup of a new OTP token.
- Calendar: Event dialog will only make writeable calendars selectable.
- Calendar: The create first calendar dialog will popup if there are no writable calendars.
- Calendar: mayChangeCalendar is named "Create/Delete calendars", edit is allowed based on permissions. 
    A user with owner permission must also have create/delete permission on the module to delete a calendar
- Calendar: Fixed error when caldav server had acknowledged alerts
- Calendar: Fixed wrong day headers when your timezone is west from UTC
- GOUI: Implemented custom time picker to support 12 hour time format
- Tasks: Fixed are you sure close message on save
- Files: Fixed invalid deletion of file acl's in garbage collection
- Core: Fixed bug in garbage collection stopping on api keys
- Projects3: Fixed migration of billed status from projects2
- Projects3: Reset registrations billed state when invoice is removed
- Timeregistration2: fix timer button

08-07-2025: 25.0.35
- Core: Small fonts issue
- Core: 32 bit support, (Issue quota / postfixverwaltung #1272)

07-07-2025: 25.0.34
- Calendar: fixed 12-hour time format
- Calendar: fixed patchThisAndFuture trying to set modifier
- Core: fix generic spreadsheet import
- Newsletters: fix import new contacts

30-06-2025: 25.0.33
- Files: fix external link to folder in folder panel
- Support: new icon for link to prevent confusion with old tickets module
- Core / email: Improved converting url to anchors so text cursor won't jump to last line
- Address book: Contact color back in grid
- Calendar: Added copy/cut/paste to right-click menu of events
- Calendar: Delete full day event from weekview with Delete key works
- Calendar: Drag and drop full day event in weekview
- Calendar: Make calendar visible when new event is created
- Calendar: Fix weekview horizontal lines aligned for Safari.
- Calendar: When invites are inserted the calendar of the email account owner will be used.
- Calendar: If user has no default calendar selecter the invite will be safed in the first owned calendar
- Calendar: When updating invites all the calendars owned by the user will be searched for the event.
- Calendar: Added location to html of invite emails and fixed translation.

23-06-2025: 25.0.32
- LDAPServer: Fix primary key error

20-06-2025: 25.0.31
- Projects3: Improved finance integration
- Core: Upgraded SourceGuardian encoder to fully support PHP 8.4
- CalDAV: Fixed sync problem in tasks and calendar
- CalDAV: Added collections to sync a single calendar.
- CalDAV: Show sync error in the account dialog per account or per calendar.
- Core: Fixed cursor jump in Firefox
- Calendar: Fixed Windows scaling issues in weekview.

16-06-2025: 25.0.30
- Calendar: Fixed display issue in e-mail invite
- Core: Auto dismiss alerts for entities no longer known
- Timeregistration2: Tasks only querying in subscribed lists
- Core: PHP 8.4 compatibility
- Studio: patch generated models with type hinted attributes
- Studio: generate new models with type hinted attributes
- Files: Use load() instead of reload() otherwise it might retrash the files as it will send the last load param
- Core: All model properties have types so keys will always be a string according to JMAP spec
- Tasks/Core: Fixed invalid change event in tasklist combo where the tasklist name would be the value in the change event
- Tasks: percent complete render error when hidden
- Tasks/Support: Fixed comment load on new ticket / task
- Addressbook: City, state and country in grid
- Files: only display 'Share' button if any menu items available
- Core: Clearer label for 'allowRegistration' system setting
- Calendar: Added include in availability option for calendars with owner based default.
- Calendar: Added popup windows with event details when hovering over the event and a setting to turn them off.
- Calendar: Fixed render glitch when added new event in weekview before existing events.
- Calendar: Participant field description is now "Add participant or resource"
- Calendar: Added color column for calendar resources. remove add approve until it is supported.
- Calendar: Resource group owner will have manage permission to the resources in the group by default.

05-06-2025: 25.0.29
- Tasks: Sync subscribed tasklists and remove sync settings
- Comments: show principal name
- Leavedays: refactor notification code
- Core: module panels not visible to admins in user settings
- Core: Fixed custom fields loading problem in system settings
- Savemailas: Save link description when creating links
- Maildomains: Return sums as int to hopefully fix issue quota / postfixverwaltung #1272
- ActiveSync: Fix for all day events spanning an extra day

02-06-2025: 25.0.28
- Calendar: Custom fields not saved
- Core: TreeSelect was broken in GOUI

02-06-2025: 25.0.27
- Leavedays: fix status loop when disapproving leave day requests
- Leavedays: show number of open requests
- Freebusy: Fix non editable free busy permissions after user creation
- Supportclient: Fixed grouping in support lists
- Core: XSS error fixed in my account -> sync
- Core: Reflected XSS in Look and feel section of the application

30-05-2025: 25.0.26
- Core: Fixed store load error when using custom filters
- Address book: Removed duplicate action date in detail
- Calendar: removed displayfield() for description as it had some unexpected results when the description was overridden for an occurrence
- Calendar: New event without any changes wouldn't save anymore
- Calendar: all events disappeared in week view when deleting an event that has participants or is recurring
- Core: Stretch custom date fields so larger labels don't run out of the field
- Core: Fixed creating double user groups and duplicates will be removed.
- Addressbook: filter hasemailaddresses and hasphonenumbers work with false too
- Finance: Added "nextContractDate" to finance document model
- Core: Added dateAdd() and subtr() function to template parser

27-05-2025: 25.0.25
- Calendar: Show import button if calendar event is not a valid scheduling object
- Calendar: Show open calendar button on all invites
- Projects2: Find project tasks in time registration window

26-05-2025: 25.0.24
- Files: fix permission error when restoring file as end user
- ActiveSync: Z-Push problem fixed: z-push sync for notes and tasks #1289
- Newsletters: When setting max messages per minute to 0 it will send as fast as possible and doesn't default to 120 messages per minute
- Newsletters: When a sending limit is applied the sending time is now taken in to account too.

20-05-2025: 25.0.23
- Projects: Problem with loading project panel
- Z-Push: Created new Z-Push repository

19-05-2025: 25.0.22
- Core: Z-Push upgraded to 2.7.5 (With some patches from us).
- Files: Fixed opening file from link or search result

12-05-2025: 25.0.21
- Core: fix database error in disk space calculation cronjob
- ActiveSync: Fixed error in calendar sync because ownerId was changed to getOwnerId()
- CalDAV: Fixed error in calendar sync because ownerId was changed to getOwnerId()

08-05-2025: 25.0.20
- Core: Fixed: Blind Stored XSS in Phone Number Field Enables Forced Redirect and Unauthorized Actions
- Core: Fixed:  DOM-Based XSS in all Date Input Fields Allow Arbitrary JavaScript Execution
- Core: fixed Stored XSS in Tasks Comment Section
- Files: Fixed: Group-Office vulnerable to path traversal Vulnerability ID: JVN#23673287
- History: Fixed cross site scripting vulnerability JVN#30520482, JVN#87138325 and JVN#72111431
- Email: fix deprecation error
- ActiveSync: Fix broken utf-8 when recreating MIME for Z-push
- Comments: use translations for tasks feedback
- Core: Reset create link button on close so it won't fail when dialogs perform an async task before opening. This happened with documents in finance.
- Finance: Add payment due date to Zugferd invoice
- Addressbook: Filter ICD combo by selected country from address


25-04-2025: 25.0.19
- Maildomains: Wrong quota display and cleaned up code

15-04-2024: 25.0.18
- Files: Disable new webdav locking by default to test it more. can be enabled with $config['webdavEnableLocks'] = true;

14-04-2024: 25.0.17
- Files: If files on disk match the entity path eg. addressbook/Public/contacts/A/Albert Foo/ then it will be connected by the database check.
- Address book: Option to hide index character and icon to show more contacts on screen
- Address book: update action date form detail view
- Core: Use geo: https://en.wikipedia.org/wiki/Geo_URI_scheme for address links so user can choose the maps app
- sync: workaround for Z-push error SyncObject->Check(): object from type SyncMail: parameter 'to' contains an invalid email address 'undisclosed-recipients:'. Address is removed.
- tasks: correct invalid state where due is before start date
- Core: Updated German language thanks to the University of Greifswald

08-04-2025: 25.0.16
- Demo data: Longer timeout so it won't time out after 30s.
- Tasks: Default color for task lists
- Files: fs_trash folder was missing for some users

08-04-2025: 25.0.15
- Calendar: Upgrade errors if files not installed part 2

08-04-2025: 25.0.14
- Maildomains: Cannot create Alias #1255
- Calendar: Upgrade errors if files not installed
- ActiveSync: Was still not fixed.

08-04-2025: 25.0.13
- Files: If you had files module installed but disabled the upgrade failed
- ActiveSync: Wrong lib included which made sync fail

07-04-2025: 25.0.12
- Maildomains: Fixed bug in dialogs not splitting user and domain
- Maildomains: Fixed bug in checking max aliases and max mailboxes
- Webdav: Creating new files was broken
- Studio: fix creator and modifier names in grid
- Core: refactor old calendar event models out of system tools

01-04-2025: 25.0.11
- Core: fix order of database migrations
- Studio: automatically patch modules to 25.0 code base
- Core: fix multiple upgrade errors
- Core: fix theme colors

25-03-2025: 25.0.10
- Calendar: Fixed install on MySQL 8
- Calendar: Auto install on fresh install
- Core: Catch database exception in legacy session handler to catch the case where a session exists but the
  database is not installed yet. Reported in issue #1242.
- Fixed exception in database check. Reported in issue #1242.
- Supportclient: add 'select all lists' checkbox to sidebar
- Finance: When duplicating quotes or invoices etc. default to the same book
- Finance: Optimized database index
- Core: Optimized database indexes for core_change, search.
- ActiveSync: Optimized notification queries
- Core: Updated German translation thanks to University of Greifswald

18-03-2025: 25.0.9
- Core: Build error
- Core: maximum call stack exceeded error

18-03-2025: 25.0.8
- E_mail: Save mail as project
- E-mail: Save mail as appointment
- Calendar: Create and browse links from event dialog
- Calendar: Fixed unsubscribe from calendar
- Core: Color picker render issue

10-03-2025: 25.0.7
- Calendar: fix cancellation event
- Time zone fix
- Phpstan
- Create link button
- several updated dependencies

06-03-2025: 25.0.6
- Calendar time tracking build problem fixed

03-03-2025: 25.0.5
- Calendar time tracking module
- Tasks / Support: Easier task dialog

18-02-2025: 25.0.4
- Many bug fixes and tweaks
 
27-01-2025: 25.0.3
- Calendar: CalDAV Client module added so you can add external caldav calendars
- Project3: Module was missing

10-01-2025: 25.0.2
- Calendar: Calendar didn't load in Docker version
