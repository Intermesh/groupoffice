- Email: find correct translation string for 'Advanced'
- Core: Added extra check for post_max_size php.ini setting to GO test script
- Core: Better rendering of accented capital letters in form fields
- Core: Fixed regression in permissions tab in module management
- Core: Ignore missing foreign keys when deleting custom fields
- Multi instance: Brought back allowed modules tab for instances. Thanks to Pieter van de Ven.
- Core: Order global search results by id descending to speed up search
- Core: Logo could be cleaned up by garbage collection
- Calendar: zooming in could cause events to move to a day ahead
- Files: Add index on expiry time to speed up portlet
- ActiveSync: Fixed z-push-admin.php and z-push-top.php CLI commands
06-11-2020 6.4.189
- Core: upgrade output is logged
- Core: When installing module first check if it's not already installed to prevent data loss.
- Core: invalid allowed_modules string could show incorrect installed/enabled status in the modules section
- Core: Ignore error when creating a link that already exists
- Core: demodata error when clicking 'no'
- Core: welcome message is displayed
- Core: Custom field type Encrypted text showed hash value

03-11-2020 6.4.188
- Core: Refactored custom fields to handle functions inside other functions and detect infinite loops for template fields
  and function fields.
- Core: Stop CRON execution when upgrade is needed
- Core: Run GarbageCollection once per day at midnight instead of every hour
- Core: added more Romanian translations thanks to Safety Broker de Asigurare SRL
- Projects: Removed projects v1 to v2 upgrade. UPgrade must be done in v6.2.
- Serverclient: Works when using e-mail as username
- Core: remove double key and clean up core_customfields_select_option before adding foreign key in upgrade
- Core: Fixed internationalization of search keywords
- Custom fields: Only 1 column on mobiles]
- Projects: Link was not established when creating from other items
- Billing: Create link to quote and contacts when automatically creating task
- Projects: Status filter applied to search when not needed
- Core: Search splits words from text area fields only and not from small fields. So initials in a contact remain intact for example

27-10-2020 6.4.187
- Core: Set core_acl.ownedBy to 1 when user has been removed.  
- Core: Cleanup address books and note books on user delete

27-10-2020 6.4.186
- Core: error on modifiedAt in old framework when saving custom fields
- Core: Fixed [6.4.185] Error: PDOException with update #623

27-10-2020 6.4.185
- Calendar: Error when adding event while in read only calendar
- Custom fields: Could be returned as text
- Core: optimized search keywords
- Address book: include notes in search keywords
- Core: Upgrade form 6.3 was broken due to toggleGarbageCollection()
- Core: New configuration option to logout users when inactive for more than x number of seconds.
- Core: Raised size of search cache from 190 chars to 750 chars
- Core: fixed <br /> tags in error messages 
- Sieve: make sure result is defined
- OfficeOnline: Send locale string with country. en_us instead of just en.

23-10-2020 6.4.184
- Core: Template parse gave error on arrays
- Core: Chips component error on empty value in custom fields

23-10-2020 6.4.183
- Email: Fixed download inline image on linked messages
- Core: Add constraints to acl from core_search
- Filesearch: raise default index file sizze limit to 10MB
- Core: Configure secondary and accent color for Paper theme
- Core: Fixed missing GC cron job Groupoffice #620

22-10-2020 6.4.182
- Core: some modules couldn't be uninstalled from system settings
- Core: Fixed too large cc field in e-mail composer
- Core: Disabled phone number auto linking
- Email: more user friendly icon and tooltip for CC / BCC submenu
- Tickets: remove limit on writable ticket store for custom fields
- Translations: Added Bulgarian translation thanks to Nikolay Stoychev.
- Timeregistration: Activity name and code visible on ediding time registration
- Core: Template and function custom field use text values instead of id's of select fields
- Core: Turn off GarbageCollection while upgrading
- Studio: Checks for reserved PHP Keywords
- Studio: Generates commented at and has links to filters
- Core: Share custom filters with everyone by default on new installations

13-10-2020 6.4.181
- Core: In system settings, extra filter 'Disabled users', make sortable by change date
- Core: Custom field of type function could cause error "Division by zero"
- Core: Fixed safari 14.0 crash on pasting image in HTML editor fields
- Core: JMAP didn't track change if only custom fields were modified
- GOTA: Signed jar file

13-10-2020 6.4.180
- Email: Fixed scroll bars in recipient fields
- Core: Special download actions for modules were broken

11-10-2020 6.4.179
- Address book: CSV export broken
- Core: some modules couldn't be installed from system settings
- Core: fixed dismiss all button in notifications panel

10-10-2020 6.4.178
- ActiveSync: Invalid date string in message source caused sync loop

09-10-2020 6.4.177
- Core: Custom fields error in old framework

09-10-2020 6.4.176
- Address book: If module setting 'restrict to admins' is on, restrtict import/export to both admins and users with 'manage' permissions
- Address book: Added color back in to contacts
- Core: Custom fields saved as text to search cache
- Core: New template custom field: https://groupoffice.readthedocs.io/en/latest/system-settings/custom-fields.html#template-field
- Studio: Only users with "Manage" permissions on the module may edit and create.
- Core: Users have auto generated avatar with color and initials
- Email: Font colors sometimes not working
- Email: Add unknown recipients dialog didn't show up anymore

06-10-2020 6.4.175
- Core: Dark theme showed e-mail text in light font colors.
- Core: notification style improved
- Studio: Supports fixing the package name by setting $config['business'] = [ 'studio' =>  [ 'package' => 'foo']];
- Multi instance: Creates studio package folder and sets it in the instance config

05-10-2020 6.4.174
- Core: sort comments explicitly by creation date
- Core: Added Romaian holidays and translations. Thanks to safetybroker.ro 
- Projects: Added finance report with date filter showing all costs, hours, income and budgets
- Files: Fixed blob ID appearing in files after overwrite
- Core: Date columns included time stamp T00:00:00 which lead to date changing when in a negative time zone.
- Core: fixed Multi select custom field / chips component rendered small list.
- Core: prevent spell check on text fields and enable on text area's
- Core: Fixed: 6.4.70 user:department value #490 by adding it to user account dialog
- Calendar: Added missing charset and method to calendar invites
- ActiveSync: Fixed problem where some attachments didn't show on iphone/iOS

01-10-2020 6.4.173
- Core: Fixed shifting in date fields
- Core: Speed up 6.3 to 6.4 upgrade
- Core: allowed modules can work with packages now. eg. $config['allowed_modules'] = ['legacy/*', 'community/*', 'business/newsletters'];
- Core: Toggle notifications when icon is clicked
- Core: Optimized (custom field) filter loading
- Address book: New setting to restrict export to administrators
- ActiveSync: Set USE_FULLEMAIL_FOR_LOGIN back to the default value (true)
- Core: Test script checks whether modules subdirectory is writable if Professional License available
- Studio: return user friendly feedback if module directory not writable;
- Address book: Unlinking organization updates search cache
- Email: When links were removed they were no longer removed when there are no links left
- Studio: Replace permissions panel with share panel
- Studio: Unlock a studio module upon opening the wizard after confirm
- E-mail: Always process calendar invites. (not just when message is unread)

25-09-2020 6.4.172
- Timeregistration: When changing the start time, the end time will change instead of the duration
- ActiveSync: Upgraded z-push to 2.5.2 and fixed e-mail sending problem on iOS 14.0. You might need to correct the email address in your iOS account too!
- ActiveSync: Fixed no results when searching All folders. It will search inbox only in that case for performance reasons. We'll fix this later.
- Core: Restore correct height of windows when closing in collapsed state

21-09-2020 6.4.171
- Release in GitHub

21-09-2020 6.4.170
- Time registration: use start time of same weekday in previous week as start time for first entry of the day
- DAV: Fixed case sensitive login
- Email: Worked around error if status could not be fetched from IMAP
- Core: Updated PT-BR Translations thanks to Everson Guimarães!
- Core: Disable username field if using external authentication
- Core: Fixed shifting custom field date column in some timezones
- Files: Fixed upload to files module where files with identical content wouldn't upload more than once
- Core: Fixed not found error on compressing folders.
- Custom Fields: Fixed render bug in field dialog.
- Newsletters: Bugfix, make SMTP accounts sortable and scrollable in System settings;
- Newsletters: add text filter to SMTP combobbox, sort SMTP combobox items by name.

15-09-2020 6.4.169
- Core: Bugfix in language export
- Projects: Revert search for projects to old method
- Notes: Fixed e-mail -> save as note
- Core: Improved upload notifications. (Fixes safari 14 crash)
- Core: Toolbars in a side panel next to a grid crashed Safari 14.0 (100% cpu usage)

15-09-2020 6.4.168
- Core: custom fields don't return id in data
- Tickets: tickets where searchable for all users allowed to create tickets. The same thing happened to mail linked to those tickets
- Calendar: Optimized loading performance
- Custom fields: Fixed error when using encrypted text field

08-09-2020 6.4.167
- Core: if Activity Log enabled, show successful login and logout attempts
- Leavedays / calendar: Fix for holidays blocking calendar entry

07-09-2020 6.4.166
- Core: allow login if there are no restrictive rules at all for you instead of no rules at all for the whole system
- Notes: added simple CSV import and export;
- Timeregstration: set default status upon copying registration;
- ldapauth: fixed bug in server creation dialog.
- Calendar: When checking for conflicts, leave days are taken into account.
- Calendar: Month by date recurrence will turn into Month by day when re-opened.
- Timeregistration shows html tags when editing an entry with newline characters
- Customfields: Multiselect customfield had a very small list width
- Newsletter: Separated User list and Contact list in the Person select dialog for the Email composer.
- Core: Support --debug flag for cli.php
- ldapauth: group member sync for ActiveDirectory broken
- projects: fixed error when you didn't have access to the contacts linked to projects
- Core: New feature to "Archive" users.
- Projects: Sometimes PDF report didn't render if page break occurred on table header.
- CustomFields: fixed refresh bug on conditionally hidden field.
- Address book: dialog failed to open without manage permisions for tickets module (if installed)
- Calendar: Bug changing "this and future events" #202021084 (was broken in 6.4)

27-08-2020 6.4.165
- Time registration: fixed Firefox bug in Timesheet
- Email: Workaround if message has invalid From header
- Core: fixed render bug in link browser window
- Files: Added permission checks to compress functions
- Core: Upgrade from 6.3 failed if comments module was not installed.
- SMIME: fixed error in linked email with inline attachments
- Core: Prevent combo from expanding when opening dialogs

25-08-2020 6.4.164
- Email: users are able to delete their own Email templates
- Billing: Fixed the PDF template tax totals when printing costs
- Time registration: Timer button will use Notification area to save/show the timer
- Time registration: new time insert dialog. (small date field as this is already set in the new view)
- Time registration: Drag-n-Drop to move / set duration / holt Alt-n-Drag to copy / Click Add time + Hold-n-drag to set duration 
- Core: Added authorisation check to SSE
- Customfields: fix User customfield will display in the detail field when not empty
- Files: added Folder customfield to be displayable in the grid.
- Core: Fix scrollbar issues when focusing/clicking on a textarea with autogrow
- Contact: When creating a contact from unknown email address there was one email field to many
- Language: updated pt_br Thanks to @flaviozluca
- Customfields: fixed division by zero for functionfield in newer PHP versions
- Core: Fixed bug to enable deleting comment
- Core: Better link color in dark theme
- Bookmarks: fixed bug deleting bookmark categories
- Customfields: Several bugfixes import and export from projects  
- Customfields: Improved layout and define columns for showing custom fields net to eachother.
- Core: Fixed display of relation fields in legacy modules
- Core: Fixed error handling in grid delete

30-07-2020 6.4.163
- Bookmarks: fixed bug where logo didn't save
- Email: Workaround if "From" header is missing in email message
- Core: disable JMAP sync states on rebuild search cache and database check. Reset state when done.
- Address book / carddav: Database check fixes missing uri's and import will generate uri if uid is already present
- Tickets: re-enabled ticket groups

30-07-2020 6.4.162
- Studio: Was missing license definition which caused install to fail without license.

28-07-2020 6.4.161
- Core: Date range component

27-07-2020 6.4.160
- billing / core: Removed duplicate translations;
- ldapauth: check if mail and username attribute are present for sync, allow larger queries
- officeonline: Auto detect wopi client URL instead of using system settings url.
- Core: phone number autolinking only if surrounded by word boundaries.
- Filesearch: Error in files when not having permissions for filesearch module
- Files: fix for invalid ACL's in root folders causing integrity constraint errors
- Studio: New module to create your own modules!
- Core: Removed incorrect country translations for French
- Address book: Send vcard by email feature
- E-mail: Import vcard from attachment feature

14-07-2020 6.4.159
- Billing: fixed problem where items grid wouldn't load anymore
- E-mail: Drag and drop caused jump to page 1 in list
- Projects: generate proper keywords for search function;
- Documents: Implemented filters in file search module and added custom fields back into edit panel
- Custom fields: Save failed when custom fields had only a select fields.

13-07-2020 6.4.158
- Core: Updated German translation
- Files: Fixed minor bug in download function
- Core: Fixed rendering of yesNo custom fields in grid and detail panel.
- LDAP: Created multiple accounts when using email for username
- Files: Fixed folder upload problem where previous folder upload was created empty on a second upload

13-07-2020 6.4.157
- Core: Added some common file type icons
- Core: new config option $config['frameAncestors'] = 'http://examplea.com https/exampleb.com'; to allow Group-Office in 
        a frame.
- LDAP auth: Use e-mail for mail username works for SMTP too
- Core: Auto link URL's and emails in html editor and html rendering
- Email: Case insensitive file extension checking on uploading file in email composer
- Core: Restored CSP object so it can be extended by modules

07-07-2020 6.4.156
- Billing: Field 'invoice_no' is now a varchar
- Core: Fixed 500 error upon rebuilding module cache
- Core: Fixed custom fields saving new select box.
- Core: Custom fields, generate database name as per MariaDB naming conventions.
- Address book: Update search cache of employees when changing company name
- LDAP Auth: Added option to login to IMAP server with email instead of username 
- Core: Added complete rebuild search cache option.
- Core: Fixed broken link display in e-mail
- Core: Set security headers: 
    	- X-Frame-Options: SAMEORIGIN
        - Content-Security-Policy
    	- X-Content-Type-Options: nosniff
    	- Strict-Transport-Security: max-age=31536000
        - X-XSS-Protection: 1;mode=block
- Core: Use relative URL's in webclient
- Email: reload grid and keep position but don't select next mail
- Email: Contact autolinking will link to all contacts organizations too

30-06-2020 6.4.155
- Email: Problem with empty emails

30-06-2020 6.4.154
- Custom fields: Changing multi select options could destroy all values of the field
- Email: Pasted or dropped image resized to max-width: 100%
- Leavedays: Incorrect email when booking was made for another user
- Address book: Removed duplciate "Sort by" last name setting.
- Core: Updated translations 'Forgot username?' to 'Forgot login credentials?'
- Files: search results > send by email, prevent full path from showing in attachment view
- Core: Bug in manual install without Acpu

29-06-2020 6.4.153
- Core: Fixed manual install error

26-06-2020 6.4.152
- Core: Loading issue when used in subdirctory

26-06-2020 6.4.151
- Core: Collapse notification panel when clicked on mobile
- Core: Added new toolbar when user has selected multiple items
- Billing: selecting a TAX rate in the order dialog items is fixed
- Contacts: German contact salutation was incorrect
- Core: Made it possible to brand and style the install pages
- Multi instance: If hostname is does not match installation of manager it will display a not found notice.
- Email: Email download link broken in plain text email without template
- Address book: Generate missing URI and UID's
- Custom fields: Set fields hidden or shown by default in grids
- Custom filters: Add custom input fields to the navigation area.
- Custom fields: Rename select field database name gave constraint error.
- Libreoffice online: Fixed printing and download as PDF.
- Email: Save calendar invite where you are not a participant

15-06-2020 6.4.150
- Tickets: Search unseen only and fix missing domain in email
- Email: Error on some ICS attachments or calendar invites
  			
11-06-2020 6.4.149
- Calendar: missing go-hidden class showed calendar accept links not intended for GO
- Calendar: Use email account for sending invites
- Calendar: Fix for "No participant found for this event"

11-06-2020 6.4.148
- Email: Suggested contact link of mails in "Sent" folder will look for "to" address
- Core: File upload was broken when Group-Office was not running in the root of the domain.

09-06-2020 6.4.147
- Core: Typo in setIsCondfidential leading to oauth problems

09-06-2020 6.4.146
- Email: select next message when dragging message to other folder
- Core: Oauth failure in generating private key
- Core: Database check fixes file acl problems

08-06-2020 6.4.145
- Core: Support for OpenID / OAuth 2.0 so we can integrate with Rocket.Chat
- Address book: Added zipcode to contact search
- Core: Use host header to determine API endpoints so you can have multiple hosts to connect to GO
- Core: Upload errors where hidden in collapsed panels
- Core: CTRL + F7 debug was broken

25-05-2020 6.4.144
- Address book: Added birthday column
- Core: Render issue with hidden HTML custom field
- Newsletters: Sometimes errors were reported with an incorrect email address
- Email: Aliases didn't show without manage permissions
- Address book / core: Database check fixes mapping of files folders in address book
- Address book: Upgrade from 6.3 to 6.4 could cause lost mapping of files folder. 

25-05-2020 6.4.143
- Core: error when loading more items in link browser fixed
- Address book: Added organization city and organization country to contact filters
- Address book: Improved simple text search by using global search cache
- Calendar: add 'send email' dialog to context menu actions on appointments 
- Address book: Address books searching and sorting in the combo when editing contacts
- Core: filters can have sub groups making complex filters possibl. For example where conditionA and not conditionB 
- Core: Disable cron jobs failing due to uninstalled modules

20-05-2020 6.4.142
- Core: Updated Polish and Croatian translation
- Core: 6.4.141 Quota cronjob not displayed correct #580
- Tasks: left panel is resizable
- Core: fixed disapearing notifications
- Address book: Export -> Labels didn't download
- Address book: contacts filter also showed organizations
- Projects: Fixed project example file and automatically find's contact and customer from address book

15-05-2020 6.4.141
- E-mail: Attachments from and to items filters out entities that doesn't support that
- Projects: fixed status, type and template filter to accept the name as text
- Email: Link mail when using e-mail files
- Projects: fixed failing report when user was deleted
- Projects: Fixed resource not showing in edit dialog

13-05-2020 6.4.140
- Address book: Fixed merge of files
- Core: Database check could set wrong owner to ACL's
- Custom fields: Required condition matches multiple words
- Files: fixed 6.4.137 Error Uncaught TypeError: fb.sendOverwrite is not a function #576

12-05-2020 6.4.139
- Core: Add to all and reset buttons didn't change all existing permissions
- Core: Custom fields of type date and date time didn't print on invoices
- Core: Short date in list preference also applies to links in detail views.
- Core: Added tooltip to dates so they show the full date and time.
- Core: Updated French translation
- Core: Link browser in menu as button
- Core: RequiredCondition in custom fields didn't behave well with hiding fields

08-05-2020 6.4.138
- Core: updated German translation
- Core: Suppress store load error when computer went to sleep
- reminders: add function was broken in the latest release
- Calendar: showed 12h format with 24h format in settings
- Billing: View message of status change
- Files: folder upload with drag and drop

01-05-2020 6.4.137
- Calendar: Fix for 75th anniversary early may bank holiday
- Files: Restored sync file system tool in System settings -> tools
- Calendar: Import all day events in user time zone always
- Core: Users with manage permission couldn't mange permissions
- Custom fields: Adding of type Notes was broken
- Projects: Extended automatic name template with {contact} and {customer}
- Custom fields: Fixed problem with conditionally hidden and required at the same time
- E-mail: Put e-mail from template next to other e-mail options
- E-mail: fixed mailbox root not working
- Address book: Contact custom field supports address book selection. (The CustomField Contact type does not use the addressBookId filter #548)
- E-mail: Fxied Imap - folders with [ ] are badly parsed #561
- Calendar: Fixed Integrity constraint violation if calendar color changed to auto #575
- postfixadmin: Fixed Postfix Maildir Folder is Hardcoded #547 - Added $config['vmail_path'] = '/path/to/vmail/';
- postfixadmin: Email Usage Not Showing Correctly #546
- Tasks: Made category combo searchable. Fixes #506
- Calendar: Fixed Setting reminder to no sets it to zero minutes #456
- Core: Updated French translation

28-04-2020 6.4.136
- Newsletters: Dragging attachment to composer was broken and moved template management to main screen
- Core: Fixed Maximum callstack error when start module was set to non-existing module
- Email: fixed template selection dialog that shows when there are more than 10 templates
- Core: Correct Function type field in custom fields. Rename Function to FunctionField

22-04-2020 6.4.135
- Core: Custom fields marked as required are only required if they are visible
- Log: too long descriptions could lead to error

21-04-2020 6.4.134
- Core: Improved notifications
- Core: Handle unlimited upload limit
- Core: Some PHP 7.4 issues
- Core: Removed notification flyouts
- Address book: On 6.3 upgrade move orphans to an address book called __ORPHANED__ so the upgrade can continue
- time tracking: set start of day to end of last time entry of that day
- Billing: grouping on status gave error on reload
- Calendar: All day event black text color in dark theme
- Core: Suppress column eval() errors

17-04-2020 6.4.133
- Address book: Added 'Department' field.
- Core: installer broke on mysqlnd check
- Core: PHP 7.4 issues. (Pro not ready because Ioncube for 7.4 is still in beta)

16-04-2020 6.4.132
- Core: Improved file upload error handling
- Assistant: files opening twice

15-04-2020 6.4.131
- Email: open attached file fixed for new uploader
- Email/Tickets: Fixed ticket and e-mail counters. Issue: some issues on 6.4.130 #574
- Tickets: Changing company in ticket didn't change company name
- Tickets: Implemented show ticket function for external links
- Core: Check for mysqlnd driver on upgrade and install
- Core: Files not uploadable on older iOS fix by removing accept="*/*" (maybe)
- Newsletters: Added "creator" column.

10-04-2020 6.4.130
- Billing: attachment could not be found
- Core: Connection error when dowloading file

10-04-2020 6.4.129
- Core: New upload function with drag and drop support in the file browser and detail view.
- Core: New notification slide panel
- Core: Links can have descriptions again. Entities can be found on link descriptions.
- Notes: New module to encrypt notes
- Email: Delete folder directly when folders can't be moved into trash
- Email: Empty folder will also remove subfolders.
- Address book: Sort on firstname too when sorting on last name
- Address book: Added first name and last name columns
- Custom fields: Export user, group and contact custom fields to text
- Billing: Check if telesales and fieldsales agent's still exists
- CardDAV, CalDAV: Allow longer DAV uri's changed from 190 to 512 chars
- Assistant: Message if not installed can be dismissed.
- Tickets: fixed TypeError: undefined is not an object (evaluating 'this.disableTemplateCategoriesPanel.setModel')
- Tickets: Invoicing failed on companies without country set.Set book earlier so it can be used for invoice country.
- Notes: Added image insert menu button
- Comments: Added image insert menu button
- Custom fields: Type User, Group and Contact didn't show in legacy modules
- Billing: update customer_name when contact changes too
- Email: Fixed problem for some IMAP server where an attachment was downloaded with zero bytes
- Core: Fixed error field style in Dark mode theme
- Core: Implemented Dark theme html editor style
- Core: Clear default calendar and task list in settings works
- Core: Fixed connection error dialogs that could occur when downloading a file
- Address book: Fixed error after creating new contact
- Projects: Use {customer:* and {contact:* template tags on template projects, tasks and jobs. See https://groupoffice.readthedocs.io/en/latest/using/projects.html#jobs
- E-mail: Drag files into editor to attach
- E-mail: Fixed problem where tree wouldn't load if one of the mailboxes failed to open.

17-03-2020 6.4.128
- Billing: fixed error in opening invoice created by a deleted user.
- E-mail: fixed error when loading message while it was deleted.
- Core: missing use statement in GO.php

17-03-2020 6.4.127
- Core: Catch notification errors to fix error on Android phones.

09-03-2020 6.4.126
- Core: Select Heading, paragraph or code block in html editor
- Files: Fixed missing download link when not using templates in email
- Notes: Improved style and removed "Read more..."
- Address book: Don't show default country in address
- ActiveSync: Address was not synced if not filled in completely.

05-03-2020 6.4.125
- Core: Fixed error when double clicking some items.
- Time tracking: projects with no template caused error in time tracking.

02-03-2020 6.4.124
- Core: Improved connection error handling
- Address books: URL's were not displayed
- Timeregistration: Set date to current view when adding time
- Timeregistration: Small visual enhancements
- Core: fireEvent 'mapping' will not bubble down
- Billing: fixed broken delete button
- Leavedays: Fixed invalid float value when saving employee

28-02-2020 6.4.123
- Calendar: Fixed error when saving resource
- Core: Fixed error on saving groups
- Email composer: sorted search recipients on last contact mail time
- Tickets: Fixed status filter and status change in tickets
- Billing: Added Save PDF to order files menu item

21-02-2020 6.4.122
- Projects: Business module code accidentally in projects

21-02-2020 6.4.121
- Billing: broken add and delete invoice button
- Core: Removed deprecated timezones

21-02-2020 6.4.120
- Core: Send test message on Notification failed if you did not re-enter the password.
- Core: Adding custom field select option didn't save
- Billing: Clear items on opening new order
- Core: Connection timeout shows error dialog
- Billing: HTML formatting on frontpage text
- Core: Handle invalid sort state which may happen when a (custom) column has been removed

20-02-2020 6.4.119
- Core: Don't use notification and popups on mobile
- Google Authenticator: Show invalid code error
- Google Authenticator: Accept spaces in code.  Fixed: 2FA Improvement #537
- Tickets: Improved detail view
- Billing: Improved detail view

18-02-2020 6.4.118
- Core: Removed user "Permissions" and moved it to a "Visible to" tab.
- Core: Visual enhancements
- Core: Added counters on collapsed detail panels

17-02-2020 6.4.117
- Tickets: Note background color restored
- Projects: Error on login when not a projects admin

14-02-2020 6.4.115
- Core: Clear indexedDB cache when /install/upgrade.php has been executed
- Calendar: Fixed event link not working first time when creating from another item via + button
- Core: Various visual enhancements
- Core: Fixed "Models are read only error"

11-02-2020 6.4.114
- Files / Core: fix for error loading comments after collapsing file browser popup
- Projects: Support new framework filters
- Projects: Added export to main grid
- Core: fixed filter after fixed date

10-02-2020 6.4.113
- Core: Don't encode installation files with ioncube.
- Core: Support fixed dates in date filters 

07-02-2020 6.4.112
- Core: Fixed bugs in group dialog with loading and changing users when deselecting
- Core: Updated German translation
- Address book: Select contact field always showed contacts and organizations
- Tickets: Changing contact in ticket didn't work properly

03-02-2020 6.4.111
- Core: Remove fields from fieldset too after deleting fieldset and added loadmask
- Core: Optimized indexes of core_link table for faster filtering in the address book
- E-mail: Fixed converting non-latin characters when creating labels
- Core / E-mail: Fixed paste and drop of file in html editor
- Core: smaller font size for printing. Fixed at 12px.
- ActiveSync: Fixed problem with invalid imap flags reponse causing mails to stay unread on the devices using ActiveSync
- Core: Fixed invalid output problem on installation
- Custom field: template parses {{createdAtShortYear}} and uses the last 2 digits of the year #554
- Billing: Fixed font size in billing tax rate combo
- Newsletters / Email: Fixed Add sender to address list function

30-01-2020 6.4.110
- Files / Core: Db check fixes acl problem with files
- Newsletters: Newsletters set Content-Disposition: inline on attachments. They did not show up on Outlook
- Calendar: Fixed error message when changing calendar
- Calendar: Fixed error after saving with link
- Sync: Only writable email accounts should be selectable for sync
- ActiveSync: Some IMAP servers returned a response that GO did not understood which lead to an empty inbox on the phone

28-01-2020 6.4.109
- Note: decryption was broken for second encryption algorithm

28-01-2020 6.4.108
- Core: System settings only submits dirty values. Fixed a bug that caused SMTP password to be cleared for notifications.
- Core: Setting empty quota didn't work
- Sync: Fixed error in my account when no email module is available
- Core / tickets: fixed error when tickets option "Show confirm dialog when closing tickets" was enabled
- Core: Fixed user export / import
- Files: Fixed reload issue after creating new folder for an item
- Files: Refresh UI on change in folder or file dialog
- Email: pagination on templates

27-01-2020 6.4.107
- Email: fixed missing email from template option
- Email: Send didn't work when link was present

24-01-2020 6.4.106
- E-mail: Add attachment to e-mail from item
- E-mail: Save / Download inline images context menu
- E-mail: added email all files button
- Comments: After opening an item in a popup an error would occur on every comment update.
- Comments: cmd + enter on macos didn't work in Firefox
- Users: Show disabled filter didn't work
- Address book: Adding new organisation in new contact dialog raised an error.
- Address book: Fixed Error in /usr/local/share/src/www/go/modules/community/addressbook/convert/VCard.php at line 105: Call to a member function format() on null at 2020-01-24T15:17:27+00:00

22-01-2020 6.4.105
- Core: Load external pages directly because check for existing tabs fail in current browsers
- Files: Fixed error on search when having custom fields
- Core: Fixed custom logo not displaying

21-01-2020 6.4.104
- Core: Fixed: PDOException 'shortDateInList' #536
  Happened when default for shortDateInList was set to false
- Project: E-mail all files from a folder
- Notes: Fixed note decryption
- Billing: fixed outstanding orders export
- Billing: Fixed missing contact in recipient when creating invoice from contact
- Core: support cmd + backspace on macos for deleting in grids
- Core: Fixed small prints in Firefox
- Filters: Some filters were not working with "NOT"
- Filters: Sort combo alpabetically 
- Address book: A manual sort was required after changing sorting by last or first name
- Address book: Fixed missing contact fields in templates (email, company post address)
- Leave days: Year summary didn't show if you had hours from last year but no new hours
- Email: Zip of all attachments failed if content disposition was not attachment
- Sieve: Remove :create flag in fileinto command
- Tickets: Leave ticket blank by default didn't work anymore
- Calendar: Set resource title of private event to "Private"
- Calendar: Don't allow calendars from others as default calendar
- Smime: Added OCSP revocation check

16-01-2020 6.4.103
- ActiveSync: Z-push fixed problem with tasks on iOS
- Files: removed document and e-mail from template in file and folder panel
- Files: Doubleclick from search was broken
- Core: #202020048 Remove mcrypt dependency #542
- Custom fields: Fixed problem with required condition

14-01-2020 6.4.102
- Address book: Fixed merge error with duplicate values
- Email: Fixed error message when saving email as task
- Newsletters: Users can be recipients now
- Files: Fixed comments not showing for folders in filebrowser popup
- Tasks: Fixed comments editor error in Chrome after adding task
- Core: Fixed error when saving new group with module permission set
- Timeregistration: Works without projects module permission again
- Updated Croatian holidays
- Core: Reminder request could trigger reload loop
- Projects: Fixed name column state
- Core: Updated Norwegian and German translations
- Core: Focus on first invalid field and tab when save fails

13-01-2020 6.4.101
- Core: cron will run even though another instance of the cron process is still running. 
- Newsletters: When cron runs check if newsletters has been active in the past minute. If not then start sending.
- Addressbook: Fixed bug in company custom fields migration
 
13-01-2020 6.4.100
- Core: Fixed problem where list could load older request results
- Newsletters: Send email to address list owner when someone unsubscribes
- Core: new filter "Has links to..." to find items linked to another type. For example find all contacts with invoices.
- Core: Error handling for uncaught exceptions
- Notes: ctrl + enter to submit
- Address book: Adding contact to two groups immediately after eachother would only add it to the last one.
- Address book: CSV import can update contacts
- Address book: Improved import / export
- Address book: Auto detect Outlook CSV mapping for import
- Carddav: Fixed sync problem due to invalid uri's
- Custom fields: Template custom field added and fixed error function custom field
- Core: Use ErrorHandler::log instead of trigger_error to prevent exit of function on minor warnings
- Core: Fixed truncate holidays tool

07-01-2020 6.4.99
- Core: Error could occur with module permissions because some cache was shared which should have been per user
- Comments: Unable to edit fixed.
- Comments: CTRL + ENTER to save
- Tickets: CTRL + ENTER to save
- Core: Improved CSV import and export to be more compatible with other formats

02-01-2020 6.4.98
- Assistant / files: Assistant host could be wrong when using proxy on the server
- Billing: report timezone bug showing wrong results in year overview.
- Email: White background in dark theme for mail
- Email: Fixed count() error when searching
- Core: Small visual UI improvements
- Core: Updated Croatian translation

23-12-2019 6.4.97
- Core: header color override didn't work anymore

23-12-2019 6.4.96
- Files: Use template with E-mail download link
- Comments: Composer only rendered at one item
- Core: Dark mode theme (Beta)
- Address book: Colored icon with initials when no photo is present.

20-12-2019 6.4.95
- Custom fields: Fixed visual problem with select options
- Custom fields:ƒ Fixed problem that adding a field with a duplicate name was possible.
- Core: Added new allowed groups function for authentication. You can restrict IP addresses per group from where you are allowed to login.
- Address book: Added filter for contacts being in a group or not
- Newsletters: Double click to open contact

19-12-2019 6.4.94
- Core: Updated Brasilian Portugese language
- Custom fields: There were 2 extra decimals for numbers in templates
- Projects: Set contact on new invoice
- Core: Optimized various date column widths
- Core: fixed export error with multi select custom fields in it.
- Core: Paste from spreadsheet editors as HTML and not as an image
- User: account creation checks for the max users count
- Core: Improved printing view for Firefox
- Address book: Select contact sorts alphabetically
- Projects: Select user for time entry was missing

17-12-2019 6.4.93
- Email: improved search toolbar
- Core: Use accent color for active search to make it more clear
- Email: Emoji insert button added
- Comments: Some buttons were hidden when container is small
- Core: relation of type 'map' automatically changes state
- Core: Fixed date and date time format in templates
- Custom fields: Fixed save error when cache was not cleared and cleaned up code
- API keys: Fixed delete error for API keys
- E-mail: Reimplemented add unknown recipients
- Core: Updated German translation
- Core: Fixed incorrect info on synchronisation settings as admin
- Files: Fixed file not found bug when downloading files
- Core: Fixed missing back button on small screens for system settings dialog and user settings dialog
- Newsletters: Unable to add new recipients when last recipient was removed
- Custom fields: Hidden custom fields could show in detail view 

10-12-2019 6.4.92
- Address book: Add contact was broken
- Core: Encode filename in upload header to support UTF-8 filenames
- Core: try to convert CSV file uploads to UTF-8
- Core: reverted enter to save because it caused problems when searching inside TabbedFormDialog.js


10-12-2019 6.4.91
- Custom fields: db migration contained broken code.
- Email: content type application/eml opens within Group-Office

10-12-2019 6.4.90
- Zpush: mail sync was broken

09-12-2019 6.4.89
- Files: We did an update to Group-Office which comes with a new implementation of the Group-Office Assistant. If you use this please update it to the new version. You can find it under "Files" for your operating system at this page:
https://groupoffice.readthedocs.io/en/latest/using/connect-a-device/connect-a-device.html

- Postfix admin: Auto grow alias field
- Core: "Login enabled" checkbox visible when using IMAP or LDAP authentication
- Email: Fixed error in sieve disconnect
- EMail: Added new header X-Group-Office-Title: Group-Office 
- Core: Cleaned up EntityStore code
- Address book: Sorry, an unexpected error occurred: The contact groups must match with the addressBookId. Group ID: 10 belongs to 3 and the contact belongs to 1
- Projects: Removed broken natural sort and added created and modified at columns
- Custom fields: Ignore maxLength on text area's
- Address book: Don't open links when selecting them for text copy
- Core: Disable spell check on search fields
- Core: Database check continues if there's an exception
- Core: Different polyfill promise to support Windows XP with old Chrome.

06-12-2019 6.4.88
- Files: New Assistant implementation without webdav requirement. Clients need to be updated!
- Address book: Added zipcode to text search and as zip: 1234 AB

02-12-2019 6.4.87
- Files: Missing new folder button
- Core: Export sometimes only exported 40 records.

29-11-2019 6.4.86
- Projects: Resizable detail view
- Files: Quicker access to files via toolbars.
- Files: Folder upload restored.
- Billing: contact: and compnany: template tags added back in
- Address book: Some companies might not have been migrated to the new address book.
- Core: removed incomplete employees module

28-11-2019 6.4.85
- Core: Logo didn't display if you didn't set a different primary color
- Core: Reload did too many requests causing unnecessary load on the server
- Projects: Added projects grid and works on mobile
- Core: Enter to submit for older dialogs
- Core: Reduced push checks to every 30s instead of 5s to reduce load on server

26-11-2019 6.4.84
- Files: works on mobile
- Core: Fixed incorrect module sort order on first load
- Core: Fixed image viewer for mobile
- Core: Fixed install problem on multi instance

22-11-2019 6.4.83
- Core: Load state only on desktop
- Core: Copy html editor style from text area element so it matches style from css and doesn't zoom on mobile
- Address book / Core: Move files when properties affecting the path change and delete files when contact is deleted
- Core: Fixed logging of deleted contacts and notes
- Core: Fixed fatal error that aborted the 6.3 to 6.4 upgrade in some cases
- Notes: Supports activity log
- Core: Fixed error when setting module permissions on group
- Core: Added title's to combo box list items so you can see the whole text when it' cut off.
- Files: New folder button was missing in "Save as" mode
- Projects: Icon column growing too large bug
- Projects: Wrapping of tables for better display on smaller screens

20-11-2019 6.4.82
- Core: Create debug log file if not exists
- Core: Added several uninstall commands and fixed entity type register
- Core: Restored state from server

19-11-2019 6.4.81
- Core: Fixed switch user
- Core: Performance optimzation by using jsonSerialize and output each jmap method indvidually
- Core: Changed state saving to cookies so that you can have different states on different machines

19-11-2019 6.4.80
- Firefox: Workaround for indexeddb state error when firefox is in private browsing mode
- Core: Disabled modules were loaded in the old framework settings causing problems.

18-11-2019 6.4.79
- Core: Replaced localForage with go.browserStorage to fix problem with Group-Office not always loading in multiple tabs.
- Newsletters: Fixed problem with new lists not saving and showing
- Core: Performance enhancements
- Users: user display name can't be null

15-11-2019 6.4.78
- Core: Just log could not unserialize cache message
- Core: Fixed dissapearing fieldset's after changing values
- Comments: collapsible again
- Address book: Fixed delete of contacts
- Address book: Show selected organizations when creating a contact from an organization

14-11-2019 6.4.77
- Address book: problem with listing contacts without salutation

14-11-2019 6.4.76
- Activesync: fixed Can't get not existing property 'timezone' in 'GO\Tasks\Model\Task'
- E-mail: E-mail printing blank pages fixed
- Custom fields: Fixed adding unique indexes
- Demo data: Fixed install
- Core: Use thumbnailer for photo's an avatars

12-11-2019 6.4.75
- Billing: add total_outstanding
- Core: Added check for mysqlnd driver for system requirements
- Core: Lot's of performance optimizations
- Fixed: Addressbook - Create and Modified date shows "undefined" next to the time #496

11-11-2019 6.4.74
- Core: System settings and My Account work on mobile
- Address book: Edit form works on mobile
- Core: Global search works on mobile
- Core: Start Menu full screen on mobile
- Core: Fixed responsive issue where grid would become smaller then configured
- Tickets: Works on mobile
- Core: Sort custom filters alphabetically
- Core: Fix for user timezones different then client OS
- Address book: Added street to filter options
- Newsletters: Improved performance
- Email: Save all attachements to items worked only one time
- Core: Fixed AccesToken created from API-Key will expire after 1 week of inactivity #292
- Time tracking: Works on mobile
- Core: Smoother scrolling by preloading more.
- Address book: Show who modified and created in detail
- Notes: Show who modified and created in detail
- ActiveSync: Fixed GroupOffice isn't respecting addressbook permissions - CRITICAL (#492
- Core: Updated Spanish

05-11-2019 6.4.73
- Core: Fixed error where grid would not load
- Core: Updated Spanish translation
- LDAP: Group sync failed on some servers that returned "memberuid".
- Sieve: Don't autocreate missing mailboxes

31-10-2019 6.4.72
- Core: Use SQL_CALC_FOUND_ROWS to calculate total
- Core: Updated Spanish translation
- Core: bundle Foo/get requests to improve performance.
- Core: Use disk cache for CLI as Acpu is not enabled on CLI.
- Core: Performance improvements in ACL queries
- Address book: City missing from contact in templates
- LDAP Authenticator: Also match users based on e-mail address
- Address book: Put organizations on top in contact detail view
- Hoilidays: removed "From" boxLabel 
- Projects: Fixed undefined index contact_id error when creating project from mail
- E-mail: Pass contact ID and don't search by email for e-mail templates

28-10-2019 6.4.71
- Core: Improved delete performance
- ActiveSync: Don't sync organizations with ActiveSync as it caused problems on ios not shoin either the contact or the company
- Core: custom fields were missing in forms

26-10-2019 6.4.70
- Address book: Implemented Duplicate and merge function
- Ldapauth: Fixed missing ldapauth_server_user_sync table
- Core: JSON util for detecting invalid UTF-8
- Core: Don't add full text index to core_search on update because it's removed later anyway.
- Address book: Add job title to search cache description

22-10-2019 6.4.69
- Core: Revert to older italian translation because it was corrupt

22-10-2019 6.4.68
- Core: Updated Norwegian and Czech translation
- LDAP Auth: Bind to ldap before authentication

15-10-2019 6.4.67
- Addressbook and notes: Hide totals in nav bars
- Core: Fixed import CSV for custom fields values that are exported as text (Select, Multiselect)
- Projects: Fixed activity sort in time tracking
- Billing: Fixed total not always updating in expense dialog
- Core: new total display of grids also showed in nav bars. Removed now.
- LDAPAuth: Fixed users and group sync

14-10-2019 6.4.66
- Core: added total number of rows in new grid panels
- Email: Create links when replying to linked messages and from message dialog
- LDAP Auth: Fixed Sync usernames and authentication on sync
- Calendar: Events and tasks report mailer was broken
- E-mail: Autolink all contacts with matching email address
- Address book: Add suffix and prefix to detail view title
- Core: CSV import would not import anything in some cases
- Assistant: Didn't work with @ in username

11-10-2019 6.4.65
- Contacts: Fixed problem with duplicating phone number and possibly unlinking company
- Newsletters: NIce error message when testing without recpipients
- Core: Updated Brasilian Portugese and Italian translations
- Freebusy permissions: Could not add new users when installed
- GOTA: GOTA listed for users without permission becuase of missing permissions check in old framework module check
- Calendar: Fixed error in calendar when you didn't have permissions for the favorites module
- Calendar: Start in calendar / view where you were last
- Core: changed global search into normal index with wildcards

04-10-2019 6.4.64
- Core: Track changes in other models in entity controller when doing a set request so it can return all modified entities
- Address book: Organizations field was not hidden when newsletters module was installed
- Core: Clear legacy cache before upgrade
- Core: Workaround for hidden custom fields
- Comments: Visually improved

01-10-2019 6.4.63
- Custom fields: Fixed problem in loading tree select fields for the second time
- Core: Attempt to solve very rare loading hang problem
- Core: Comboboxes take 3 chars to start searching instead of 4.
- Core: ctrl + f7 enables debug log for new framework
- Email: Search in all or subfolders didn't work
- Custom fields: Validate chips and treeselect field so required flag for cusotm fields of type select and multiselect work
- Newsletters: Open sent item doesn't show template.

30-09-2019 6.4.62
- Core: Each entity type has it's own color in the system
- Core: New group member dialog. Fixes serverclient group controller permission issue #472
- Core: Fixed user display name set to empty
- Core: Half hours in working week didn't validate

27-09-2019 6.4.61
- Filters: Contact filter did not respect isOrganization setting of custom field
- Advanced Search: Search contact, users and group custom fields by text too. And text are wrapped with wildcards.
- Core: Debug log file more sensible and include line and class number.
- Users: Delete broken
- Core: Switch user didn't work when logging in from multi instance environment

26-09-2019 6.4.60
- Core: proper error message when delete fails
- Address book: Add "Add to contact" option when clicking e-mail address
- Core: Enhance select people dialog
- Newsletters: Fixed problem with newsletter attachments
- Core: sort on "creator" and "modifier"
- Fixed: e-mail-settings #461
- Serverclient: Improved server client error reporting

24-09-2019 6.4.59
- Google Authenticator: Fixed problem where it would enable when saving user
- Calendar: brought back forthcoming and past events
- Tasks: incomplete and completed tasks separate
- Address book: Users address book was filled with empty contacts
- Calendar: Fixed more... positioned over day number

23-09-2019 6.4.58
- Address book: added business fields in detail panel of orginisation
- Address book: Create personal address books for each user
- Users: Only show module tabs in user dialog where this user has access to
- Notes: Default note book setting and every user gets a personal note book
- Sync: settings use default notebook and address book by default
- Newsletters: Create filter based on address lists in address book
- Newsletters: Add contact to address list in edit dialog of contact
- Billing: Fixed issue with number field not loading in product dialog

19-09-2019 6.4.57
- Custom fields: Query language works with text for select and multiselect
- Users: Problem with deleting users because of default calendar
- Address Book: Brought back salutation field
- Address book: Added notes filter
- Projects: Extended project report with comments
- Files: File wouldn't open by default when WOPI was installed
- Core: Missing link button in new dialogs.
- Billing: Fixed problem where amounts got multiplied by 100 when editing numbers with decimals
- Workflow: Fixed workflow delete button
- Files: Fixed file access to address book access denied
- Projects: Time entries are searchable

17-09-2019 6.4.56
- Core: Disable logging during upgrade for performance
- Core: fixed custom fields upgrade problem

16-09-2019 6.4.55
- Problem with start module

13-09-2019 6.4.54
- Core: Sometimes a user was not in group everyone
- IMAP / LDAP auth:  Clear database password when logging in with IMAP or LDAP authenticator
- Core: raised default max upload size to 1GB
- Core: fixed scroll to top in infinite grids
- Carddav: carddav ignores sync-settings #460
- Email: Fixed Search in Email with 2 strings #444
- Sync: Default sync settings when creating a new user
- Core: Mask grid when deleting
- Core: Serveral issues with forms including a checkbox

12-09-2019 6.4.53
- Billing: Fixed tax percentage decimals in PDF
- Billing: Fixed translation for Quantity / Amount in order items
- Carddav: Fixed Birthdays on carddav not synced #410
- Core: Import function shows error messages per line

10-09-2019 6.4.52
- Billing: Fixed group summary gross total in PDF.
- Caldav: events with status needs-action were not synced. Change status to needs-action to tentative as needs-action is not a valid vevent status. 
- Carddav: Create with vcard 4.0 format was broken (davdroid)
- Core: Added user import with ability to create mailbox on mailserver too
- Serverclient: Fixed missing domain checkboxes in user dialog
- Core: Fixed bug in custom fields migration
- E-mail: Set pasted image filename
- Address book: contact:city tag was not parsed
- ActiveSync: Fixed timezone issue with all day events

09-09-2019 6.4.51
- Core: Normalizing strings could corrupt UTF8
- Address book: Filters were broken

03-09-2019 6.4.50
- Bookmarks: Languages readded
- Bookmarks: Possible to set permissions on category
- Projects: Sub project link does not navigate to the start page anymore
- Address Book: Unable to unstar
- Address book: Change in telephone links so you can select the text
- Core: Link browser didn't paginate. It loaded all links which could be very slow.
- Email: Template variables work_fax and work_mobile didn't work
- Core: Image viewer has print and open in browser button
- E-mail: In print small part of subject could be missing
- Core: Keep scroll position on delete in grids but move to top when paging
- Email: Address book dialog works for cc and bcc
- Email: Select newsletter lists in composer address book
- Core: Updated NL and IT translations
- Core: CSV export was very inefficient. Speed improved dramatically.
- Newsletters: Removed CAST() function from query to support older MySQL versions
- Address book: Shrink to fit data when migrating custom fields and row size is too large during migration
- Billing: fixed missing company name in recipient
- E-mail: Fixed ICS parsing error. Fixed #440
- Projects: Added permission type to detail view

02-09-2019 6.4.49
- Core: Fixed link date not displaying in link browser
- Address book: Added initials field
- Core: Dismiss icon missing in reminders
- Core: order global search by modified date
- Address book: System setting to automatically link e-mail to contacts

30-08-2019 6.4.48
- Core: Install comments and bookmarks by default
- Core: Fixed demo data

30-08-2019 6.4.47
- Multi instance: upgrades all instances automatically
- Core: Ability to extend content security policy in modules
- Core: SSE improvements and option to disable it
- Demodata: updated for new modules

27-08-2019 6.4.46
- Core: Custom fields were blank in CSV export.
- Core: IMAP auth was broken when groups were modified. Removed permission check in user model that is executed in controller
- Bookmarks: Fixed always reloading of website data

26-08-2019 6.4.45
- E-mail: Open contact detail menu from email address instead of edit dialog
- Core: added boolean to be able to show/hide customfields inside a dialog.
- Google authenticator: Fixed issues when setting up new authenticator.
- Core: Backup & Confirm before upgrade
- Core: Upgrade on command line
- Core: Upload pictures select bug
- Core: Auto detect CSV delimiter and fixed import of multiple properties
- Core: Saving quota in user dialog didn't work because of broken compositefield

22-08-2019 6.4.44
- Core / Calendar: Search button can be in bottom toolbar (Calendar search)
- Core: Fixed group dialog when default permissions for group was set
- Address book: Use lastname first when creating files folder
- Core: Fixed pluload UI
- Core: Updated French translation
- Email: Fixed html encoding in header bug

19-08-2019 6.4.43
- Address book: Install failed

19-08-2019 6.4.42
- Core: Icon's didn't render on non standard port.

17-08-2019 6.4.41
- Core: Added users and user groups to select dialog (Composer, Calendar)
- Calendar: 6.4.24 - Calendar - Error when accepting invite from other GO user #424
- Document templates: Fixed Can't get not existing property 'photoFile' in 'go\modules\community\addressbook\model\Contact' error with ODT files

16-08-2019 6.4.40
- Core: Upgrading cleared module settings (Only affected one custom module)
- Core: Module sorting (two community groups)
- Address book: Search only showed contact with email

16-08-2019 6.4.39
- Core: 6.4.36 E-Mail created from task with wrong address #443
- Core: Fixed saving module permission in group dialog
- Address book: Changed detail view layout so that email addresses and phonenumbers can be clicked directly and smaller image
- Address Book: Contact salutation template tag fixed and configurable in the address book settings
- Address Book / Core: New image field with upload from URL support
- Address Book: Create files path like in 6.2 for address book files
- Files: Removed UTF8 to CP850 conversion when creating ZIP files as it works in Windows 10 now
- Core: Added English / Philippines language and holidays

13-08-2019 6.4.38
- Core: Adding select option destroyed all data
- Core: Fixed multiselect custom field migration
- Email: Fixed invalid autocomplete query returning too many results
- Address book: Search in email by default too

12-08-2019 6.4.37
- Calendar: Fixed Error in 
  /usr/share/groupoffice/modules/calendar/model/Participant.php at line 408: 
  Call to undefined method go\\modules\\community\\addressbook\\model\\Contact::link()
- Core: Custom fields migration fixes
- Core: Fixed upload screen missing style

19-07-2019 6.4.36
- Core: small ui enhancements
- Newsletters: Fixed incorrect parsing of images in email templates
- Address book: Sanitize phone numbers
- Email: Fixed reset buttton on search type change
- Email: Remove links from print
- Core: Improved create link menu button with filters
- Core: CSP Allow data: uri for fonts for browser extensions
- Files: Fixed location bar not set initially and  not visible in popup file browser
- Core: Fixed export CSV error
- Calendar: Fixed missing link when creating new Event from item
- Core: Link show search cache date instead of linking date
- Core: Link delete button does not show through date anymore.
- Calendar: Home button when opening link takes you to the date in the calendar too
- E-mail: Save attachment to item works with folder now too by saving directly in the folder
- Address book: Sort address books alphabetically
- Email: Link to item dialog could be destroyed
- Core/Files: Try to clean invalid UTF8 in file names
- Address book: Fixed contact ActiveSync problems
- Core: Custom fields upgrade fix for missing select options (TreeSelect, Select and Multiselect)

18-07-2019 6.4.35
- Core: upgrade error fixed

18-07-2019 6.4.34
- Core: Error in duplicate with custom fields of type Notes
- Core: Readable items may be linked
- Core: show correct icon in create link button for contact and organization
- Bookmarks: Handle non existing user id's in bookmarks upgrade
- Billing: Fix for house numbers from address book
- Billing: Copy matching custom fields from address book again but by database name
- Billing: Fixed bug in translating invoice PDF
- Billing: Fixed unsuable UI in product dialog with custom fields.
- Comments: Company comments were not migrated correctly. Can be fixed by finding out the old entityTypeId from the comments_comment    table. Then do:

   update comments_comment n set entityTypeId=(select id from core_entity where name='Contact'), entityId = (entityId + (select max(id) from ab_contacts)) where entityTypeId = <OLD ENTITY TYPE ID>;


15-07-2019 6.4.33
- Core: Exclude composite fields from form posting again because this gave a lot of saving problems.

11-07-2019 6.4.32
- Email: Fixed unlinking in email message
- Address book: {contact:cellular2} and {user:cellular2} work
- Newsletters: Sent items were not showing in newsletters
- Core: IE 11 support
- Leavedays: Employees needed aproval were not bold anymore

11-07-2019 6.4.31
- Core: Bug in template parsing

11-07-2019 6.4.30
- Billing: Fixed ODF templates
- Email: Fixed missing email in to field of composer bug
- Billing: Link message when emailing from edit dialog
- imapauth / ldapauth: Fixed e-mail account creation
- address book: Scrolling in user profile
- Updated Italian
- Updated Czech

09-07-2019 6.4.29
- Core: System settings dialog was reachable for non admins
- Task / Projects and Billing: HTML encoding bugs in description

08-07-2019 6.4.28
- Email: Fixed attachment encoding error
- Core: Fixed contact link to maps #438
- Core: Core / Extjs language was missing
- Core: Language download button only worked when dev module is installed. This is no longer necessary.
- Address book: 6.4.27 - Address book - Add organization - cosmetic issues #436
- Core: Fixed XSS vulnerabilities
- Email: Print of an email genarate empty pages Fixed #435

02-07-2019 6.4.27
- Core: Install without ioncube or license failed
- Custom fields: Fixed custom fields permissions not editable
- Comments: couldn't be add by non admin users
- Address book / Notes: added commentedat, createdby, modifiedby filters
- Notes: Added custom filters component

02-07-2019 6.4.26
- Address book: Added organization filter for contacts "org"
- Address book: configure name sorting by last or first name
- Address book: Create a new organisation when creating a new contact #426
- Core: Object values were always posted even if they weren't dirty (Problem with acl's)

01-07-2019 6.4.25
- Core: Simplified search. All words will be used with AND instead of OR. A wildcard will be placed after each word. 
- Core: Fixed upgrade error can't find module core/groups
- Comments: Fixed comment permissions and label editing
- Core: Loading mask could stick sometimes
- Email: Click on email from list that has just been deleted elsewhere forever show the Loading ... pop-up #425
- Emailcomposer: Insert inline image in composer through the upload button 
  opened a file chooser in which you could only choose folders. 
  This is now changed to be able to choose (image)files.
- Address book: Fixed "function" and "first_name" in email templates
- Core: Fixed downloading language translation file from system settings
- Serverclient: Fixed autoload issue of Controller.

24-06-2019 6.4.24
- CardDAV: It was not possible to add new carddav accounts. And sync was broken.
- Comments: Comments cannot be deleted #414
- Addres book: Icons and address book name were at the same place.
- Core: Combobox, when pagesize is given the property "calculateTotal" needs to be send to the server.
- Core: Fixed deleting mapped properties by setting them to null
- Authentication: Fixed adding groups to LDAP and IMap auth profiles

21-06-2019 6.4.23
- Custom fields: Implemented the dbToText function for the select customfield so it's value is showed instead of it's id.
- Email: Improved autocomplete search
- Address book: Display adressbook of contact #405
- Address book: Added address book to detail view and fixed default/Last choosen addressbook is not preselected anymore #399
- Core: Create link button in dialog showed "undefined"
- Custom fields: Updating filters didn't work always
- Core: Check if files module is installed. Fixes 6.4.22 SQLSTATE[42S02]: Base table or view not found: 1146 Table 'web36_db9.fs_folders' doesn't exist #406
- Core: Mask UI improved. Mask will wait for 500ms until it shows.
- Core: Changed font to Lato on all platform so UI is consistent
- Core: Fixed horizontal scrollbar issue

18-06-2019 6.4.22
- Calendar: Fixed ambiguous id error
- Notes & Comments: Clicking hyperlinks opens new tabs
- Tasks: Fixed comment in continue task
- Email: Fixed error when sending email from contact without an email address
- Core: Fixed upgrade and installation bugs
- Address Book: Fixed address book sorting issue. Fixes #400.
- Files: Fixed issue that type is not a property
- Billing: fixed grouping of items

11-06-2019 6.4.21
- First release. Read the release notes here: https://groupoffice.blogspot.com/2019/06/group-office-64-released.html

- Calendasr: Show unconfirmed holidays
- Projects: Send company id when selecting contact

28-09-2020 6.3.94
- Assistant: removed install sql that could cause:
    Exception in /usr/share/groupoffice/go/base/Module.php at line 298:
    SQL query failed: UPDATE `fs_filehandlers` SET cls =
    'GO\\Assistant\\Filehandler\\Assistant' WHERE
    cls='GO\\Gota\\Filehandler\\Gota'

28-09-2020 6.3.93
- Files: Added permission checks to compress functions
- SMIME: Check OCSP locally if smime_root_cert_location is set in config.php
- Calendar: Missing resource admin email #201919703
- Calendar: Category permissions  #202020841
- Tickets: Only messages from agent are sent to CC contact #201919432
- Calender: Bug changing "this and future events" #202021084
- Calendar: No notification when a participant is removed #202021083
- Core: Workaround Safari 14.0 hang with 100% cpu usage

16-07-2020 6.3.92
- Core: upgrade problem with trigger

07-07-2020 6.3.91
- Calendar: Fix for "No participant found for this event"
- Core: Start with create trigger in 6.2 upgrade to avoid problems when it's not allowed later on.

05-06-2020 6.3.90
- Core: Check if db is in invalid state (partially upgraded to 6.3) before upgraded
- Sieve: Fixed bug where sieve dialog showed folders of other account
- Files: Assistant installation replaces GOTA file handlers
- Core: Removed NO_AUTO_CREATE_USER from sql_mode because it doesn't work in Mysql 8 anymore and it wasn't needed anyway.

27-01-2020 6.3.89
- Tickets: Show confirm on closing tickets option broke module settings and email viewing.

22-01-2020 6.3.88
- Core: removed broken required_condition from 6.3

21-01-2020 6.3.87
- Sieve: Remove :create flag in fileinto command
- Tickets: Leave ticket blank by default didn't work anymore
- Calendar: Set resource title of private event to "Private"
- Calendar: Don't allow calendars from others as default calendar
- Smime: Added OCSP revocation check

19-12-2019 6.3.86
- Projects: Send company id when selecting contact
- User: account creation checks for the max users count

17-12-2019 6.3.85
- Files: We did an update to Group-Office which comes with a new implementation of the Group-Office Assistant. If you use this please update it to the new version. You can find it under "Files" for your operating system at this page:
https://groupoffice.readthedocs.io/en/latest/using/connect-a-device/connect-a-device.html

- Calendar: Show unconfirmed holidays
- Core: Fixed upgrade when $config['webmaster_email'] is not present.
- Email: Fixed showing emoticons in email

01-11-2019 6.3.84
- Projects: Fixed activity sort in time tracking
- Billing: Fixed total not always updating in expense dialog
- Projects: Send contact ID when creating project from e-mail
- Tasks: Fixed link to project after cancel
11-10-2019 6.3.83
- Core: Working week didn't accept half hours
- Time Tracking: Sort time tracking activities by name
- Calendar: Calendar remember state
- Assistant: Didn't work with @ in username
- Core: fixed 6.2 email settings upgrade

19-09-2019 6.3.82
- Projects larger expense budget dialog and resizable
- Billing: Force select of book in duplicate dialog
- Core: Clear old framework cache on upgrade
- Project: create invoice without grouping time entries by employees. are time record will be group into 1 group
- Comments: Removed 10 comment limit
- Projects: Don't reset travel distance on existing time entries

11-07-2019 6.3.81
- Core: Enable / disable Add linked item buttons based on permissions
- Core: Update Czech translation
- Email: Fixed attachment encoding error
- LDAPAuth and IMAP auth: Fixed email account creation

24-06-2019 6.3.80
- Calendar: Fixed error where appointment dialog wouldn't load

24-06-2019 6.3.79
- Core: Bug in script loading prevented GO from starting

21-06-2019 6.3.78
- Address book: Fixed bug where to address in composer was not filled when creating mail from contact
- Projects: Fixed problem where projects wouldn't load after opening a project from a link
- Core: Faster boot time due to caching in the browser

18-06-2019 6.3.77
- Files: Fixed issue that searching files throws an error about an ambiguous column
- Fix for keyboard navigation in Firefox 67

03-06-2019 6.3.76
- Fixed some 6.2 upgrade issues.

27-05-2019 6.3.75
- Tickets: Added missing language in tickets for Brazilian portuges
- Email: Fixed render issue when pasting multiple recipients in email composer
- Smime: validate if import is in PEM format. Fixed S/MIME Certificate Import Error #288
- core: fixed language issue with pt_BR
- billing: show country in full name

21-05-2019 6.3.74
- core: Use varchar 190 field for search keywords
- Scanbox: Fixed "link to item" feature
- Core/Links: Added singleSelect config to createLinkWindow
- core: Smaller paddings on trees and grids
- email: red color on email flags
- customfields: function field could unset other custom fields.

14-05-2019 6.3.73
- Sync/email: Fixed paging and searching in select email in sync settings
- Core: Created new setting callto_open_window to control if a phone number click will open a window
- Core: Fixed issue that when a link is created on model->save and the Link/set is called afterwards, that an error is thrown when the link did    
        already exist. Now the error is ignored when validationerror status == 11 
- Core: Clear listeners before creating the new ones again.
- Core: Fixed issue that the columnSelectGrid for export could crash when pressing "Delete" button to remove a column.
        Double click or drag/drop should be used. Delete is disabled now.

06-05-2019 6.3.72
- Core: Auto logout when checker fails fixed
- Core: Show route in log when access denied error is logged
- Core: Fixed upgrade issue
- Email: Fixed email client updating on every checker request

29-04-2019 6.3.71
- Core: Fixed search caching error

25-04-2019 6.3.70

- Core: Improved logging of JSON parse errors in JMAP API
- Address book: For user contacts, only use user display name to populate contact when creating a new one
- Groupoffice Assistant: Fixed url for downloading GroupOffice assistant
- Email: Fixed permission issie in email account combobox (At sync settings)
- Core: HTML editor: Fixed "Capital after punctuation" functionality in combination with the shift key
- Core: Login screen - Fixed problem that language selection was not clickable when a message was shown.
- Core: Fixed upgrade error for some mysql servers not supporting a large index on core_search

08-04-2019 6.3.69

- Core: Correct UTF-8 encoding on language import
- Core: Prevent license error in System tasks maintenance
- Reminders: fixed add reminder in reminder popup module
- Leavedays: changes in Monthwindow for extending and fix un undefined issue.
- Core: Pin TCPDF to version 6.2.22. Higher versions break image loading in PDF
- Address book: search sent invalid data to the server causing problems on some servers
- Core: Selected group members should be on top when editing groups
- Core: Overriding permissions in projects was undone by users editing a project with write permissions
- Files: Files and folders should not have a file browser menu item
- Core: Updated French translation
- Custom fields: Fixed rename of tree select slaves
- Core: Don't trackResetOnLoad in TabbedFormDialog because this will create invalid default values because those dialogs are reused.
- Core: Links open in popup window like in 6/2
- Billing: Fixed rounding issue with round up or down enabled

01-04-2019 6.3.68
- Email: Fixed subfolders with \ as delimiter
- Timeregistration: Improved error message when time entry with break fails
- Addressbook: Fixed custom field import 
- Core: Old framework registered entity incorrectly. "linkedEmail" instead of "LinkedEmail"
- FileSearch: Fixed links to attached indexed attachments in the displaypanel. (Was broken due to new GO63 router)
		*** Running the filesearch index again is needed to let this work ***
- Calendar: Added home button to jump to default calendar
- Email: Autolink linked items when replying to message
- Projects: Fixed some issues in new invoice dialog
- Core: Overflow ellipsis on old displaypanel section headers
- Core: Handle / ignore open_basedir errors
- Assistant: Opening files with assistant in shared folder din't work in all cases.
- Core: Upgrade 6.2 to 6.3 fixed problem when links had an unknown entity id in them.
- Email: Fixed filename issue with spaces in email attachment download

07-03-2019 6.3.67
- Customfields: fixed format of max fieldlength for customfields when it is set to 1000 or more. 
- Core: Updated Czech translation
- Core: Fixed bug that 30 day trial button did not show.
- Core: Upgraded to SabreDAV 3.2.3 to fix sync problem with some CardDAV clients (DAVDroid, em client)
- Core: Add validation to Mapping::addRelation() so developers can't map entities.
- Core: Dont' open new tab on tel:// click
- Core: Added maxlength props to text and mediumtext dbtypes
- Files: Decompress zipped files in folder with read only permissions will throw an access denied exception. 
- Tickets: Search didn't search message content anymore
- Multi instance: Use cookie in when logging in from multi instance too.

25-02-2019 6.3.66
- Core: Fixed removal of Admin group from acl in groups when using "Apply defaults"
- Users: added ID column (hidden by default)
- Calendar: Restored select all button for calendars. Ticket #201918192
- Sync: Fixed error that occured on empty folders.
- Core: Show description when searching for links
- Sync: Fixed ticket #201715362. All day event one day short on android.
- Email/files: Fixed ticket #201918173. File browser didn't reset.
- Billing: Fixed months in search bar of billing
- Core: Use cookie to store authentication token to make it available in new opened tabs / windows
- Customfields: Make the "Max. number of characters:" field usable for textarea customfields too.
- Savemailas: Fixed issue that "Save as"->"File to item" link window also showed entities that did not have file support.
- Core/Links: Added "entities" property to filter the list of entities in the link window.

21-02-2019 6.3.65
- Core/Files: Fixed display of linked files and folders in the displaypanel.
- Favorites: Fixed gear icon to manage the favorites.
- Core: Multiselect grid, Added check if the tools property is given.
- Tasks/Start page: Put headers back so the startpage widget can be sorted on date.
- Calendar: Fixed problem that opening event dialog the 2nd time did not work.
- Core: Don't search in TEXT fields by default
- Address Book: Removed automatic copy to post address because it makes more sense to 
  use the button
- Core: Fixed Combobox display of html entities.
- Document templates: Fixed linking in new email from template
- Address book: Added address book name to contact and company detail
- ActiveSync: Fixed z-push-admin and z-push-top command line utilities
- Serverclient: Server client stayed inactive if installed before 6.3
- Core: Fixed error handling in rebuild search cache

12-02-2019 6.3.64
- Calendar: Fixed event dialog crash bug. (FileBrowserButton could not be destroyed)
- Document templates: Fixed space problem in docx templates
- Tickets: Use template vars in due date mail
- Billing: Fixed problem in copying matching custom field names
- Document templates: Selecting sources was broken

07-02-2019 6.3.63
- Email: fixed z-index lowering in sanatize function
- Tasks: Removed ignore ACL from porlet. Tasks should not be shown when permissions are revoked.
- Cron: Added column that will show the last occured error.
- Core: Fix Color in columnYesNo renderer for Paper theme.
- Core: grid column Text renderer will nl2br
- Style: Changed links style so they so not look like normal text
- Core: Normalize UTF8 filename on upload. Fix for broken upload function with utf8 filenames.
- Core: Normalize problem with sync filesystem and filenames in different utf8 encoding.
- Core: Fixed problem with invalid redirect after installing demo data

04-02-2019 6.3.62
- Core: Auth should give 401 response and not 403 on bad login
- Calendar: Fixed calendar grid render issue when events didn't have a gap between them
- Core: Implemented 'calculateTotal' param for jmap to improve performance on query requests. (global search)
- Billing: No search button in select product catalog
- Core: Added "new" button for advanced search window.

31-01-2019 6.3.61
- Core: Request SSL check works when server is behind proxy too
- Core: Listeners relied on cache to be persistent causing missing properties "taskSettings" and "googleauthenticator"
- Assistant: base64 encode paths so that it works with UTF-8 on all platforms and browsers. 

  *** Assistant 1.0.4 is required after this upgrade ***

  https://groupoffice.readthedocs.io/en/latest/using/connect-a-device/connect-a-device.html

28-01-2019 6.3.60
- Files: clear shared folder cache so incorrect structures will be rebuilt automatically.
- Projects: Incorrect fee when copied from activity type
- Sieve: Was inactive because of invalid permission check
- Core: Added missing index on core_search.keywords
- Contacts: Added ID column to merge dialog (default hidden)

22-01-2019 6.3.59
- Mailserver: Server client module works again
- E-mail: Fixed Email Attachment wiggle #275
- E-mail: Fixed Sieve issue #268
- E-mail: Fixed Tooltips in the way of email #276
- Files: Fixed global folder search
         Fixed Search Folder #287
- Core: created checkbox to enable login message.
        Fixed Missing Features in 6.3.x #290
- Start page: Portlets must only be declared if user has permissions for the module
              Fixed Disharmony between Files and Start Page #291
- Custom fields: Fixed disable custom field categories
- Core: Invalid sql in cron->getAllUsers()
- Core: Language didn't merge recursively with English leading to incomplete
        country selection combo box.
- Files: Fixed incorrect shared folder tree
- Core: Removed robots noindex header because they don't have any effect because
				robots.txt is used.
- Core: Updated Brazilian Portugese and Czech language
- Core: Fixed display of short date when choosing date further away than a week.
- Core: Fixed clicking to dismiss notifier messages
- Core: Moved search field from pagination bar to the top toolbar in Multiselect grids
- Core: Added a display name colum to the permissions add dialog. This will make it easier to search for the correct user to add.
- Core: Use Apcu caching if available.
- Core: Update in store could make values null that were prefetched. See ticket #201817274.
- Projects2/Timeregistration: Fixed issue that the invoiced check icon for time entries was not displayed anymore.
- Core: Delete search cache and links when entities are deleted.
- Core: Run checkdatabase as admin. Fixes #266

11-12-2018 6.3.58
- Core: Fixed access denied error in upgrade.

10-12-2018 6.3.57
- Billing: Fixed loading address data when opening Orderdialog from contact (link)
- Files: Copy custom field settings too when copying folders
- Core: moving a folder failed when on different volumes

04-12-2018 6.3.56
- Core: Switch user was broken

03-12-2018 6.3.55
- Holidays: fix in copy holidays from last year
- Core: Always join customfields when joinCustomFields is set. because this is a core module now.
- DAV: 'host' property used for DAV baseUri
- Files: Creating folder in shared folder will not make you the owner anymore
- Addressbook: fixed birthday portlet on start page
- Addressbook: fixed importing contacts without type (works for vcard property groups)
- Core: GO::config() will use 'host' from config.php if it is specified
- Core: Fixed session reset problem (Export and Smime)
- Calendar: Fixed link creating when adding event to another item
- Core: Checkbox in tree panels never fired "checked" on checkchange.
- Core: Fix for ticket #201817154. Unclosable window remained when window was 
  hidden after submit while being dragged

26-11-2018 6.3.54
- Files: Fixed issue that files could not be deleted when a search is active.
- Core: Merging of globalconfig.inc.php failed when config array was defined as array();
- Core: Incorrect quota error message
- Core/Calendar/Tasks: Add recurrence fieldset to the core and apply to the calendar and task module.
- Webdav: Added an ignore for Microsoft Office lock files

20-11-2018 6.3.53
- Core: Number conversion error on some systems
- Core: Fix in ExtJS for new browser spec where FormElement.action would no longer be empty
- Holidays: Fix for creating an empty workweek when none exists yet
- Email: New base64 encoding function for unicode ascii character in mail folder names
- Core: Added support for icon style in MenuItems with property iconStyle: cssProps
- VCard: Fix import of phone number without a type are within a vcard group
- Core: Numberfield broken when loaded before render
- Tickets: First message content was not set on ticket when importing from imap 
  making {MESSAGE} unavailable in templates
- Files: Recent file didn't show files from folders without ACL id
- Files: Fixed shares folder with complex sharing structures

16-11-2018 6.3.52
- Core: Check if createdBy or modifiedBy is set in detail panel to avoid crash
- Core: Delete contact didn't work

15-11-2018 6.3.51
- Core: upgrade broke all passwords. If your password break again after this 
        upgrade then move /var/lib/groupoffice/defuse-crypto.txt to 
        /home/groupoffice/defuse-crypto.txt
- Notes: Removed adding createdBy in init of the Notebook entity. 
				 This causes issues when creating an instance of the object when no user is logged in.
- Core: Implemented domain combo box with default setting on login screen so
				LDAP and IMAP authenticator users can logon without entering the domain.
- Core: Added delete buttons in more menu of authentication grids
- Core: Prevent duplicate key errors in core_entity in 6.2 to 6.3 upgrade.

12-11-2018 6.3.50
- Language: Updated Hungarian
- CreateLinkButton: fixed issue that reset did not clear the new added items.
- Multi instance: Storage and user quota can be set in multi instance module
- Multi instance: Trials will be deactivated after 30 days
- Multi instance: Possible to set welcome message on start page via API
- Multi instance: copy system settings from manager to new instance

08-11-2018 6.3.49
- Merged 6.2 fixes
- Use globalconfig.inc.php in new framework too. (Thanks to pvdvendjc)
- Core: fixed issue with GO62 to GO63 update in combination with modules that are already refactored.
- Core: Added QR code generator
- Googleauthenticator: Use the QR code generator to generate the GA QR code
- Webdav: Fixed Webdav quota information

30-10-2018 6.3.48
- Demodata: Fixed demo data module that kept asking to add data
- Calendar: Fixed bug when clicking on grid created appointment on the wrong day
- Core: Fixed ZIP file bug with utf8 characters in filenames
- Core: Added Create link button to email composer, task, note and event dialog.
- Core: Fixed issue with form loading while it was not yet rendered. 
				(Fixes loading of the user settings dialog when accessing it from the users grid.)

30-10-2018 6.3.47
- Googleauthenticator: Improved setup of authenticator
- Files: Added quota panel to user settings.
- Projects: Fix in working week for calculating task due dates.
- Core: Removed double slash in API endpoints
- Email: Fixed email folder subscribtion treeview
- Notes: Textarea high will grow when resizing the dialog
- Core: Language fix for legacy modules
- Core: Fixed treeview for Internet Explorer
- Core: HTMLEditor button are small so more would fit on screen
- Core: Rebuild search cache skipped every 100 records.
- Core: All search terms must match instead of any
- Core: Short date format also use days of the week in text for last week
- Core: Fix for some components not translating
- Core: Updated languages Magyar and Bahasa Indonesia
- Projects: Bug in jobs fixed where save didn't work
- Core: core/email: Fixed bug in creating ZIP files with utf-8 characters
- Core: More human friendly error messages

23-10-2018 6.3.46
- Core: Fixed tree view for Firefox

23-10-2018 6.3.45
- Debian package uses apache maintainer scripts for enabling and disabling the config.

23-10-2018 6.3.44
- Projects: added default columns that are on screen to time registration export
- Core: Improved search algorithm for new entities (Notes, Users and groups)
- Core: Find user groups on display name
- Core: Set cookie with far future expiry date to remember language after browser close
- Core: added CreatedModifiedBy display panel template.
- Contacts: added created/modified koloms to contact and company grid
- Projects2: Fixed display and format of the external rates. Caused issues when default rates were set.

15-10-2018 6.3.43
- ATTENTION: System settings need attention for default user groups and group visibility.
- Core: Group and user defaults are sub dialogs in system settings. 
- Core: Group visibility defaults can be configured and reset.
- Core: Cleanup user data in old framework when user is deleted
- Core: Added new preference to show long or short dates in lists.
- Core: Fixed URL detection when server is behind proxy or rewriting rules.
- Core: Small UI enhancements
- Email: Linked emails with attachments having identical names always opened the first attachment.

09-10-2018 6.3.42
- Core: Search index script will index only missing results.
- Core: Brought back the Link browser. Improved links display.
- Core: Prevent core modules from being disabled with config['allowed_modules']
- Updated German translation
- Core: added icon to permissions panel to distinguish users and groups

04-10-2018 6.3.41
- Core: Error in lock function

04-10-2018 6.3.40
- Core: Added "Edit contact" in more menu at System settings -> Users to connect a contact to a user.
- Core: Remove non existing groups from ACL on upgrade
- Core: Added a more detailed error message to the Lock->lock() function in case the .lock file cannot be created
- Core: Don't redirect to entity detail view after creating new link
- Tickets: Fixed issue that a ticket agent did not get a correct formatted message.
- Files: Changed "public folder" icon in grid.
- Core: Fixed tabs in user settings menu
- Core: Fixed issue with generating XLS file reports in Projects2
- Core: Translation in some parts were not working correctly.
- Core: Convert tables to InnoDB before upgrade if necessary
- Customfields: removed maxlength of 190 characters on textareas

25-09-2018 6.3.39
- Core: merged 6.2.105
- Files: File custom field didn't load and save
- Core: Translations can be exported and imported as CSV file
- Core: Cron jobs don't deactivate on error anymore
- Billing: Fixed search query to work with amounts and numbers in regular fields

18-09-2018 6.3.38
- Core: Links didn't show when you didn't have access to one of them.
- Projects: Fixed double icon in tree
- Files: Fixed undefinded index "deleteSuccess" error.
- Files: Add "Browse x files" button in detail views
- Core: Better error message when language is invalid and fixed Norwegian
- Core: Make sure customfields and search module are installed and enabled before upgrade
- Core: Merged 6.2.104

11-09-2018 6.3.37
- Start page: RSS date was not showing
- Core: Fixed errors in check database and rebuild search index.
- Core: Global search sometimes didn't give results.
- File search: File index aborted on error
- Files: fixed search returning invalid id's and file property dialog from search
- Merged 6.2.x branch
- Address book: Batch edit problem in address book
- Address book: Drag and drop contacts didn't work
- Leavedays: Sort on employee in holidays module
- Fixed google authenticator barcode for iOS
- Support newer encryption library in notes
- Fixed custom php field

30-08-2018 6.3.36
- Fixed loading error in group edit dialog

30-08-2018 6.3.35
- addressbook: fixed merge dialog
- files: shared root folder will be seen when there parent is not accessible by the current user
- users: the list filters disabled users by default.
- users: searching while filtering will work together
- favorites: will not be rendered when there is no permission.
- dav: Performance optimizations
- core: User icon didn't show when selecting a contact / user.
- billing: Billing report filter didn't work
- files: fix acl in recent files portlet
- sync: Require sync module access for activesync
- projects: added natural sort to project name
- core: added expression support to ActiveRecord order clause
- addressbook: fixed advance search with custom fields
- core: fixed change password in user settings

23-08-2018 6.3.34
- Fixed broken XLS export
- Updated Z-push to 2.4.4
- ActiveSync, Cal-,Card- and WebDAV use new framework for authentication so that IMAP and LDAP authentication work
- Users: added disabled filter and color to show disabled users
- User: added column to see which authentication methods are configured
- Goolge authenticator enable/disable fixed when logged in as Admin
- Framework Customfield:getAttributeByName() function fixed
- Dokuwiki module compatible with 6.3
- Users grid has disabled filter and shows disabled users lighter

09-08-2018 6.3.31
- Changed install SQL for notes as it failed to rename a column with a key on some systems
- Delete buttons in dialog work
- Delete option added in more menu of grid and detail view in notes.
- Removed old z-push install dir.

06-08-2018 6.3.30
- Small UI improvements
- LDAP bind login uses full DN
- Refactored login dialog so that Firefox will prompt to save password.

02-08-2018 6.3.29
- Fixed various small bugs

31-07-2018 6.3.28
- Bug in installer made it fail on ACL error.

30-07-2018 6.3.25

UPGRADING: make sure custom fields and search module are installed.

Core:
- ActveSync, CalDAV and CardDav are now open source!
- Z-Push 2.4.2 included
- ActiveSync has no time limit anymore
- ActiveSync Spam folder syncs too now.
- New JMAP API backend
- Flux technology in frontend
- Optimized frontend client building
- New theme (All old ones removed due to incompatibility)
- Easier theming using SASS
- Simplified ACL model by using groups only. Every user has a personal group to keep individual permissions.
- Database optimizations. More clear names and defined foreign key relations
- Two factor authentication using Google Authenticator
- New UI design for Links
- Custom fields database name can be defined now
- New authentication mechanism
- Improved translation API
- Frontend Router to make pages directly accessible
- New global search
- New installer
- Removed config "init_script" Use a listener on "init" event instead.

	public static function initListeners() {
		GO::config()->addListener('init', '\GO\Awesome\AwesomeModule', 'init');
	}

	public static function init() {
		//init stuff
	}

- Improved User settings dialog
- New System settings dialog to ease the configuration of Group-Office.
- New user and group management in system settings
- Easy color and logo setting for UI.

Notes
- Uses new API
- Redesigned detail view

Address book
- Redesigned detail view

Comments
- Always use read more links and removed settings
- Remove comments from edit dialogs. Always use detail views.

15-03-2018 6.2.86
- Moved disk cache directory to file_storage_path because tmp is containerized on newer linux versions.
- Removed Object class and put functionality in Model because Object is 
  a reserved word in php 7.2
- GOLog truncate data when content is longer than 500 chars
- Remove users from ticket agent selection fields when user is disabled
- Fixed calendar display of leave days with negative hours
- Added a group field for address lists and a way to create groups for address lists. (Address book ->Administration -> Address lists -> Manage groups)
- Changed the address list filter in address book to a Grouped grid
- Changed the address list grid in email module to Grouped grid (new email ->address book ->address list tab)

08-03-2018 6.2.85
- Fixed parse error in german translation

06-03-2018 6.2.83
- Log: Added jsonData column to go_log table that contains modification info on update of activerecord.
- Billing: Order status has a checkbox to stop asking the user to sent clients a notification about the status change.
- Global: Make server email validation compatible with the client side email validation.
- Projects2: Fixed display of report button.
- Pr2Analyze: Fixed export button when no column was sorted
- Projects: Added finance permission to ACL overwrite
- Projects2: Fixed duplication of income items. Also copies item lines now.
- Users: Added mtime to grid column
- Projects2Analyzer: Fixed issue that the "Analyze" button was not shown anymore.
- Email: fixed incorrect auto linking to the recipient when message was linked to another contact or company from the address book.
- Email: Fixed display of attachments that are set as disposition=attachment

05-02-2018 6.2.82
- Fixed Add project bug
- Billing: Calculating the markup works when changing the unit cost
- Rebuild search cache works for events again.

05-02-2018 6.2.81
- Projects: separate completed and incomplete tasks
- Imapauth: Default value for imap_encryption
- Notes: Bug in encrypting notes
- Projects: Added contact field to income
- Projects: Finance permissions can be configured per permission type instead of global. Warning, some configuration might be necessary

29-01-2018 6.2.80
- Billing export: solved display of correct status_name and project_name label
- Clear links to saved e-mail that no longer exist.
- Solved PHP warning in CSV reader
- Settings module: Fixed checkboxes for renaming existing tasklists and calendars
- Email: Added tool tip on messages in the list in email module that displays in which mail folder they are stored. (Only visible when searching in mailbox.)
- Selecting employees from another addressbook then the company through globalsearch is no longer possible
- Email: Linking an email is fixed for emails that have an uuid that was longer than 190 chars.
- Calendar: Leavedays will be shown in the color of the calendar.
- Fixed encryption upgrade issue when mcrypt was not installed

23-01-2018 6.2.79
- Fixed encryption upgrade issue when mcrypt was not installed
- Chat: Set fixed version for CDN of conversejs. (Version 3.0.0)

22-01-2018 6.2.78
- Replaced mcrypt with defuse/php-encryption
- Custom fields: Fixed value when using a sum in a sum
- Projects: Add past events to project panel
- Webdav: Fixed security problem where an invalid shared folder ended up for the wrong user in webdav.
- Projects: Fixed sorting in project analyzer
- Carddav: Corrected inalid vcard version 5 in cardav database

15-01-2018 6.2.77
- Favorites/Calendar: Added tooltip to the favorite calendar store so they are also displayed in the favorites calendar list.
- Smime/Email: Fixed issue with Smime and Bcc header

11-01-2018 6.2.76
- Tickets: Added ID column to types grid in settings
- Customfields: Fixed issue that customfield limitations where not obeyed in the batch edit dialog of the addressbook.
- SaveMailAs: Fixed issue that an email is not linked the 2nd time the "link to task" menu item is used.
- Projects2: Fixed issue that a (income)contract notification will be send multiple times when a contract is recurring in the time it is active.
- Projects2: Fixed an issue that items where not duplicated when a recurring (income)contract was duplicated.
- Addressbook: contacts and companies are now sortable by address book
- CardDAV: only convert v4.0 vCard to v3.0 because this is the only version with image encoding bug.
- Contact: toVObject would generate VERSION:5 instead of 3.0
- Files: Removed hardcoded limit of 200 subfolders
- Bcc header was missing in sent items when signed with smime
- Project duplication didn't copy customer in all cases.

12-12-2017 6.2.75
- CalDAV: Email invitations works again and free busy report works now
- Billing: Added id to booksPanel so it's state is saved. (books list on left side)
- Billing: Added "project_name" column to current grid export when it is enabled in the current grid.
- Billing: Fixed issue that the required status field in the status dialog did not update based on the selected book.
- File folders from items without ACL will receive the ACL of the module.
- Contacts/Carddav: Convert Vcard V4 to V3 version due to image encoding bug
- Caldav: Set replyto email in IMIP plugin to the user's email address
- Projects2: Fixed name sorting for employees grid when displaying first name first.

30-11-2017 6.2.74
- Caldav: Korganizer failed with rrule's on caldav
- Calender: sending email to new participants only works
- Core: Added "default_text_separator" and "default_list_separator" to the config.php file so they can be set globally for new users.
- Fix: Comments are not linkable but were visible from the add link dialog.
- Addressbook: Fixed issue that employees where not moved with their company when the company changes from addressbook.
- Leavedays: Fixed icons in the status column
- Projects2: employees grid, sort on name now working

21-11-2017 6.2.73
- caldav: Rebuild icalendar files to avoid corrupted files in the database
- caldav: handle participants without mailto value better
- Comments: Fixed issue when building searchcache and the comment is empty
- Email: Fixed comparison of email "from" and "email address" so it is case insensitive. (So they will not be marked in red anymore)
- Core: added .dwg and .rvt filetype icons
- Projects2: Restored the description field in the income dialog.

16-11-2017 6.2.72
- Add invoice reference to search cache
- Performance improvements in calendar
- Calendar: Fixed issue with printing the calendar. (FPDI error)
- Core: Fixed issue with start_module config from user settings. This was broken when using bookmarks as a module.

14-11-2017 6.2.71
- Switched to quoted printable encoding for message content in e-mail. When the body exceeds 200kb in size it will switch to base64 encoding to avoid out of memory errors.
- The Max users configuration only counts for enabled users
- Projects2: Added a checkbox to apply Employees and their rates to existing projects with the same template. (Projects2 -> Administration -> Templates -> [Template] -> Employees -> [Employee] -> "Apply to all existing projects with this template")
- Billing: Added articleIdAndName function to product model
- Files: Added cronjob to notify users about folder changes more regularly
- Projects2: In the income dialog it is now possible to add multiple income lines.

09-11-2017 6.2.70
- Leavedays: some times it showed up one day too long in the calendar
- Billing: fixed markup field issue when it could not be calculated
- Fixed complete icon in Holidays module when tasks is not installed
- Removed limit from tree select customfield
- Files: Shared folders of projects are now only listed under projects in the files tree when "hidesharedprojectsfs" module is installed

07-11-2017 6.2.69
- Created php-7.1 build and repository
- Fixed create link for safari
- Billing: Removed limit from Status store
- Customfields: Added a function to customfield types that will be triggered after a related model is saved

02-11-2017 6.2.68
- Allow comment entry in task dialog after apply
- Raised order ID length to 100 in billing
- Second search in global search was broken
- Item markup will change when the item price is changed
- Project shows total percentage for budget / actual

23-10-2017 6.2.67
- Filter for project types in analyzer

23-10-2017 6.2.66
- Added "For manager" option to project template jobs.
- function customfield will not recalculate value on display
- Show parent project in task list

19-10-2017 6.2.65
- Added UK Bank holidays
- Change new Calendar component to Ext.Calendar to prevent Group-Office from thinking the calender is installed.
- German language update for Administration
- Reload e-mail message after linking or creating a task
- Keep last link search in create link dialog
- Hide time entry grid from project dialog when timeregistration module is not available.
- Sort servermanager modules alphabetically
- Added apply button to task dialog
- Increased comment database field size to MEDIUMTEXT
- Use basic auth for caldav and carddav instead of digest for broader support. For webdav digest is used by default to support windows.
- Fixed all day event bug with thunderbird syncing with caldav
- Don't update all employees when no relevant properties were updated

17-10-2017 6.2.64
- Disabled calendar_auto_link_contacts by default as it has a large performance impact
- Calendar speed optimizations
- Fixed saved export function for Firefox
- Fixed bug where saying overwrite no overwrote the file anyway

03-10-2017 6.2.63
- Fixed open task bug
- Fixed save of customcss and other event listener changes.

02-10-2017 6.2.62
- Addressbook: Country code will be displayed as country names even if only the code is in the database
- Core: Moved cache folder from temp dir to data dir to prevent cache corruption after reboots.
- Core: Added calendar javascript to sources
- Core: Added environment class to GO (GO::environment()) which has the isCli() function.
- Files: use the new GO::environment()->isCli() function in the syncfilesystem function of Folder model to log all folder names if called from the CLI.
- Copy new email to recovery email in user add dialog
- Z-push 2.3.8 upgrade
- Fixed occurences count recurrence sync in z-push
- Fixed issue when an exception was made with multiple participants and caldav
- Fixed initials when merging events in the calendar view to show the calendar user initials and not the event creator.
- Billing: Ordered order statuses alphabetically (None always on top)
- Core/Links: Added ability to filter the filtertypes of the linkWindow. (So only certain types can be chosen)
- Sieve: Fixed layout of size checkbox group in the criterium dialog.
- Servermanager: Changed default config.php file permissions of new installations from 640 to 644.

18-09-2017 6.2.61
- Addressbook: Fixed export with companies button in the interface of GO.

18-09-2017 6.2.60

- Core: Link dialog starts with blank search so latest items show immediately
- Core: Added search cache "name" value to the "keywords" value and cleanup/remove the empty texts
- Addressbook: Make addresslist linkable
- Addressbook: Refactored the addresslist grid and the addresslist dialog
- Calendar: optimized save
- Workflow: Also send notification when user approves a step
- Workflow: Fixed incorrect label copy = move in administration
- Core: Cleaned up code for forgot password functionality. 
- Smime/Email: Fixed issue that sometimes the Date is not displayed correctly
- Holidays: Fixed deletion of holiday entries
- Holidays: make use of new gotype = 'time' option
- Core: Activerecord - Added gotype = 'time' support for database "time" columns.
- Sort ticket statuses by name
- Export: When "Use database column names" is not checked, then for treeselect customfields no id is showed in front of the values.
- Projects2/Timeentries: Timeentries did not display for projects. (LEFT join on StandardTask instead of INNER join)

11-08-2017 6.2.59
- Fixed issue that GO was not building the listeners correctly.
- Optimized moving a project
- Fixed getRelatedParticipantEvents() on non object error

08-08-2017 6.2.58
- Debian package dependency failure

07-08-2017 6.2.53
- Require PHP lower than 7.1 because 7.1 is not supported by Ioncube
- Tickets: fixed search so it also searches in the customfield values.
- Added php-xml to dependencies as it's required for utf8_encode() since php 7
- Postfixadmin: Increased the size of the "alias" textarea field
- Files/Start page:Recent files grid on startpage - Added a name column.
- Log: Removed the "Delete" Keybinding for the activity log grid
- Core: Fixed creation of cache listener files. Now the file is written at once, so <?php tag is not included for a 2nd time
- Ldapauth - make is possible to chances the ldap user password. set the config to activate: $config['ldap change password'] = true;

01-08-2017 6.2.52
- Core: Added GO.form.DateFieldReset field
- Core: Check cachelisteners after module installation.
- Core: updated XSS detection so it checks for a space after the tags
- Email: show Email Selection Template dialog if you have more than 10 Email Template

18-07-2017 6.2.51
- Core: Fixed issue with the creation of listeners in the cache folder
- Addressbook: Fixed SQL error in install.sql
- Calender: fix cli export 

18-07-2017 6.2.50
- Error in German language

17-07-2017 6.2.49
- Debian packages are signed with new secure key now 0758838B
- Updated German language
- Calendar: Add setting to check if there is an event conflict with to one you try to add
- Project: Fix too short customer field
- Core/Calendar: Add config param "calendar_auto_link_participants" to toggle link calendar events to contacts
- Addressbook: Create folder for on create of a contact or company on the server by activating in the address book
- Core: go_modules table, make the id field larger so it can store 50 chars instead of 20
- Calendar: add admin permissions if it is cli to export ics
- Addressbook: Add cli addressbook export
- Core: csv fclose check
- Calendar: fix participant send meeting request
- Calendar: Fixed issue with reminders on "0" minutes that did not display the reminder icon in the calendar.
- Ticket: Expand ticket list with agent tickets

10-07-2017 6.2.48
- Fixed deliveries editor on purchase orders
- Removed trim on mailbox so that folders with space on end work
- Core: fix go number field get error function
- ServerClient: Fix update password by update go password
- Core: Fix advanced search custom combobox
- Core: Add a config parm to show a message on the login page: $config['login_message'] = 'Hello<br />We are doing an update';
- Email: Add check for link tag props need to be count == 4

29-06-2017 6.2.47
- Projects: Time entry totals in project view showed incorrect values

27-06-2017 6.2.46
- Fixed: Broken contact search
- Fix advanced search multi select custom field option

27-06-2017 6.2.45
- Fixed: Broken company search
- Fixed: Create exception in series with free busy permission was impossible
- Project2: add project merge function

26-06-2017 6.2.44
- Fixed access denied error in calendar
- Calender: resource group remove the limit on the store

26-06-2017 6.2.43
- Fixed access denied error in calendar
- Calendar: Add remoteComboTexts for resource group combo
- Addressbook: Added addressbook_ids property to contactSelect and companySelect comboboxes

20-06-2017 6.2.42
- Calendar: Update mtime of event when changing participants of the event
- Billing: fix file acl users
- Core/Files: Added stripInvalidChars check on filename when creating a tempFile
- Core: Set "engine" of temporary tables to "Memory" so they perform faster
- Projects2: Added a invoiced units column to the timeentries grid in the project display panel

12-06-2017 6.2.41
- Merged 6.1 fixes
- fix SQLSTATE[HY000]: General error: 1364 Field 'parent_id' doesn't have a default value 
- Calendar: Reminders can be set to null from now. The behavior of a reminder with a "value" that is set to "0" will be that it will trigger on the exact moment of the event.
- Chat: Add multi groups to chat view
- Tickets: Added option to show a confirmation dialog when closing a ticket through the close button in the displaypanel
- Core: Fixed issue with createlink functionality and Firefox in the Html editor
- Calendar: Fixed free busy
- Tickets: Always update "last_agent_response_time" when an agent responses to a ticket.

29-05-2017 6.2.40
- Files: Fixed issue that file handlers where always displayed, also when the user didn't have rights to the module the filehandler was added from.
- Billing: Undo custom fields to billing items
- Billing: add custom fields to billing items
- Core: Fixed some issues when using PHP 7.1
- Defaultsite/Tickets: Added password expire feature to the external ticket website.
- Z-Push install defaults to 2.3.6

15-05-2017 6.2.39
- Tickets: Improved security of ticket page
- Email: Check e-mail address spoofing in display name
- Billing: removed unique name check as article no. should only be unique
- Billing: paging in expense categories
- Files: Bug with moving files in contact permission denied while it should be allowed
- Core: added a disable and enable function for modules
- Core: debug info for adding new modules of finding modules

08-05-2017 6.2.38
- Leavedays: Fixed deleting of holidays as a manager
- Core: Removed hardcoded theme group-office when product_name is used
- Core: Added a "getOriginalValue" function in the Config.php file so we can check if a config is set in the actual config file.
- Core: Added check for "debug" in the actual config file for the descision to delete the Cache listeners
- Tools/Calendar: Added description field to admintools items and added button to truncate holidays in calendar
- Email: Multi email popup
- Addressbook: Add gotype color to fix the color search problem
- Calender: fix load resource store
- Documenttemplates: Fixed issue with document templates when the current model is not defined in the template
- Customfields: Let the api return all customfield content, so also the content of textarea fields.
- Tickets: Don't display used template in ticket display panel when it's a note
- Core: Added config option to change the "help -> report a bug" url ($config['report_bug_link']);
- Calendar/Holidays:	Changed display of holidays in the calendar so the holidays are only showed in the calendars that are set as default for the users.
											To display holidays of other users, you'll need at least "read" access to that other users default calendar.
- Calendar: Show all usernames of users who have the calendar that is in process of being deleted as default
- Core/Users: Changed alternative e-mail to recovery-email address at settings dialog.
							Also make giving the current password required when changing this recovery email address
							Make the recovery email address required and changeable inside the users module.
- Email/Message: fix Ext id
- Email/Sieve: Fixed issue that utf-8 characters did not display correctly in the outgoing email of a sieve vacation message.
- Core: Fixed some issues with the switch user module and the new "single login" feature

19-04-2017 6.2.37
- Holidays: Fixed loading issue cases by 
- Billing: Add field extra costs
- Core: Fixed length issue with footprint column in go_clients table
- E-mail encoding improvements so that hotmail does not detect it as spams

18-04-2017 6.2.36
- Core: Added functionality to force users to be able to login on 1 location at a time.
				This can be enabled by setting the configuration variable: $config['use_single_login'] = true; in the GO config.php file.
- Calendar: When trying to delete someone's default calendar, the error message will display the name of the user that has this calendar set as default
- Email: Fixed issue that the download all attachments context menu did not work in a message popup window.
- Core/Tools: Updated the getBrowser function to retreive the clients browser information
- Add jpegPhoto to upload photo to Group Office
- Calendar/Leavedays: Only show personal holidays (leavedays) in the user's default calendar.

13-04-2017 6.2.35
- E-mail sync problem fixed
- Core: Added GO.util.HtmlDecode function
- Sieve: Fixed issue with sieve vacation rule loading and special chars
- Workflow: Notification top uploader when workflow process is complete

11-04-2017 6.2.34
- Fixed approval by non admin user in leavedays
- Core: Added ip-address to lost password email
- Core: Fixed issue with processing the customfield displaypanel response when the permissions check is disabled
- Z-push bug: some devices kept syncing
- Autosize task description
- debug_usernames works with http authentication too (dav, activesync)
- Sieve/Email/Smime: Fixed issue with sieve vacation message response in email account dialog in combination with a file upload field (SMIME module)
- Core/Users: Added options to make a password expire so the user needs to update it.
							This can be done from the users module, and with a config variable: "force_password_change" in the config.php
- Core: Added error code possibility to json response on Exception
- CalDAV: Calendar will export to ICS correctly in timezones that do not apply DST

03-04-2017 6.2.33
- Updated German language
- File size is correct when opening e-mail drafts
- Chat button position changed
- Z-push unseen flag
- Projects2: Fixed setting the "Supplier" automatically when the "Contact" is selected in the Expense budget dialog.
- Addressbook: Added a default addressbook selection tab to the global settings dialog so a user can select a default addressbook.
- Calendar: The full day events imported from a ICalendar source are created in UTC timezone
- Core: Added config variable to remove the "remember login" checkbox from the login dialog. 
				$config["remember_login"]=true; Set it to "false" to remove the checkbox.

28-03-2017 6.2.32
- Fixes of 6.1.135
- Advanced search: Add reset function
- Advanced search: Fix Create go custom fields

27-03-2017 6.2.31
- Core: Added the actionSendSystemEmail function in the Core and the created  the also needed GO\Base\Mail\SwiftAttachableInterface
- Make the criteria form bigger
- Don't import categories from meeting requests
- Add autoExpandColumn to save query grid
- Addressbook: fix query panel layout by company

20-03-2017 6.2.30
- Upgraded chat client
- Merged 6.1.133

20-03-2017 6.2.29
- 6.1 fixes
- Included z-push fix states for server manager
- Calendar: Event after save acl fix 
- Solved issues with PHP 7.1 (cannot use $this as a parameter)
- Set IE document model to IE10 to fix insert image problem
- Updated Norwegian translation
- JUpload launches as java webstart application now because of the browsers dropping java support.
- Update query view
- Fixed SelectAgent for ticket combo. Loads remote to fetch only agent for the selected ticket type

07-03-2017 6.2.28
- Comments: Add the possibility to hide the original comments tabs in the adressbook module. (In settings and in config.php with the "comments_disable_originals" key)
- Tickets: Added ticket report mailer cronjob
- Core: DateTime - Added getDayEnd and getDayStart functions
- Email: Added functionality to save all attachments of an email to the files module.
- Email: Added functionality to save all attachments to a linked model.
- We've come to the conclusion that stack traces can't be logged securely. So 
	we've removed the /home/groupoffice/log/error.log functionality completely 
	since removing stack traces would make it identical to the normal apache error 
	log.
- Calendar: Fix save exception event of participants
- GO base router: Fix namespace error's

28-02-2017 6.2.27
- Fixes from 6.1.131
- ActiveSync Meeting request response fixed
- Permission denied in send meeting request
- Fixed zpush admin for 2.3.4
- Requested holidays can be edited until they are approved.

21-02-2017 6.2.26
- Merged 6.1.130
- Fixed reply in plain text error
- $config['webdav_auth_basic'] will use basic auth for cal-/card-dav and check for module access
- customfields: fix custom fields disable tab if item is undefined

13-02-2017 6.2.25
- Merged 6.1.128
- all day event spant one day more on ios with EAS
- Import all SMIME certificate aliases
- Added iterate_query for dovecot
- Tickets: Fixed issue with users that don't have access to customfields.
- fix empty timezone for users
- You need read permission for tasklists connected to calendars for caldav to get the tasks

31-01-2017 6.2.24
- Merged 6.1.127 fixes
- Fixed problem with ldapauth/imapauth where new activesync accounts failed.
- Implemented $config['webdav_auth_basic'] to switch to basic auth for webdav.

09-01-2017 6.2.23
- Fix mail follow up reminder
- Make custom field category by type of manageable
- Add count to rrule in calendar
- merged 6.1.125 fixes

02-01-2017 6.2.22
- Fixed reading of text bodies in z-push 2.3.
- Fixed mail send problem in z-push 2.3 and iOS

16-12-2016 6.2.21
- Merged fixes of 6.1.123
- Implemented Z-Push 2.3 support

28-11-2016 6.2.20
- Merged fixes of 6.1.122

08-11-2016 6.2.19
- Merged fixes of 6.1.119

07-10-2016 6.2.18
- Merged fixes of 6.1.114

06-10-2016 6.2.17
- Merged fixes of 6.1.113

01-09-2016 6.2.15
- Merged fixes of 6.1.104

25-07-2016 6.2.14
- Merged fixes of 6.1.99

19-07-2016 6.2.13
- Merged fixes of 6.1.98

18-07-2016 6.2.12
- Downgraded sabredav to 3.1 because 3.2 had too many issues
- Merged fixes from 6.1

14-07-2016 6.2.11
- Fixed error in caldav sync

12-07-2016 6.2.10
- Upgrade savbre dav and vobject and installed with composer now
- Use vcard 3.0 for photo issues

07-07-2016 6.2.9
- Return updated contact after sync when new company is found
- Merged 6.1.96 (not released yet)

23-06-2016 6.2.8
- Merged 6.1.93

21-06-2016 6.2.6
- Merged 6.1.90

09-06-2016 6.2.5
- delete contact properties with carddav works now.
- Update company properties if empty when updating from carddav 
- Merged changes up to 6.1.87

04-05-2016 6.2.4
- Caldav and carddav return etag headers now on update and create.

04-05-2016 6.2.3
- Merged 6.1.85 fixes
- Sabredav updated to 3.1
- Fixed caldav and carddav bugs

21-04-2016 6.2.2
- Merged 6.1.84 fixes
- Fixed caldav and carddav problems

01-03-2016 6.2.1
- Merged changed in 6.1
- Refactor GO\Base\Util\String to GO\Base\Util\StringHelper for PHP7

30-10-2015 6.2.0
- SabreDAV updated from 1.8 to 3.0.5
- Addressbook: Contacts remove photo from GO when the PHOTO attribute is not set in the imported VObject.
- Added an exception for when creation of searchcache record fails.




12-06-2017 6.1.138
- Core: Make setValidationError function in Model public instead of protected
- Core: Added fireEvent for validate function in activeRecord
- Projects2: Remove expenses if you don't have finance permissions

15-05-2017 6.1.137
- Calender: fix load resource store
- Documenttemplates: Fixed issue with document templates when the current model is not defined in the template
- Customfields: Let the api return all customfield content, so also the content of textarea fields.
- Email: fix mailto function subject
- Billing: Fixed download of catalogue import sample file.
- Addressbook / send newsletter: Is already running.
- Savemailas: Automatically fill in the event subject when creating it from an email

11-04-2017 6.1.136
- Add employee bug
- Files larger then 2MB didn't start uploader automatically
- Core: Fixed issue with processing the customfield displaypanel response when the permissions check is disabled
- Calendar: Fixed issue with showing tasks in a calendar view
- Addressbook: Fix repeatedly start mailing list
- Calendar will export to ICS correctly in timezones that do not apply DST
- Billing: Fixed issue with linking projects with the project select field in order dialog
-	Core/Email: Add config value swift_email_body_force_to_base64 to turn off the base64 mail body

28-03-2017 6.1.135
- Opening files from contacts failed the second time.

27-03-2017 6.1.134
- files: Fix up button
- Timeregistration: Fixed problem on deleting time entries when the hoursApproval is not installed.
- Addressbook/Customfields: Fixed problem on contact and company customfields where a duplicate ID is set for "Only from these addressbooks (IDs)"
- Tasks: Added id column to tasklists grid in "Task module > Administration > Task lists"

20-03-2017 6.1.133
- Email: fixed the delete button for account that have permissions higher than "read only"
- Addressbook: fix company combo query
- Comments: Removed the config option $config['comments_disable_originals'] and added the following 2 config options instead:
						$config['comments_disable_original_contact'] = true;
						$config['comments_disable_original_company'] = true;
- Email: Fixed disabling the "properties" button on mailboxes the user has no manage permission for.
- Timeregistration2: fix language problem by no promises on projects

07-03-2017 6.1.132
- Comments: Add the possibility to hide the original comments tabs in the adressbook module. (In settings and in config.php with the "comments_disable_originals" key)
- Tickets: Added ticket report mailer cronjob
- Core: DateTime - Added getDayEnd and getDayStart functions

28-02-2017 6.1.131
- Updated Czech language
- Addressbook: fix company add employee
- Core/Email: Implemented GO::config()->imap_sort_on_date boolean that can be used in combination with Microsoft Exchange Servers that do not support server side sort.
  When set to true, this changes the sort property from "ARRIVAL" to "DATE" or "R_DATE" if possible
- Legacy "crammd5" support for SASL auth library is now supported
- Core: Added debug info for when debug is set to true and a json_encode error occurs
- Email: Added clean_utf8 functions for email addresses that are returned to the client. (This will fix the json_encode error that sometimes prevent the email inbox from loading)
- Addressbook: Added id property to the ContactDialog with the value: 'addressbook-window-new-contact'

21-02-2017 6.1.130
- A4 Landscape print option for billing PDF templates
- Comments: Fixed printing, always collapse comments when printing displaypanel
- Core/Comments: Fixed cutting off comments within a html tag (Displaying readmore link) and added new cutstring class for it
- Linked email: Removed the edit button for liked email
- Core: Removed always throwing an alert dialog when the "fail" callback is called in the GO.request() function.
				Now the "fail" callback will be responsible for throwing an alert if necessary.
- leavedays: add company name in to the grid
- Addressbook: Add "copy to post address" button in the company dialog
- make GO::user() return null when not logged in.

14-02-2017 6.1.129
- Size mismatch error on deb archive

13-02-2017 6.1.128
- Reply in plain text mode was broken
- Billing: Added config option to be able to choose if the billing payment method will be applied on duplicate. $config['billing_clear_payment_method_on_duplicate']=true;
- Fixed bug in SASL library so it works with digestmd5 auth
- hide the tab strip item with the labels in the email account when the config option for enable_email_labals is not ON
- Fixed bug in renaming folders with webdav

31-01-2017 6.1.127
- Fixed scroll to top in projects tree bug
- Fix calender reminders
- Fix empty timezone for userless events
- Fix calender reminders

23-01-2017 6.1.126
- LDAP group sync works with Active Directory
- Update file upload
- Do not change template when the from field changed and the email is created from template
- Calendar: All day events will be all day on the same day in every timezone (no shifting but ignore time)
- FindCriteria: Changed name of useExact to partialMatch in the "addSearchCondition" function because it was coded the other way around
- Fix string to time project analyze

09-01-2017 6.1.125
- Email/Core/Debugging: Added a "debug_email" option to make sure all outgoing emails will be send to the given email address.
												This is useful when debugging Group-Office and when you don't want to send unwanted emails to active customers.
- Fix ldap code error
- Fix upload after selection
- Income billing template for description units roundup
- Normalize Carites Return Line Feed
- Fix file size chack of upload
- Fix dual upload file select window in chrome
- Add upload error on template manager
- Check model folder if not found
- Fix 'Add senders to' 'CC' and 'BCC' 

16-12-2016 6.1.124
- Added search bar to address list selection dialog via compose
- Email: Fixed issue with linking items that don't have an ACL. (Added fallback to module ACL)
- Email: Give the saved email an more unique name on disk when it's linked. (This was a problem if 2 users sent an email with a link at exactly the same second)
- Ticket Type was not saved on update fix
- Note decryption would leave the not empty fix
- Addressbook/Mailings: Increased max length of the subject of a mailing to 255 chars (was 100 chars)
- Updated Czech and French translation
- Cronjob for servermanager outputted tesseract data so mail was always sent.
- Correct spamassassin directory permissions for mailserver so sa-update doesn't fail
- Core: Fixed issue with displaying html encoded chars in the linking combo box
- Core/Export: Added a function for setting custom labels in the export.
- Fix add item in multiselect panel
- DocumentTemplates: Added filling of email subject if this is set in the email template.
- DocumentTemplates: Make the field tags available in the subject line {project:name} etc.
- Files: Moved hardcoded string to language file so it can be translated.
- Fix opening upload window and title
- Fix filter combobox in project recource and in Support ticket owner
- Email: Fixed problem of loading the "to" field (strtolower problem)

28-11-2016 6.1.123
- Exclude vendor folders from encoding in pro version.
- Fix set new password for sent mail address

25-11-2016 6.1.122
- Removed exact online module specific libraries and put them in the module.

24-11-2016 6.1.121
- Add email full reply header
- Add gotype to Modified by
- Fix user reset password mail token
- fix scroll to top by select mail folder
- Fix ticket type save
- Calendar: fixed reminders for recurring events
- fix view disk usage en quota in progress bar
- Update projects after merge contact and company
- Possible to use alias:name in e-mail templates
- When deleting a user the projects employee records are deleted too

14-11-2016 6.1.120
- Tickets: Fixed issue that when a ticket was imported through IMAP and the agent is set to the user with id 1, That the detection of who as last responded to the ticket was not working anymore.
- Tickets: Fixed issue with tickets that are unclaimed after the customer answers to it through the GO user interface.
- Fix update users email2 Unknown column
- Billing: Fix javascript infinity problem with floats for rounding totals
- Billing: Fix formula for calculation profit margin
- Projects2compat: Readonly customfield categories are no longer showed in the edit dialog

08-11-2016 6.1.119
- Signed jupload jar applet
- Email: Fixed loading of addressfields(to,cc,bcc when they are added to the params
- Core: Don't print suppressed error messages (with @) in debug mode
- Tickets: fix ticket update script (edit mtime)
- Sent newsletters are deleted together with the mailing list as permissions depend on that now.

03-11-2016 6.1.118
- Don't stop upgrade if locks can't be created. Warn about it instead.
- Fix utf8 problem, db will be converted to to utf8mb4_unicode_ci v2
- Email/Startpage: Added tooltip to the email portlet tabs so you can identify from which mailbox it is.
- Fixed broken case sensitive search
- Custom template tab for projects2compat on project will work with custom field permissions
- Project portlet can show the project name without the path
- realization of contract price projects will only sum the invoiced incomes
- fix interface ticket type e-mail notification

27-10-2016 6.1.117
- Key too large SQL error on installation occured on mysql servers with ROW_FORMAT='COMPACT'
- Tickets: Added option to only let ticket module managers reopen tickets.
- Locked maintenance upgrade action so it can't run twice
- Tickets: added agent select option to the ticketmessage dialog
- Fix update (Include InnoDB conversion too when converting to utf8mb4) script!
- Billing: fixed template footer issue with html encoding
- Timeregistration2: Fixed issue with the include_break checkbox when it's first checked and after that unchecked.

25-10-2016 6.1.116
- Include InnoDB conversion too when converting to utf8mb4

25-10-2016 6.1.115
- Email: Fixed contextmenu "Forward as attachment" that was broken when no email-template was used.
- fix email subject template switch
- Tickets: Fixed issue with users who don't have a contact model anymore and where the photo url is requested
- Files/Core: Creating a thumbnail with a very long filename didn't work (filename too long) This is solved now.
- Fix render create AT in notes
- Fix acl after submit installation dialog server manager
- fix disapprove work flow
- Fix utf8 problem, db will be converted to to utf8mb4_unicode_ci
- Billing: Payments rounded to full numbers

07-10-2016 6.1.114
- Files and Folder will not trim any attributes when saved
- Calendar update query to fix Rrule is much fasten
- Fixed displaying recurring event details dialog.
- Fixed calendar current view print
- New signed GOTA included now

06-10-2016 6.1.113
- Add other template tags in ticket  on new ticket
- Fix type template interface
- Serverclient was broken because of new tls feature in IMAP.
- Signed GOTA with new certificate
- Default value for user email2 was missing.

03-10-2016 6.1.112
- Fixed imap auth to work with tls and novalidate_cert
- Made name field of tasks larger
- Create spam folder by default
- Calendar: Fixed display of events on the last day of the month for calendar month printed page
- Fix until date field by post calculation
- Calendar: Fixed html encoding/decoding in list view and in the pdf output

27-09-2016 6.1.111
- File module infinite reload bug fixed.
- Customer and type in project global search results
- Use composer in CLI too (to fix mailings error)

23-09-2016 6.1.110
- fpdi error in billing fixed

23-09-2016 6.1.109
- Upgrade issues with IMAP accounts and calendar RRULE
- total_paid data truncated error

22-09-2016 6.1.108
- Ignore acl in update of calendar rrule

22-09-2016 6.1.107
- Updated Norwegian and Czech translation
- Postfixadmin: Added check for domain on alias creation
- Files: Added content expire date field to files.
- Billing: Fixed PDF stationery paper error
- Email: Added 2 checkboxes for allowing self signed certificates when using SSL or TLS (smtp and imap)
- Moved Swiftmailer to composer and updated it
- Email: Added TLS support for IMAP
- Billing: Don't autoselect the article from the catalogue anymore when typing in the description field in the Items grid

13-09-2016 6.1.106
- Changed UK address format
- Fixed issue with saving password in user model and email/account model that contains a space at the begin or the end (Trim disabled for these columns)
- Fix project2 financial export
- Email/Sieve: Fixed an issue with sieve extensions when no sieve is installed
- Fix reset password second email address
- Access denied error and home folder listing at contacts display
- Fixed unable to delete calendar, beforeDelete was returning false
- Add project name to project analyze

05-09-2016 6.1.105
- Reverted rrule
- Total paid was not reset on duplicate invoice
- Fix custom field HTML export displaying data
- Add in leave days disapprove reason why message
- Fix scroll by holidays
- Fix in time registration the recalculating of the duration after change the end time
- Fix remove calendar with no user
- Fix leavdays permissions check
- Calendar: Fixed delete function on all day event in the daysGrid when you are a participant on an event.
- Billing: Fixed line break issue in order ODF documents
- Email/Sieve: Disable date in the past for the out-of-office "Deactivate after" date picker.
- Updated French translation

18-08-2016 6.1.104
- Moved perl packages dependencies from mailserver debian package to recommended
- Calendar: Make contextmenu usable for full day invitation events that can be accepted. (So they can be deleted)
- Billing: make user/creator customfields available in the ODF parser
- Sieve: Improved sieve so it can also check the vacation module on the server.
- Sieve: Disable dates in the past for the out-of-office "Activate at" date picker
- Fix tree sort in projects2capat 
- Fixed install.sql query for tickets. (fresh install missed a required default value)

10-08-2016 6.1.103
- Fix number and verifier tag in ticket mail
- Calendar: Fixed RRuleIterator constructor problem.
- Calendar: Fixed RRULE problem with empty INTERVAL= properties.
- Made billing pdf listen to $config['pdf_font_size']
- Fix task reminders
- Fix bug first time create time entry

04-08-2016 6.1.102
- Removed invalid depencency from mailserver debian package

04-08-2016 6.1.101
- Excluded blob templates from the trim rule on save.
- Fixed duration with 12 hour format time in time entries
- Fixed IMAP logout error
- Set mysql mode to traditional in live mode too
- Fix project reminder_time bug
- Add move to spam folder in email context menu
- Fixed issue in billing PDF, the closing text would not display when the total are not printed.
- fix  time approve week view
- new RRule implementation for recurring events

28-07-2016 6.1.100
- Fixed delay in time entry dialog
- No longer possible to delete a user its default calendar. 
- Global search was broken
- fix php5.4 array syntax error in rrule

25-07-2016 6.1.99
- fix linking remember the search
- In e-mail add filter by flag
- Newsletter play and pause buttons will toggle and disable when sending is completed.
- Login problem for some users fixed
- In time registration by add new time entry select the project where are time is registration as last on
- Small tweaks in spamassassin config

18-07-2016 6.1.97
- Fixed bug in ticket search
- View newsletter list by address list ACL
- Fixed duration calculation in time entries
- Raised phone number field to 50 chars
- New : Add mail as a attachment to a new e-mail
- fix project template job events type display
- play and pause buttons for the newsletters will toggle each other. and both hide if sending is completed.
- trim spaces before and after database fields of type string (varchar, char). if trimOnSave = true
- fix calendar would sometimes display '@ null' when the location was empty
- fix file tree download
- fix bug file browser by reload wrong folder

11-07-2016 6.1.96
- Fix infinite loop in calendar week + day print when 2 recurring events would start at the same time 
- Fix calendar tooltip: description showing html code
- Added email validation in the email-account dialog
- Updated German language pack
- Add pear Auth/Sasl lib to support sasl auth and avoid error on PHP7
- Make HTML possible in custom field select options again.
- Enabled pyzor and razor for mailserver package
- Fix date view in note grid
- Fix remove leaveday type
- Fix: Importing a task with a duration will work
- Addressbook: Fixed display of comment when only the email field is set for contacts

29-06-2016 6.1.95
- Updated FPDI library (for PHP7 support) to version: 1.6.1
- Updated TCPDF library (for PHP7 support) to version: 6.2.12
- Calendar: Prints are now displayed correctly for events that are recurring.
- Fixed HTML encoding bugs due to previous XSS fixes.

27-06-2016 6.1.94
- XSS fixes caused some problems with rendering numbers and booleans (tasks and time registration)
- Add by appointment  in tab particpants  in the add dialog fix search and paging by address lists and user groups 

23-06-2016 6.1.93
- Home folder was displayed in items with missing folder
- Fixed various XSS security issues
- Fix batch edit folder_id error
- Used new Ioncube encoder. A loader update might be required.

21-06-2016 6.1.92
- Bug in login with old PHP versions

21-06-2016 6.1.91
- Deb Depenencies

21-06-2016 6.1.90
- PHP 7.0 dependencies
- Login issue on old PHP versions

21-06-2016 6.1.89
- Build issue

20-06-2016 6.1.88
- Fix remove user groups
- Fix mt940 split statements 
- Fix leaveday manager order for multi managers
- Removed security_token parameter from calendar invitations and address list unsubscribe link
- CTRL+F& Debug only available for admin
- Don't return the password hash and digest in the users overview anymore.
- Fixed problem that advanced search didn't work anymore when the customfields module is not installed.
- Servermanager usage fixed

07-06-2016 6.1.87
- Fix export ICS file todo list
- Projects2: Added checkbox to enable and disable to apply the status filter on the project search results
- Z-push: Removed company phone from sync because that results in 2 "work" numbers on the device which is not desirable.
- Manager filter in tickets to replace "show my tickets"
- Removed default country setting for contacts and companies
- Hide disabled users by default unless $config['hide_disabled_users']=false; is set.

25-05-2016 6.1.86
- Prevent removal of admin from admins group
- Addressbook/User: Create a user from an addressbook contact now sets the correct remote combo text for the company field.
- Fixed the addressbook selection in the favorites module.
- Fix holdPosition by reload email messages grid
- Show only the active custom fields by file batch edit
- Fixed go checker
- Tickets: added message content search to tickets search function
- Tickets: added extra parameter for searching in the ticketno. only.
- Core: Fixed sound for new emails.
- In projects 2 add parent project search
- Z-Push: Companies added to a contact on the client device are now also synced to GO.
- Core: GridPanel.js - Added check for store.reader
- PHP7 compatibility

03-05-2016 6.1.85
- Access denied on exporting private events
- Projects2 partlet filter is now editable
- Fix imap get response
- Added setting to enabled and disable popup for email and reminders separately for desktop notifications
- Fixed sound for new emails
- Wrong links didn't show up in sent items
- Projects2: Don't use the "Status" filter in the projects search results.
- Projects2: Don't use the "Show mine only" checkbox in the projects search results.
- Updated Czech translation
- Fix agent name by new tickets
- Add merge data and data check to batch edit
- Users: set thousands and decimal separator to VARCHAR(1) instead of CHAR(), this makes a space character possible.

19-04-2016 6.1.84
- Fixed billing bug with invalid column
- Updated Norwegian and German translation

18-04-2016 6.1.83
- Disabled spell checker by default. You can enable it by setting $config['spell_check_enabled']= true;
- Prevented that notes would loss there decryption hash when a file folder was added without providing a password.
- Workflow: Use of Config()->noreply_email for outgoing emails
- Z-Push: Fixed saving of tasks from mobile to server on the same day as today
- Fix importing data from vCard with type=PREF specification
- Fix function create rrule for 5.4 and lower
- Email: Strip strange chars from the link tag when sending the email
- Leavedays: By leavedays edit the remove of year credits. it is now not possible to remove them if thar already leavedays booked
- Addressbook: Fix address book by Contact to user

07-04-2016 6.1.82
- Calendar month print will calculate daylight saving time
- Subscribe function was broken
- Updated Russian translation
- Fix mail folder subscribe
- Calendar: Attendance window: 0 needed to be a possible value for the reminder. So I removed the minValue for the field.
- Add Project: Ticket types expansion
- Calendar: Added config option to disable autolinking contacts to the event that were not the organizer

04-04-2016 6.1.81
- Add by Email in 'Subscribe' dialog select all and deselect all
- Fix custom work for DG after edit leavedays module
- Language of an OrderStatus will be displayed in the same language the User views GroupOffice. If this is not available pick the first language added by the administrator.
- Added Delete button en replaced add button to begin of the blue toolbar in the projects module
- Tickets/Addressbook: Added fix for loading ticketGroup acl.
- Email: Add by Email in 'Subscribe' dialog select all and deselect all
- Leavedays: Fix custom work for DG after edit leavedays module
- Billing: Language of an OrderStatus will be displayed in the same language the User views GroupOffice. If this is not available pick the first language added by the administrator.
- Projects: Added Delete button en replaced add button to begin of the blue toolbar in the projects module
- Billing: Removed purchase order creation from the PDF button in the Order dialog.
- Fix holidays:
		type sort in gui
		year summary with 0 credit
		sum credit main grid
		add admin as manager
- Files: Disabled a . at the end of a folder name. (Webdav on Windows OS cannot handle this.)
- Email/Sieve: Added more information to sieve error messages
- Added company variable the view when running actionRegister in the site module
- Fix empty checker messages and email notification trigger only on new e-mails and more then 0 unseen
- Projects2: Fixed permission problem on contact and company when adding new project
- Tickets: Fixed problem with determining ticket agents when a user is removed.
- Fix Time tracking week number selection for slower environment
- Files: Changed up-icon in the "Default" theme and changed German translation.
- Calendar: Fixed creation of RRULE. Changed: "RRULE:FREQ=MONTHLY;BYSETPOS=1;BYDAY=MO,TU" To: "RRULE:FREQ=MONTHLY;BYDAY=1MO,1TU"
- Calendar: Added separate reminders for participants which can be set in the participation dialog.
- Fix email multi select labeling
- Holidays/Calendar: Fixed display of time and day data in the calendar list, also fixed time minutes 0 prefix.

04-03-2016 6.1.80
- Build error

03-03-2016 6.1.79
- Mail in gmail app marked as read bug fixed when using Exchange
- Fix mail selection in billing 
- Fixed calendar freeze when displaying a Leave day that had a start time and a duration with a decimal and a dot a decimal separator.
- Fixed remote combo field for holidays credit type
- Fix export "Contacts with companies" for companies in address book
- Added Pakistan to the list of countries
- Add hold position in email messages grid by reload after move
- Projects2: Fixed manager filter store so it will display all users of GO.
- Projects2: Show the projects of type "container" always in the tree when filtering on project manager.
- Removed sort and hideable option from the SelectOptionsGrid in select customfield
- Fix leavedays bugs !!!
- Fix CORE deleteFeedback for GO.grid.GridPanel
- Fix by project the reload of the subgrid after set statuses
- Fix invoices update customer information 
- Holidays: Repositioned the starttime field in the leaveday dialog.
- MT940 Parser for German DATEV standard 
- Removed libwbxml binaries for windows as they were detected (wrongly) as a virus.

22-02-2016 6.1.78
- Updated German translation
- Allow deletion of files and folders in folders that belong to a writable contact,company,project etc.
- invalid grouping in reminder window was possible
- deleting a user could cause a temporary error in the calendar.
- In file module, reset the file list (Download links) that will be add in to the mail to

16-02-2016 6.1.77
- Fixed billing upgrade script for GO 6.1
- Fix leavedays csv export
- Billing: Fixed problem that when searching orders, some orders weren't displayed in the search results. (This where orders without items)
- Remove text check for stop item. it is not compete bale wife adder languages
- Email: Fixed problem with "save mail as" functions that use plain text email.
- Fix the contacts where the company is not set by the interface bug

12-02-2016 6.1.76
- Fixed upgrade bug in leavedays

12-02-2016 6.1.75
- Updated French and German Translation
- Upgrade z-push to 2.2.8
- Calendar synced number of years back in time instead of months when no client date was sent
- Create alias on each new mailbox in postfix
- Leavedays: Always add a 0 in front of the hour when it is only one digit long.
- Projects2/Timeregistration2: Fixed timeentry default project loading
- Email: Removed autolink div and replaced it with a "normal" links div to show all links for emails at once.
- Export: Fixed the problem that a string "false" is handled as a bool "true" when selecting the checkboxes on the export dialog.
- Fixed bug with repeat header on every page in billing PDF
- Email: Only do TNEF extraction for winmail.dat files
- Files/Filesearch: Fixed context menu
- Billing: Costcode combobox: Changed list order to "code" instead of "name".
- Email: Make email-addresses in emailreciepients object case insensitive.
- Add disapprove msg dialog to hoursapproval
- Encrypt functions moved to the Util class so it can be reused
- When dragging a recurring event and then cancel the action the event will return to the original place.
- Fix multi select even by one click in calendar
- Projects2: Added a manager filter combobox to the projects tab.
- Batch edit: Fixed a problem in the batchedit grid with displaying combobox data
- Fix sorting the users in a group in the correct order
- Billing: Fixed availability of some tabs in the book dialog when a new book is created.
- Fix config check mail template

21-01-2016 6.1.74
- Fix pagination in project combox by time entry dialog
- Fix double dtstamp in ics event
- Caldav: Added check for event RRULES if they are valid, it can happen that an RRULE has an exception event for every instance (So no event is displayed at all)
- Fix the status and premises of a event in resource calendar
- Configure Spam folder with $config['spam_folder']
- Fix refresh email grid by add label
- Shared home folders were not visible

15-01-2016 6.1.73
- Fixed error that happened in php < 5.5

14-01-2016 6.1.72
- Show yourself in the holidays module if you manage other users
- Tickets export column headers improved
- Remove X-Priority header when set to normal
- Fixed needs-action status in calendar
- Remove the no german national holiday 'Rosenmontag', 'Fastnacht', 'Aschermittwoch'
- Fix email template by link mail
- Fix 'save as' mail by link
- Filesearch index will cache more characters
- Fix the link mail to a task by save as
- Calendar module change that will make it possible to filter on categories with custom module
- Fix current week selection

05-01-2016 6.1.71
- Fixed pdf renderer income selection by id
- Fix mail to by leavdays

- Settings can still be saved when the Addressbook module is disabled
- Document templates: Fixed display of Male and Female (Changed from M and F to Male and Female)
- Fixed "notice error" problem when the 'customfield' property does not exist in the cfcol.

22-12-2015 6.1.70
- Ioncube encoding error in previous package

21-12-2015 6.1.69
- Email: Fixed "total" count of emails in the email list if you use old filters.
- Fix new event resource status
- Billing: Fixed costcode grid display problem. (Global vs book specific)
- Documenttemplates: added contact:photo support
- Documenttemplates: When printing multiselect fields in a template, then replace the | with a ,
- Fix remove of mail folders and e-mail in it. 
- Calendar: Fixed display of tasks in the calendar list when using "calendar_tasklist_show=1" in the config.php file.

15-12-2015 6.1.68
- E-mail messages and attachments could be saved to read only folder
- Default and limit sync period to 6 months old on caldav and z-push for the calendar.
- Added support for ' inside email addresses. Like o'reilly@intermesh.nl etc.
- Fix upgrade workflow from GO5 to GO6
- Addressbook: Added ID column to the addressbooks grid.
- Billing: Added payment method field to the order dialog
- Tickets: Fixed problem when sending email to ticket agent and the email addresses are separated by a line break.
- Core: Added util function to replace line breaks for a given char.
- Fix find inactive tickets
- Calendar/Tasks: Fixed the display of tasks in the calendar.
- Fix permission in leavedays
- Fix install mediawiki iframe

07-12-2015 6.1.67
- Tickets upgrade query fix

07-12-2015 6.1.66
- Add new media wiki auth plugin
- Tickets: Fixed ticket system upgrade for tickets that don't haupdve a type set.
- Fix cronjob relation

04-12-2015 6.1.65
- Problem with dates defaulting to 01-01-1970
- ImapAuth: When the user does not have access to the email module, then don't create the mailbox for this user.
- Fix tasks ics export

03-12-2015 6.1.64
- Formatting Unix timestamps will work with dates before 1-1-1970
- Fix default addressbook is not set
- Import address lists
- Automatic project ID's in project templates
- Subject is configurable in e-mail templates
- Calendar: Fixed "delete" button in the event contextmenu after editing the event.
- Fix the isLate functionality from ticket system
- Fix linking of E-mail
- Updated Spanish translation

26-11-2015 6.1.63
- Adding event to participant calendar didn't work with web link and free busy permissions enabled
- Copy and rename to existing name resulted in deletion of the copy
- Added error message if files that users try to import can not be read
- Email/Addressbook: Fixed "Create email from selection" button
- Calendar: Fixed amount of days on calendar buttons in german language
- Fix new ticket with note msg “late status”

20-11-2015 6.1.62
- Email: Disable Skype browser plugin code injection on telephone numbers in the email composer. (Done with meta tag)
- Wrong check for manager in leavedays module
- Calendar: Fixed problem with showing resource customfields when enabling a resource in the event dialog.
- Core: Added "getValueAsBoolean" function to Xcheckbox
- Timeregistration2: Use "getValueAsBoolean" function of Xcheckbox to fix the problem that break cannot be entered anymore.

12-11-2015 6.1.61
- Fixed error in filesearch module

11-11-2015 6.1.60
- Quota user was missing in some cases
- When a download link is created for a file that was already shared the random code will stay the same

10-11-2015 6.1.59
- Installation fatal error fixed.
- Filesearch module has a right click menu on file just like in the Files module
- Tasks: Fixed problem with automatically getting reminder options from the global settings, (When using quickadd)
- Added ID field/column to the users grid
- Fix the created by of a ticket
- Fix for 0 hours by add holiday request

05-11-2015 6.1.58
- Tickets/Billing: Copy "vat info", "crn" and "state" of ticket->"company" to the billing order when using the "tickets->bill" functionality.
- Smime: Fixed problem when viewing "Public certificate"
- Change ACL owner if owner of model changes
- Fixed display problem of hotmail messages with inline images
- Projects import fixed
- Projects2: Only enable duplicate button when you have write permission on the parent project.
- Groups: allowed to create Group when a user (other the admin) has manage permission on the groups module
- Calendar: Users with manage permission on the calendar module can manage resource groups as well
- Calendar: Enable the delete button for events you are invited for.
- Fixed wrong display of special holidays that are defined in the holiday file.

22-10-2015 6.1.57
- Z-Push 2.1.5 update
- Projects2: Added parent project path to the project edit dialog.
- Added missing params column in go_cron table.

20-10-2015 6.1.56
- Cron Class not found error fixed

20-10-2015 6.1.55
- Export button in in File search module is working
- File Search will search recursive when a folder is selected.
- Get the correct quota in the quota bar when another user's folder is selected
- Right click in the search result of Files module will give more option in the context menu
- System files will not try to added quota to a user for older Versioning files
- Option in billing to auto set the status of an order to Payed when enough payment is added
- Option to hide the public calendar URL
- Cron jobs can have parameters via the GUI.
- Create event via web link for GO users
- Z-Push 2.2.4
- Email: Fixed problem when emailing with only BCC address and debug mode is set to True.
- Projects2: Enable and disable the correct items in the context menu based on the user's permissions.
- Status of an order will automatically be set to the paid status when enough payment is added
- Project will not set values from its parent if the field are not available in the select template for the new project
- Fix error message that is showing when installation is disabled in the config.php file
- Projects2: Disable the "Add project" button when the user wants to add a new project to the root node and doesn't have manage permissions on the projects2 module

09-10-2015 6.1.54
- Invalid contact color fixed
- Projects2: Added parent permissions check when adding new project.
- Projects2: Use the parent permissions to disable/enable the "Add project" button
- Won't reset the Project display panel when added a new project anymore.
- Deleted user groups left some garbage in the database
- Email: Added functionality to decode uuencoded attachment in emails
- Ticket import, increased the cc_addresses field to fit more email addresses

06-10-2015 6.1.53
- Added Cron task for Correcting the quota user (will execute ones after this update)
- Quota will be added the the user that owns the home folder the file is placed into
- Filesize of older versions will also be added to the quota of the owner.
- Recalculation will recalculated based on above changes.
- Added prefix for contact and company images.
- Use date sent instead of date received as default column in mail
- Use Base64 encoding for mail body to improve performance and memory usage
- Inviting a participant to an exception of a recurring series didn't work

02-10-2015 6.1.52
- Fix for password hash in older php versions < 5.5.
- No infolog message when logout is called for a user that's not logged in.
- Projects2: Fixed automatic entry of project manager (current user) when creating a new project.

24-09-2015 6.1.51
- Projects2: Fixed template events bug cased by change in SelectEmployee combo
- Projects2: Changed the taskspanel collapse button to the GO default. This solves the problem of not being able to hide the panel when the panel itself is disabled.
- Holidays month report grid will show correct weekend and week numbers for each month
- Holidays that are booked over 2 months will show correctly in month report grid
- Email: Fixed problem with UUencoded attachments in some emails.
- Billing: total amount paid will be set to 0 when duplicating an order

18-09-2015 6.1.50
- Updated Spanish translation
- Files: file quota will be added to the owner of the home folder the file a placed into
- Holidays: Year credit column is back, Editing year credit with double click works, total hours are calculated from start + end time

14-09-2015 6.1.49
- BETA: Outlook 2013 support for ActiveSync. For testing only!
- Duplicate links option for billing
- Addressbook: fixed display of telephone number in the company employees tab.
- Last ID field of an order book no longer uses thousand separator
- Empty error when entering weak password
- warning about closure and callable
- use password_hash function if available (PHP 5.5+)
- Email: Disable moving the INBOX folder itself to a subfolder For MS mailservers that allow this
- Fixed sieve date operator combobox loading for existing criteria
- Enhancements in holidays module.
- Calendar context menu removed for items that are not calendar events

02-09-2015 6.1.48
- IMPORTANT: Fixed security problem with LDAP authentication. If you use this you must upgrade immediately.
- Calendar: Changed permissions for calendar categories.
		Global calendar categories now still have permissions.
		Calendar specific categories now have the same permissions as the calendar itself instead of own permissions.
- Files the quota bar will show the quote of the owner of the folder that is viewed
- Remove PHP customfield type from Group-Office core and change to database to use Phpcustomfieldtype module
    NOTE: if PHP customfield was used install the "Phpcustomfieldtype" module to continue using it
- When deleting a user his calendar and calendar views will be removed as well.
- Email: Added a checkbox to the account settings to manage if the signature is printed above or underneath the reply message block.
- Sieve: Added currentdate sieve functions

27-08-2015 6.1.47
- Log entry of projects2/template-icons on each login removed
- Add button in tickets and notes disabled if no category / type was selected.
- Raised department field of contact to 100 chars
- Core: Added error message for when a linkhandler cannot be found
- Calendar: Fixed cases for enabling the Delete menuitem in the calendar event context menu.
- User::sudo() will work without being logged in.
- Saved exports: make label sortable in the column chooser

25-08-2015 6.1.46
- Change user in time registration fixed
- Caldav: added missing table "dav_calendar_changes"
- Fixed problem with posting multiselect customfield in the notes module

20-08-2015 6.1.45
- LDAP disk_quota support by running it as admin
- Select project manager bug
- Create new acl lists when copying folders
- Tasks/Schedule call: Fixed several issues with the schedulecall dialog
- Set always_populate_raw_post_data to "-1" for z-push resyncing problem in PHP 5.6
- Common: Fixed reminder sound for Default and Extjs theme
- CalDAV will look at the calendar version and uses this is a sync token instead of mtime + count
- The manager field is searchable in the project dialog
- Addressbook/Email: Fixed sending of newsletters when imapauth module is used and the config for saving passwords in the DB is set to False.
- Search within billing items
- Calendar: Fixed calendar selection in event "move" dialog when an event is edited before.
- Customfields: Added a read only text field as datatype
- Disapproved time entry can be edited and will be set to closed afterwards
- Fixed compatibility with php 5.3 code in smime module.
- Don't ignore application/applefile
- Unnecessary sync of template items folder removed
- Fixed export in activity log
- Include init_script if set in $config['init_script']

10-08-2015 6.1.44
- Select all users as project manager
- Autocomplete in custom select field
- Delete reminders with their parent object
- reminder sorting fixed
- Use search query in total value for invoicable projects
- Year Credit can be created if you have manage permissions on the leavedays module
- Sieve: Removed auto adding of "Stop" for rules other than "Spam".
- Sieve: Make sure "Spam" rules are always the first.
- Creating invoice from time entries (post calculation) can insert comments in invoice item using {comments} tag
- Change Z-Push install script to download the latest 2.2.2 version
- Added "Show Mine Only" button to Projects V2

03-08-2015 6.1.43
- Comment option removed from link dialog
- Updated norwegian language
- Removed x-ua-compatible meta tag from all themes
- Drag multiday event in month grid for second day will create correct exception
- Fixed Softaculous issue with installing

27-07-2015 6.1.42
- Select the first visible item in a multi select list when none selected (Tasklists, Notebooks)
- GOTA Login dialog did not appear anymore when logged out in Group-Office
- WebDAV locks stored in database for better performance.
- Fixed issue where the VALARM of a VEVENT was missing the hours in the local timezone difference on full day events
- Fixed bug in billing items autocomplete
- Brought back new sub item in projects2
- Added start date to sub projects grid
- Fixed full view of profile image
- Added URL to vcard export
- Double substraction of a minute on all day event bug fixed
- Support for multiple EXDATE values comma separated in ICS files
- Theme selection: Fix for when Group-Office is branded that the Group-Office theme is not found anymore.
- Billing: Itemsgrid - Height of items description editor in the items grid is automatically changed to the height of the cell.
- Added workflow information to the event display dialog
- Removed PHP5.4 short array declaration method from smime CertificatControlller
- Fixed bug in calendar week print when an event would run from before 19:00 till after 07:00 the next day
- Fixed bug where event duplicated on recurring events with exceptions
- Improved etag values for caldav everywhere
- Replaced Flash notification sound player with a HTML5 version.
- Files: Added a download button to the files grid.
- Sieve: Fixed problem with saving of Out of office tab when sieve file was empty initially.
- Added relational data to Group.php and UserGroup.php
- Sieve: Fixed problem with the Out of office tab "Aliasses" field when putting in multiple addresses on multiple lines.
- Bookmarks: Make local urls also available to add as a bookmark.

17-06-2015 6.1.41
- Improved etag values for caldav
- Removed funambol all day flag as it causes problems with thunderbird all day event changes
- Set project field when creating linked invoice from project
- Applied new task colors to links and start page as well
- Sieve: Fixed index problem with sieve rules

15-06-2015 6.1.40
- Updated Czech and German translations
- Billing: Added tax_code display to the tax column in the items grid
- namespace problem in ttf fonts for tcpdf
- Sieve: Fixed sieve out of office problem with multiple aliases.
- Sieve: Move the "Out of office" rule always to the end of the sieve file.

10-06-2015 6.1.39
- Email: fixed importing VCards in Email attachment
- Calendar: Undo the recurrence not correct exception because it causes the calendar for not being loaded at all.
- Tasks: Fixed loading problem of the tasks grid. (Sometimes the loadingmask kept hanging)
- Email: Composer will reload signature without full reload
- Email: Labels context menu will change when Labels are changed without full reload
- Sieve: Removed "Stop" from default "Out of office" rule
- Billing Order item description field autocompletes the description of a product as well

05-06-2015 6.1.38
- text attachments displayed inline only when no disposition=attachment is present
- Sieve: Updated German language
- Billing: Items Grid: Disabled "getEditorParent" because of problem with Chrome browsers for not being able to open the texeditor anymore
- Duplicating a project saved to soon, causing validation errors with unique custom fields
- Projects2 analyzer: ACL permissions would cause total time entry duration values to multiply
- Fixed isImage detection for uppercase extensions
- Projects2: Added invoiceable option for project income. With exclamation mark in grid
- Set combobox text in contact field on the Schedule call dialog
- Projects2: Added Duplicate button to Project Panel, added Edit button to Tree ContextMenu
- Groups: Fixed error message "Failed to run delete from model" problem with deleting users from groups.
- Sieve: Added default alias always to the sieve alias(:addresses) line
- Sieve: Added reply time to the out of office panel.
- Sieve: Restyled  out of office panel with an advanced properties section
- Fixed missing 'incomplete' column when sorting tasks on 'completed at'
- Added "length" parameter to the debug action so a longer log can be requested if needed
- Fixed namespace problem with Exception class in Store.php file.

26-05-2015 6.1.35
- Projects2: Expense Budget will save the contact and supplier
- Email: Labels can be added when the email account is selected
- Searching recursive in email folders will show correct total number of mails.
- Reminders: order the list on the vtime property
- Files: Fixed copy and paste functionality over different file browser dialogs/windows
- Invalid utf8 when creating short usernames in the calendar

20-05-2015 6.1.34
- Updated VObject lib to 3.4.3
- Fixed DTSTART value in VTIMEZONE object when exporting to ICS
- IMAP communication bug with empty body
- Fixed display of status Open in ticket never close status settings
- Display html and text attachments in view

13-05-2015 6.1.33
- Added config option for max thumbnail-able image file size (set default to 10MB)
- Calendar: Fixed display of tasks without start_date in the calendar
- Email em_labels table engine was changed to InnoDB
- Projects2 Expense budget has more field and dialog is updated
- Projects2: unset invoice_number when contract is duplicated
- Process SMIME handlers on reply too but hide encrypted body.
- Updated German translation
- Set language and timezone when user logs in with dav
- Various caldav related fixes
- Billing: Fixed problem with "Enter" in the billing items description field to start a new line.
- Added "Download selected" option in the context menu of the file manager. (Creates a zip of the selected files and presents the download dialog.)
- Added a "Limit"(Maximum size) option for the zipped files file size. ($config['zip_max_file_size']  = 256000000;)

07-05-2015 6.1.32
- Drag and drop projects from grid to tree
- Added custom fields to projects portlet on start page
- Added export of subprojects
- Fixed short array syntax problem.

07-05-2015 6.1.31
- Added z-push config constant for modules/z-push/config.php define("BACKEND_GO_DEFAULT_BODY_PREFENCE", SYNC_BODYPREFERENCE_PLAIN); to solve blackbertt password problem:
	https://forums.zarafa.com/showthread.php?10884-BlackBerry-Passport-does-not-get-HTML-email
- Check available storage client side in files upload dialog
- Files tree will expend when a favorite folder item is selected to enersable Up button
- Updated Czech and German translations
- Use putenv to fix charset issues in documents module
- Add config.php option "zpush_always_send_attachments" for testing: http://talk.sonymobile.com/t5/Xperia-Z1-Compact/Z1-Compact-Problem-With-EAS/m-p/866755#11220
- Support multiple email aliases in SMIME verification
- Sort on manager in leave days module
- Added default color set to GO.form.ColorField, removed custom colors from calendar ColorPickerDialog
- Email invites: events can always be updated from an email and a link was added to open the event
- Sieve is working without SSL domain check in PHP 5.6
- Add random $salt to user password for PHP 5.6
- Leaveday: would give an error when trying to delete a Year Credit
- Multiselect group will not select first item on reload when nothing was selected (see groups in user module)

29-04-2015 6.1.30
- Task start time is now optional
- IMPORTANT: z-push 2.2.1 support. Upgrade z-push too after this upgrade!
- Wrong custom field function result in rare cases
- Permissions for calendar categories on upgrade are set to owner now
- Changed the calendar import success message to an info dialog instead of warning.
- Files: Added username, musername ans locked by to file properties dialog
- Zpushadmin: Fixed notice error with defining version file multiple times
- Removed row limit from visible groups in the User dialog's permissions tab
- Tickets: Fixed display problem with  the status combo in the message dialog
- Tickets: fixed problem with comments "browse" button in the display panel
- Fix sort order by name in addresslist contacts
- Email: Fixed problem with "label" column when changing account.
- Tickets: Added total ticket count to ticket statusses

13-04-2015 6.1.29
- Billing generated invoice numbers can have the year prefixed with 2 digits as well using lowercase %y
- Sending Newsletters will not remove spaces in encoded characters
- Projects/Start page: Fixed status filter problem in the projects startpage widget.
- Set session cookie to secure on SSL connections
- Calendar: Exception on the first day of the recurrence didn't work
- Link of second email reply not automatically linked
- Tickets: Fixed html encoding problem for type combobox in the ticket dialog
- Projects: Added contracts functionality for income.
- Comments: added category column in grid
- Tickets: fixed issue when creating invoices from tickets
- Smarter reminder import for events and tasks
- Don't stop importing on large vevent objects
- Export error in tasks when custom fields module is not installed
- private flag synced with caldav
- Icalendar export ordered by start time to avoid recurring event problems.
- Disable copy on calendar invites
- delete exception on all day event not working for caldav
- SaveMailAs: Added the plain email body to the tasks description field.
- Email: Some email attachments would not show in exceptional cases
- Tickets: Fixed store call to billing/costcodes for "administration->rates" costcode combo.
- Customfields: its now possible to create more then 127 categories, field was tinyint(4)

30-03-2015 6.1.28
- Community with just billing license didn't work
- Calendar: Disable the recurrence tab when editing a recurrence "child" event.
- Projects2: Task will use default working week to calculate due date when user is not set
- Tasks: sorting tasks by project works with Projects2 now
- Leavedays: only users with write permission to the leavedays module can delete booked leave days
- Added $config['calendar_disable_merge'] = true; to disable merge of events in calendar view
- Bookmarks: Fixed problem with showing bookmarks without having a grid state set in the database.
- Cron: Fixed namespace problem with GOFS Cronjobs
- Tickets: Changed the ticketNumber to a static value for the email subject. So removed the ticket number prefix from the subject property of the ticket settings.
- Tickets: Added a fix to check if a cc email address is set that is also used as a import mailbox for tickets

18-03-2015 6.1.27
- Fix missing manage permissions for calendar category owners
- Creating a task from email in Thunderbird works
- Task linking with projects is now compatible with Projects2
- External fee of a time entry will be recalculated if the activity type changes
- Calendar portlet: Don't display the events in the appointments widget that are not shared anymore to the user.
- Notes: Fixed reset of dialog when getting an encryption password error message.
- Documenttemplates: Added button "Email from template" to the "New" menu in the displaypanels for items.
- Summary(Start page): Fixed problem with removing group "Everyone" from announcements.

13-03-2015 6.1.26
- Projects2: Controller action that takes over closed weeks from Projects v1, also during Projects2 install.
- Improved error message of cron when ioncube is not installed
- Fixed duplicate key error when creating projects
- Holidays: Added 5th of may as a holiday every 5 year (2015, 2020 etc. ) (Only in Dutch holidays)
- Bookmarks: Added extra column view to bookmarks
- Addressbook: Fixed measurements of contact Photo thumb
- Billing: Removed faulty costcode controller file
- Mail/Sieve: Added check if the mailbox extension for sieve is available on the mailserver
- Files: Removed acl check for diskquota when deleting a file
- Files: Added check for file already exist in addFileSystemFile

06-03-2015 6.1.25
- Email: Fixed namespace problem with sending email.
- Files: Fixed problem with deletion of files when deleting them one at a time. (Sometimes the file was not deleted from disk after that.)

05-03-2015 6.1.24
- fixed unlink permission denied error on windows when sending email
- New config option $config['disable_mail'] = true; to disable all mail sending
- Calendar: Added pager to categories selection in the event dialog
- Email/Sieve: Added possibility to check for sieve capabilities on the server.
- Billing: Fixed problem with duedate calculation if the bTime field is empty

02-03-2015 6.1.23
- Fixed GOTA on windows servers
- JsonPost validation and fixes for the TabbedFormDialogs
- Event store will not try to search for events when calendar is not found
- Use OPENSSL_CIPHER_3DES for smime encryption
- Billing: Fixed problem that the trackingcodes or costcodes are not saved when only editing one of those fields in the items grid.
- Billing: Fixed display of 0 in the pdf_template and odf_template fields of the order status when they are not set
- Tickets: Fixed enters in email templates for mails that are send to the ticketagent.

26-02-2015 6.1.22
- Billing: sales agent names optional in order grid.
- Address book: pagination for addresslist select when setting up a new newsletter.
- Calendar & Tasks: fixed displaying task details in calendar list view.
- Email: Fixed bug in emailcomposer that didn't apply the changes made in the "Source editor" when saving/sending while the composer is in source edit mode.
- Strip non ascii chars from message-id in mail to workaround Incredimail bug
- Billing: optimized autocomplete in trackingcode combobox of the itemsgrid  
- Email/Sieve: Out of office saving dates conversion bug fixed.
- Tickets: Email to agent is now of content-type "text/html"

20-02-2015 6.1.21
- Updated French translation
- Tasks: Fixed loading of displaypanel after closing the "Continue task" dialog.
- Sieve: Fixed form validation of "Out of office" tab when creating a new account.
- Namespace bug in sieve module
- Restrict deletion of project templates that are in use.
- Tickets: Fixed wrong ticketcount
- Tickets: Sort agents for "change responsible agents" on their names
- Leavedays: fixed display of estimated leave hours (sometimes it was not shown).
- Leavedays: display used holiday hours in the leave days in the calendar module.
- Leave days / holidays: Year credit export button for admins and holiday managers.
- Fixed linked items when using document templates.
- Better font display in email client.
- Tickets billing bug fixed
- Better filename when saving mail to PC

18-02-2015 6.1.20
- Billing: Fixed and optimized the trackingcode and costcode selects in the items grid. Added autocomplete to the select fields
- Fixed updating calendar event from external email client
- To utf8 bug in active directory
- Email: Added $config['email_autolink_companies'] so email will also be linked to 
         the company when a contact has a company relation.
         ($config['email_autolink_contacts'] also need to be set to true)
- Sieve: Added :create after the fileinto action. This will autocreate the folder if it doesnt exist.
- Sieve: Fixed "Mark as read" sieve rule.
- Sieve: Added new out of office tab in the email settings dialog.
- Projects: don't focus project field on editing
- Cron job doesn't send mail but logs warnings

09-02-2015 6.1.19
- Brazilian language Saturday is UTF-8 encoded
- Comments: Changed order column of comments from ID to Ctime
- Email: Added properties button to the email client which will give the user direct access to the account settings window.
- Files folders were always created for projects
- project template create error
- Login bug on windows servers
- UTF-8 conversion is done for ActiveDirectory LDAP servers
- LDAP_OPT_REFERRALS is set to 0 by default for LDAP connections
- Leaveday module manager may change the YearCredit and approve registered leave days
- Active Directory auth fix: LDAP_OPT_REFERRALS is default set to 0

26-01-2015 6.1.18
- Autocomplete failing after moving email composer fixed
- Search with utf8 characters in address book was broken
- Fixed font-style display in some emails

23-01-2015 6.1.17
- Export timeregistration error fixed
- Include ID's in columns for export
- Fixed SMIME decryption of outlook messages
- Fixed IE bug with colorfield in company dialog
- Support repeat on workdays of thunderbird in caldav
- Fixed caldav bug related to case sensitity in the UID database fields
- Document templates failed with special characters in the name on some servers.
- Merge events in calendar was broken
- Ignore unsupported reminder values in icalendar
- Delete task bug in community version
- Uninstall of module did not work completely. Some installed checks still returned true.
- Wrong parent ID in z-push mail folders which prevented sub/sub/sub folders from syncing 

19-01-2015 6.1.16
- Resources not showing

19-01-2015 6.1.15
- $config['calendar_tasklist_show'] option is merge from 6.0
- $config['debug_usernames'] for turning on debug mode for specific users.
- Replace recurring series completely with caldav to avoid problems of buggy clients
- Repeating event with caldav that started on 0:00 hours repeated on wrong weekday
- Replace contact with user in calendar event adds appointment to calendar now.
- Fixed open task from context menu in calendar
- Projects2: button to duplicate incomes and expenses.

12-01-2015 6.1.14
- Billing license check error

07-01-2015 6.1.13
- License check failed in 6.1.12

07-01-2015 6.1.12
- Removed project time entry overlapping check
- Replace namespace delimiter with dash in listeners cache
- Fixed to field in email sent folder in wide view.
- Restored windows binaries for zip and libwbxml
- Automatically delete file when download link expires is available again
- Projects2: added column 'customer' for sub projects.
- Timeregistration: If time is next day was checked the field will be checked when editing the time entry
- Billing: fixed due date for new created recurring events
- Cut attribute lengths when importing tickets
- Deleting task category deleted tasks too.
- Permission error when creating a project in the root and template inherits type from parent
- Notes: Fixed problem with creating notes from the "New" menu in other modules
- Correct week number in disapproval email of time entry
- Custom fields: fix to display custom fields of type Heading.
- Custom fields: fixed on-click event of Contact/Company custom fields.

15-12-2014 6.1.11
- Fixed mail send error that occurred with php < 5.4

13-12-2014 6.1.10
- Invalid build 6.1.9

12-12-2014 6.1.9
- Shared files tree regenerated cache when item on ACL is delete
- Fixed when Editing a time entry dialog or added a time entry from the project's task list the task-field was empty
- Active not category was not selected on new note
- Save email as note was broken
- Copy links when task recurs
- Disabled acl overwrite checkbox if you don't have manage permission
- Fixed disappearing event from month grid after second edit
- Billing: Added tracking codes also to the order book cost codes
- Filesearch has a limit of 3 minutes to read a file for indexing.
- permissions panel not set after save of note book
- Move event via context menu back in time failed
- Copy customer and contact from parent project if set.
- Only save to email to sent folder when at least one recipient succeeded.
- Select project was rendered to small in some cases
- Overtime hours columns in projects2 will be added with default value 0. if you already have the columns the values will not be changed
- Canceled calendar items will be semi-transparent in IE7 and IE8 as well
- Projects2: Added option to attach a new project to items in GO from the  "new" button. In case of creating a project from an order, the customer and company will automatically be filled.
- Billing: Changed template for the Contact select field, contacts are grouped by addressbook and the company is displayed behind the contact.
- Tickets: Changed button url for external ticket page to "newTicket" instead of "ticketList"
- Projects2: Project names cannot contain slashes.
- Core: fixed timezone setting for Z-Push sync.
- Workflow: namespace fix.
- Addressbook: Added vcard export option to the addressbook export menu. The contacts of the current contacts grid are exported.
- Hour approval: if an entry get disapproved the user will receive an email about this.
- Hour approval: Week list will remember the week you are working in when approving entries
- Timeregistration: Weeks/Months will show a red cross if it contains disapproved time entries.


03-12-2014 6.1.8
- Disable paste upload as it doesn't work with current chrome anymore
- Change note password fixed
- Ignore class not found in database check when custom field categories of old modules exist.
- Added task ID to grid
- Groups were limited to user pref in user dialog
- Time Tracking: No overwriting travel distance with default distance.
- Hour approval: Managers do not need to be member of the projects to approve entries. When they are manager of Hour Approval
- Hour approval: When hour approval is installed. Closed entries may be change by mangers of the Hour approval module as well

28-11-2014 6.1.7
- Billing: Improved Costcode and trackingcode selection in the order items grid
- Dropbox: Implemented new api for dropbox 

26-11-2014 6.1.6
- Projects2 - Fixed wrong check for internal and external rates with project resources
- TimeEntry Dialog used the http POST method for submitting tasks 
- Calendar: When adding participants, check if participant already can be found, if so then update the existing one, otherwise create a new one.
- CalDAV tasks have support for "% complete" and "Priority"
- CalDAV calanders will resync when tasks linked to the calendar are updated

21-11-2014 6.1.5
- Hide financial data in projects
- Calendar: Fixed double deletion of events when removing a calendar.

20-11-2014 6.1.4
- A project container has status "None" on new installations that is created when Projects is installed.
- Possible to add Human Resources to the Project Templates
- Files in the Display panel will open in click and show properties when clicking on the info icon.
- Separated the "filterable" and "show in tree" checkboxes in the project status
- Added overtime rates with ability the enable them for each resource to Projects.
- Incorrect message-id in sent folder of email
- Fixed error when moving projects
- Fixed template limit of 30 in select for new template action
- Projects2: Added finance permissions tab to the settings dialog so that the users who can see the financial data of projects can be managed.
- Address Book Advanced Search: now possible to search by ID.

13-11-2014 6.1.3
- New licenses failed
- Updated German

12-11-2014 6.1.2
- Set max_execution_time to 0 in download action

11-11-2014 6.1.1
- Initial 6.1 release
- Changed back to the old licensing system because the new system was confusing people.

11-11-2014 6.0.35
- Filesearch: Fixed namespace issue in the filesearch cronjob
- Address Book: Fix for IE11 color picker bug in Contact Dialog.
- File Search: Fixed searching for file types.
- Email: Fixed problem with enable/disable templates button when the user has no permissions to use the addressbook module
- Billing: When duplicationg orders from one book to another, check if one of the two books is a purchase order book. When one of the 2 is a purchase order book then swap the unit_price with the unit_cost parameter for every order item.
- Billing: Added contextmenu to order grid that adds the functionality to change the order status of the selected orders.
- Calendar: When importing an ICS file with an appointment that has a recurrence pattern 'last X of month', an error message is given but the import continues.
- Documenttemplates - Fixed Exception that was throwed when no customfieldrecord could be found
- CalDAV will except slashed in imported events

06-11-2014 6.0.34
- Fixed broken email viewer in files module
- Fixed ODF generation in billing
- Trim leading backslashes in autoloader
- Script compression sent wrong headers which broke script loading in some rare cases
- Email: added new config property to the emailcomposer to disable the template button (config.disableTemplates)
- Tasks: Added pagination to the category select combobox
- Fixed AbstractController::getRoute() function with namespaces.
- Accept YYYYMMDD format when importing vCard with birthdays
- If fs/Folder fails to copy (because destination might be inside source folder) the already created folder record will be deleted 
- Added clarification for CalDAV tasklist sync to the settings->synchronization panel
- Calendar: fixed warning message for the event accept external page
- Site module: Fixed some namespace problems that broke the site treepanel
- Saving ProjectV2 TaskGrid will submit all grid data else the grouping functionality will break


28-10-2014 6.0.33
- Sorting users respects sort of name order
- Billing, added functionality for multiple payments per order. This update will also convert existing payed orders to the new method.
- Billing: Fixed display problem of costcode combos: Changed displaying "code" instead of "name" in the combo
- Fixed problem with attachments in the \tmp\attachments\ folder that didn't get removed after sending an email from a mime source

23-10-2014 6.0.32
- Nexus sends no search folder. We default to inbox search now.
- Sieve rules parser updated
- Address Book & Custom Fields: fixed advanced searching Treeselect custom fields in Address Book.
- The tasks shown in the calendar will use the start time of the task
- permissions of key.txt invalid after fresh install
- Added full text search option to quick search
- Billing: Fixed menu item New > Invoice/Quote.
- Projects2: Template Events of type "job", "task" and "project".
- Brought back amavis anti spam and anti virus solution for mailserver.

20-10-2014 6.0.31
- Fixed quota calculcation for mailserver
- Fix for self referencing constant
- Projects2 : Return of the Action (a.k.a. Task Template, a.k.a. Template Event) of type Project.
- Fix for Excel HEAD request "Microsoft Office Existence Discovery": respond with 501 Not Implemented.
- Improved ProjectsV2 tasks grid load and save speed when there are many tasks
- Billing: Added due date for orders. Also added 2 template variables that can be used in the templates: {due_date} {due_days}
- Renamed Mwst to Ust. in German language file.
- Install bug in servermanager due to namespaces
- Remove quotes from SINCE and BEFORE queries to IMAP server as in RFC1730
- Company does not have to be in the same address book when creating user from contact. (company will move to selected address book instead)
- System test: fixed checking of max upload size
- Calendar: Name the exception event if calendar import is from Outlook.
- Calendar & Email: Fixed bug for creating emails from calendar events.
- Z-Push 2.1: Fixed timezone bug in iPhone task sync.
- Tickets: Fixed report: count also tickets in status Open.
- Calendar: Fixed calendar name positioning in month view Print.

08-10-2014 6.0.30
- save attachment bug
- Calendar month print will now show events between before 08:00 and after 17:59
- Fixed summer- / wintertime issue in the calendar's month print
- Calendar: Event context menu, disabled copy of private events

07-10-2014 6.0.29
- Signed GOTA jar file unit 2016.

06-10-2014 6.0.28
- Email: Fixed problem with saving attachments of linked emails.
- Fixed syncml namespacing bugs
- Fixed problem with file uploader when no user quota was set.
- Dropbox: Fixed problem with finding dropbox api files (namespaces)
- Google drive: Fixed problem with finding Googledrive_service class file
- Fixed problem with timeentry status that was automatically appoved even when the hoursapproval2 module was installed.
- Project display bug

01-10-2014 6.0.27
- Quota fixed
- User import broken due to namespaces
- Fixed problem with &$this variable that is passed to the listener functions (Changed to &$self) (Only breaks in Ioncube encoded files)
- Modules: Make sort order field sortable in the grid
- Added comments panel in the dialogs of the following items: company, contact, event, note, task

22-09-2014 6.0.26
- Small fixes

22-09-2014 6.0.25
- Public release
- Removed config.php to prevent upgrade issue
- Fixed problem with creating new sieve filterset.

19-09-2014 6.0.24
- Forgot to commit one fix

19-09-2014 6.0.23
- Billing template logo bug fixed
- Merged v5 fixes.
- added selectRecord function in the multiSelectGrid component.

17-09-2014 6.0.22
- Fixed table cal_events "files_folder_id"
- Added sort order field in the modules grid
- customcss: Make public uploaded files accessible without logging in to GO.
- Improved invoicing in projects2 module.
- Prefix and Suffix in custom field.

04-09-2014 6.0.21
- Bug in invoicing from projects

03-09-2014 6.0.20
- Bug in invoicing from projects

29-08-2014 6.0.19
- Invoicing for projects2.

21-08-2014 6.0.18
- Added icons for male and female
- Added colorpicker to contacts and companies. With this colorpicker you can set the text color of the contacts and companies in the Grid.

18-08-2014 6.0.17
- Integrated WebODF editor for online odt documents.

14-08-2014 6.0.16
- Fixes for customer module.
- Default theme fixed.

12-08-2014 6.0.15
- Fixed public symlink
- Fixed dovecot auth for postfix

12-08-2014 6.0.14
- Fixed install bug
- Removed spamassassin, amavis and clamav integration. Manual installation works better.

12-08-2014 6.0.13
- Namespace bugs
- Fixes of 5.0.77

12-08-2014 6.0.12
- Lists: Increased timeout for importing lists so the javascript doesn't throw an error after 30 seconds.
- Lists: Make deleting of records more efficient by setting cascade on the database table.
- Lists: Added maximize option to lists browser window.
- Timeregistration2: Fixed editing of existing time entries (showEditDialog call)
- Lists: Saving dialog state of listrecord dialog and added scroll option to this dialog
- Lists: Added pagination to listrecord grid
- Lists: Fixed ordering of listrecord items in list
- Lists: Added dutch translation
- Lists: Fixed bug with the browse button and namespaces
- Summary: Automatically open the Summary module if the logged in user has an unread announcement.


06-08-2014 6.0.10
- Projects2: Tags like {project:name} and {project:path} now available for the Name field of Project Template Jobs.
- Timeregistration 2: Cannot add/edit time entries in closed week.
- Billing: PDF: if entire summary block at the end does not fit on one page, it is put at the beginning of the next page.

01-08-2014 6.0.9
- Holidays: National holiday hours displayed in holiday manager's main grid.
- Holidays: Warn holiday manager if a month's credit becomes negative.
- Holidays: Improved way of displaying available credits.
- Projects 2: Import Projects CSV window is now able to create an example CSV file.
- Projects 2: able to turn off Project Income using Project Template settings.
- Projects 2: able to filter Invoice:Income Grid on Project Type & "Already Invoiced".
- Files: Fixed namespace problem in Jupload controller (uploading folders)
- Added {link} property to email templates. This will display the text of the linkfield in the email content.
- Calendar: Added direct_url property for calendar views
- Core: Distinction between holidays that are free days and holidays that are not.
- E-mail: link to easily move emails to and from spam folder.

6.0.0
- Paste images in chrome
- Support for pasting images and drag and drop nodes in site module
- Use XCache for caching values
- Workflow: email and reminder for file uploader when file is disapproved.
- Using namespaces in PHP code


17-06-2014 5.1.8
- ActivityLog to file function
- 5.0 fixes

16-05-2014 5.1.7
- PHP custom field
- 5.0.57 fixes

13-05-2014 5.1.6
- Billing: PDF Templates can have stationery paper
- Ticket messages styled like conversation and notes display yellow.
- Files: Users without disk quota can still upload files.
- Calendar: Able to import very large ICalendar objects.
- Calendar: Notification to user when attempting to import an ICalendar with a recurrence containing 'Last X of month' pattern.

11-02-2014 5.1.2
- Included LDAP setting saving feature

29-01-2014 5.1.1
- Merged fixes of 5.0.34-39

22-01-2014 5.1.0
- Calendar: Overview of participant and resource availability per day.
- Tasks & Reminders: get reminder when another user puts a task in your task list
- Added user quota support
- Show ticket template per message
- Custom IMAP flag support (Message labels)

- Workflow: Fixed: When disapproved: email and reminder for workflow starter and for workflow owner.
- Ajax timeout increased to 3 minutes for all of Group-Office.
- Calendar: Now possible to copy events from a read-only calendar to a writable calendar using right-click menu.
- Fixed problem with opening document templates in the addressbook
- Calendar: list view now also respects the read_only property of the event
- the value in the go_settings table can save more data (LONGTEXT)
- Proper "File not found" message when a temporary file can no longer be found.

22-09-2014 5.0.81
- Calendar & Tasks: Fixed updating of Tab "Visible tasklists" when loading, showing, creating CalendarDialog.
- Remove focus from windows/dialogs that are closed (hide)
- Special strings "GROUP_EVERYONE", "GROUP_ADMIN" and "GROUP_INTERNAL" for $config['register_user_groups'] and $config['register_visible_user_groups']
- Document Templates: It is now possible to use custom field tags in Document Templates for Contacts. (e.g., {contact:col_25} and {contact:col_84})

17-09-2014 5.0.80
- Build error

17-09-2014 5.0.79
- Calendar: Disable context menu item "Create email for participants" when it's a private event and it's not yours.
- Added a little bit more height to the password dialog.
- Changed email toplevel domain name validation so new toplevel domains are also accepted
- Calendar: Month view: no need to left-click an event before right-clicking it anymore.
- Fixed max length problem in customfields.

20-08-2014 5.0.78
- Upgrade broken because of to strict permissions
- Translation errors fixed
- Bangladesh translation added.
- GOTA: Changed moveuploadedfile flag in replace function
- Billing: Fixed problem with linking orders that don't have a status yet.

13-08-2014 5.0.77
- Addressbook/Email templates: Fixed problem with template parsing and offset for "IF" statements.
- Added config 2 options for the files module to remove the addressbooks/projects folders from the files tree:
	$config['files_show_addressbooks'] = true;
	$config['files_show_projects'] = true;

08-08-2014 5.0.76
- Add xss prevention headers
- Secured maintenance tools
- Removed deprecated sites module
- Address book / Search : The "Name 2" field now used in the search cache for all future companies, or, if you recreate the search cache, for all companies with Name 2.
- Email: Fixed copying of multiple messages. Also set the "SEEN" flag on the copied messages
- Files will be sorted by filesize correctly
- Reminders can be removed from a task
- Calendar: Fixed problem with strtotime and the YYYY.MM.DD date format in the recurrence pattern calculation
- When renaming a mailbox folder the current name will display in the dialog
- Expunged email will be removed just before store load again
- Users: Admin's Users Grid: sort order of name now according to current user's settings.
- Bookmarks: URL validation in Bookmark URLs extended to also allow URLs like https://wiki:username@wiki.mydomain.org
- Custom fields: Future encrypted Custom Fields become TEXT fields in database.
- Projects: Tags like {project:name} and {project:path} now available for the Name and Description fields of Project Template Actions.

31-07-2014 5.0.75
- Fixed wrong language problem on the login page error messages.
- Error when saving contact without a company

30-07-2014 5.0.74
- Fixed notice
- Address book: Added sanity check: Contact's company must be in same address book as the contact.

29-07-2014 5.0.73
- Calendar: Fixed problem in IE10 when loading the 1day, 5days and 7days grid.
- Added configuration variable to enable/disable encoding of the callto link.($config['encode_callto_link'] = false;)
- Sieve & E-mail: More specific 'sieve not used' note in email account: filter tab.
- Calendar: Fix for not displaying a multiday event on the last day when the endtime is set to 0:00
- Addressbook: Fixed problem with using the import dialog for the 2nd time.

18-07-2014 5.0.72
- Project in billing selectable for v1 and v2.
- Removed the ability to delete the items that are marked as default in the settings->synchronization tab.

17-07-2014 5.0.71
- Billing: Fixed sql problems with order when a project is attached and then projects2 isn't installed.
- Prevent timeout on module permission window
- Added new config option $config['files_disable_filesystem_sync']=true;
  The automatic sync can cause timeouts on large installations and should not be
  required if you don't modify the filesystem externally.

16-07-2014 5.0.70
- 5MB limit on thumbnailer
- Addressbook: Changed maximum length of mailing subject from 100 chars to 255 chars.
- CardDav: 'comments' field of newly imported contact also shown in CardDav VCard.
- Brought back flash uploader runtime for older browsers because html4 was not working in IE8
- Added cellular2 to the emailtemplate predefined list.
- Billing: Changed projects support from projects to projects2
- Billing: Added fields to costcode so you can add a name and a description to it.

10-07-2014 5.0.69
- Updated Portugese-Brazil translation
- Fixed not working if conditions in e-mail templates
- Invalid calendar state that did not select own calendar on load
- Urlencode phone numbers in callto links

03-07-2014 5.0.68
- Size mismatch on debian repos

03-07-2014 5.0.67
- Broken message dialog in ticket system

02-07-2014 5.0.66
- Private events where shown in new calendar prints
- Moved file search index to separate cron job
- Ticket types will always sort by group name so groups wont show up double

12-06-2014 5.0.65
- Bug in checking user permission when sending mail

12-06-2014 5.0.64
- Forward messages that were attached fixed
- Touch parent and current folder when moving files and folders so refreshing won't sync with the filesystem. This improves performance.
- Updated Croatian translation
- Funambol bug with multiple exceptions in a recurring event fixed
- Event reminder set correctly for funambol sync
- Show expunged mail with line-through
- Skip deleted messages in ActiveSync
- Only update modified properties  on recurring orders when saving
- Don't ask to add unknown recipient if the recipient is a visible user
- CalDAV did not handle resource conflicts correctly
- Billing: three new optional invoice PDF columns.
- Calendar: fixed bug where an exception event is created twice.

04-06-2014 5.0.63
- ExportXLS error fixed
- Added div overlay to the print function so the links in the preview should not be clickable anymore
- Updated German


03-06-2014 5.0.62
- Fixed missing ExportXLS error when exporting.
- Fixed problem with address list changes. Only save them when the address list settings panel is enabled.
- Updated German and Czech language
- Added Brazilian Portugese holidays file

28-05-2014 5.0.61
- Limit file search index size to 10MB
- Send an empty reminder object in sync so reminder will be cleared on clients
- Removed the flash upload runtime from the plupload uploader because it was causing problems in older IE browsers.(Most browsers support HTML5 anyway)
- Fixed error when unchecking the "link company" and "link contact" in the email message panel.
- Fixed problem with smime certificate check when changing the "from" field in the email composer
- Fixed upload button for smime PKS12 file in email account dialog
- cleanup links and search cache on deletion of module

21-05-2014 5.0.60
- Template bug in site module.

21-05-2014 5.0.59
- Sorting and page size bugs in projects2
- Reply all in e-mail was broken in previous release
- Last time entry chopped off in browser.

19-05-2014 5.0.58
- Fixed access denied error in GOTA because java made an extra request to the
  codebase url defined in the jnlp file
- Removed mail headers that caused some spam filters to treat mail as spam.
- BUG FIX: When using different templates for accounts a sender change needed
  two attempts when replying
- Formprocessor: Send the email message to the addressbook owner in his own language.
- HTML editor: Fixed problem with syncing text in the html editor when changing between html and source mode.
- Google Drive: fixed problem with authenticate and empty file_id
- Billing: Enabled template tags in the closing text
- Updated Norwegian
- Fixed auto link message in mail for invoices
- Publish template assets with copy instead of Symlink for windows support

13-05-2014 5.0.57
- IMAP authentication failed with store_password=false
- Files and Documenttemplates: Added check for invalid characters in the filename
- Updated Norwegian
- Company address display at contact improved

13-05-2014 5.0.56
- Increased syncing of mail items with ActiveSync when using the "any period" option from 2 weeks to 1 month.
- Fixed bug: Email messages on the Iphone where missing when the message contained an invalid email-address
- SMIME: SMIME certificate only works for the owner of the email account, not when the email account is shared.
- Billing: PDF generator, fixed bugs in the pagenumber display and fixed bug with full page images creating a blank page.
- Tasks: Fixed problem with the reminder of tasks when the task is recurring. (The reminder of the recurring item wasn't changed.)
- Don't close the upload dialog when the config parameter "upload_quickselect" is set to false.
- Email: Composer -> The "from" combobox doesn't need to be editable.
- Tickets: Added "ticket group" to display panel
- Address book: user permissions check for custom fields in Advanced Search
- Address book: show company info in contact display panel.
- Form processor: name of address book is mentioned in the "new contact" notification email.
- Calendar: Moving event with resources now checks if the resources are available.
- SMIME: Also disable the "Encrypt with SMIME" option in the email composer when there is no certificate set for the account.
- Addressbook/Billing: Fixed display of records in companySelect field.
	Fixes issue with address book name displayed in recipient field of the order dialog in the billing module.
- Added update query that sets the correct permissions for the installed modules. (Because some permissionlevels are removed.)

16-04-2014 5.0.55
- Updated German translation
- BUGFIX: most smtp send errors were ignored in the mail module
- Calendar: Improved notification flow for participants for when event has been updated.
- In some cases old project folders could be left in the files module after
  renaming projects.
- Sync filesystem ignores ACL and system folders because they don't exist anyway.
- Tickets: Users can only edit their own messages and not the messages from others.
	When you have manage permissions on the ticket module, then you can edit messages from others.


14-04-2014 5.0.54
- Changed delete permissions for ticket messages.
	Messages can only be deleted when the user has manage permissions on the ticket module.
	(So now it is the same as  deleting tickets)
- Bug in file custom field that could cause fatal error: Call to a member
  function getDefaultHandler() on a non-object in
  /usr/share/groupoffice/modules/files/customfieldtype/File.php.
- Fix for quickadd panel position after resize
- CalDav: Fixed synchronization of event recurrence exceptions.
- BUG: Adding a new participant to an existing event that is an exception for a
  recurring series could find a complete random event and it will copy the name,
  location etc to it. This happened recently when we fixed a bug where an event
  was not created for a new participant in a recurring exception.
- The temp files of admin were cleared when the cron job runs. This caused
  missing e-mail attachments when you were composing as admin.
- Font size display error in some e-mail messages fixed.


08-04-2014 5.0.53
- Error on save contact and company because of application/json header in response

07-04-2014 5.0.51
- Fix ticket template bug where you could not set "Ticket created for agent"
- Database check aborted on an error. Memory optimizations were made as well.

- when another agent commented on a ticket, the agent got an invalid e-mail.
- pass through original smime source for iphones
- Fixed automatic sort order for ticket type groups
- Fixed problem with ticketmessage "type_id" and "has_type" properties.
- Workflow: email and reminder for file uploader when file is disapproved.
- Calendar: new Dutch King's Day.
- Remove (0) from telephone numbers when parsing to the callto link
- Fixed: Ticket messages imported from IMAP can be edited again.
- Bring back export button for ticket users so they can export the tickets that are currently on the screen.
- Dropbox: Added new Curl.php for NSS SSL backend and fixed bug in Database

27-03-2014 5.0.50
- Updated German translation
- Disable swiftmailer disk cache on windows
- Resource can be added and used without administrators
- Calendar will only send an email to the resource administrator when the booked event is in the future
- Holidays: Fixed client-side bug when creating a new leave day entry.

26-03-2014 5.0.49
- new PHP 5.5 OPCache caused database install problem.
- Database check uses less system memory
- RSS feeds were broken
- Automatically open file chooser and auto start file upload in e-mail and file module
- Custom fields: File custom field maximum number of characters: 255.
- Calendar: Improved workflow of resource allocation by resource group admins.
- Fixed reload pagination problem of the contacts grid when adding comments to a contact
- Added override of Ext.removeNode function because of IE9 problems
- Updated German translation

19-03-2014 5.0.48
- 5.0.47 had an invalid folder www in it.


19-03-2014 5.0.47
- Billing: able to turn off reference in billing PDF template
- Calendar: resource application and confirmation workflow now also works in shared calendars
- In some cases participants did not get events when they were added to an existing recurring meeting
- Fixed duplicate linking with experimental auto_link_contacts enabled.
- Updated SabreDAV to version 1.8.9
- Billing: group headers were printed after first group item.
- Mark as unread was not possible anymore with a shared account
- IMAP Flags are enabled for delegated e-mail account.
- Removed irrelevant permission levels in module permissions window.
- Email on new ticket runs from the external ticket page as well
- Check for CLI works on cgi versions now too.

14-03-2014 5.0.46
- Wrong mime type for PDF in billing. "application/x-pdf" was used instead of "application/pdf".
- Calendar export exdate property must have time as well for android
- Billing: changed export income so it looks to the cost codes of the separate items instead of the full order.

- Made number custom fields smaller so they stand out more with right alignment.
- Exporting contact as vcard now sets it's address type as home instead of work.
- Projects2: New csv export of time entries.
- Removed z-push 2 backend. Everyone should upgrade to 2.1.
- Updated z-push 2.1 download link
- GOTA 1.1.13: fixed launch problem for utf8 characters in filename on Mac OS X

05-03-2014 5.0.45
- CalDAV will update and delete the correct event when events are imported and exported with the same URI
- Fixed rename(move files/folders) functionality (move files/folders did not work properly)
- Tickets: Fixed dependency problem with loading the tickettypes dialog when the email module is not installed.
- Tickets: Fixed searching for default type when loading a new ticket. Now it also checks if the user has create permission for the type.
- Billing: Fixed bug in the selection (date range) of expense items when exporting them.
- Projects2: Search for employees and resources works now
- Corrected key event for auto capitalize. It didn't work in Firefox.
- Files & Email: option to automatically delete expired download links.
- Holidays: possible to delete an employee's entire year record.
- Holidays: when registering new holiday, show hours in national holidays.
- Projects2: Default icon in the absence of an icon in the subprojects grid.
- Projects2: Columns are sortable in sub projects grid.
- Projects2: reports didn't include last day of period
- Projects2: New project shows available tempaltes sorted by name

18-02-2014 5.0.44
- Fixed bug in custom fields copying from projects1 to projects2.
- Improved project reporting.
- Show time tracking comments in hours approval 2.
- Timer bug used end time as start time
- Undismissable reminders bug with recurring events
- ActiveSync: Don't enter meeting data if there's no organizer
- Fixed task recurring bug that didn't work for tasks in the future

17-02-2014 5.0.43
- Schedule Call Dialog: Fixed permission problem.
- Billing: Fixed break as last item inside itemGroups when printing PDF
- Calendar: Print Category Count - Only print count for items that are visible for the user.
- Group-office theme: Added strong/b style for fixing bold text inside blockquotes
- Fixed auto capitalize function
- Show CC field by default.
- Double click show email message in popup rather than an expanded preview
- Corrected query for hoursapproval
- Updated German
- Subprojects of projects possible
- Fixed Bynari collaborator Outlook sync

12-02-2014 5.0.42
- Fixed projects grid
- Import VCard: works again.

12-02-2014 5.0.41
- Updated bulgarian language
- Fixed problem with automatic mailbox creation
- Projects 2 folders were not available through webdav
- Always return an INBOX when IMAP connection fails on ActiveSync to prevent some devices from crashing.
- Email: Fixed bug in Email Composer's "send to" field.
- Custom fields & Address books: optional address book restriction on Contact &
  Company Custom Fields.

11-02-2014 5.0.40
- restart apache later on installation

11-02-2014 5.0.39
- Added -p option for cli password
- Fixed html decode problem in the subjects when using the Mail saveas functionality.
- Fixed ticket batch edit.
- Fixed state saving of addressbook westpanel accordeon.
- Fixed button to add template folders and files in projects V2
- Fixed SyncML server for Funambol client
- Some mailservers returned folders as "false".
- Corrected postfixadmin_token error message. Should be serverclient_token.
- Form Processor: enabled social media fields in formprocessor
- Z-Push UTF8 iphone bug with missing messages fixed

29-01-2014 5.0.38
- Optimized ticket listing page
- logout in activity log
- Calendar: Fixed display of mini calendar in older themes

27-01-2014 5.0.37
- Fixed bug that mail is not saved in the sent items folder when the addressbook module is not installed.

24-01-2014 5.0.36
- Previous version could make invalid dovecot confuration
- Fixed iOS all day event bug with caldav

23-01-2014 5.0.35
- Mailserver installation on latest Ubuntu and Debian fixed
- Copy links when creating exception
- Projects name template not working anymore after saving settings
- Don't move project to a project in tree drag and drop
- Email: Fixed quotation of text in saved email subjects
- Tickets: Disable "mark as read" button in the display panel when not enough permissions.

20-01-2014 5.0.34
- Parse rrules in caldav failed in some cases with mutliple weekdays
- Build error in 5.0.33
- Billing: New Export Catalog Products function.

20-01-2014 5.0.33
- Check language input on login form so auto submission tools can't input incorrect language settings.
- Updated German language.
- Address Book: Batch edit custom fields are now sorted alphabetically.
- Address Book: No longer possible to add employees from different address book.
- Address Book: Regular users can now create their own e-mail templates and document templates.
- Address Book: Mailing log now shows time of mailing finish.
- IP Whitelist: Group-Office IP whitelisting now possible on a user group basis.
- Email: Hide namespaced imap folders when they are empty.
- Z-Push 2.1: Fixed duplicate include of replied/forwarded email on the iphone. (Smartforward)

15-01-2014 5.0.32
- Notice in e-mail viewing
- Accepting calendar invitations was broken.
- Password with colon (:) failed with SyncML
- Removed requirement for CLI on cronjob controller so it can be called as URL as well.
- Address Book: for new salutations, the first letter of the middle name and
  the first letter of the last name will be capitalized.

10-01-2014 5.0.31
- Support ThinkFree office integration
- Added date in event search result and activity log
- Email: Created mailboxNotFound exception that is translatable through the global language files
- Email: Merged multiple linked item blocks to each other so it will only display one block.
- Email: Hide link information in email prints
- Changed email-address for Email Reminders, it is now using the CONFIG->noreply_email.
- Added CONFIG->noreply_email to configuration file.
- Removed install queries from timeregistration module (no longer needed)
- Future weeks/months will show in the timeregistration panel and current week/month will be selected.
- Close button added at the bottom of the ticket display panel.
- An amount of seconds can be supplied to auto-logout after inactivity. (works in IE)
- Rich Text editor will automatically insert a capital after a dot (.) when it's enabled.
- Email: Fixed problem with "read only" Inbox folder and Imap namespaces.
- Added pagination to "Emailcomposer->Adrressbook->addresslists" grid.
- Added lists display to the displayPanel of orders, folders, files and events (When installed)
- Activity Log: Added Log messages when ACL's are added to items
- Billing: Order attribute for amount that has been (partially) paid.
- Address book: When sent mailings grid reloads (every 5 seconds), it stays on the current page.

02-01-2014 5.0.30
- Timeregistration show future weeks.
- Billing module could not find order error message because of change to the new year.
- blur active form fields on tab change. Otherwise auto complete combo boxes
	will remain focussed but the autocomplete functionality fails.
- Email: Namespaces on the mailserver will be added to the folders response too.
- Email: When sending email with a link, the linked email time will be set.
- Address book: Contact suffix as column in contacts grid.
- Users: fixed import user CSV.
- Holidays: Show leave days / holidays in the owner's calendar.

16-12-2013 5.0.29
- Made mailserver interact with Group-Office with unique token. Which is easier
  to manage and more secure because the password is not stored on disk.
- Removed old unneeded file with small security problem.

13-12-2013 5.0.28
- Auto loader improved
- Changed projects2 layout and improved navigation
- Employees can submit new holiday entries and their manager can approve them
- Added "Add holiday" button to timeregistration2 tab too.
- Removed sum from billing for performance reasons

11-12-2013 5.0.27
- Prevent new task list on ios from crashing activesync. All tasks will be
  synced to all lists. Not elegant but it seems to be the only way to prevent
  a crash.
- Calendar printing: removed start time for the 2nd printed instance of events
  that take longer than 1 day
- Calendar module: Added print option for event count per category per calendar.
- Fixed exception dates import from CalDAV
- Added new create link dialog for the HTML editor in the site module
- Autolink a contact will check if the same contact is not already selected in
  the link field
- Hard-deleting a user works again in the Users module
- Removed some calendar event data from response when event is private.
- Copy resources with event too

29-11-2013 5.0.26
- Check if meeting request has relevant updates before reimporting it when
  accepting in the mail
- Delete folder bug
- User group filter showed wrong total count
- User delete impossible.
- Changing project type didn't work on projects v2
- Fixed problem with exception events that are not removed when the main event
  is removed.
- Fixed bug with recurring events that are moved after creating an exception in
  it. Sometimes it happened that the recurrence was not correct after that.
  That's fixed now.

27-11-2013 5.0.25
- Change ACL level always allowed for admins
- User add error when max_users was set in config.php
- Tickets: CC input field for ticket notifications: sanity check when submitting
  CC addresses.
- Files: Prohibited: compress folder that is inside a read-only folder.
- Address book: Extra check if uploaded photo exceeds the server's limit.
- Added more information to number and contact field in the schedule-call dialog.
- Z-push admin didn't work with old 2.0 states in z-push 2.1 because they had to
  be converted to lowercase.

24-11-2013 5.0.24
- Fixed build error in 5.0.23

24-11-2013 5.0.23
- Fixed iphone WBXML error when inviting appointment participants in z-push 2.1
- Tab notification for new emails and tickets will expand when there is a large
  number inside
- German translation for birthday portlet added
- Fixed setting company image and edit dialog link of company image
- Company Display panel has proper Rowspan sp picture doesn't overlap the
  details pane
- Ticket reports will only be available to users with manage permission to the
  ticket module.
- Billing: Able to print invoices for costs.
- Lists: Search bar in Lists Panel.
- Z-Push2: Fixed rare issue where task time on device is one hour too early.
- Users module: filter panel for user groups.
- Projects 2 : default mileage for time registration.
- Tickets: CC input field for ticket notifications.
- Tickets: Closed ticket will be reopened on receiving ticket email of customer.
- Link items: prohibited to link a read-only item.


12-11-2013 5.0.22
- Updated Norwegian
- Tickets: fixed an issue with the report chart where the flash plugin would not load when GroupOffice was in a sub directory
- Tickets: added diagram that shows average response time on new tickets per month
- Removed task  description tooltip
- Replaced sql_calc_found_rows with separate count() query to improve performance
- Replaced large IN queries with temporary tables to increase performance.
- Listing error in billing module when sorting on status.

08-11-2013 5.0.21
- Bug in tickets module when billing module wasn't there.
- Excel import for billing items
- Tickets: there is now no longer an execution time limit for the ticket invoicing process.

07-11-2013 5.0.20
- Hide password in e-mail network response.
- Tickets: Possible to use {agent:*} tags for email templates
- Ticket module has a report for the average ticket solving time per agent per month
- Ticket Module has a report for amount of tickets solved per agent per month
- Billing: optional paging in invoice/order PDFs.
- Billing: totals row in orders / invoices grid.
- Address book Email templates: Email Account Settings: Show default templates dropdown in IE (fix).
- Billing: Cost Code field for Catalog Products.
- Tickets: Cost Codes for ticket rates. Cost codes in used ticket rates can be invoiced.

01-11-2013 5.0.19
- Bug with decimal values in timeregistration grid.
- Added email account selection for ticket types. Now you can set up a separate email account for each ticket type to import tickets from email.
- Billing: able to search invoices/orders by month/year.

30-10-2013 5.0.18
- Hide disabled fields in projects 2 module
- The automatic link email to contact will only link the email once.
- Calendar: Fix: Able to turn off calendar publishing.
- Custom Fields: Textarea custom field input field has no maximum length.
- Summary / Start Page: Times of all-day-events are not displayed, to denote they are all-day-events.
- Email composer: always change signature when changing from-email address.
- Email composer: fix for spontaneous default account template creation on opening Email Composer.
- Fixes of 4.2.27

24-10-2013 5.0.17
- SMIME decrypt bug
- iconv error

23-10-2013 5.0.16
- Fixed problem with some email attachments that didn't have an extension.
- Fixed problem with external link not opening the correct folder in the files module
- Email & Address Book: Default email template for email account.
- Address Book: Added address book column in Address List Management
- New theme CSS enhancements
- Default Site Module: able to register for Address List from contact form.
- 4.2.26 fixes


21-10-2013 5.0.15
- Install bug in projects2 module.

21-10-2013 5.0.14
- Included new improved projects and timeregistration modules

18-10-2013 5.0.13
- Fixed deprecated /e modifier in PHP.5.5
- Calendar hang in IE when browsing other weeks than the current week.

18-10-2013 5.0.12
- Missing version number in 5.0.11

17-10-2013 5.0.11
- Added date and time indicator
- Custom Fields & Addressbook Advanced Search: can now search through multiselect custom fields.
- Billing: able to import order items from CSV file.

10-10-2013 5.0.10
- Searchbar was missing in add contact dialog for the address list
- Images in vcards will be base64 encode without whitespace (for carddav sync iPhone)
- the database field for dav_contacts that hold data is changed to LONGTEXT for VCARDs with large base64 encode images

07-10-2013 5.0.9
- Updated Norwegian
- Customfields of a ticket will appear above the messages in the eastpanel
- Fixes of 4.2.23

02-10-2013 5.0.8
- Remove auto phone formatting until matured
- Double decode of binary vcard data
- Calendar module: icons for private, recurring and events with reminder(s).
- Modules tabstrip will always be in the order of the modules module.
- Address Book module: social media links for contacts.
- Custom field text field length can be set. Max row length error is handled
  when maximum is reached.
- Small layout tweaks
- Contacts is extended with an Action date that can be filtered on. It can be
  set when you add a comment too.
- load reject message in sieve dialog
- Fixed context menu when searching in e-mail
- Fixes of 4.2.22

17-09-2013 5.0.7
- Disabled custom field will not be displayed in the display panel on not enabled categories
- Added holidays module

17-09-2013 5.0.6
- Icons back in start menu
- Appointment participants are linked automatically to the contacts
- Rates in CSV export
- WebDAV uses file permissions from file_create_mode
- Fixes from 4.2.20

11-09-2013 5.0.5
- Fixes from 4.2.19
- Layout issues

10-09-2013 5.0.4
- Build error: pro modules missing.

09-09-2013 5.0.3
- Small theme fixes
- Fixes from 4.2.17

20-08-2013 5.0.1 (beta)
- Small theme fixes
- Fixes from 4.2.12

15-08-2013 5.0.0 (beta)
- Complete redesigned interface
- Added recursive search for Imap folders
- VoIP will rewrite tel: links to callto: in Firefox in Windows
- Updated VObject to 3.1
- Updated Sabredav to 1.8.6
- Address Book module: photos functionality for companies.
- Projects module: 'show in tree' check box for project statuses.

13-12-2013 4.2.29
- Disallow deleting system folders through files module
- Auto loader improved


29-11-2013 4.2.28
- Disallow deleting system folders through files module
- Raised post address 2 field to 100 chars
- When someone other the the ticket agent add a new message to the ticket it will be marked as unseen
- Zpushadmin can now also work with zpush2.1
- Projects templates fix: Also use 'days offset' in task action.
- Fixed refreshing Favorites calendar display after permission change
- Added permissions option to multiselect dialog

30-10-2013 4.2.27
- Zpushadmin can now also work with zpush2.1
- Fixed problem with emailing files from the scanbox and then change the template of the email.
- Show birthdays of calendar owner instead of logged in user
- GOTA uses GET parameters and has permissions attribute in manifest
- JUpload has permissions attribute
- New resource could have wrong permissions initially
- Imageview didn't want to resize image

24-10-2013 4.2.26
- Rebuild GOTA with permissions attribute in manifest
- Saving settings cleared addresslists when this tab was not selected and activated in the settings
- Fixed SMIME Message view with clear text disabled
- Add timestamps to backup jobs

17-10-2013 4.2.25
- Billing: Order PDF - Extra spacing in top after Page Break, if logo is on every page.
- Carddav supports viewing readonly address books
- Fixed Contact pictures of VCards base64 encoding for older vObject libraries
- Don't include inactive users in e-mail search
- Signed JUpload java applet
- JUpload fixed for IE
- It was possible to create an event without organizer
- Include companies in e-mail search
- Ask for meeting request invitation when new participant was added

10-10-2013 4.2.24
- Disable file manager until upload is complete to avoid change of folder while uploading
- Contact info panel could crash when appointments were attached
- Added default parameter to FilesearchController::getStoreColumnModel  so it will be compatible with the AbstractModelController
- Special statusses of the billing module will be loaden for the current book when the Book dialog opens
- AbstractModelController::actionExport() will only SELECT custom field column if there is a customfield record available
- Contact pictures of VCards are working with CardDav sync (were base64 encoded twice)

07-10-2013 4.2.23
- Fixed the Attachment Context menu on the Email dialog when clicking a .eml attachment
- Calendar view export will extends when the calendar name doesn't fit on one row.
- Signed GOTA with real code signing certificate
- Handle missing models in search
- Files: Fixed path display when using the "up" button
- Disabled session_inactivity_timeout because it breaks IE when enabled.

02-10-2013 4.2.22
- Add redirects for Caldav and carddav service discovery so that iOS7 works.
- Module access is checked now for WebDAV, CalDAV and CardDAV
- Updating e-mail accounts with LDAP auth failed.
- Workaround ActiveSync problem where 'mailto:' is included in the mail address.
- Fixed disappearing file browser button when making display panel very small
- odt should not be renamed by google drive module
- Fixed problem with getting "due time is smaller that start time" error  if you complete a task with a recurrence.
- Added dependency check in sites module for the customfields module to be installed.
- Signed GOTA
- session_inactivity_timeout was reimplemented

17-09-2013 4.2.21
- Export contacts/projects/etc.: custom fields not exported if current user has no access to custom fields module.

17-09-2013 4.2.20
- Added sorting on category in the tasks grid.
- Bug in imapauth auto account creation
- CRLF Bug in calendar export

11-09-2013 4.2.19
- Z-Push 2 performance issue. There was a wrong check on changes on the calendar.
  This query was slow and caused all devices to sync when anybody made a change in the calendar.
- Upgraded PEAR net_sieve page to 1.3.2
- LDAP Auth module blocked login when not configured.
- Export contacts/tickets/projects/etc: CSV exports will now have no more '<br />' tags.
- Export contacts/tickets/projects/etc: fixed bug (one comma too many in MySQL query) and added 'max execution time=0'.
- DAV tables cleaned up

10-09-2013 4.2.18
- Package build error. Pro modules missing.

09-09-2013 4.2.17
- Root Addressbook and Project folder read only in files module
- Peek when viewing attachments
- SMIME certificated grid will reload on show.
- Fixed owner that was not showing up in the administration->tickettypes grid of the ticket module.
- Addressbook: Added pagination to the "Administration"->"Addresslists" grid
- Fixed default date / time problem on task reminder.
- Sieve: Rules table is refreshed after every single rearrangement.
- New LDAP parameter to create mailboxes with the serverclient
- Charset conversion bug in zip attachments
- Photo was not saved on vcard import
- Refuse to move events to other weekdays when they recur by weekday also in month view

03-09-2013 4.2.16
- IMip handler for CalDAV to support apple invitations better.
- Don't ask to notify event participants when nothing relevant has been changed
- Disable address books collapsing when favorites is not installed
- Changed the default sort of the project templates store so it uses the "name" instead of the "id" to sort.
- Fixed bug where email got cut of on the word begin
- Cut of encoded strings on the end of email attachment names

30-08-2013 4.2.15
- Removed unseen caching. Broke checker.
- Update Norwegian, German and Swedish translation
- Could not find relational ACL error in billing module

27-08-2013 4.2.14
- Everybody may create calendar views
- Corrected mistake in 4.2.13 release

27-08-2013 4.2.13
- Z-push 2 did not close DB connection while pinging.
- Internet Explorer will never open email attachments inline for MS Office mime types.
- Get capability from IMAP login command
- Query unseen messages only when modseq changes
- Option to show event status in views
- Bug in reminder calculation
- Refuse to move events to other weekdays when they recur by weekday

19-08-2013 4.2.12
- Bug in attachment download
- Fixed security vulnerability
- Minimized disk access in files module
- Detect company employees for document templates as well so they don't need to be linked
- Added participant status to view grid.
- Added a check for empty "name" property in an email part to determine if it is a mail body or an attachment
- Added check for (left)mousebutton in onmousedown event in the calendarGrid so the dragevent will not be fired when doing a right click
- Billing module, duplicate order: duplicate window --> default no link to original, no duplicate window --> default link to original.

13-08-2013 4.2.11
- Fixed duplicate entry error on installer.
- Fixed problem with downloading attachments on old Android phones with ActiveSync.
- Disabled session_start() for webdav to fix Mac Finder issue with uploads
- Project cached attributes are changed to display useful text in the link dialog
- In the ticket email, added links to files that are attached to a new ticket message.
- Fixed "Show unread" /  "Show all" button state in the email module when switching message panel position.
- Address Book module: List company name along side contacts in Address List contacts grid.

05-08-2013 4.2.10
- Moved address list filter from accordion to separate panel underneath the accordion panel.
- Fixed imap import for tickets when no subject is given in the email.
- Address Book module: It is now possible to export all hidden fields into CSV, HTML and PDF.
- Check if base64 encoded strings can be divided by 4 if decoded line by line to
  avoid corrupted mails.
- 3rd contact e-mail showed up with email nr 2.
- Removing contact photo error
- Mask sticked when canceling folder delete in e-mail
- Removed publicly visible version number
- Updated German
- Copy e-mail with drag and drop when holding ctrl key
- Option to convert old office files in Google Drive module

29-07-2013 4.2.9
- support for z-push without storing passwords
- Disconnect button for Dropbox
- Z-push 2 fixes for LG phones
- Z-push admin shows more info
- Fixed Notice:  Undefined index: sort in /usr/share/groupoffice/modules/tickets/controller/TicketController.php on line 54
- custom header valdiation for sieve rules
- Try to use z-push password when e-mail password is not stored in db
- Smaller contact pictures so it won't break sync
- Fixed caldav problem when accepting an invitation by mail before it was synced to TB.

25-07-2013 4.2.8
- Added BIC number to company fields of the addressbook module
- The paste-from-word button in the wisywig editor is translated and Dutch and German translation was added
- IE 10 Comma problem

19-07-2013 4.2.7
- Fixed euro character encoding problem in emails
- When changing th FK of the relation the contains the ACL field (eg contanct <--> addressbook) the old permission level will be checked as well
- Delete keyboard shortcut disabled in tasks grid if deleting is not allowed for one of the selected tasklists
- Hide contact photos folder from file manager
- Some attachments missing 1 byte.
- 123 error when moving folders in e-mail
- Customfield blocks: will still work after changing the containing contact's/company's name.
- Address Book: More debugging possibilities when sending mails using the Newsletter feature.
- Some recurring event exceptions couldn't be accepted through e-mail

08-07-2013 4.2.6
- Replace {autoid} tag on apply in project dialog
- Disabled the delete key in the tickets dialog if the user has no manage permission on the module.
- Fixed display of contact photo in email when quicklink is disabled
- Users can only add bookmark categories when they have write permission on the bookmark module.
- Users can only add views when they have write permission on the calendar module.
- Added percentage complete for tasks in calendar
- For the calendar properties window: Show "None" in the Caldav Tasklist selection when tasklist_id is 0
- Email Sieve: Fix to always use custom response subject when $config['sieve_vacation_subject'] is enabled in config.php .
- Fixed problem with rights of the config.php file when creating a new installation in the servermanager
- Email: Change label of "show unread" button when activated.
- Billing: Removed total and costcode from the order displaypanel because of too much data that is displayed
- Z-Push Admin: Added searchbar to devicesGrid
- Calendar/Tasks: Added checkbox in calendar settings to choose if you want to display completed tasks in the calendar or not.
- Zpush-admin: Show used AS version also in the Grid
- to work around office login prompt: http://support.microsoft.com/kb/2019105/en-us
  never use inline on IE with office documents because it will prompt for authentication.
- Timeregistration: Added toggle button so the grid can be displayed next to the form instead of underneath it.
- ZPush: Fixed Grey appointments on iphone
- SaveMailAs module: Email contact/company linking. Fix to not show sender company, when the current user has permissions to see the sender contact, but not the sender company.


27-06-2013 4.2.5
- Added support for quick linking Company to email and updated the displayed text for quicklinks.
- Added a config option to disable the quicklinking in emails. ($config['allow_quicklink'])
- Enabled sorting tickets by ticket agent (responsible).
- Added an option to the email account dialog (properties panel) to choose  if clicked emails will automatically be marked as read or not.
- Billing: Added field to set the length of the order number. (how many 000 that are in front of the orderno)
- Added dropbox module
- Moved scanbox and googledrive module to pro package
- Updated French and German translation
- Fixed incorrect content length header on downloading message source
- better way of stripping non utf8 characters
- Billing: Enabled tags for contact and company in order PDF template.

24-06-2013 4.2.4
- Better memory management with sending mails.
- Updated czech language
- Fix from zoom in in on the calendar in chrome where a lot of "More.." links where shown

19-06-2013 4.2.3
- Fixes from 4.1.79

18-06-2013 4.2.2
- Improved WebDAV performance
- Calendar module: Notification about files of a private event, once the
  checkbox 'Private' has been clicked.
- Better memory management in z-push 2 backend. Raised default max attachment
  size to 100MB.
- Optimized Z-Push 2 ping process with ChangesSink

13-06-2013 4.2.1
- Upgrade TCPDF to 6.0.020 and changed default font to freesans because of display problem on iPad
- Upgraded SabreDAV to version 1.8.5
- HTTP Authentication for requests
- Tickets can only be deleted by users who have write or manage persmissions on the ticket system.
- Send multiple download links at once
- Permissions on start page announcements so visibility can be controlled
- Easy link checkbox to link e-mail conversations to a contact directly
- Contact photo's are shown in e-mail and can be set much more easily with download URL's.
- Custom field blocks feature
- Models show last modified by username
- In tickets a mail can also be send automatically when a ticket was just claimed
- Mail accounts can be shared read only
- Send the invitation emails in the language of the participant if the participant is a known user of GO.
- Added selection for holiday file to the settings->regional settings panel and to the user dialog in the user module.
- Workflow: history can be appended to PDF files.
- Workflow: Files can be copied to a directory
- Holidays region can be selected per user in the settings dialog.
- Upgraded to Extjs 3.4.1.1
- Tickets can be created by sending an e-mail
- Ticket types can be grouped
- Anonymous ticket posting is very simple to setup now.

4.1.80
- Fix from zoom in in on the calendar in chrome where a lot of "More.." links where shown

19-06-2013 4.1.79
- Fix for SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry when accepting appointemnts
- Fix for invalid IMAP part reading. UID could be appended.
- Project types can be removed again

13-06-2013 4.1.78
- Updated German
- Z-push 1.5 fix that it gave errors when there were no items to sync.
- Fix for creating new users with LDAP
- e-mail (non sieve) filter grid didn't reload
- Recurring events could have one extra day when synchronized

13-06-2013 4.1.77
- If it's only a single all day occurrence then the end day should be the same
	as the start day for the iOS devices. If it lasts more than one day we should
  add one day.
- Log rotation broken
- Create installation broken in servermanager

12-06-2013 4.1.76
- Exception in tickets

12-06-2013 4.1.75
- Activity log was removed after two weeks. Only one day every two weeks was saved.
- Fixed bug with not recognized email-address in the SMIME certificate
- Billing catalog, import products: added address book field for newly imported supplier companies.
- Removal of billing orders possible even if it has a number.

07-06-2013 4.1.74
- Added config option "zpush2_max_attachmentsize". Maximum syncable attachment
  size in bytes (default 10MB). If you set this too high you'll get a
  memory_limit exhausted error and sync will fail completely.
- Updated Thai translations
- Unable to upload attachments to e-mail templates
- Added LDAP group synchronization
- Sort file templates alphabetically
- ZIP empty folders broken
- Projects module: show fields in project east panel according to its project template. E.g., if 'manager' is not enabled in the template, it is not displayed.
- Orders can be deleted again except if read only flag is set for status

04-06-2013 4.1.73
- Apache alias defaults to z-push2 now. Be careful with upgrading
  /etc/apache2/conf.d/groupoffice.conf if you still use z-push 1.5!
- Able to select out of more than 50 address books to add to address list.
- Updated Croatian
- SQL error in notes Upgrade.

29-05-2013 4.1.72
- Fixed Thai translation
- Updated German translation
- In ticket email templates, {contact:salutation} uses the ticket contact's set salutation.
- Fixed renderer for texts in grids
- Fixed comments response in actionDisplay for displaypanels
- ZpushAdmin: renamed comments into comment because comments was a reserved parameter in GO
- Better output of showing mailbox usages in Postfixadmin cron script.
- Better way of creating ZIP archive of e-mail attachments so that foreign characters will work on Windows
- Invalid event reminder property could prevent ActiveSync from syncing events.
- Removeduplicate functions in tasks, addressbooks and calendars are  now translatable
- $config['locale_all'] in config.php for Group-Office locale setting, example values are: "en_US.UTF-8" or "nb_NO.UTF-8".
- $config['tcpdf_font_size'] in config.php for font size in PDF exports, default is "9"

24-05-2013 4.1.71
- relational query cache didn't work
- Added supported models for Customfields so only the supported models can be chosen in the type selectbox.
- Remove duplicates buttons in calendar, tasklist and addressbook administration dialog
- Added query to remove duplicate participants in the cal_participants table. (Also adds "unique" on the event_id and email column)
- Received email attachments without a name will receive a default name.
- Fixed issue with the user calendar colors. (Sometimes it loads the wrong colors)

22-05-2013 4.1.70
- Template parser will not see {tag} with white spaces between { and } as tag to be replaced.
- The HTML email message CSS part will add style with new line characters to disable tag parsing
- Show maximum upload size.
- Reverted file thumbnail view by default
- Sales per month report for billing products
- Required custom fields are not validated server side to prevent errors when
  hiding custom fields in the interface
- Don't use store reload but load to prevent delete to repeat after editing a rule
- Disappearing file panel bug

17-05-2013 4.1.69
- Updated Norwegian
- Fix for "This is not a recurring event" error
- Added default sort to the select document template grid and enabled name column sort

17-05-2013 4.1.68
- Demo data for linked e-mails
- Keep contact and users in sync
- Select first e-mail template as default for new users
- Possible to export tickets without manage permission on the ticket module
- ImageViewer does not resize image when clicking "Normal size".

16-05-2013 4.1.67
- Added demo data module so new users can have a better first experience.
- Remember state of several grids in the Email Composer's Address Book dialog.
- Added SortableDataView object so a dataview can be sorted
- Fix for IMAP servers that dont support utf8
- Changed default event organizer. The default is the calendar owner except if
  the owner is admin. In that case it will default to the logged in user.

14-05-2013 4.1.66
- Only first 30 bookmarks shown
- Don't delete types with projects
- Show all document templates
- Custom fields not saving if you had an invalid number
- Remember size state of east panel in Files module across user sessions.
- Remember size state of west panel in Address Book module across user sessions.
- Fixed scroll behaviour in files tree
- Removed old admin only grid column from groups

13-05-2013 4.1.65
- Optimzed SQL query to lookup e-mail addresses.
- Support foreign characters in ZIP files.
- Added French translation to address book
- Shared directory is accessible again in webdav server.
- Running sync file system in the CLI will not print html tags anymore
- Fixed for flagged message broken on some IMAP servers
- Generate listeners immediately after upgrading
- Show all ticket types (without limit) in the ticket module
- Workflow module: fix around the use of groups that can approve steps.
- Contact photo will be updated every time the contact form is loaded.
- Fixed loading of project report PDF template.
- Fixed: cropping of very large plain text mails.

07-05-2013 4.1.64
- Fixed conflict checking on resources when an event itself is in a conflict.
- AbstractSettingsCollection loads more efficiently
- File browser refresh button syncs database with filesystem for the active
  folder and it's direct subfolders.
- Event with non existing user caused error.
- Locale set to "en us" UTF-8 to make functions consistent. This fixes a problem
  with UTF-8 in document templates.

06-05-2013 4.1.63
- Bug in __isset of ActiveRecord

03-05-2013 4.1.62
- Optimized shared folder listing by caching the shares.
- Added mtime to ACL's so we can detect changes.
- Ticket system did not send mails

03-05-2013 4.1.61
- Project folders were moving to an invalid folder location
- Added filter bar for groups in Email composer address book
- MoreData tag wasn't handled by Syncml server which caused problems with Funambol
  Outlook sync when syncing lots of data.
- Wrong display of reminder time
- Use utf-8 charset for searching in IMAP
- IMAP and LDAP authentication did not log validation errors
- Added pagination to the Addresslist filter and remove limit from addresslist management panel
- Reverted saving order statuses to old json.php the OrderStatusController is not finished
- E-mail expunges mailbox when loading messages
- License error when ticket page is used on different domain with CMS

01-05-2013 4.1.60
- IE7 Didn't load anymore
- Force SSL didn't work anymore
- Hide treeselect slave option
- Updated Croatian translation
- Moving in billing catalog didn't refresh grid
- Markup field in billing dialog showed NaN and did incorrect calculations with some regional settings
- Added YYYY-MM-DD date format to settings dialog

26-04-2013 4.1.59
- Can't set property error when replying to e-mails with inline images

25-04-2013 4.1.58
- Calendar bug with organizer status and custom fields requirement
- Deformed thumbnail bug
- Duplicate holidays bug
- Broken counts in addressbook (ACL table was joined when not necessary)

24-04-2013 4.1.57
- Problem with custom fields settings tab.

23-04-2013 4.1.56
- Added birthdays and holidays to merged calendar views in the calendar (If enabled in the calendars).
- Calendar color chooser: Fixed IE fix for color picker left margin
- Fixed internalUrls in emails in linked email window
- MultiSelectGrid can have extra valid value that might not exist in the database
- MultiSelectGrid is capable of have a store prefix (when selectable store changes)
- Fixed color selection when multiple calendars are viewed in the calendargrid
- Start page: Files portlet now has a pagination.
- Projects module: more informative error message when new project type can not be saved when saving a new project.
- Fixed refresh of calendargrid for events that are updated to whole day events
- Fixed server checking of autolink items when replying email messages
- Enabled TabScroll for main tabpanel
- Tasks module: return of "priority low" arrow in tasks grid.
- Fixed external link function for workflow.
- Fix incorrect mail encoding specifications from win-nnnn to Windows-nnnn
- sort index of custom field changed from tinyint to int to fix a sort bug when having over 127 fields
- Work around if IMAP server fails to respond to LIST-EXTENDED command\
- Updated Swedish and German translation
- many many relations were not deleted automatically
- Deleting addresslist kept multiselect filter enabled for the deleted list
- Security fix that could cause data loss.
- Quick edit grid changes if you change the column layout.
- Only search for links in link field when hitting enter
- Also decode BCC mime headers
- Use standard error dialog in e-mail composer so it's scrollable
- Duplicate company dialog bug
- Always use double digits for hour in logs

15-04-2013 4.1.55
- Added encoding check to email body to convert mail encoding to UTF-8.
- ActiveRecord->findByAttributes() and ActiveRecord->findSingleByAttributes() will create an IN query when the value is an array
- Fixed print button for SMIME signed emails
- GO_Mail_Message will not replace {variables} in the head of the HTML email template because CSS <style> might use { } characters
- Address book will load companies and contacts even when the eastpanel is collapsed
- New action to purge old events from the calendars
- Incorrect style definitions in html mails
- added  {prevdate} tag for printing recurring invoices periods
- The generated ICS files will have correct daylight saving time set
- Fixed saving of tasks recurrence to "no recurrence" value
- Order Dialog in billing is now modal
- Order Model will check for recurring Orders before Delete
- Recurred Order without an order_id can no longer be deleted
- Adding an Order status to an order book will no longer throw an Notice exception
- Increased integer size of file size column
- Optimized custom fields tree import
- SVG thumbnail and file handling added
- Add template attachments after switching template in e-mail composer
- Include second meeting request attachment for Outlook 2003 compatibility
- New option to log syncml only for specific users
- Calendar exceptions reappeared erroneously in caldav after changing the start time of the main event series.
- Prevent uneditable merged events in the calendar

05-04-2013 4.1.54
- Sort order wrong for resources in event dialog
- Holidays were listed at the bottom in calendar list view
- Updated Swedish

02-04-2013 4.1.53
- $config['allow_profile_edit']=false; flag works again
- Small Z-Push 2 beta fixes
- Update Norwegian translation
- Bug in displaying custom fields
- Show all links button not shown when it's not needed
- Hours approval: week disapproving works again.
- Calendar: admin can set event reminders in his own calendar.

29-03-2013 4.1.52
- Fixed multiple bugs in the Z-Push 2 beta implementation
- Calendar group view print fixed
- Added "Show all" link to 15 latest links displayed for all items.
- Added pagination toolbar to search results that doesn't know the total of
  results to keep performance optimal.
- Holidays could be generated on the wrong day
- Appointment invitations could have a confusing "Update event" link.
- redirect email filters had an input for multiple addresses but this is not supported by sieve
- Some messages were not displayed because they had body tags in the middle of an html message
- Show timeout error instead of JsonStore load exception
- GO will recognize IE10 as Internet Explorer browser isInternetExplorer() will return true
- Download filenames with UTF-8 character in IE10 will work as expected
- Reset password was broken
- removed time before tasks and birthday events in the calendar's month view
- Cron: enabling the users and group tabs when clicking on apply will now work.
- Changed labels on the button to add an email address to a new or existing contact in the addressbook
- Cost price of product dialog of billing will format correctly when loaded
- MultiSelectGrid will check for an ACL field in the linked model if $checkPermissions is not provided
- TimerButton didn't load time from database on reload the way it should (fixed)
- Billing, duplicate order: No more link between original order and duplicate order.
- Billing, recurring order: Link between original order and recurring order created on status change.
- Updated German language
- Config::get_setting() will return null of the config option is not yet in the database
- DbStore can attach extra PK values for the deletion of records with an PK of multiple columns
- Ticket dialog will clear the attachments queue after show or load
- Calculate mail usage in About dialog too.
- Comments and files are copied when you merge an item without deleting the source item.
- Always cache search results in default system language
- Hide disabled custom field categories in the display of contacts and companies

24-03-2013 4.1.50
- Ticket module kept hanging in the weekend.

24-03-2013 4.1.49
- Bug in ActiveRecord
- Move appointment with right click could create a corrupted unremovable appointment

22-03-2013 4.1.48 (Not released)
- When GroupOffice renders the Timer button it checks if it was already running and adjusts the button accordingly
- Comments in the EastPanel will not disable the scrollbar and adding from the EastPanel works in all panels
- Time registration: show all weeks of current year & automatically select current week.
- Link fixed to unsubscribe from address lists.
- Prevent double submits in dialogs by disabling buttons after click
- Refresh ticket status counters when changing a ticket status
- Wrong time format in trial creation dialog of servermanager
- Search on a 3 letter word didn't find results in some cases.

20-03-2013 4.1.47
- Changed cron so it can run multiple cron-jobs next to each other (Starting new job every minute)
- Searching for projects (to link them) will show the full project path (in case 2 subprojects have the same name)
- Projects overview grid will have a column path
- Z-Push2 - Added Delete functions to event/task/contact and note backend
- Z-Push2 - Only attach attachments of old mail when forwarding it, not on reply.
- Moved pspellsupport check to GO.settings instead of GO.Email
- Fixed category selection in Note synchronization when using Z-Push2
- Type combo will be loaded correctly when opening ticket dialog for the first time.
- Now recurring events are also shown in the pdf of the daily task/event mailer.
- Fixed filename of pdf in the daily task/event mailer (No commas)
- Fixed sorting the email folder tree and folder names that only have digits
- Drag&Drop in billing catalog was fixed
- Double click on an item in the Select Product Dialog will submit the form (add item to order)
- Fixed subscribing to IMAP folders
- Removed invalid l char that was displayed on login screen
- When adding an email to an existing contact the dialog will find contact without mail address as well
- The Edit button on the dialog that asks if you when to add an email to an existing contact is renamed to "Merge"
- Calendar group view grid is clickable to add events
- Added missing settings tab tables
- Tickets style matches e-mail now and tickets that were not answered within 24 hours are marked red.
- Custom fields are Open-Source now
- Addressbook search speed optimizations
- Installation always used euro for currency
- Fixed SQL error after deleting custom field
- Links were not searched correctly in the links dialog

15-03-2013 4.1.46
- Encode attachment string parts in z-push2 synced emails
- Fixed bug with showing all contact customfield tabs in the settings dialog
- Undefined time in portlet
- Updated Norwegian and Brasilian Portugese
- Automatic mail checker will run again

14-03-2013 4.1.45
- Strip unsupported datastore options from synthesis syncml client
- Added "checker_interval" option for config.php to change to interval for the automatic mail checker
- Fixed sending timeregistration approval email when closing week (uses new Swift mailer)
- Fixed problem with enabling of users and groups tab in the cron dialog.
- added options to user administration dialog for selection contact customfields tabs for displaying in the Global settings panel.
- Updated Croatian
- Popup reminders: improved handling of date and time when editing popup reminder.
- Fixed host config autodetection and time format selection at install time
- Updated Norwegian
- Fixed empty event dialog bug in firefox

12-03-2013 4.1.44
- IMPORTANT: New cron / task scheduler system. If you installed manually you must adjust
  the cron job as descripbed here: http://wiki4.group-office.com/wiki/Manual_install_on_other_Linux,_Windows_or_Mac_systems#Cron_job
- e-mail checker could update with wrong number sometimes
- Added support_link config option
- Tasks and contacts clickable in calendar
- Signed GOTA for 6 months.
- ExtJS base translations were missing
- Updated French
- Updating copy sieve rules could delete rule
- Editing an event in the month grid for the second time without refreshing
  loaded an empty dialog.
- Description missing in caldav.

08-03-2013 4.1.43
- Exclude noSelect folders from the folder listing in z-push2
- Remove auto check mailboxes automatically when they don't exist anymore
- Removed tinymce and ext source from scripts because they are not used.
- File creation date is not updated when editing files.
- All day events import had incorrect time
- Editing a sieve copy rule filter turned into a move rule
- Added date and time to e-mail client and dates always have leading zeros now for better alignment.
- Fixed sorting issue in calendar list view

08-03-2013 4.1.42
- Updated German
- Updated Swedish
- Updated Norwegian
- Don't sync shared private events
- Last event of a recurring series could not be edited
- Escape config values on Debian package installation
- Restored missing auto groups functionality in servermanager
- Private exceptions are called private now
- IMAP auth error reporting fixed
- Create default addressbooks, calendars etc. for imported users
- Login message was broken
- Full URL was always auto detected, even when set in config.php
- Fixed printing of read-only calendars.
- Added address book selection when creating new user so the administrator can choose in which address book the user contact will be saved.
- Added address list selection panel to the settings dialog.

06-03-2013 4.1.41
- In some cases Group-Office didn't load.

05-03-2013 4.1.40
- Wrapper class for ActiveStatement so that we can use persistent database connections
- Old framework not needed for boot anymore
- Prevent deletion the user with id=1
- Raised contact salutation field to 100 chars and cut it if it's longer.
- Unseen messages were not highlighted in e-mail portlet.
- Billing: optional extra costs line in orders if that is set in the order status. Can be removed manually or overwritten by new extra costs line when status is changed.

01-03-2013 4.1.39
- Improved calendar view grid
- Create temp dirs with 777 permissions to avoid permission issues after upgrade
- Clicking a day number on the month grid goes to the day view. Clicking on
  empty space in the cell adds an event.
- Everybody may create calendar views
- When freebusy permissions module is not installed users will always save
  meeting requests directly in to other user's calendars.
- Changed style of unread messages in e-mail client.
- Added date received column next to date sent column and made date received the
  default sort column because it is faster on old IMAP servers.
- Don't validate passwords on user import and fixed error reporting bug.
- Only last attachment was forwarded if there were duplicate names
- E-mail account dialog doesn't connect to IMAP if not necessary

28-02-2013 4.1.38
- Repeating exception event creation happened before conflict check which caused invalid duplicates.
- Don't create mailboxes with . or / chars in them
- Upgrade process created temp folder with root permissions.
- Moved cache folder to /tmp/cache and made it a config.php option too
- Removed lots of old redundant code
- Forwading mail with multiple attachments always gave a max size exceeded error.

27-02-2013 4.1.37
- Fixed problem with .(dot) date separator in the calendar headers
- Refactored free busy info in event dialog
- Show usage in about dialog in servermanager installations
- Moved private checkbox to first page of event dialog
- Include time in the error dialog so you can lookup relevant errors in the error log
- Outlook SyncML error fixed
- Check if the attachments in the e-mail composer exceed the max_attachment_size config value
- Fixed event copy with daylight saving. (Events that didn't last a full day where not copied to the correct date selected when copied from a summertime to wintertime date.)
- Cache folders for searching again. Rebuild search index is required to make current folders show up.
- Update appointments from icalendar invitation

22-02-2013 4.1.36
- Fixed caldav problem with tasks
- Updated German
- Don't sync private events anymore on shared calendars with z-push
- Fixed error messages in the task module

20-02-2013 4.1.35
- Read only calendars work in CalDAV
- Implemented free busy access check in participants grid
- limit description of links to 500 chars.
- Upgraded SabreDAV to 1.8.2
- All models are searchable by ID
- Restrict maintenance tasks to command line or tools module access.

15-02-2013 4.1.34
- File module errors fixed.

14-02-2013 4.1.33
- Filesearch does not index new files on the fly when opening them because it
  can use too many server resources.
- Filesearch didn't index files with () in the filename.
- Filesearch supports PDF with scanned image too now.
- Filesearch OCR language configurable now. See http://wiki4.group-office.com/wiki/Configuration_file#Filesystem_settings
- Set file mtime when updating with webdav
- Skip models in the database check that don't really need to be checked.
- Update English language for email module
- Show linked past appointments as well as linked future appointments in Display Panel.
- Show linked completed tasks as well as linked incomplete tasks in Display Panel.
- Display description of linked tasks and appointments (events) in Display Panel;
- Don't enable info log automatically. Only log when it's set.
- Disappearing event in calendar group view
- Editing recurring event always edited entire series in calendar group view
- Added -q option to cli to be quiet
- Send file handler straight away to avoid browser security measures

12-02-2013 4.1.32
- Remember checkbox values from the export dialog
- "email_on_new" functionality now contains ticket message in email body.
- Sort view calendars
- .ml-unseen-row and .ml-seen-row classes in vertical view of email message list fixed
- Update Norwegian and German
- Disabled IMAP message cache because it used a lot of diskspace and performance gain is questionable.
- Added MS Outlook style to client to fix some strange negative text indents.
- Set participant status from email
- Preset appointment name when creating it as a link
- Re-implemented accept and decline links for external programs
- Implemented default handlers so people don't have to make a choice
- Edited ticket messages turned into notes
- Removed pdf from google drive support
- octal problem with file_create_mode and folder_create_mode that prevented installation in some cases.

08-02-2013 4.1.31
- Fix broken e-mail template images
- Calendar name not visible in view grid

07-02-2013 4.1.30
- Billing ODT and PDF template now support {customer_countryname} tag to display the full country name
- Billing PDF templates can now use %project:xxx% template vars when a project is set in the order.
- Templates: Clear %xxx:xxx% template tags too when leaveEmptyTags is set to false.
- Fixed "Access denied" problem when having multiple calendars selected and you don't have write permission to the first one when adding new event.
- Filesystem check will keep running after an error on one of the items
- IMAP folders not escaped in ACL commands
- small bug in  logout message in infolog
- Improved style for adjusting paragraph tag margins
- Fixed bug that added global categories when viewing an e-mail with icalendar attachment
- It was possible to invite contacts to appointments without an e-mail address
- Sort the calendar views alphabetically.
- Context menu on ticket messages
- Load e-mail accounts in separate threads so it won't hang on a slow account
- Set decimals of Number custom fields.
- Advanced search: tree panel custom field support.
- Added 2nd mobile field to contacts
- Added download option to file context menu
- Automatically add surrounding wildcards to global search query
- Import private attribute with caldav and icalendar import
- Email module: copy emails between mailboxes and between accounts.

31-01-2013 4.1.29
- Start time instead of mtime for calendar search results.

31-01-2013 4.1.28
- Bug with deleting participant events in recurring series
- Updated Norwegian, Bulgarian and German
- newlines in <pre> tags were deleted when viewing mail
- Database check repairs webdav permissions if they're broken
- Serverclient domains box was very small in height with more than one domain
- Sort all day events alphabetically in the calendar Month view
- Show week 1 of 2013 in Hours Approval module.

29-01-2013 4.1.27
- Updated Hrvatski language
- Added AntiFloodPlugin to newsletter sender
- Fixed bold subject in email.
- Double IMAP UTF7 encoding for status calls
- Event/store will return results in the order of starting time for correct List display in calendar
- New file handler system so users can choose their application of preference
- Only show incomplete tasks in display panels
- Added simple content editing to sites module
- Fixed status=0 when creating event in outlook and synced with Funambol
- Updated Norwegian language
- Show correct quota in servermanager


28-01-2013 4.1.26
- Added strong password checks. (See http://wiki4.group-office.com/wiki/Configuration_file)
- Added Google Drive connection module to Documents package. You can edit documents directly in Google docs.
- When a standardTask contains a code or description this will be added to the time entries description field when selected it
- Bug on setting disabled custom fields on new addressbook
- Move cursor to top on Chrome in Html editor
- Fix for relative rss links
- Show as busy flag for resources works again
- Event description missing in export
- Automatically add resource group admins to resource calendar permissions
- Change calendar organizer when you change calendar selection.
- Automatically search for user_id in calendar participants (Based on email)
- Calendar: Touch(update mtime) the event when a participant is updated.
- Fixed sieve filter "set mail as read".
- Fixed required custom fields of type ContactsSelect and CompaniesSelect.
- Better way to search for contact or user
- Last all day event of repeating series not shown in day view
- Generate new ACL for old project calendars so it doesn't delete project permissions when it's deleted
- Set time registration status filters for all users in administration.
- Invitation links were not clickable with a very small screen
- Changed default PDF font to dejavusans to support UTF-8
- Added optional php5-mysqlnd dependency
- Made date sent on invoice PDF a template option

23-01-2013 4.1.25
- New tags in invoice items for the recurrence period.
- Prevent moving of special e-mail folders like drafts, trash and sent items
- Database check didn't flush output anymore

21-01-2013 4.1.24
- Temp file problem

21-01-2013 4.1.23
- Fixes in e-mail domains management
- Updated Hungarian language
- Tempfiles cleared bug

21-01-2013 4.1.22
- Broken 21 build

21-01-2013 4.1.21
- Error when you didn't have access to custom fields.
- Servermanager can sort on usage

21-01-2013 4.1.20
- Clicking Apply + Ok didn't save event in calendar.
- Updated Bulgarian translation

19-01-2013 4.1.19
- Error when you didn't have access to custom fields.
- Added search fields to addresslist and templates grid in administration panel of the addressbook module
- Added search fields to views, resource groups and resources grid in administration panel of the calendar module

18-01-2013 4.1.18
- Changed the extension function in the fs_file class so it will not change capital letters to lower letters
- In the email message grid added a row class for seen and unseen messages (.ml-seen-row and .ml-unseen-row)
- Various small calendar fixes
- Removed admin_only flag from user groups as it is replaced by permissions.
- Updated Swedish

15-01-2013 4.1.17
- Fixed incorrect sorting on startpage
- Updated German

11-01-2013 4.1.16
- Creating folders with mailbox root set resulted in empty error message.
- Various calendar / participants fixes
- Updated Scanbox module so it can handle files without an extension

10-01-2013 4.1.15
- Updated Portugese Brazilian translation
- Problem with deleting contacts

09-01-2013 4.1.14
- Events will create a UUID on databaseCheck()
- Event store will display the tasklists of all selected calendars
- In the email template dialog the button to add the company name to a template is fixed
- Added support for contact template tags in the email template from the ticket system
- Removed text from read/unread button in ticket panel to save space on the toolbar
- Added $config['log_max_days'] option for the maximum number of days in the log module
- Calendar portlet refactored
- Updated Italian and German

04-01-2012 4.1.13
- Wrong mails for resource confirmation
- Use mysqldump for backing up mysql databases now. Old script skipped databases with a hyphen in the name.
- Colors in calendar are working again.
- Color choose dialog checks ACL.
- Refresh account in cached e-mail messages
- Monthly recurring events could jump to 0:00 hours on Debian but not on Ubuntu
- Updated German
- Hide portlet panel for emails when no folders are selected
- Request only first page of calendars in init
- Cancelled events show red dot

03-01-2012 4.1.12
- New parameter for disabling editing of e-mail aliases $config['email_disable_aliases']=true;
- Reverted fix for forwarding duplicate attachment names

03-01-2012 4.1.11
- Added ticket template files to the Example template of the sites module.
- Add use permissions to members of a group by default and a small bugfix
- Get a direct link to open a folder in the file browser
- Bug in adding participants
- Server error when doing old library requests
- Updated German language
- Changing an event from repeating to none repeating could cause an invalid view
- Bug with merging custom fields
- Bug with truncating calendars
- One day missing in Outlook bug

02-01-2012 4.1.10
- 4.1.9 was not build from new repository.

02-01-2012 4.1.9
- Merged fixes from 4.0.145
- Better grid sorting error.
- Better error message for scanbox when linking to items that do not support files.

30-12-2012 4.1.8
- Incorrect data type for default numeric mysql fields
- Fix for servermanager quota's divided by 1024

17-12-2012 4.1.7
- Resource conflict check fixed
- 4.0.143 and 4.0.144 fixes merged
- Deleted participant events are set to cancelled and not deleted automatically.

14-12-2012 4.1.6 (beta)
- Updated Norwegian
- Fix disappearing panel in files
- Fixed invitation update problem

13-12-2012 4.1.5 (beta)
- File search in the filebrowser

07-12-2012 4.1.4 (beta)
- Folder bookmarks. Very handy when you regularly use a handful of folders out of hundreds.

05-12-2012 4.1.3 (beta)
- Problem with recurrence exceptions fixed in ActiveSync
- Event attendance could not be selected
- Events later then current time didn't show

04-12-2012 4.1.2 (beta)
- Small bug fixes

04-12-2012 4.1.1 (beta)
- Fixed the email tree display of  public namespace folders that are subscribed or have subscribed children
- Strange call to another controller action removed in contactcontroller
- It was possible to set addressbook to 0 for contacts
- new curl_proxy option didn't work
- Enabled custom field tabs were not showing
- Item not found error when clicking non events in calendar
- event description without htmlentities

30-11-2012 4.1.0 (beta)
- New calendar invitation system
- When using imap or ldap auth you can choose not to store passwords in the database.
- Improved GUI of the calendar view
- User groups can be added to a calendar view
- Normal users can no longer edit e-mail addresses associated to an account.
- Billing module can import payments from an MT940 file
- Ticket system can be setup for non-users too through the new sites module.
- Sites module refactored completely.


4.0.148
- fixed problem with daylight saving when copying events

11-01-2013 4.0.147
- Merging 2 records will unset fields with type 'date' and value '0000-00-00'
- Some invalid UTF8 strings could cause an empty mail display
- Custom fields contain empty strings instead of null values by default now.
  A database check must be performed to correct old columns.
- Fields were not sorted in advanced search dropdown

09-01-2013 4.0.146
- Fixed displaying of a red row when the user is disabled
- Companies and Contacts will still be imported if the email is invalid but without email-address

02-01-2013 4.0.145
- Fixed bug (Couldn't save alias: Field aliasAddress is required Field aliasGoto is required.) in serverclient module creating a user and adding a mailbox.
- Default task reminders will be set based on the given start_time when added from the quickbar
- Time registration in the first week was impossible
- Date saved when creating new expenses for projects in Projects module.
- Left and right panels in Notes module are collapsible.

18-12-2012 4.0.144
- Speedup link search by removing pagination
- Removed "Delete old events checkbox" from sync settings because it has no effect anymore.
- Add all search results to addresslist button
- Wrong display of arrows in all day multiday month event
- Custom fields were not copied when invoicing tickets
- Use custom from name when using SMIME for signing ticket messages
- Folders are no longer selectable in search.

13-12-2012 4.0.143
- Type label for ExportGridDialog will be translated
- Sync contacts with ActiveSync when company changes too
- Solved Access denied exception when using an SMIME email account for system messages
- Updated Bulgarian
- Better display of multi day events in month grid.
- Tasklist refresh button didn't refresh list
- Chrome grid render bug fixed
- Incorrect margin on P tags in e-mail display
- Translated file search labels

10-12-2012 4.0.142
- Validation check repeat end time for events
- Bookmark upload was broken
- Countries were not translated always.
- Prevent IMAP error: A5 NO Client tried to access nonexistent namespace. ( Mailbox name should probably be prefixed with: INBOX.
- Tree custom fields were missing on export

07-12-2012 4.0.141
- allow AR->find() to search for NULL value (change default operator from "=" to "IS" if value is NULL
- Included index time in filesearch preview
- Tool to create missing default calendars, tasklists etc.
- Added Bulgarian translation
- Failed e-mail account creation for LDAP login blocked login completely.
- Plain text version auto data tags were not replaced in newsletters
- Use search query in quick edit window for projects

05-12-2012 4.0.140
- Salutations were not used in newsletters
- Added russian translation to GOTA
- Updated German and Italian

04-12-2012 4.0.139
- Before importing contacts invalid email addresses will be remove so that the rest of the record is imported
- Package build error for 4.0.138

04-12-2012 4.0.138
- Creating orders from contacts didn't work
- When we merge two contacts the contact photo is not merged.
- Merge contact photo too
- addressbook permissions caused error in webdav on some installs
- Tags "{user:work_phone}" and "{user:work_fax}" are now selectable in Email Template Dialog.
- Added the "company_id" field to the batch editor of a contact
- Removed the UUID field from the batch editor of a contact

03-12-2012 4.0.137
- New class GO_Files_Fs_UserLogFile to create log files inside the current user's personal folder.
- Tasks did not show in 4.0.136
- Custom time registration fields were not editable afterwards

03-12-2012 4.0.136
- Strange call to another controller action removed in contactcontroller
- It was possible to set addressbook to 0 for contacts
- new curl_proxy option didn't work

30-11-2012 4.0.135
- Bookmarks icon in start menu
- Bookmark context menu respects permissions
- Added large public folder icon
- When opening a draft it always used the first alias and not the original one
  if you have multiple.
- Formatting booleans as yes/no broke some functions like calendar views

29-11-2012 4.0.134
- Added $config['curl_proxy'] config option
- Ignore different quotes in check language
- Fixed bug with the check if a user is a manager for a ticket type
- Now the search for known email addresses in the email composer will only search for the contact name, email, email2, email3
- New reminder field caused error when empty

28-11-2012 4.0.133
- Customfield-headers will not be shown if they are not followed by none empty customfields
- Added a SystemMessage object for creating emails that need to be sent from the GO system
- Let the ticket module make use of the new SystemMessage object for sending the ticket emails
- Better error handling for newsletters and include addresslist filter in select dialogs
- Updated German translation
- Repeat every x number of days/weeks/months is now a number input field instead of a combobox
- Week was missing from reminder selection in event dialog
- Added "Created by" column to tasks grid
- Format boolean columns as Yes and No
- Global categories are only accessible for calendar admins
- Command line download from shop update tool was broken
- Name doesn't have to be unique of addressbook, tasklist and calendar
- Added $config['gota_blacklist_extensions'] option to prevent GOTA to be used on certain file extensions
- Updated TCPDF
- Clear messages grid on portlet when last folder was removed

23-11-2012 4.0.132
- Added a check to show the "Show mine only" checkbox in the ticket grid. From now it will only be showed when you have manage persmissions on one of the ticket types
- Throw an error when the "Stop" action in a sieve rule is not the last action.
- E-mail without name in header appeared as "" <email@domain.com>
- GOTA didn't run on Java 6.
- Unable to save contacts or companies when a required custom field was on a disabled tab
- Close database connection after z-push ping so the number of open mysql connections will be much lower.

20-11-2012 4.0.131
- Better error message when PDF template is missing for order
- Regex field was not enabled for custom text fields initially
- Subfolders with / as delimiter didn't sync with ActiveSync

19-11-2012 4.0.130
- Ticket status open and closed were not saved
- Added IMAP commands to debugging window
- Sometimes not all phone numbers were included in Outlook sync
- Fixed charset problem with russian characters in the item description of a bill when creating an ODF file.
- Added option to set the {order_id} in the odf file name in the billing module.
- Check if IMAP server supports quota before executing that command.
- Normalize CRLF for SMIME output because it failed on some SMTP servers

19-11-2012 4.0.129
- Task dialog didn't render without access to calendar module.
- Better address handling with syncml
- Case insensitive check of SMIME e-mail address
- Log files were readable to all users
- Email notification sounds will work again
- Ticketsgrid refreshes when displaying the tickets tab
- Debug SQL queries with CTRL+F7
- Added enabled column to users grid

15-11-2012 4.0.128
- Better reporting for IMAP errors
- Billing Order.php sent output on some apache servers

14-11-2012 4.0.127
- Increasing font-size with the HTML editor works Chrome.
- Fixed problem with Heading custom field in combination with event resources panel.
- Account selectbox on IMAP mailbox sharing now loads the accounts better.
- Updated Swedish, Dutch and German translation
- Use spam icon for INBOX.Spam too
- Fixed problem with resources when $config['calendar_category_required'] is set to true
- Don't cache autoload classes because it caused cache slam and it doesn't have a performance impact.
- E-mail account sorting was no longer possible
- Billing PDF could sometimes add empty page
- Debug with CTRL+F7 didn't work in internet explorer.
- Brought back public SMIME certificates window.

12-11-2012 4.0.126
- Added option to disable "Read more" links in the comments
- Changed display of tasks in the startpage, now only showing active tasks instead of active and upcoming.
- Added config option for comment category selection to be required. This can be managed by the server admin in the config.php file ($config['comments_category_required'] = true;).
- Added config option for the calendar's event category selection to be required. This can be managed by the server admin in the config.php file ($config['calendar_category_required'] = true;).
- Session not entirely cleared when switching user
- VTIMEZONE export was incorrect.
- VTIMEZONE fix in .125 broke caldav, calendar export and calendar invitations!

09-11-2012 4.0.125
- VTIMEZONE export was incorrect
- Root folders with subfolders still showed even when unsubscribed
- Security issue fixed that could only be exploited by a logged on user.
- Enable debug mode with CTRL+F7
- Added possibility to add the customfields of a user to an email template like {user:col_**}
- Added "Delete all items" buttons to tasklist, calendar and addressbook administration dialog
- Create folder on root level of e-mail tree

07-11-2012 4.0.124
- E-mail Inbox and account nodes expanded by default again
- Remember attachments when switching e-mail template
- Added some more options to e-mail account context menu
- Add senders to addresslist displayed addresslists twice sometimes
- Updated Norwegian translation
- Fixed UTF8 problem in IE with downloads

06-11-2012 4.0.123
- Addresslist filters in the Addressbook module will only apply to the grid next to it.
- Fixed status message problem in ticket panel
- Sort the files in the projects displaypanel on mtime DESC
- Mark complete e-mail folder as read function added
- Separate context menu for e-mail account
- Prevent deletion of special folders in e-mail like Sent items, Trash and Drafts
- Continue to next page on billing could not be unchecked
- Updated German translation
- Link descriptions implemented
- Fixed "Could not unserialize key data from file" notices in log

05-11-2012 4.0.122
- Create new user group: able to add new users immediately after group has been created.
- Latest versions of PHP 5.4 demand that email messages encodings are explicitly defined in GO, and so they are now.
- RecurrencePattern could go into endless loop
- TreeSelect custom field will display the value formatted correctly
- The quick add bar for tasks will use the specified date for begin and end time of task
- Updated Norwegian translation
- When using LDAP authentication it will not fall back to Group-Office login when LDAP authentication fails.
- Smarter address and housenumber handling with ActiveSync
- Use <base> html tag to correct relative links in HTML e-mails
- Multiple resources were not saved
- E-mail folder with a " sign was not selectable

29-10-2012 4.0.121
- New log file contained too much info.
- Make relative image urls absolute in rss feeds
- Updated Italian translation

29-10-2012 4.0.120
- Changed order of the add and delete button in the standard T-bar of the gridPanels
- Make title of exportDialog translatable
- Added more space between the product amount and the tax per line in the Billing PDF
- MoreData support in syncml
- Errors logged to Group-Office log file accessible with file manager by admins
- Fixed deleting of old versioned files

25-10-2012 4.0.119
- Table load speed optimization with multiselectgrid for addressbook, tickets, notes. tasks and billing
- Default status of calendar event is accepted
- Disable editing and deleting of merged events when viewing multiple calendars
- Fixed html display of emails with multiple body tags

25-10-2012 4.0.118
- E-mail showing malicious content bug

24-10-2012 4.0.117
- Option to open/close a selected week for all users (Billing, Time Registration, Hours Approval).
- Reminders were not synced with Outlook
- Links were not clickable from e-mail portlet
- Conflicting events check didn't work with busy flag and repeating events
- SMTP password wasn't updated on LDAP login
- Shared folders could appear twice in the shares tree
- Create folder permission check failed if a parent folder didn't exist
- Added Incomplete filter to the tasks list
- Links in email window on startpage didn't work
- Unset invalid attributes while using importVObject function in Contact model.
- Throw a fileNotFound exception when a file that needs to be downloaded is not found on the server.
- Updated German translation

19-10-2012 4.0.116
- Also show disabled users in selectuser
- Correct key.txt permissions on fresh install
- Check if project folders are available in webdav
- Better error reporting for LDAP auth and sync
- Log about changes to postfix mailboxes and domains
- Fixed upgrade from 3 to 4 issues
- Custom field records were not deleted

17-10-2012 4.0.115
- PDF error in billing

17-10-2012 4.0.114
- Invalid weekly recurrence rule for funambol
- Error Call to a member function isWritable() on a non-object in /usr/share/groupoffice/modules/files/model/Folder.php fixed.
- Removed call to the htmleditor's syncValue() function when the editor is in sourceEdit mode.

16-10-2012 4.0.113
- When adding / deleting contacts from address list thru the email message list, the system looks also for the second and third email addresses of contacts.
- Wrong timezone info in vcalendar export
- Check filesystem permissions before change file or folder models
- Copy paste in same files folder appends a number eg. test becomes test (1)
- Editing sieve actions resulted in duplication
- Controller actions can be locked to only one user.
- Custom fields were disabled in last version.

12-10-2012 4.0.112
- Email validation will no longer accept 2 dots right after each other in domain part
- First ticket by customer did not send an autoreply message
- Workaround IE DST bug in month grid of calendar
- Reimplemented show from others in ticketsystem
- Changed the classname for MultiSelectGrid in projects to: GO_Projects_Model_Status
- In PHP 5.4, ReflectionProperty::getName() sometimes erroneously returned false.
- Portlet folders were not deleted along with account.
- Projects portlet double click didn't work
- GOTA didn't work with servers that used chunked encoding
- Updated German translation
- Sorting of shares fixed.
- Dutch translation for billing module
- Regex handling of customfields didn't work properly when there was no flag given.
- Order PDF template will show item group totals for every item group not just the last one
- Added homepage to contact info
- Set default sort order for tasklists
- Disable APC cache temporarily because it seems to cause random logouts

11-10-2012 4.0.111
- Tickettypes permissions can no longer be "read and create"
- Important fix from bug in previous version, to enable the adding of users
- When creating a new order from a Contact or Company in the addressbook the Contact or Company will be selected in the new order dialog as well
- Saved attachments could end up in the root folder in rare cases.
- E-mail reminder popped up too much
- Mute e-mail sound alone didn't work
- Updated Italian translation
- Funambol fields changed after syncing and phone numbers could get lost.
- Implemented freebusy permissions
- limit_usersearch didn't apply in acl permissions panel.
- Find users on full name didn't work
- Inconsistent sorting of full user or names. John Doe or Doe, John.
- Auto create calendar categories when importing or syncing
- Sort by TO field in sent items
- Visible tasklist selection was broken
- Default user id was not set anymore since last update.

08-10-2012 4.0.110
- Email message priority was not shown
- Changes to e-mail templates were not saved
- Updated German and Norwegian translation
- Auto link text misplaced in some languages.
- Initial sorting of tasks was incorrect.
- Only show module permissions tab in group dialog when you have access to the module management module.
- Close inactive tickets through cron job and exclude a status
- Pass beforeLogin $params by reference to modify in modules
- Read only custom fields could not be enabled in grids
- Don't crash on permission error for e-mail account in sync
- New comment form rendered twice sometimes
- Database check will set empty properties with default values when they're empty.
- Entering an empty string into a custom field will result in a null value in the database.

03-10-2012 4.0.109
- Remove spaces at beginning and end of fields before CSV-importing them.
- Attach files button in ticket dialog didn't work the second time.
- Database check could delete recurrence end time in very rare cases
- Changed: Open composer for sent items when you double click
- Added a special Swift preference to escape dots. For some buggy SMTP servers
  this is necessary.
- Import category color for appointments
- Create read only custom field categories. So you can make sure people
  can view them but not alter them.
- Save event time in Time Registration fixed.
- Opening advanced search dialog from SelectContactDialog / SelectCompanyDialog.
- More elegant error message for empty winmail.dat files

01-10-2012 4.0.108
- Copy event in calendar moved the event
- Comments didn't show text in browse all dialog.
- Question for meeting request was asked when you put an event directly into someone else's calendar
- Tickets buoy indicator showed invalid number for non-agents
- Don't cache tickets files because there are permissions issues. Everyone has read access to the types but may not see other peoples files.

01-10-2012 4.0.107
- in the task edit dialog Tasklists with create rights can be selected
- Fixed undefined index "-1" error recurrences
- Sort on column 'name' in Add User Permissions window.
- ActiveSync: Always try to get a sender name when sending an email
- Old custom field tags didn't always work on the billing PDF
- Upgraded plupload to version 1.5.4
- invalid subject on event invitations
- Also popup and play alarm when email panel is open.
- Improved calendar invitation messages in the e-mail client
- Saving company in user dialog was broken
- Calendar category import and export support
- Import recurring events with days would shift.
- Crash if filesearch module was loaded before files
- Update could add duplicate admin e-mail accounts.
- Some recurrence patterns were not accepted by iOs with Caldav
- Project template wasn't competely applied the second time

26-09-2012 4.0.106
- HTML field with help text crashed
- Gota didn't open second document
- Adding tasks using the quick add bar will respect the default reminder settings of a user


25-09-2012 4.0.105
- Remove old line in italian language file of billing module that gave errors
- Calendar resources can now be deleted
- project selectType and templateSelect dropdown is disabled when permission_level < writeAndDelete
- modified attributes of ActiveRecord will work with types other then string
- Drag and drop a task on a different tasklist now is in MVC style
- Fixed issue with Dismiss a reminder in the calendar module.
- Fixed "undefined function $this->escape...." bug in filesearch.
- Added German translation to the filesearch module
- Unsubscribe link was broken
- E-mail desktop integration didn't work with Internet Explorer
- Absolute positioned elements in e-mails could mess up layout
- Added new tag for document templates: {contact:sirmadam} Sir or Madam depending on the gender.
- Strange page breaking of PDF's in billing
- Time Registration save hours bug fixed
- On delete: set "max_execution_time" to 3 minutes because it can take a while to delete all relations of an object.

21-09-2012 4.0.104
- Change: behavior of ActiveRecord->duplicate() won't call delete() before it is saved and wont unset multicolumn PKs
- Change ActiveRecord->hasFiles() will return true if column files_folder_id is in the database false otherwise
- Invoice PDF was displaying the totals on the right side when not displaying any prices on invoice lines
- cached zip file reset after consecutive email attachment downloads by full zip.
- Comments module enhanced with categories and easier input fields.
- Updated French translation
- Find matching company on import in matching address book (import + syncml issue)
- GOTA asks for password when session is expired or user logged out
- Appended the subject of the appointment to all email subjects.
- Updated Windows registry file for e-mail clients
- E-mail filter dialog was broken in Internet Explorer
- Reminders didn't include name in e-mail subject
- Deleting e-mails failed when trash folder was disabled
- Linking e-mails were always saved in the root folder even when selecting a different folder.
- After enabling sharing in folder properties the permissions panel didn't activate immediately
- Group management by non admin user didn't work right
- Weekly recurring tasks shifted day

20-09-2012 4.0.103
- Billing PDF templates got unwanted extra columns

20-09-2012 4.0.102
- GOTA didn't launch
- Copy/pasting products in the catalog of the billing module will work
- Duplicating active record relations will keep the primary key when it exists of more then one column

19-09-2012 4.0.101
- Default values for custom fields were not set correctly.
- Check for php 5.3 or greater when upgrading
- Creating a time-registration from a calendar event works now.


19-09-2012 4.0.100
- Invalid validation for agent in ticket
- Quota display was divided by 1024
- Updated Russian translation
- Updated Italian translation
- Added config option "$config["default_max_rows_list"]" to set the default for new users. (max is 50)
- Added Android detection for GOTA
- Automatically fill in start date for new project

17-09-2012 4.0.99
- Somehow pro debian package was missing

17-09-2012 4.0.98
- Ticket rates in Add/Edit ticket dialog will only be shown where there are ticket rates
- Validate if the selected agent can be agent for the selected ticket type
- Save button is shown in the Ticket administation dialog when adding rates
- Automatic creation of call reminder task with newly created orders.
- Changing user settings will no longer place old user data into a session in setCompatibilitySessionVars() themes will get changed right away
- Will not set default values for ActiveRecord set in the code when importing
- Will not convert a date field when no date is set when importing
- PDF for Invoice/Order wouldn't print total prices when piece price or product price where not printed
- PDF for Invoice/Order will now accept the page format from the template
- Upgraded SabreDAV library to 1.6.4
- Allow underscores in custom ICS tags for importing.
- Error with undefined variable in calendar invitation
- Optimized code for recurrence calculations in the calendar so it will not calculate more than needed.
- Fixed problem with accepting invitations that have a different mime-type than 'text/calendar'
- Groups module could be opened twice
- Custom field column cache was not cleared when modified.
- Disable sorting on alias and mailboxes columns in postfixadmin
- Updated German translation
- Adding users and groups to manage permissions only got read permissions.
- HTC deletes old appointments. We don't like that so we refuse to delete appointments older then 7 days with ActiveSync.
- Updated Swedish translation
- Normalize CRLF to LF in database to prevent sync issues.
- Not all contacts were synced when sending multiple packages in syncml
- Invalid XML in JNLP file caused GOTA to fail with OpenJDK
- Browse comments in ticket didn't work
- Added beforeValidate function to the ActiveRecord
- Tag "{weeknr}" for new projects' names, just like the tag "{autoid}".
- Added afterLoad function to the ActiveRecord
- Updated Norwegian translation

11-09-2012 4.0.97
- Important security problem fixed!
- Change: the priority selectbox will be shown to customers, they can set the priority of there own tickets.
- Fixed bug when using a different database port
- Now the replacement of fields(%field%) is also working in the footer of a billing PDF
- Added diff function to GO_Base_Fs_File class
- Import failed on events without name.
- Trash folder in e-mail didn't work since last update
- Fixed date format bug in ActiveRecord when not logged in
- Fixed temp folder bug in Config.php when not logged in
- Added check for Amavis configuration file change so it will not be overwritten on every update.
- Updated CLI only check
- Calendar import more memory efficient and fixed some bugs
- When moving messages the folder switched to the folder messages were moved to.

06-09-2012 4.0.96
- Option to automatically check mailboxes for unread messages.
- Keep the state of the fileDialog tree to the last selected folder, this is kept in the current session
- Added an email portlet to the start page
- Switch to plain text mode when composing e-mail on an ipad
- Fixed syncing of recurring events and its exceptions
- Debughandler for sieve removed in constructor, this interferes with the debug handler of z-push
- Fixed upgrade 3.7 to 4.x crash when column layout changed in some cases.
- If you don't click the permissions tab when creating a user, the defaults are not applied for the memberships.
- Public ICS calendar export wasn't visisble without logging in.
- IBAN was not shown on company info if bank no. was not entered
- Strict checking in setAttribute. Renaming 1 to 1.0 didn't work.
- Correct log dir permissions after debian package upgrade
- When a user uploaded the same attachment twice, a number was appended.
- urldecode query string in mailto handler
- Show correct owner in event dialog
- LDAP user sync script added.
- Sorting on name in groups module
- Reimplemented e-mail filtering without sieve support
- Added workflow support to the billing orders
- Removed Apply button from ticket dialog
- Added new portlet that shows files uploaded in the last 7 days.
- Fixed: file does not exist: /themes errors in apache log
- Subfolders of the sent, drafts and trash folders in the e-mail were not treated as such.
- Serverclient did not update the mailbox in the servermanager
- Error on logging e-mail
- {date} tag didn't work in document templates.
- Document templates can work for other files like PDF as well. No tags will be replaced.
- New mailto handler see http://wiki4.group-office.com/wiki/E-mail#Set_Group-Office_as_your_default_e-mail_client
- New e-mail notifications were broken

03-09-2012 4.0.95
- Missing content-lenght header caused GOTA problem.

31-08-2012 4.0.94
- Bug: No temp file for inline attachment

31-08-2012 4.0.93
- Added new recently modified files portlet.

31-08-2012 4.0.92
- Encoding characterset was not set when displaying saved email messages, didn't display russian characters
- Forgetful FilesGrid now remembers its state
- Calendar: removed GO version from calendar print
- Change go_log ip field to 45 chars to support ipv6
- Updated Norwegian language
- Added experimental contact autolink option
- Rollback of permission check by deleting shared mailbox messages
- Fixed: Rounding error in billing module
- Updated German language

29-08-2012 4.0.91
- Fixed: SyncML duplicated contacts on client when there were more than 200 contacts.
- Fixed: Inline images were corrupted when switching e-mail templates
- Optimized: speed in rendering custom field columns
- Fixed: Fixed resize of customfieldsdialog optionsgrid
- Updated Italian translation
- Fixed: Digest authentication for WebDAV, CalDAV and CardDAV failed after changing password. Logging into Group-Office again re-enabled the login.
- Improved: Advanced search improved with visual indication and it's translatable.
- Fixed: Printing calendar, events have a black background

28-08-2012 4.0.90
- Fixed: GOTA crashed after java update 1.7.06
- Fixed: When adding a link to an email the receiver does not have access to the mail could not be read. New behavior: will just not link the item.
- Missing feature: When adding a new project "Create new project type" wasn't working yet
- Fixed en language in email module.
- Fixed blinkTitle bug
- Urldecode added to the store load function of the bookmark module
- Event->delete() will ignore ACL permissions when it is a resource of the current user
- Database fix for recurring events that could have been added when there was a bug in previous version.
- Updated German language
- Updated Italian language
- No files got attached to the email composer when using "Send files" in the Files module when the users default template is 'None'
- Email module MessageGrid will expand when it is collapsed and the visible email gets deleted.
- Birthday was not showed for the correct year.
- Added check for delete permission on the email account when a user tries to delete emails.(Only applied on the server side)
- The customfield dialog can now be resized
- Customfields: Hide permissionlevels for categories.
- Calendar: Always ask to send invitation mail when adding participants (Don't check for the "add directly to the other calendars" checkbox anymore)
- Email: Fixed bug with attachments that have an empty line that get corrupted when forwarding them.
- Email: Fixed bug with semicolon in text_to_html function
- LDAPAUTH: Fixed mapping for uid in LDAP authenticator
- Email: Fixed save attachments as zip function
- Autocreate Group fixed in servermanager

08-08-2012 4.0.89

- Fixed problem with default background color in events
- Fix export orientation when exporting PDFs works again
- Fix calendar:
 -- update event with resource.
 -- delete resource from event
 -- select resource when only write permissions
 -- send mail when deleting event to participants
- Fixed label Agent when exporting tickets
- Fixed hr translation in Links module.
- Fixed task bug in Caldav backend
- User can enter IMAP password on IMAP connection failed.

03-08-2012 4.0.88
- Fixed bug: E-mail download link date one day off
- Signed GOTA
- Added aclModified check to activeRecord to determine if an Acl is changed.

02-08-2012 4.0.87
- Fixed Bug: Cannot export and import uuid in addressbook contacts. (Now the uuid field is available in export and import)
- Fixed Language: Timeregistration translation in French for customfields is fixed
- Updated German translation of GOTA
- Fixed bug in SyncML

02-08-2012 4.0.86
- Fixed Bug: Timeregistration export table columns are on top of each other when having a long project name
- Fixed bug: Settings->Synchronisation->Email-Account is not saved after panel submit
- Fixed bug: Autocomplete in e-mail sometimes didn't work
- Fixed bug: Funambol sync sometimes imported garbled characters.
- Updated Norwegian and German translations

01-08-2012 4.0.85
- Updated German
- Small bug fixes

30-07-2012 4.0.83
- Small bug fixes

30-07-2012 4.0.82
- Sync only selected addressbooks and calendars with CalDAV and CardDAV
- Small bug fixes

30-07-2012 4.0.81
- Fixed file system permissions issue.

30-07-2012 4.0.78
- Bug in install.sql of addressbook that made new installations fail.
- Bug in Hungarian language files
- Bug in monthly recurring event.

27-07-2012 4.0.77
- Updated German
- CardDAV didn't work on the Mac
- Other small bug fixes.

26-07-2012 4.0.76
- Small bug fixes
- Updated Norwegian
- Manage SyncML devices

26-07-2012 4.0.75
- Fix for latest funambol client
- Fix for iOS caldav and carddav connection

25-07-2012 4.0.74
- Bug in multiselect panels.

25-07-2012 4.0.73
- Better memory management in ActiveRecords
- Updated German translation
- Small usability improvements
- Bug fix in SyncML (Outlook sync)
- Small bug fixes
- Username and password cookies were not encrypted
- Security bug in calendar
- Notes can be encrypted with a password now.
- New encrypted custom field

12-07-2012 4.0.72
- Small bug fixes
- Delete old mail replaced by move old mail so it can be used for archiving too.
- Bug in connecting to postfix
- Edit e-mail button opened GOTA. Now it launches the E-mail viewer.
- Bug fix with selecting next mail after deleting one.

12-07-2012 4.0.70
- GOTA wasn't working on some servers
- Some tags were missing in document templates.
- New E-mail notify sometimes didn't show.

10-07-2012 4.0.67
- Strange characters from Romanian and Hungarian language files removed.
- Bug in e-mail sync with some IMAP servers.
- Folders with underscores didn't expand sometimes
- All info panels show the type and name on top now. More consistent.
- Workflow module has an icon now.

09-07-2012 4.0.64
- Various small bugfixes

06-07-2012 4.0.63
- Don't autolink Drafts, Sent Items and trash messages.
- E-mail files function
- Scanbox module included

05-07-2012 4.0.62
- Cache problem with reply all and language change

04-07-2012 4.0.60
- Speed optimizations in mail client
- Sync unread flags bug
- Small bug fixes

03-07-2012 4.0.57
- Cache problem that caused a lot of problems in the e-mail client

02-07-2012 4.0.56
- Bug in CalDAV with Applie ical fixed.
- Bug in calendar invitations fixed.
- Bug in announcement formatting.
- Bug with changing fonts in E-mail composer. Font selection removed.

26-06-2012 4.0.50
- Lot's of bug fixes thanks to the beta testers!
- SabreDAV upgraded to 1.6.3
- SwiftMailer upgraded
- Searching uses full text index now to achieve better performance
- E-mail module no longer uses database cache. It connects to IMAP directly.

11-04-2012 4.0.1 (beta)
- Complete new MVC PHP framework
- Extensive security audit by Cigitel.
- Users are stored in the addressbook too.
- Ticket groups for better permission management.
- plupload used for file uploads
- gnupg support dropped. smime still supported.
- Filesearch module for deep searching files content.
- Document workflow module.
- SabreDAV upgraded to 1.5.6
- Swift Mailer upgraded to 4.1.5
- Project tasks have a relation now and have a percentage complete field
- Photo support in sync.
- Sync multiple calendars
- Billing module can create MS word and open-office invoices, quotes and orders.
- Billing module can handle purchase orders and stock.

Addressbook
- Batch edit in addressbook.
- Merge companies and contacts
- Easier and more secure advanced search.

E-mail
- Embed pasted images from firefox into mime mail for better memory management
- Links in messages become relative links when cut & pasted. We make them absolute again on when the message is sent.
- Automatic linking of e-mail replies when sent from a projects, contact, company etc.

WARNINGS:
- Projects: batch report queries will not work anymore. They need to be reconfigured.
- Billing: Custom fields in templates used to be %Name of customfield% This should be replaced by {col_1}



06-01-2012 3.7.42
- Bug fix in Russian reminders.
- Small adjustments for customer customization module.
- Some other small bugs where fixed.

22-12-2011 3.7.41
- Small bug fixes

16-12-2011 3.7.40
- Bug with mail headers on some servers

07-12-2011 3.7.39
- Create mail folders was broken
- Download of email attachments bug

01-12-2011 3.7.38
- Bug in calendar events display with multiselect

30-11-2011 3.7.37
- Missing table on calendar bug.

29-11-2011 3.7.36
- GOTA was broken in latest releases.
- Calendar colors with multiple selection can be defined by the user.
- UID of calendar items got lost when syncing with ActiveSync. This caused problems with CalDAV.
- Forwarding inline images that came from Apple imail failed sometimes.

17-11-2011 3.7.35
- Several small bug fixes
- Added Vietnamese

18-10-2011 3.7.34
- Creating initial sieve script was broken
- ldapauth and z-push could trigger an endless logout loop.

10-10-2011 3.7.33
- Worked around Funambol bug in client 10.0.1 where line wrapping is not supported.

30-09-2011 3.7.32
- Fixed sort bug in addressbook

30-09-2011 3.7.31
- Updated French and Russian.
- Fixed several small bugs.
- Added addressbook column to companies grid.

26-09-2011 3.7.30
- Various small bugfixes.

02-09-2011 3.7.28
- Fixed bug in ActiveSync with Android 2.3.3
- Other small bug fixes.
- Fixed bug in delete old mail
- Bug fixes with webdav
- New module for admins to become other users

17-08-2011 3.7.27
- WebDav locking is enabled
- Fixed some webdav bugs.
- TCPDF upgraded to latest version.
- Bug in some e-mail display.
- Smime decryption bugs fixed.
- Syncml decoding error fixed.
- Translation updates.
- Better GUI for selecting tasklists, addressbooks and note categories in Settings -> Sync

28-07-2011 3.7.26
- Smiley's broke some e-mails and did not work case insensitive.

26-07-2011 3.7.25
- SMIME info on print
- Disable beeps for e-mail separately
- Context menu on event to Send mail to all participants
- Context menu on contacts to send mail to multiple
- Simple smiley support in texts
- Caldav iphone status bug. Imported status defaults to accepted now. iphone doesn't like the status needs-action.
- Fixed SQL and command injections after extensive security testing.
- New caldav_max_months_old config.php parameter to control the maximum age of synced events.
- E-mail composer HTML editor fix for Chrome.
- Updated german translation
- When a download link is sent in the files module. Info about that should be shown in the info panel.
- Set global vacation subject via configuration file
- In some cases webdav only created 0 byte files

23-06-2011 3.7.24
- New config variable to set subject for sieve vacation messages.
- Worked around php smime header bug.
- Made a checkbox to enable the login screen text at the settings module.
  Don't forget to check this when you already have a text setup.
- User groups list was showing less rows then it should.
- User groups are searchable
- Bug in calendar with removing a single instance from a recurring event.

16/06/2011 3.7.23
- Fixed bug with etags in caldav
- New ticket message template tags and attach files when saving an e-mail as ticket
- Check if SMIME certificate address matches the sender address
- Fixed some SQL injection vulnarabilites.

06/06/2011 3.7.22
- Timer in timeregistration didn't add values.
- You can now download attached file from the composer.
- Small bug fixes

31/05/2011 3.7.21
- Worked around PHP bug in smime functions for older php versions.
- Calendar tooltip was broken due to ExtJS upgrade

25/05/2011 3.7.20
- All day events that repeated were shown with one day too much.

25/05/2011 3.7.19
- Fixed SMIME bugs and enhanced it with icons.
- Problem with CalDAV not sending some appointments to the client.
- Small bug fixes.
- ExtJS upgrade to 3.3.3

19/05/2011 3.7.18
- SMIME didn't work with attachments.
- Other small SMIME bug fixes.
- Updated Norwegian language pack
- Calendar list background color match the event colors when you select multiple
  calendars.
- Tasks counter and indicator appears when new tasks are created for you.


17/05/2011 3.7.17
- SMIME didn't work on some MySQL servers
- SMIME enhancements and bug fixes.
- Other small bug fixes.

16/05/2011 3.7.16
- Downloading attachments was broken
- Changing the smime preferences didn't work properly.

13/05/2011 3.7.15
- Removed nagging about adding new customer to addressbook in the billing modules
- Minified script of smime was missing.

13/05/2011 3.7.14
- When using the timer in the timeregistration it now adds the time to existing
  entries.
- New module for SMIME signing and encryption
- Template and permission type can only be changed if you have manage permission.
- Projects couldn't be dragged to the top level
- Created new config option nav_page_size to set the number of displayed
  calendars, notebooks, tasklists etc. per page.
- Added report parameters to PDF export at Projects report
- Invitations for occurences of a recurring event didn't work right.
- Problems with CalDAV and recurring events fixed.
- Upgrade SabreDAV vendor software to version 1.4.3
- New log/info.log in home directory that currently logs logouts, successful-
  and failed logins.

29/04/2011 3.7.13
- Unpacked archives didn't show contents in the file manager
- Users with manage permissions for a file folder can manage the permissions now.
- SMTP password wasn't updated if LDAP or IMAP password changed and
  'smtp_use_login_credentials'=>true in imapauth.config.php
- Add billing order was broken in IE
- Hide empty sections in information panels
- IE9 and Chrome style issues fixed
- Project reports depended on Custom fields
- Delete permission is respected in the calendar
- Participants status copied when using the links from an external mail client.
- Bug in PDF printing

27/04/2011 3.7.12
- E-mail editor was behaving strange in Internet Explorer
- Sorting in projects detailed report grid.
- New TCPDF version required cache folder for PNG files.
- IMAP ACL enabled in Debian packages
- Small bug fixes

22/04/2011 3.7.10
- Group-Office wouldn't start if local_listeners.php wasn't there.
- Bookmarks module imports favicons and it uses 16x16 icons now so we can use
  those in the start menu too.

22/04/2011 3.7.9
- IMAP ACL support
- New local_listeners.php script that can be put into the config directory to add
  custom listeners. See: http://www.group-office.com/w/index.php/Event_handling#Adding_event_listeners_in_a_simple_way
- Several bug fixes

07/04/2011 3.7.8
- Upgraded TCPDF to version 5.9.065
- Bug where ID of a permission type was shown instead of name in the projects module.
- German translation updated.
- Norwegian translation updated.
- Small bug fixes
- Bookmarks can be set as module tabs

06/04/2011 3.7.7
- Resource busy status for unconfirmed booking is configurable per group
- Fixed security token mismatch error in IE.
- Fixed bug in saving statuses displayed in the Projects portlet on the summary page.
- Maintenance text on login screen can be added in the settings module.
- File versioning on Windows servers didn't work right.
- Some e-mail messages containing garbage characters didn't display for the second time.
- Small misalignment of amount in pdf invoices fixed.
- Clicking on links in ticket dialog works now
- Updated Norwegian translation
- Change default font size in e-mail
- New module to limit freebusy info access
- Bug with shared accounts and inaccessible attachments fixed
- Option to disable security token check $config['disable_security_token_check']=true;

01/04/2011 3.7.6
- Timeregistration and calllog had the same link id for customfields.
- Small bug fixes

29/03/2011 3.7.5
- Fixed problem with adding mail accounts to the postfixmanager.
- Small bug fixes

25/03/2011 3.7.4
- Merged all bug fixes from 3.6 into 3.7

15/03/2011 3.7.2
- Various small bug fixes reported at Sourceforge.
- Prevent Cross-site request forgeries: http://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)

10/03/2011 3.7.1
- Public icalendar file
- Sieve e-mail filtering support
- Optimized for very large installations 20.000 users.
- Sharing of e-mail accounts
- LDAP authenticaton has more flexible mapping
- Automatic project name with id number
- Standard timeregistration entries selectable from a list.
- Custom fields for timeregistration entries in Multiple day mode.
- Preset the folder view for other users.
- Standard e-mail folders are created in English on the IMAP server and are
  translated by Group-Office. This provides better consistency.

16/03/2011 3.6.30
- Removed cross site request security measure because it causes problems in IE.

15/03/2011 3.6.29
- Various small bug fixes reported at Sourceforge.
- Prevent Cross-site request forgeries: http://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)

08/03/2011 3.6.28
- Corrected corrupt Thai translation file.

02/03/2011 3.6.27
- Autocomplete for contacts in billing module works again.
- Bug with monthly recurring event.
- Updated Polish translation

28/02/2011 3.6.26
- Availability check fix
- Java upload applet upgrade to fix Czech translation issue.
- Calendar entries could be corrupted by changing the end time of a recurring item.
- Other small bug fixes.

23/02/2011 3.6.25
- Updated Norwegian
- Console.log function was causing an error in projects.
- Added some Czech translation files.

22/02/2011 3.6.24
- Tasks recur on due time and only the due date is shown in the calendar.
- Problem with logging in to the phpbb3 admin panel is fixed.
- Custom fields in tickets weren't formatted.
- User custom field is in the grids now.
- Don't send invitation to the calendar user that the appointment is created in.
- Client side sort array mismatch bug fix
- Translation updates: Czech, German and Swedish

11/02/2011 3.6.23
- ExtJS upgraded to 3.3.1 (Fixed grid state restore issue)
- Z-push 1.5 compatibility
- Invitations without e-mail module work better now
- Various small bug fixes

01/02/2011 3.6.22
- Problem with adding tasks with ActiveSync fixed.
- Reminders bug fixed. Some snoozed reminders wouldn't come up again.
- Don't ask to send invitation when "Add directly to calendars" is checked.

27/01/2011 3.6.21
- Thunderbird SyncML bug fix
- Small bug fixes

18/01/2011 3.6.20
- RTF parsing bug
- Fixed force_login_url

17/01/2011 3.6.19
- Attaching files was broken

17/01/2011 3.6.18
- Bug fix in e-mail display
- Bug fix with inline images in e-mail
- Bug fix in contact synchronization
- Translation updates

03/01/2011 3.6.16
- Feature to specify a maximum number of users in the license

31/12/2010 3.6.15
- Templates bug in community version fixed

28/12/2010 3.6.14
- Unicode bug with inititals in the calendar
- Send invitation updates when changing events in grid.
- Timeregistration bug in the last days of the year.

22/12/2010 3.6.13
- Install bug in servermanager

22/12/2010 3.6.12
- Small bug fixes
- Brought back Java upload to e-mail composer.

10/12/2010 3.6.11
- Updated German, Norwegian, Italian and Estornian translations
- Small bug fixes

08/12/2010 3.6.9
- New config.php option to turn off Flash uploader as it's not working with self-signed
  certificates. The option is 'disable_flash_upload'.
- Bug fix in outlook sync with recurrence rules.
- Added second name field to companies.

03/12/2010 3.6.8
- Timeregistration bug

03/12/2010 3.6.7
- Delete files was broken
- Updated Norwegian translation

02/12/2010 3.6.6
- CalDAV didn't work on PHP 5.2.

02/12/2010 3.6.5
- Performance improvements in the new ExtJS 3.3 update.
- Performance improvements by combining a lot requests to the server and load as
  little objects as possible.
- New bookmarks module.
- New calllog module for simple administration of phone calls.
- New module to schedule popup reminders for users or groups with a text page.
- WebDAV and CalDAV support with FreeBusy info (http://www.group-office.com/wiki/CalDAV).
- Collapsible sections in all information panels.
- Separated tasks and events from other links in the information panels.
- Instead of blocking a user after 5 bad logins it presents a CAPTCHA now.
- Export of contacts can be disabled for users.
- Many improvements to the custom fields:
  1. Option to make a field required.
  2. Add small help text to the fields.
  3. Better editing of select fields and an import function for the options.
  4. New option to allow a multiple selection with select fields.
  5. New treeselect field. This can be multiple related select fields. The data
     of the next select field depends on the selection of the other.
- Linked items show a link icon if they have links attached too.
- Reminders can be snoozed more easily.
- Add new link directly from "New" menu.
- New Wordpress bridge module.
- New Mediawiki bridge module.

Files
- New secure download link feature. Send a download link to someone by mail so
  the receiver can download this file securely.


Calendar and Tasks
- Better support for calendar invitations from other clients such as Outlook and
  Thunderbird.
- Drag and drop tasks in tasklists.
- Project calendars.
- Improved appearance.
- List view shows quarters.
- Show weekdays in the calender month view.
- Added categories to tasks and events.
- Automatic holidays to calendar.

E-mail
- Added e-mail templates, addreslists and newsletters to the community version.
  We separated document templates and save e-mail as into new pro modules
- When composing an e-mail the user is no longer asked to select a template each
  time. It starts with the user selected default template and can be changed in
  the composer if necessary.
- Remind collapsed/expanded folders and accounts in the tree.
- Easier adding of attachments and inline images.
- Remind enabled CC/BCC fields.
- Option to always reply to a read confirmation and always request a read
  confirmation.
- When a message is sent to an unknown recipient you can also add the e-mail
  address to an existing contact. There's also an option to disable adding
  unknown recipients.
- New e-mail portlet that can display e-mail folders in different tabs.
- Linking multiple messages is shown in a progress bar and handled in multiple
  requests if this operation takes a long time.

Billing
- The scheduled call for quotes skips weekend and holidays.
- Added a total column for rows and a markup function.

Projects
- Export function

2010/11/17 3.5.41
- Fixed small bug in new Document from template feature when you had a company
  and a contact linked to a project.

2010/11/12 3.5.40
- GOTA bug in terminal server environment
- Minor bug in ActiveSync fixed.
- Upgraded jupload applet to v5 and fixed multiple folder upload
- Authenticate on about.php
- Autoscrolling on mailbox domains in add user dialog.

2010/11/08 3.5.39
- Faster moves in file manager
- Fixed hangs in gnupg module

2010/11/02 3.5.38
- Mailings sent duplicate body
- Escaping value problem in projects billing template
- 0% VAT line with a manual page break

2010/10/19 3.5.37
- Bug in adding contacts
- Better way of syncing the second address line with Outlook
- GOTA uses home dir for temporary files now instead of the temp dir.

2010/10/19 3.5.36
- Duplicate inline attachment bug

2010/10/18 3.5.35
- Some debug code was left


2010/10/15 3.5.34
- Some small bugs were fixed
- Margins in PDF reports for projects


2010/10/08 3.5.33
- Some messages couldn't be saved in Group-Office.

2010/10/08 3.5.32
- Removed thousands separator from export in CSV
- Sync mappings for funambol outlook and blackberry improved
- Sorting of view calendars

2010/10/07 3.5.31
- Export projects function
- Bug where some folders where not available to users.

2010/10/05 3.5.30
- Z-push push function never pushed data anymore
- Project reports gave an error on some mysql versions


2010/10/01 3.5.29
- Some features required for a special import

2010/09/27 3.5.28
- Fixed Blackberry contacts field mapping
- z-push checks changes every minute on the server now and not every 5 seconds
  which is z-push's default.
- Resources were only loaded when customfields module is installed.
- Reset password with servermanager was broken

2010/09/23 3.5.27
- Wrong config.php was included

2010/09/22 3.5.26
- Updated Norwegian translation
- Added some tags to templates for e-mail.
- Updated z-push config file in sync module.
- Fixed blackberry sync issue with latest funambol client

2010/09/17 3.5.25
- Some fixes in z-push backend
- Fixed password corruption in postfix module when updating settings
- Fixed small bugs

2010/08/20 3.5.24
- Some events were added for a custom sync script
- Updated Thai and Norwegian translation

2010/08/16 3.5.23
- Small bug fixes

2010/08/16 3.5.22
- Time shift bug in timeregistration using alternative method fixed.

2010/08/16 3.5.21
- Task recurrence bug

2010/08/12 3.5.20
- Sync with Symbian phones was broken
- Remind login didn't work without php mcrypt extension

2010/08/10 3.5.19
- Sync supports latest Funambol clients for Outlook and TB3 beta client.
- Sync supports SyncEvolution
- Note sync supported
- Read only addressbooks,tasklists and note categories can be synced now too.
- E-mail window remembers size

2010/08/06 3.5.18
- Update for Debian packages only because of invalid php_value in apache config.

2010/08/06 3.5.17
- TLS support for LDAP
- Automatically add e-mail account from LDAP credentials
- Bug with shifting days in timeregistration
- Bug with invalid attachments in some e-mail templates.

2010/08/02 3.5.16
- inefficient hoursapproval queries
- Import PST script

2010/07/27 3.5.14
- Small security issue fixed and some bug fixes
- Print buttons in timeregistration and hoursapproval module
- Updated Estonian translation

2010/07/19 3.5.13
- Hoursapproval view in chrome and safari fixed
- Updated German Brazilian portugese and Spanish

2010/07/19 3.5.12
- Broken print button in e-mail

2010/07/16 3.5.11
- Updated Norwegian, Swedish and Brazilian Portugese translation
- Important bug fixes

2010/07/06 3.5.10
- Search for companies only searched on name field. Now it searches on all again.

2010/07/05 3.5.9
- Bug fixes and translations update

2010/06/21 3.5.8
- Added totals report to projects
- Fixed small bugs

2010/06/16 3.5.7
- Updated Norwegian translation
- Fixed small bugs

2010/06/08 3.5.6
- Updated Spanish translation
- E-mail charset decoding failed on some server configurations
- Limit appointments older then x days server side for activesync too because
  android always wants to sync all appointments
- Small bug fixes

2010/06/01 3.5.5
- Updated German, Norwegian and Czech translation
- Bug where event owners would get resource e-mails
- E-mail display bugs
- Security fixes
- Small bug fixes

2010/05/26 3.5.4
- Bug fix in searching some IMAP servers.
- American time format bug in calendar
- Bug fix for attachment filenames without encoding specification

2010/05/25 3.5.3
- IMAP authentication was broken

2010/05/24 3.5.2
- Improved security. E-mail passwords are encrypted in the database now and
  with the new blacklist module remote IP addresses are blocked after 5 consecutive
  login failures.
- Small bug fixes

2010/05/14 3.5.1
- A lot of bug fixes and improvements to the tickets module. Customers can login
  through the Group-Office interface now too.
  !!! Important !!! all ticket agents need manage permissions now. Only
  members of the admin usergroup can change settings

- Wrote custom IMAP library that replaces the php-imap extension. It's much
  faster, has more features and now the php extension is no longer required.
  There's really a huge performance improvement especially when connecting to
  remote IMAP servers.

  !!! IMPORTANT !!! POP-3 is no longer supported in this release. POP-3 doesn't
  really make sence for a webmail client so we didn't implement it in the new
  lib.

- Upgraded to ExtJS 3.2.1
- Dialogs are now only rendered on demand so Group-Office will start faster
- Reminders and new e-mail popup when you are not working in Group-Office. This
  way you'll have a visual on screen.
- Document templates work for Office 2007 files now too.
- Easier filtering on links and search results
- A lot of small enhancements everywhere.

2010/05/04 3.4.23
- Some bugs with participants were fixed
- Subject of message as filename for zip of attachments

2010/04/26 3.4.22
- GOTA opens office 2007 files by default now.
- License check with billing module only fixed.

2010/04/16 3.4.21
- custom fields in templates
- More export functions in projects / timetracking
- Easier type filtering in global search and link browser window.
- Small bug fixes

2010/04/14 3.4.20
- text_to_html function was broken

2010/04/13 3.4.19
- A lot of small bug fixes

2010/04/12 3.4.18
- Fixed calendar sync bug with z-push.
- Better logging with z-push

2010/03/29 3.4.16
- z-push timezone bug fixed.
- callto: links can be customized in config.php with callto_template
- Better title in dialog for invoices, quotes and orders.
- Improved calendar import so it can handler larger files.
- Small bug fixes

2010/03/29 3.4.15
- Improved remove duplicates script

2010/03/29 3.4.14
- Error in empty mail folders

2010/03/22 3.4.13
- TinyMCE was broken
- Fullscreen mode was loading very long
- Attachment decoding failed with non-ascii characters
- Fit windows when larger then the viewport

2010/03/18 3.4.12
- Send button could dissapear sometimes.
- Updated Norwegian translation

2010/03/17 3.4.11
- Manual page break in quotes or invoices
- Added size column to E-mail messages grid
- Small bug fixes

2010/03/15 3.4.10
- Fixed issues with IMAP cache
- Easier error reporting in syncml

2010/03/08 3.4.9
- Updated Norwegian and Czech translation
- Browser doesn't ask to open GOTA anymore. It launches automatically.
- Fixed printing of merged calendar views
- Added calendar name to event tooltip in merged calendar view
- Fixed bug in z-push ActiveSync when deleting items from phone to GO.
- Fixed bug where cursor in Firefox would jump up in the e-mail composer the
	first time.
- Fixed small bugs

2010/03/04 3.4.8
- tmpdir wasn't always present.
- Crash on empty to field in e-mail bug

2010/03/02 3.4.7
- Select participants needed addressbook and e-mail module. Now it works without
  too.
- Fixed files folder problem with addressbook import
- Sending newsletters was broken due to new license check system.
- Small bug fixes

2010/03/02 3.4.6
- Fixed license invalid error.

2010/03/01 3.4.3
- Fixed sendmail bug, subfolders and non-ascii characters bug in z-push
- Fixes for iphone sync.
- Fixed bug where calendar would crash if you had access to a calendar with
  tasklists in it that you didn't have access too.

2010/02/25 3.4.1
- Corrected z-push instructions

2010/02/23 3.4.0
General
- Microsoft ActiveSync support for sync with iphone, android, Nokia etc.
- Maximize window automatically when the window is larger then the current
  screen resolution.
- Create links from files
- Created a copy function in the billing catalog
- Improved browsing of links and global searcing. The linkbrowser shows a
  preview on the right just like e-mail works. This way it's much easier to
  browse links and search results.
- Possible to predefine some default link folders. They will automatically be
  created when you create a new project for example.

Calendar
- Add user groups and addresslist to appointments at once.
- Compose e-mail from the New menu at contacts, companies and project which will
  be linked automatically.
- Conflict checking on the server when adding appointments
- Button to jump to own calendar
- Created a merged view in the calendar to view appointments from multiple persons
  in one grid.

E-mail
- Cache mail body to improve speed and reduce load on the IMAP server
- Show addressbook when selecting contacts in autocomplete and addressbook dialog
- Added spellcheck
- Added buttons for indenting and clear formatting.

Addressbook & Addresslists
- Advanced search improved. You can build any SQL query.
- Added image to contacts
- Easier adding of contacts to addresslists. You can add an entire result of an
  advanced search query.
- More control over sent mailings. There's one central status screen where you
  can see all sent mailings. From there it's possible to pause and start them
	again. You can also view the log and message from the status window.

Projects
- Automatic rounding of timeregistration entries to 15 or 30 minutes for example.

2010/02/19 3.3.21
- Small bug fixes

2010/02/11 3.3.20
- Small bug fixes

2010/02/08 3.3.19
- Updated Russian translation
- Speed up the typing in the e-mail composer

2010/02/05 3.3.18
- Fixed popen warning that occured on some shared hosting setups.

2010/02/01 3.3.17
- Small feature enhancements in the hours approval module
- Updated Norwegian translation

2010/01/29 3.3.16
- Small bug fixes

2010/01/21 3.3.14
- Bug fixes and fixed a strange segmentation fault in apache when using Ioncube

2010/01/18 3.3.12
- Bugs fixed

2010/01/14 3.3.11
- Bug in sync fixed
- Bug in OO templates creation fixed

2010/01/13 3.3.10
- Added session_inactiviy_timeout config option to automatically logout inactive
  users.
- Synchronize multiple tasklists
- Delete sync devices
- Bug fixes

2010/01/04 3.3.9
- Debian package groupoffice-mailserver conflicted with vacation package
- Updated quota query for Dovecot 1.1
- Bug fixes

2009/12/21 3.3.8
- Fixed bug in function to strip dangerous elements in HTML

2009/12/18 3.3.7
- Updated Czech, Norwegian and German translations.
- Fixed Synchronization problems
- Improved invitation system and scheduling of appointments for multiple users
	directly.
- Fixed bugs in the resources system
- Fixed other small bugs

2009/12/10 3.3.6
- The package in 3.3.5 seemed to be 3.3.4.

2009/12/10 3.3.5
- Save module permissions at user properties was broken
- Fixed bug in outlook sync. Phone numbers were not correctly sent from Outlook
  to Group-Office

2009/12/09 3.3.4
- Mailings didn't start automatically
- Added config option to disable profile editing in the settings dialog.
- Fixed small bugs

2009/12/08 3.3.3
- Drag and drop e-mail messages was broken

2009/12/07 3.3.2
- Brought back upgrade2to3.php with it's own function libs
- Updated Norwegian translation
- Reorder e-mail accounts by dragging them around in the e-mail treeview so
	we can sort on e-mail address in the administration dialog.
- Small bugs fixed

2009/12/03 3.3.1
- Sort IMAP folders for mbroot detection
- Small bug in calendar
- Removed upgrade2to3.php because it doesn't work anymore. Upgrades from 2.18
	must first be done with version 3.2 and then from 3.2 to 3.3.
- Small installation issues fixed

2009/12/01 3.3.0
- Added zlib compression to increase startup speed. It loads much faster now.
- Resource management in the calendar to manage meeting rooms for example.
- Enhancements to the projects module
- Multiple calendars and tasklists on the summary page.
- Create invoices from projects and timeregistration
- Separated timeregistration into it's own module.
- Hours approval module so that managers can approve the timeregistrations
- 2 different styles of entering timeregistration. Besides the week grid there's
  also a form for multiple entries per project per day.
- Added custom fields to billing templates, tasks and calendar events.
- Changed ACL system to impove overall performance on large installations
- Birthday calendar
- Systemusers module to automatically create Linux system users with
  Group-Office accounts and generate .forward files for vacation replies in the
  e-mail module
- Automatically perform system update when new sources are replaced.
- E-mail reminders
- Tickets module


2009/11/30 3.2.50
- Small bug fixes

2009/11/19 3.2.49
- Fixed corrupted CHANGELOG.TXT
- Small bug fixes

2009/11/13 3.2.48
- Small notice fix
- Missing line break in calendar export

2009/11/12 3.2.47
- Sync could overwrite contacts with another one!

2009/11/11 3.2.46
-	In a rare occasion attachments could stay in the e-mail composer after sending
	the e-mail.

2009/11/10 3.2.45
- Improved SyncML server. Some issues with Funambol Outlook client were fixed
	and Thunderbird contact sync works perfectly. The calendar still crashes
	every once in a while.

2009/11/05 3.2.44
- Fixed small bugs

2009/11/04 3.2.43
- Better way of sending mailings
- Thai language entry was missing

2009/11/04 3.2.42
- Better error reporting when linking e-mail messages.

2009/10/26 3.2.41
- Fixed Synchronization issues where times would shift by an hour and GO
	supports the MoreData command properly now.

2009/10/23 3.2.40
- Removed absolute paths from javascripts

2009/10/20 3.2.39
- Updated Russian translation
- Calendar time was changed when setting a repeat interval later
- Custom fields sort order bug
- Show tasklist title in the list
- Module write permission cache bug

2009/10/14 3.2.38
- Added Hungarian translation
- Fixed bugs

2009/10/07 3.2.37
- Updated French and Thai translation
- Fixed bugs

2009/09/24 3.2.36
- Javascript error in IE7 fixed and some other minor bug fixes
- Unzip and zip with special characters failed
- Folder structure messed up with Jupload when uploading with windows

2009/09/21 3.2.35
- Problem with some queries not paging anymore.
- Small bug fixes

2009/09/16 3.2.34
- Fixed broken calendar view in IE6
- Small bug fixes

2009/09/03 3.2.32
- Put all the relevant scripts together in the database check to make it simpler
- Fixed bug where hmailserver didn't add body to reply#
- Small bug fixes

2009/08/28 3.2.31
- Detect import encoding in addressbook, calendar and tasks
- Fixed small bugs

2009/08/27 3.2.30
- Upgraded to Swift 4.0.4
- Rewrote newsletter mailer

2009/08/26 3.2.29
- Several small bug fixes

2009/08/24 3.2.28
- Fixed bug in billing module where country was incorrectly saved

2009/08/21 3.2.26
- New report for billing that shows the total turnover per customer
- Synchronization supports multiple addressbooks now.
- Saved e-mails and templates were not displayed again.
- More efficient database connection
- More efficient script loading

2009/08/18 3.2.25
- Fixed bug which prevented saved e-mails from displaying.

2009/08/18 3.2.24
- Adding attachments to mailings works on windows now too.
- Fixed icalendar import bug
- Added support for multiple RSS feeds on the start page.
- Modules can be closed with right mouseclick.
- Calendar printing was off one day in some timezones
- Address formats for all countries now.


2009/08/13 3.2.21
- Too large padding on first grid cells
- Move e-mail to another IMAP account.
- Larger timeout when sending e-mail
- Added missing licenses from components
- Updated TCPDF to version 4.6.024
- Updated PhpThumb to 1.7.9
- Updated TinyMCE to 3.2.5
- Added new address formats option for contacts so you can localize the display
	of addresses in Group-Office.
- No confirm of deleting mails when they will be moved to the trash folder.
- Small bug fixes

2009/08/06 3.2.20
- Function to clean invalid UTF-8 strings that caused unreadable e-mails
- Updated Norsk translation
- Added custom fields functions to users
- Compatible with 5.3. There are still a lot of deprecation warnings though.
- Fixed destroyed htmleditor in e-mail composer when it was collapsed

2009/08/04 3.2.19
- Invalid iconv call bug on attachments
- Updated Czech

2009/08/04 3.2.18
- Time registration column header bug
- Windows compatibility with Open-Office templates

2009/07/29 3.2.17
- Problem with search query for contacts

2009/07/29 3.2.16
- Upgrade to ExtJS 2.3
- Confirmation e-mail added to web form processing
- Better SMTP error reporting in e-mail
- Override newsletter mailing smtp server to always use a specific e-mail server
	for mailings.
- Smarter search function in projects

2009/07/28 3.2.15
- Automatically sync filesystem with files database for easier external access
	of files.
- Fixed some issues in preparation to move to ExtJS 3
- E-mail filter bug. Adding a filter to a second account failed.
- Updated German, Czech and French translation
- Added Norwegian Bokmål translation
- Other bug fixes
- Debianized the package.

2009/07/10 3.02-stable-12
- IMAP auth module was broken
- Read notification didn't send correct alias
- Alarm sound didn't play anymore
- File templates bug
- Project template bug

2009/07/09 3.02-stable-9
- License check for pro version was still in the comments module.

2009/07/08 3.02-stable-8
- Moved comments to Community version
- Bug fixes

2009/07/07 3.02-stable-5
- Project templates extended with subprojects and tasks
- A responsible user can be selected at a project and you can filter on that.
- Script to sync files with the filesystem was added.
- Changed lost password procedure. Now an e-mail is sent with a link to click on
	where the user can change the password. This method is more secure because
	with the old method is was possible to change someone's password if you knew
	the e-mail address.

2009/07/06 3.02-stable-4
- Various bugfixes
- Updated German translation

2009/06/24 3.02-stable-1
- Changed files module. All the modules store the files in logical paths now.
  eg. The files of a contact are in contacts/Addressbook name/S/Merijn Schering
	All the filesystem entries are stored in the database now for better performance.
	The downside is that the database needs to be synced when changes on the
	filesystem are made by another program like Samba, FTP, WebDAV etc.

- Multiple e-mail sender aliases per mailbox can be defined now.
- Store a default e-mail template so it always shows on top.
- PDF export / mail function for the addressbook and billing module
- Projects treeview for easier navigation
- Add event to multiple calendars without sending an invitation
- A lot of Windows enhancements. libwbxml, zip and unzip work now and the
  installation is improved for Windows too.
- GOTA didn't always report failure. Error handling is improved.

2009/04/28 Version 3.01-stable-34
- Added Greek translation
- Minor bug fixes

2009/04/17 Version 3.01-stable-33
- Some e-mails could affect the Group-Office CSS styles

2009/04/16 Version 3.01-stable-32
- User delete didn't work correctly. Module items were not removed.
- Empty search panels bug
- Sometimes comboboxes lost their size

2009/04/15 Version 3.01-stable-31
- URL rewrite didn't work for plain text

2009/04/15 Version 3.01-stable-30
- LDAP authentication module
- htmlspecialchars was accidently removed from plain text e-mails

2009/04/14 Version 3.01-stable-28
- Small bug fixes

2009/04/10 Version 3.01-stable-27
- E-mail quote style improved
- Updated German translation
- Better switching between editing modes in e-mail client

2009/04/09 Version 3.01-stable-26
- E-mail content type preference was not saved correctly.

2009/04/08 Version 3.01-stable-25
- Fixed error in German GnuPG translation
- E-mail signature in plain text mode

2009/04/07 Version 3.01-stable-24
- IE 8 compatibility
- Small bug fixes

2009/04/03 Version 3.01-stable-23
- SMTP with SSL or TLS didn't work anymore

2009/04/01 Version 3.01-stable-20
- Small bug fixes
- Weeknumbers in calendar monthview

2009/03/31 Version 3.01-stable-19
- Plain text e-mail option
- GnuPG encryption
- Show new files uploaded by others in the files module
- Folder templates in projects module
- Upgrade SwiftMailer to version 4.0.3
- Updated German and Czech translations
- Bug fixes

2009/03/24 Version 3.01-stable-18
- Small bug fixes

2009/03/18 Version 3.01-stable-17
- New module to process forms from website that can add contacts and send mails.

2009/03/17 Version 3.01-stable-16
- Fixed bug that caused strange behaviour when you have a lot of all day events on
	some screen resolutions

2009/03/17 Version 3.01-stable-15
- Fixed bug that caused sync to fail

2009/03/16 Version 3.01-stable-14
- Configure Group-Office as the default e-mail program
- Fixed errors in Romanian translation
- Bug with dragging all day events fixed
- Some attachments attached by IE were not found when sending the mail.
- New PHP events system
- Bug fixes

2009/03/10 Version 3.01-stable-13
- Problem with synchronization in stable-9 fixed
- Link descriptions added
- Problem with creating appointments in the calendar

2009/03/09 Version 3.01-stable-9
- Fixed bug in PDF printing
- Default reminder and background color for appointments and tasks
- When a new OpenOffice document is created from a template in the addressbook it is
  saved at the contact and the GOTA is launched.
- Calendar scale with 15 minute interval
- Compose multiple e-mails at once
- Autosave. When an e-mail is saved to drafts it will replace the existing draft and is removed when sent.
	Every 2 minutes the mail will automatically be saved to drafts.
- PhpBB3 module: The PhpBB3 module will integrate PhpBB3 into Group-Office. When a
	Group-Office user goes to phpbb3 it will automatically copy the user and log the
	user in.
- Log module. View all delete, update and add actions done by users.

2009/03/03 Version 3.01-stable-5
- Updated Czech translation
- New PDF print in calendar module
- Search trough all custom fields in addressbook module
- Various bug fixes.

2009/02/18 Version 3.01-stable-4
- Fixed problem with IMAP connections. Some people were having trouble connecting to
  an IMAP server. GO would get in an endless loop because these servers returned the
  INBOX folder as a child of itself.

2009/02/16 Version 3.01-stable-2
- Use GO as complete mailserver solution for multiple domains and with vacation support
  see: http://www.group-office.com/wiki/Mailserver for instructions

- Save e-mail as file, task, appointment and note (pro version)
- Improved inviting participants. It now sends a mail automatically.

2009/02/06 Version 3.00-stable-18
- Problem in billing module that asked for book selection each time
- Autofill values when you create an invoice from a contact or company

2009/02/05 Version 3.00-stable-17
- Check for global config file caused error with open_basedir set
- Better error reporting dialog.
- Upgraded to ExtJS 2.2.1
- Fixed minor bugs


2009/02/04 Version 3.00-stable-16
- Added extra customer information field at invoices
- Links didn't work in IE
- Added Danish translation
- Added Finnish - Suomi translation
- Support for quota on the entire installation
- Fixed some minor bugs

2009/01/30 Version 3.00-stable-15
- A lot of bugs in the projects module were fixed
- Better navigation in calendar views
- Fixed bug in event invitations
- Events in participants calendars get updated when you change the master event.
- Added Polish translation
- Fixed bugs with file linking and searching
- Better state preservation in the file manager.
- Other small bugs were fixed

2009/01/20 Version 3.00-stable-13
- Opening links and some attachments was broken. A quick new release.

2009/01/20 Version 3.00-stable-11
- Filter on types (Contact, Company, Project etc.) when searching
- Bug fixes

2009/01/15 Version 3.00-stable-8
- Czech language was missing
- Corruption in some German language files
- If you didn't have access to the parent project of a project you had permission to book hours on, you couldn't access the project.

2009/01/14 Version 3.00-stable-7
- A bug was causing the e-mail to fail in other languages then Dutch.

2009/01/13 Version 3.00-stable-6
- Select addresslist from e-mail composer window
- Simple e-mail signature
- E-mail menu with options to lookup in addressbook, compose and search
- Works with Gmail now, but the IMAP connection is often slow
- Support for UUencoded e-mail attachments
- New timesheet in projects module for easier time registration
- Better display of project information and subprojects
- Sort project statuses by drag and drop
- Portlets on the summary page autorefresh
- Browse project files from file manager
- Bug fixes

2008/12/18 Version 3.00-stable-5
- Dragging in calendar caused errors sometimes
- Rightclick menu on e-mail tree folder was broken
- Small problem with HTML to text conversion in e-mail

2008/12/16 Version 3.00-stable-4
- Detailed report printing in Projects module
- Improved support for Nexthaus syncml clients
- icalendar import for tasks and calendar
- message panel horizontal or vertical as preference
- Improved month view
- Import icalendar attachments in e-mail
- Right click menu added on e-mail addresses in email module with useful options
- Right click on e-mail attachment offers Save as option in the files module
- Bug fixes

2008/12/03 Version 3.00-stable-2
- Bug fixes
- Import/export of custom fields in addressbook (pro)
- Add /delete mail folders by right clicking the tree menu
- Added Spanish translation
- Added Brazilan Portugese translation

2008/11/24 Version 3.00-stable-1
- Last known bugs were fixed
- New caching system for the e-mail to impove performance.

2008/11/07 Version 3.00-unstable-19
Lot's of bugs were fixed from version 1 till 19. This should be the
last "unstable" release.

- Changed to MySQLi improved PHP mysql extension
- Removed smart_addslashes and smart_stripslashes functions. GO runs best
  with magic_quotes_gpc=off now and escaping values that go into the database
  is done at the lowest level. Either by the MySQLi extension or in the
  GO database class. This makes GO safer and programmers don't have to worry about
  it anymore.

- Added thumbnail view and Image viewer in files module
- E-mail blocks remote images for unknown senders
- Image attachments in e-mail can be viewed directly in GO with the image viewer (files mod required)
- Projects module was improved so it supports all 2.x features
- New Schedule call option at quotes, contacts and companies
- Support for all unicode characters in the filesystem module
- All filesystem paths are stored relative to the filesystem path configured in config.ph
  This makes it easier to move an installations and safe's space in the database.


2008/09/03 Version 3.00-unstable-1
-Complete rewrite of Group-Office with ExtJS (www.extjs.com).

2008/02/20 Version 2.18-stable-19
- Bug fixes
- Synchronization with some Sony Ericsson models fixed

2008/01/14 Version 2.18-stable-15
-Removed contact color lookup in email client because it slows down email the e-mail
 display with large addressbooks
-Fixed HTML display in e-mail client from certain clients
-Created modulair reminder plugins. So all modules can use the reminder system
-Prevent GOTA to start with certain extensions such as PDF, jpg etc.
-Small bug fixes
-Created public calendar featurein pro version


2007/11/22 Version 2.18-stable-6
-FIxed bug with IMAP authentication

2007/11/21 Version 2.18-stable-5
-Forward e-mail support for local accounts
-Small bug fixes
-File upload notifications per folder
-IMAP authentication works better together with creating system users
-Fixed upgrade from 2.13

2007/10/26 Version 2.18-stable-2
-Brought back the old mailing list feature by request of some customers
-Cleaned up code in GOTA
-Funambol 6.0 clients supported for Synchronization
-Various bug fixes
-Added support for  MySQL in utf8

2007/10/26 Version 2.18-stable-1
-Group-Office Transfer Agent (GOTA). A small Java program that saves documents.
 back to Group-Office when you edit them on your PC. (Pro version only)
-Helpdesk module (Pro version only)
-Reports module is now called Address lists and is more user friendly (Pro version only).
-Updated various translations

2007/09/10 Version 2.17-stable-13
-Added Arabic translation
-Small bug fixes

2007/08/21 Version 2.17-stable-12
-Fixed table configuration bug with register globals enabled
-Fixed small bugs

2007/08/01 Version 2.17-stable-11
-Out of office reply didn't work if e-mail mailbox was different then the e-mail
 name.
-404 error of base64.js

2007/07/22 Version 2.17-stable-10
-It was impossible to create an e-mail without a template in previous version
-Fixed FCKeditor bugs in filesystem module
-Sorted treeview by name
-Made bank number field larger in addressbook

2007/07/20 Version 2.17-stable-9
-Better import for sync
-Updated Czech language
-Implemented new version of jupload

2007/07/08 Version 2.17-stable-8
-Problem with auto_check in email for new users.

2007/07/05 Version 2.17-stable-7
-Added out of office reply for local e-mail accounts. No cron job is needed
 anymore but you need to setup sudo.
-Fixed problem in synchronization with strange characters
-Brought back e-mail popup notification
-Automatic e-mail check can be enabled per account
-Updated Czech language
-Updated Slovenian language

2007/06/18 Version 2.17-stable-6
-fixed bug with inline e-mail attachments
-Fixed display of link e-mail button in Community version

2007/06/12 Version 2.17-stable-5
-Better character set conversion in e-mail messages
-Filesystem shares bug with invalid tree structures
-Display inline e-mail attachments
-Other small bugs

2007/06/07 Version 2.17-stable-4
-Added the last 10 uploaded files to the summary
-Calendar and task reminder beeps only once if it's not dismissed.
-E-mail account save bug if you don't have admin permissions
-Custom fields class outputted a blank line which caused an header already sent error

2007/05/31 Version 2.17-stable-3
-E-mail status indicator was missing in all themes except default
-Fixed an issue with strange characters in filenames
-Fixed printing e-mails from a folder with strange characters
-Fixed other small bugs

2007/05/21 Version 2.17-stable-1
-Added Slovenian language
-Updated Turkish language
-Changed e-mail permissions behaviour
-Fixed update procedure error



2007/05/07 Version 2.17-testing-1
-Full support for Microsoft Windows
-Move module theme items to module dir
-Modular select dialog (Done with global search).
-Select and sort columns to display in tables
-global search
-Easier linking with global search
-link email (Pro only)
-Better auto add contacts in mail
-TNEF (Winmail.dat) support
-Reports module to show different queries on addressbook. Can also be used to send
 mailings with MS Office, Open-Office and the GO e-mail client. Replaces old mailing
 system (Pro only)
-New e-mail counter always in top menu bar and beep can be disabled when new mail
 arrives
-Option to show attachment paperclips and priorities in e-mail client. Slows it down when
 enabled!
-Don't create a personal addressbook by default for everyone.
-Edit multiple contacts at once
-Show month name on top of calendar print
-Adminstrative tools: check database, backup database, users import
-Help button leads to right page at docs.group-office.com
-Table configuration which allows you to reorder and select the columns you want in tables



CHANGELOG
2007/05/02 Version 2.16-14
-Added update client
-Fixed problem with quotes in e-mail names
-Don't encode language files for translating
-Small bug with newlines and translate module


2007/04/03 Version 2.16-13
-Security issue: A logged in user could read another user's e-mail with some tricks
-Saved e-mails couldn't be read (Pro version)
-Calendar admins always have access to events
-Updated Catalan language


2007/03/08 Version 2.16-12
-Fixed mailto links in e-mail
-Don't set session.save_path because PHP garbage collection doesnt work then
 anymore. This resulted in a lot of session files in the tmp dir at some distro's
-Autocomplete users only put in a , in the text field. Also an Invalid XML bug was fixed.
-You didn't fill in all required fields bug at registration and profile update

Language updates:
-German language update
-Added Turkish language

2007/02/05 Version 2.16-11
-Fixed some corrupt files

2007/02/05 Version 2.16-10

Bug fixes:
-Files with quotes went wrong in filesystem
-Missing image for adding users
-html chars in pulldown of email module
-Allow duplicate email addresses with differt names in autocomplete
-Sync recurrence 1 day minus bug
-Double file properties bug
-E-mail attachment character encoding
-Email addresses character encoding
-Double frame on user profile close

Languages updated:
-Spanish
-French
-Catalan
-Hungarian
-Italian

2006/11/29 Version 2.16-4
-Translation module
-Speed optimizations



2006/11/01 Version 2.16-FINAL
-Disable user fields in admin mode
-Create pulldown menu's in custom fields. (Professional version)
-Custom fields for users (Professional version)
-Configure welcome e-mail on user registration
-Notify admin on user sign up
-Disable/enable fields for registration
-Book overlapping times in projects module
-Improved installer
-make compatible with register_globals=on

2006/09/13 Version 2.16-FC
-Small bug fixes
-Introduction of Gallery module

2006/08/31 Version 2.16-RC1
-Quotes/Invoices module (Professional version)
-Save e-mail to filesystem and link it to anything (Professional version)
-Improved linking interface
-Add local system users and (Postfix) e-mail aliases with sudo
-Bug fixes

2006/07/12 Version 2.15-FINAL-9
-Save email attachments to a project
-Security fixes
-Background colors in calendar
-One day calendar view in emerged group view
-Events that got accepted were not synced.
-FCKeditor upgraded to 2.3
-Project fees are only visible by name not by the amount.
-Projects print function

2006/06/21 Version 2.15-FINAL-8
-Access denied bug in tasks module.
-Small bug fixes.

2006/05/24 Version 2.15-FINAL-7
-Compatibility release for Quotes/Invoices module.
-Security fix for calendar module
-Websites module can have inline php in templates and can have a template per page.

2006/05/22 Version 2.15-FINAL-6
-Websites module uses FCKeditor
-Websites module can have a different template per file.

2006/05/07 Version 2.15-FINAL-5
-Task sync (Professional version)
-Exceptions in recurring events/tasks get synced (Professional version)
-Only part of the calendar can be synced. For example only appoinments newer then
 30 days old and happen within the next 90 days.
-Company fields of contacts are synced too now.
-E-mail notification on file upload can be enabled/disabled.
-Bug fixes

2006/05/07 Version 2.15-FINAL-4
-Added adodb-time library for support for dates before 1970 and after 2037
-Added halfhour timezone differences
-Fixed addressbook install bug
-Fixed small bugs

2006/05/04 Version 2.15-FINAL-3
-Improved performance by removing calendar ACL entries. The table was getting way
 to large.
-Improved performance by removing all dropdown boxes of calendars and addressbooks
 and replace them with popup select windows.
-Improved performance by caching common queries and caching the autocomplete
 feature in the e-mail composer.
-Improved resource booking
-Option to disable preview in E-mail module
-Synthesis clients support for Synchronization (Palm client too!) (Pro version)
-Improved SyncML support. Better support for slow sync and recovery when something
 goes wrong (Pro version)
-Custom fields for projects too. Moved custom fields into a separate module. (Pro version)
-Mailing groups can be selected from the composer screen (Pro version)
-Small GUI enhancements
-A lot of small bug fixes


2006/03/10 Version 2.15-FINAL-2
-Select project/company/contact files from file select.
-File linking
-Print  working hours per user for all users
-Many bugs fixed

2006/03/21 Version 2.15
-Resource booking
-Calendar shows private event as private
-Event owners get an e-mail if somebody else modifies, deletes, accepts or declines an
 event.
-Event search
-More flexible date formats
-letter click in select control doesn't search on name only
-pagination in select control
-Multiple e-mails for contacts
-Letter click pagination does not work
-relate project to itself bug
-Project folders by ID not name
-Moving files doesn't move notes
-FCKEditor integration
-display holidays
-Dig in mail folder structure for new mail
-Don't preload images in e-mail automatically
-custom login/logout URL in config.php
-Calendar grid configurable per calendar
-expand/collapse note bodies
-Filter option to mark as read
-e-mail notify on file upload
-filter closed tasks
-Appointment e-mail reminder daemon
-Send company data to client at sync (Professional version)
-English end user documentation
-English administrator documentation
-Multiple linking. Create as many links from one item to another that you like.
-Automatic spam filter in e-mail account. (Messages need to be tagged by a server-side
 spam filter.
-Better IMAP server detection


2005/11/02 Version 2.14
-Only bug fixes and small improvements

2005/09/08 Version 2.14 FC
-Sharable calendar views
-The merged calendar view has a legend and colors per user are settable
-Admin can create all the user calendars at once with right permissions

2005/09/08 Version 2.14 RC2
-Todo templates for projects. When you create a project you can select a set of
 predefined todo's.
-Many, many bug fixes

2005/08/23 Version 2.14 RC1
-Complete new development toolkit
 A whole new set of PHP classes designed to build Group-Office modules.
 These classes make it extremely easy to create standard controls in pages such as:
 	- Tab strips
 	- Sorted tables
 	- Treeview structures
 	- Activitity listings (Listing of related items)
 	- Button menu's
 	- Select dropdown lists
 	- Radio buttons
 	- Checkboxes
 	The classes make it possible to create pages without using a single line of HTML code.
 	Pages will be programmed in pure PHP. Output of menu's, tables, tabstrips treeviews
 	will always look and behave the same.
 	The folliwng modules have been completely recoded:
 	-Filesystem
 	-Addresbook
 	-Todos
 	-Notes
 	-Projects
 	-Summary
 	The others have been made compatible with the new framework but need to be
 	recoded someday.
- Admins are really admins now. They have access to all the users calendars,
	addressbooks etc. and can set up the permissions for them.
- Recurring todo's. Todo's are stored in the same way as appointments and can recur
	now.
- More controls over the todo sharing. They are shared in the same way calendars are
  shared.
- Files can be attached to projects
- Calendar and todo reminders are set for all calendar owners and participants
-	New cron job PHP script to set vacation files for Sendmail Postfix or other mail server
 	that uses .forward files. The Out of office message can be set at the email configuration
 	page.
- Calendar view is impoved. Overlapping events are now displayed next to eachother.


2005/05/30 Version 2.13
-Added snooze dismiss to reminders
-Fixed ICS and VCF import
-Added contact colors in mail client
-Added preview frame in e-mail client
-Multiple selection can be done with SHIFT and CTRL in the e-mail client
-Header of an e-mail is smaller for the preview. It can be expanded to view more details.
-You can download all mail attachments in a ZIP file.
-Administrator tools are modules now too.
-Calendar event saving fixed
-New treeview control
-Language setting is stored in cookie
-Added system password changing
-Saving of todo's
-Javascript error with very long request URI in calendar module
-Adding new contacts to companies
-Illegal module name specified
-Konqueror compatibility with new e-mail module
-GUI improvements to e-mail module
-Admins always see all groups. Regular users see only the groups where they are
 member of.
-Fixed a lot of small bugs

2005/03/12 Version 2.12c
-Fixed wrong folder encoding in e-mail client due to PHP Bug
-Fixed Illegal module name specified bug

2005/03/12 Version 2.12b
-Fixed the disappearing of some calendar events after upgrading to 2.12
-Some e-mail messages that didn't contain a content type got chopped off at the
 first unknown character. It now ignores them.
-The To field in e-mail client was not encoded to UTF-8
-Fixed UTF-8 in javascript dialog boxes. Changed to htmlspecialchars instead of
 htmlentities.
 -Addressbook checks for a duplicate entry when creating a new contact or company.

2005/03/12 Version 2.12
-Database doesn't use persistant connections anymore
-Configuration options are moved from Group-Office.php to config.php. This way it's easier
 to separate source from local configuration options with symlinks.
-Added password recovery
-Fixed issue with PHP < 4.2. People could not delete objects.

2005/01/24 Version 2.11
-Save as draft option in e-mail client
-Support for multiple languages/sections in the CMS module
-Sort folders and files through eachother in the CMS module
-ImageManager plugin for HTMLArea in the CMS module
-Function to copy contacts/companies to other addressbooks
-HTML Editor integrated in files module
-Fixed UTF-8 bug with javascript escape funtion. This was a problem for languages such as
 vietnamese
-Added new Default theme. Theme's structure changed. Each module has it's own CSS style.
 The CSS was also cleaned up.
-Added JUpload java applet to Group-Office controls. It allows to upload multiple files at once
 and shows a progressbar. It's integrated into the email and filesystem module.
-Changed the way attachments are handled in the e-mail module. They now load into a separate
 popup window so you can keep on typing while attaching files.

2004/11/11 version 2.10
-Availability checking in calendar
-Bug fixes

2004/11/01 version 2.09
-After 2.08 there was a major bug discovered in timezone handling. When the
 clock went to winter time all appointments were shifted one hour.
-Participants in a new event were not seen as a member of Group-Office
-Included mime.types file for OS that do not have this file.
-Fixed bug with notes permissions

2004/10/28 version 2.08
-Only admins can manage user groups now.
-Filetypes are handled per theme and per language and are no longer stored in
 the database.
-Updated month view in the Calendar
-E-mail module asks to add unknown senders to your addressbook if you enable
 this in the configuration.
-Implemented latest PhpSysInfo 2.3 in a much better way then before so that it
 works on all supported operating systems.
-Used iconv to recode e-mails to UTF-8 to display them properly.
 If iconv is not compiled into PHP there could be trouble with displaying some
 messages correctly. Iconv is standard in PHP 5
-Some contributions and ideas to email, filesystem, calendar and addressbook by
 Robert Widuch. Thanks a lot.
-Autocompletion of e-mail addresses when typing them in the 'To' field of the
 e-mail composer. Also the names are included as well now when sending e-mail
 to someone from the addressbook.
-The name display order (firstname, lastname) can now be chosen
 in preferences.
-Changed tasks to be a real module with links to contacts, companies and projects
-Fixed bug with e-mail printing
-Companies, Contacts and Projects have a new activities page where you can
 view all related notes, tasks, projects and appointments.
-Removed todo's from calendar and made a real todo's module.
-Module icons in menu are sortable now by the admin
-Notes module simplified. Now it's just a plain text note and nothing else. You
 can attach them to contacts, companies, projects and files.

2004/10/05 version 2.07
-!!!WARNING!!! Removed permissions for contacts. Only permissions for the
 addressbooks can be set. Contact permissions will be REMOVED with this upgrade.
-Fixed bug with international characters in e-mail module.
-Added automatic selection of country, state, street, city based on zipcode.
 You will have to import your own zipcode table though.
-Day view modified. On viewing a calendar-view containing multiple calendars
 up to 5 calendars are displayed in a row.
-New month view in the calendar module.
-E-mail module automatically adds unknown senders and reciepents to addresbook.
-Fixed a bug with importing iCalendar files
-More small bug fixes

2004/09/02 version 2.06
-Fixed a lot of smaller bugs that were spotted by the community forum users.
 Thanks a lot everyone.
-Mailing lists improved
-Contact select improved. Now you can add contacts and companies from the select
 screen.
-New helpdesk module by Meir Michanie
-Modules upgrade individually at installation
-iCalendar import/export calendar completed
-iCalendar (*.ics) file attached with meeting requests
-Merged and emerged team views in the calendar
-Fixed bug in import from CSV in the addressbook
-Security bugs fixed. When running with magic_quotes_gpc=off GO users could
 delete some data that belonged to other users.
-vCard import/export addressbook completed
-vCard (*.vcf) file attached with personal data
-SSL Support in e-mail client

2004/05/23 version 2.05
-New authentication mechnism to make it very easy to use external servers
 to authenticate or manage users.
-New summary module that sums up all module info and announcements
-New language system falls back on English if language misses strings.
-New directory structure that puts all module files in one place and allows
 users to install/uninstall modules including the database tables.
-E-mail module is much faster
-Powerfull search function in the e-mail module
-Modules no longer use cookies to store settings like sorting of tables
-Time automatically adjusts to Daylight savings time
-vCard support added to addressbooks (currently only import possible)

2004/03/24 version 2.04
-Group-Office had problems with characters like & " > < sometimes. This
 is fixed everywhere in the program.
-Todo's in the calendar
-A lot of bug fixes with many thanks to the ones posting on Sourceforge!

2004/03/17 version 2.03
-Improved bookmarks module. Bookmarks are sharable and can be ordered in
 catagories.
-iCal import/export support for the calendar
-Projects module bugs solved
-Cyrus IMAP server had problems with sent items folder.
-Mail folders with an ' didn't work
-HTMLArea is now smarter. It returns textfields when browser is not supported

2004/03/10 version 2.02
-Improved IMAP server support
-Fixed installer bug
-Small feauture addon to the e-mail client.
-Fixed bug in calendar that didn't display first day of the week in some months
-Fixed bug in calendar that always added the current calendar to an event.

2004/03/08 version 2.01
-Implemented PHPMailer to handle all mail transport. Fixes issues with scrambled
 emails in other mail clinets.
-Email client in HTML mode sends alternative text body too.
-Better attachment handling
-Improved IMAP server support (Courier, Cyrus UW-IMAP all work the same now
 even without setting the root mailbox.
-Fixed email pagination
-Added first day of week preference.
-Fixed bug in client selection in projects module.heeey
-Fixed wron Accept and decline links in calendar module
-Fixed shared files wrong hierarchy

2004/02/28 version 2.0
-Solved bug in email client. Only first account worked properly. Filters got
 deleted when you saved an account.
-Solved bug in adding users. They weren't able to set thier privacy settings.
-When you created a note the note wasn't always readable for the responsible
 user.
-Removed menu javascript that isn't needed anymore. seems that htmlarea got
 more stable by doing this.(no more HTMLArea.I??? is undefined).
-Added history at contacts.
-Bug in addressbook selection tool solved
-Fixed bug in mail checker.
-Solved bug in opening email attachments.
-Fixed parse error when adding companies.
-Fixed more small bugs.

2004/02/18 version 1.98
-Works with relative URL now. So you can access GO by multiple hostnames.
-Selection tool for addressbook can select contacts, companies and users
 now in one or all addressbooks.
-Optimized SQL for better performance.
-Fixed a lot of small bugs
-HTMLArea resizes on window resize now thanks to Gianluca B.

2004/02/11 version 1.97
-Completely restyled interface
-Automatically checks for reminders and email now and pops up a new window.
-Addressbook redesigned and separated companies from contacts
-Bug with email account creation fixed.

2004/01/25 version 1.96
-Fixed double menu in older Netscape or Mozilla functions
-Fixed bug in calendar that told user the times were wrong
-Fixed bug with deleting calendar events
-Updated theme icons
-Implemented latest HTMLArea 3.0
-Added new functionality to the projects module
-Implemented Holidays functionality in calendar module
-Fixed install bugs.
-Passwords use md5 encryption now
-Fixed small security bug. Only GO users could have modified some acl's from
 other users.
-You can now set up indivdual permissions for each contact.

2004/01/19 version 1.95
-Bugs introduced with the conversion to work without register_globals fixed
-New compression tool in the file manager
-Addressbook starts in search mode and search mode extended with clickable
 letters of the alphabet
-Added timezone preference
-Added new fields to user table and addressbook: sex, first name, middle name,
 last name, birthday
-A completely renewed calendar module feauturing:
	-Customizable views (day, x days, week, month)
	-Greater flexibility in planning recurring events
	-Timezones are adjusted
	-Better user interface
-Projects module enhanced.
	-Now able to set status, start date and end date.
	-Better performance
-Better mime building with email client

2003/11/22 version 1.94
-register_globals no longer needed
-Bug fixes

2003/10/25 version 1.93
-Huge performance improvements. Rewrote a lot of functions to improve
 performance with a high number of users.
-New permission dialogs to cope with high numbers of users
-CMS module improved
-Fixed bugs with IMAP authentication
-Administrators can set wheter users can modify or add email accounts
-Added trash folder to email client
-Completely removed contacts module

2003/09/21 version 1.91 (GO2 alpha release)
-Group-Office still sometimes used the old contacts module. It is completely
 removed now.
-Fixed bug that caused wierd results when selecting from large addressbooks
-Smaller bugfixes

2003/09/21 version 1.9 (GO2 alpha release)
-LDAP Authentication
-IMAP Authentication
-Sharable addressbooks
-Many improvements to the CMS module
-Works with disabled magic_quotes_gpc in php.ini now
-Solved bug that caused errors when Group-Office had more then 6 users or so
-HTMLarea 3.0 beta implemented and ability to restrict it to styles in a
 stylesheet.
-Group-Office will be released with only the English and Dutch language. Other
 language packs will be released separately.

2003/08/07 version 1.12
-Added Portugese and Spanish
-Added new theme Lush
-Major Scheduler bugs fixed
-Scheduler events can now planned in interval of days, weeks or months
-Authorisation scheme changed, Autorisation code removed and now user can request
 autorisation by typing just the users
 email address. The user will recieve an e-mail and can accept or decline the
 request with that e-mail
-Signature option at e-mail accounts
-Added option with adding email accounts to set the root folder like mail/ or
 INBOX. instead  of the servertype selection
-Shared folders are highlighted in the filesytem module. Removed sharing on
 individual files to avoid confusion
-Added support for manuals in different languages in the 'Help' menu. If a
 manual is presentin the user's language then it will be displayed instantly.
  If it's not then the user can select an avialable language.
-Added dutch manual
-Updated English manual thanks to Casey Ruark


2003/07/24 version 1.11
-Added new themes Nuvola
-Minor bugs from 1.1 fixed
-Improved scheduler view and navigation

2003/07/18 version 1.1
-Restructured the files and modularised the search function and the addressbook
 so they can be removed from the program.
	-All the administrator apps are now in the folder administrator
	-All the user configuration apps are in the folder configuration
	-Moved user profile page (user.php), group members page (group.php),
	 and filetype icon (icon.php) to the controls directory
-Chinese language support thanks to Ricky Chan
-Swedish language support thanks to Martin Östlund
-Added XML Xpath parser class: http://sourceforge.net/projects/phpxpath/
-Custom settings can be stored using the config class
-New administrator tools:
	-configure startup moodule
	-configure menu's
	-automatic bookmarks when a user is created
	-create custom date formats
-imap sub folder support in email module
-HTMLarea 3.0 added as control and integrated into the mail composer so now
 Mozilla can send HTML mail too.
-notification function in scheduler
-New fancy installer that guides you through the whole process. Everybody can
 install it now.
-removed deprecated secret question and answer
-projects module works according to standards now. Everybody can create
 projects and users can subscribe to them if they have at least read
 permissions.
-new option to set a time interval that certain pages like the scheduler
 refresh.
-Contacts can have colors so they stand out
-Completely new Website management module
-Holidays support in scheduler module
-Crystal theme partially redesigned

-Improved view in scheduler and option to set background color of an event


2003/06/18 version 1.06
- From now on all changes are made by Georg Lorenz and Merijn Schering
- New functionality in e-mail module thanks to Georg Lorenz
- New theme Crystal thanks to Georg Lorenz
- Italian translation thanks to Filippo Maguolo
- French translation thanks to Hervé Thouzard
- Image display in e-mail messages that I screwed up in 1.05 fixed.
- Added some missing vars which screwed up the mail composer in English
  language file.
- Better folder handling in e-mail module thanks to Georg Lorenz

2003/06/03 version 1.05
- End user manual added in different formats thanks to Casey Ruark
- Danish language support thanks to Allan Hansen
- German language support thanks to Georg Lorenz
- Bug in new themes fixed that would cause menu to fail when you added a
  bookmark with a single quote.
- Bug in filesystem module that made it impossible to delete a file with
  dubble quotes in the name.
- Title can be easily modified in install.php without removing the name
  Group-Office from some places.
- Added option to send as text or html in e-mail client
- Cyrus IMAP support thanks to Georg Lorenz
- Removed themes that used the menu that was not GPL
- Fixed bug in email composer that made it impossible to attach files
  with the Opera browser

2003/05/17 version 1.04
- Fixed bug in e-mail module that caused problems with multiple accounts
- Fixed bug that made it impossible to add an event if you had only one
  scheduler
- Small bug fixes
- Added groups import in addressbook

2003/05/08 version 1.03
- Changed menu script to JSCookMenu by Heng Yuan because of change to
  incompatible license in the old menu
- Translation bugs by Casey
- Translation for projects module by Casey and Merijn Schering
- Small bug fixes
- added new option to 'Group-Office.php' so you can specify the local
  hostname of the email server and add options to it
  for example 'localhost/notls'when using a redhat server that requires
  the notls option.

2003/04/26 version 1.02

FRAMEWORK:
- Fixed bug in install script that created illegal file and directory
  create modes
- changed default file storage path to '/home/groupoffice/<username>'
  instead of '/usr/groupoffice/<username>'
- Make the module configuration more user friendly by listing the folders in
  the modules folder.
- Language autodetects when not set
- At system information you can view PHP information
- I declare the framework stable (including contact manager)
- Added FAQ file

FILESYSTEM MODULE:
- Search for files and folders added filesystem
- Security bug solved
- Sharing bug solved
- It's now possible to set permissions for files too.
- Some small bug fixes

SCHEDULER MODULE:
- There are small changes in the database structure. some columns were removed.
- Removed the location scheduler function and instead of that
  I added the feature to add an event to multiple schedulers at once.
- Invited users can unsubscribe events
  (delete events from thier schedulers without really deleting the event).
- Added buttons for day and week view.
- List all events for a scheduler in a table.

E-MAIL MODULE:
- Removed '/novalidate-cert' option from connect function. This option is
  needed with some Redhat distro's because of a bug in
  the redhat php-imap package. If you have a redhat server add: '/notls'
  after the host part in Group-Office.
- Fixed bug:when adding or modifying an account it was impossible to navigate
  to the mailboxes.

PROJECTS MODULE:
- Nothing done this is quite useless unless you are dutch speaking.


2003/04/08 version 1.01
- Several small bug fixes
- Fixed sharing in filesystem module


2003/03/21 version 1.0:
First release including:
- Base system
- Filesystem client
- E-mail client
- Addressbook
- Scheduler
- Project management (alpha)



