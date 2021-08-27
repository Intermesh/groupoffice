<?php
/**
 * Group-Office
 *
 * Copyright Intermesh BV.
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base
 */

/**
 * Main configuration
 *
 * This class holds the main configuration options of Group-Office
 * Don't modify this file. The values defined here are just default values.
 * They are overwritten by the configuration options in /config.php or
 * /etc/groupoffice/{HOSTNAME}/config.php
 *
 * To edit these options use install.php.
 *
 * All options can also be found at:
 *
 * http://www.group-office.com/wiki/Configuration_file
 *
 *
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: config.class.inc.php 7687 2011-06-23 12:00:34Z mschering $
 * @copyright Copyright Intermesh BV.
 * @package GO.base
 */


namespace GO\Base;

use GO\Base\Model\Group;


class Config extends Observable{
#FRAMEWORK VARIABLES

/**
 * This can be set to clear the payment method field when an order is duplicated.
 * By default it is set to true, so then the field will be cleared in the duplicate item.
 * When set to false, the value of the original will be copied to the duplicated version.
 * 
 * @deprecated
 * @var boolean 
 */
var $billing_clear_payment_method_on_duplicate = true;	
	
/**
 * Enable this Group-Office installation?
 *
 * @deprecated Block non admin logins
 * @var     StringHelper
 * @access  public
 */
	var $enabled = true;

	/**
	 * Enable sending system emails with an email account from the email module
	 * Needs to be the id of the wanted mail account
	 *
	 * @deprecated Used in tickets now but perhaps create settings there if needed.
	 * 
	 * @var int
	 */
	var $smtp_account_id = false;

	/**
	 * Enable Smime for outgoing system emails.
	 * Note: this only works when a mailaccount is used to send the system emails
	 *			 (Please see: $smtp_account_id)
	 *
	 * @depcreated Do we need this?
	 * @var boolean
	 */
	var $smtp_account_smime_sign = false;

	/**
	 * The password that is needed to sign the Smime certificate for outgoing system emails
	 * Note: this is only needed when a mailaccount is used to send the system emails
	 *			 (Please see: $smtp_account_id)
	 *			 and when $smtp_account_smime_sign is set to true
	 *
	 * @var StringHelper
	 */
	var $smtp_account_smime_password = "";

	/**
	 * 
	 * @deprecated We'll cache the headers
	 * 
	 * @var Set the client side sort of imap to receive all messages with the DATE or R_DATE flag.
	 * If set to false (Default) then it will use the sort key 'ARRIVAL'
	 * Usually done when client_side_sort is used in combination with an Microsoft Exchange Server.
	 */
	var $imap_sort_on_date = false;

	/**
	 * The Group-Office server ID
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $id = 'groupoffice';

	/**
	 * Enable debugging mode. This will log much info to
	 * /home/groupoffice/log/debug.log and will use uncompressed javascripts.
	 * You can also enable this as admin in Group-Office by pressing CTRL+F7.
	 *
	 * @deprecated Is in system settings
	 * @var     bool
	 * @access  public
	 */
	var $debug = false;


	/**
	 * Enable display_errors = on for php
	 *
	 * @deprecated Never diplay errors
	 * @var boolean
	 */
	public $debug_display_errors=false;


//	/**
//	 * Only log debug messages for this remote IP address.
//	 *
//	 * @var     string
//	 * @access  public
//	 */
//	var $debug_log_remote_ip = "";

	
	/**
	 * Just enable the debug log.
	 * 
	 * @deprecated
	 * @var bool
	 */
	var $debug_log = false;

	var $email_enable_labels = true;


	/**
	 * Set the number of days the database log will contain until it will be dumped to a CSV file on disk.
	 * The log module must be installed.
	 *
	 * @deprecated Should be in log module settings
	 * @var int
	 */
	var $log_max_days=14;

	/**
	 * Enable FirePhp
	 *
	 * @deprecated 
	 * @var bool
	 * @access  public
	 */
	var $firephp = false;

	/**
	 * Info log location. Disabled when left empty.
	 *
	 * @deprecated move to new module
	 * @var bool
	 */
	var $info_log = "";

//	/**
//	 * Output errors in debug mode
//	 *
//	 * @var     bool
//	 * @access  public
//	 */
//	var $debug_display_errors=true;

//	/**
//	 * Enable syslog
//	 *
//	 * @var     bool
//	 * @access  public
//	 */
//
//	var $log = false;






	/**
	 * Default VAT percentage
	 *
	 * @deprecated Should move to billing
	 * @var     StringHelper
	 * @access  public
	 */
	var $default_vat = 21;


	/**
	 * Default currency
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	public function getdefault_currency() {
		return \go\core\model\Settings::get()->defaultCurrency;		
	}

	/**
	 * Default date format
	 *
	 *  @deprecated is in system settings
	 * @var     StringHelper
	 * @access  public
	 */
	public function getDefault_date_format() {
		$df =  \go\core\model\Settings::get()->defaultDateFormat;
		return $df[0].$df[2].$df[4];
	}
	
	

	/**
	 * Default date separator
	 *
	 *  @deprecated is in system settings
	 * @var     StringHelper
	 * @access  public
	 */
	public function getdefault_date_separator() {
		$df =  \go\core\model\Settings::get()->defaultDateFormat;
		return $df[1];
	}

	/**
	 * Default time format
	 *
	 * @var     StringHelper
	 * @access  public
	 */

	public function getdefault_time_format() {
		return \go\core\model\Settings::get()->defaultTimeFormat;
	}

	/**
	 * Default name formatting and sorting. Can be last_name or first_name
	 *
	 * @deprecated Should move to address book
	 * @var     StringHelper
	 * @access  public
	 */
	var $default_sort_name = "displayName";


	/**
	 * Default first day of the week 0=sunday 1=monday
	 *
	 * @deprecated Should move to address book
	 * @var     StringHelper
	 * @access  public
	 */
	public function getDefault_first_weekday() {
		return \go\core\model\Settings::get()->defaultFirstWeekday;
	}

	/**
	 * Default decimal separator for numbers
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	public function getdefault_decimal_separator() {
		return \go\core\model\Settings::get()->defaultDecimalSeparator;
	}

	/**
	 * Default thousands separator for numbers
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	public function getdefault_thousands_separator() {
		return \go\core\model\Settings::get()->defaultThousandSeparator;
	}
	
	/**
	 * Default list separator for import and export
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	public function getdefault_list_separator() {
		return \go\core\model\Settings::get()->defaultListSeparator;
	}
	
	/**
	 * Default text separator for import and export
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	public function getdefault_text_separator() {
		return \go\core\model\Settings::get()->defaultTextSeparator;
	}
	
	public function getdefault_timezone() {
		return \go\core\model\Settings::get()->defaultTimezone;			
	}

	/**
	 * Default theme
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $theme = 'Paper';

	/**
	 * Default theme
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $defaultView = 'Extjs3';

	/**
	 * Enable theme switching by users
	 *
	 * @var     bool
	 * @access  public
	 */
	var $allow_themes = true;

	/**
	 * Enable password changing by users
	 *
	 * @var     bool
	 * @access  public
	 */
	var $allow_password_change = true;

	/**
	 * Enable profile editing by every user through the settings dialog
	 *
	 * @var     bool
	 * @access  public
	 */
	var $allow_profile_edit = true;
	
	/**
	 * Hide disabled users in select lists.
	 * 
	 * @var bool 
	 */
	var $hide_disabled_users = true;

//	/**
//	 * Enable user registration by everyone
//	 *
//	 * @var     bool
//	 * @access  public
//	 */
//	var $allow_registration = false;


	/**
	 * The maximum number of MB a new Group-Office user will be able to use.
	 * This can be changed per user after creating it.
	 * Empty means unlimited disk space (Option is NOT yet released)
	 *
	 * @var int
	 */
	var $default_diskquota = 1000;


	/**
	 * Allow e-mail address more then once
	 *
	 * @var     bool
	 * @access  public
	 */
	var $allow_duplicate_email = false;


	/**
	 * The default font to be used in the generated PDF files.
	 * @var StringHelper
	 */
	public $tcpdf_font = "freesans";

	/**
	 * Disable filesystem syncing from the web interface
	 *
	 * @var boolean
	 */
	public $files_disable_filesystem_sync=false;

	/**
	 * Enable spell checker
	 * 
	 * Warning: It has known issues with corrupting the HTML!
	 * 
	 * @var boolean 
	 */
	public $spell_check_enabled = false;
//	/**
//	 * Grant read permissions for these modules to new self-registered users.
//	 * Module names are separated by a comma.
//	 *
//	 * @var     string
//	 * @access  public
//	 */
//	var $register_modules_read = '';
//
//	/**
//	 * Grant write permissions for these modules to new self-registered users.
//	 * Module names are separated by a comma.
//	 *
//	 * @var     string
//	 * @access  public
//	 */
//	var $register_modules_write = '';

	/**
	 * Comma separated list of allowed modules. Leave empty to allow all modules.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $allowed_modules = '';


	/**
	 * Add self-registered users to these user groups
	 * Group names are separated by a comma.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $register_user_groups = '';

	/**
	 * Self-registered users will be visible to these user groups
	 * Group names are separated by a comma.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $register_visible_user_groups = "GROUP_EVERYONE";

	/**
	 * Relative hostname with slash on both start and end
	 * 
	 * use go\core\model\Settings:URL
	 *
	 * or dynamically determined:
	 *
	 * \go\core\webclient\Extjs3::get()->getBaseUrl()
	 * 
	 * @deprecated since 6.3
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $host = '/groupoffice/';
	
	/**
	 * Set Access-Control-Allow-Origin: * header for example.
	 *
	 * @var StringHelper
	 */
	var $extra_headers=array();

//	/**
//	 * Useful to force https://your.host:433 or something like that
//	 *
//	 * @var bool
//	 * @access  public
//	 */
//
//	var $force_login_url = false;

	/**
	 * Force an HTTPS connection in the main /index.php
	 *
	 * @var boolean
	 */
	var $force_ssl=false;

	/**
	 * The path to the root of Group-Office with trailing slash.
	 * 
	 * @deprecated Auto detected
	 * 
	 * @var     StringHelper
	 * @access  public
	 */
	var $root_path = '';

	/**
	 * The path to store temporary files with trailing slash.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $tmpdir = '/tmp/groupoffice/';

	/**
	 * The maximum number of users
	 *
	 * @var     int
	 * @access  public
	 */
	var $max_users = 0;

	/**
	 * If set, user queries will only return this maximum number of users.
	 * Useful in large environments where you don't want users to scroll through all,
	 *
	 * @var int
	 */
	var $limit_usersearch=0;

	/**
	 * The maximum number KB this Group-Office installation may use. 0 will allow unlimited usage of disk space.
	 *
	 * @var     int
	 * @access  public
	 */
	var $quota = 0;


	#database configuration
	/**
	 * The database type to use. Currently only MySQL is supported
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $db_type = 'mysql';
	/**
	 * The host of the database
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $db_host = '';
	/**
	 * The name of the database
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $db_name = '';
	/**
	 * The username to connect to the database
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $db_user = '';
	/**
	 * The password to connect to the database
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $db_pass = '';

	/**
	 * Specifies the port number to attempt to connect to the MySQL server.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $db_port = 3306;

	/**
	 * Specifies the socket or named pipe that should be used.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $db_socket = '';
	
	/**
	 * Specifies the charset that needs to be used for the database.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $db_charset = "'utf8mb4' COLLATE 'utf8mb4_unicode_ci'";
	
	/**
	 *
	 * Useful in clustering mode. Defaults to "1". Set to the number of clustered
	 * nodes.
	 *
	 * @var StringHelper
	 * @access public
	 */

	var $db_auto_increment_increment=1;

	/**
	 *
	 * Give each node an incremented number.
	 *
	 * @var StringHelper
	 * @access public
	 */

	var $db_auto_increment_offset=1;



	#FILE BROWSER VARIABLES

	/**
	 * The path to the location where the files of the file browser module are stored
	 *
	 * This path should NEVER be inside the document root of the webserver
	 * this directory should be writable by apache. Also choose a partition that
	 * has enough diskspace.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $file_storage_path = '/home/groupoffice/';


	/**
	 * Convert non ASCII characters to ASCII codes when uploaded to Group-Office.
	 * Useful for Windows servers that don't support UTF8.
	 *
	 * @var boolean
	 */
	public $convert_utf8_filenames_to_ascii=false;

	/**
	 * The maximum file size the filebrowser attempts to upload. Be aware that
	 * the php.ini file must be set accordingly (http://www.php.net).
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $max_file_size = 1000 * 1024 * 1024;

	/**
	 * The maximum file size of an image to be allowed for thumbnailing in MBs
	 *
	 * @var     integer
	 * @access  public
	 */
	var $max_thumbnail_size = 10;

	/**
	 * Maximum number of old file versions to keep
	 * -1 will disable versioning. 0 will keep an infinite number of versions (Be careful!).
	 *
	 * @var int
	 */
	public $max_file_versions = 3;


	#email variables
	/**
	 * The E-mail mailer type to use. Valid options are: smtp, qmail, sendmail, mail
	 *
	 * @var     int
	 * @access  public
	 */


	/**
	 * The Swift mailer component auto detects the domain you are connecting from.
	 * In some cases it fails and uses an invalid IPv6 IP like ::1. You can
	 * override it here.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $smtp_local_domain = '';


	/**
	 * A special Swift preference to escape dots. For some buggy SMTP servers this
	 * is necessary.
	 *
	 * @var boolean
	 */
	var $swift_qp_dot_escape=false;


	/**
	 * Set to true to prevent users from changing their e-mail aliases in the email module.
	 *
	 * @var boolean
	 */
	var $email_disable_aliases=false;


	/**
	 * We stumbled upon a dovecot server that crashed when sending a command
	 * using LIST-EXTENDED. With this option we can workaround that issue.
	 *
	 * @var StringHelper
	 */
	var $disable_imap_capabilities="";

	/**
	 * A comma separated list of smtp server IP addresses that you
	 * want to restrict.
	 *
	 * eg. '213.207.103.219:10,127.0.0.1:10';
	 *
	 * Will restrict those IP's to 10 e-mails per day.
	 *
	 * @var unknown_type
	 */

	var $restrict_smtp_hosts = '';

	/**
	 * The maximum summed size of e-mail attachments in a message in bytes
	 * Group-Office will accept.
	 *
	 * @var     int
	 * @access  public
	 */
	var $max_attachment_size = 50 * 1024 * 1024;


	//External programs

	/**
	 * Command to create ZIP archive
	 * @var     StringHelper
	 * @access  public
	 */
	var $cmd_zip = '/usr/bin/zip';

	/**
	 * Command to unpack ZIP archive
	 * @var     StringHelper
	 * @access  public
	 */
	var $cmd_unzip = '/usr/bin/unzip';

	/**
	 * Command to control TAR archives
	 * @var     StringHelper
	 * @access  public
	 */
	var $cmd_tar = '/bin/tar';

	/**
	 * Command to set system passwords. Used by passwd.users.class.inc.
	 * SUDO must be set up!
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $cmd_chpasswd = '/usr/sbin/chpasswd';

	/**
	 * Command to SUDO
	 * @var     StringHelper
	 * @access  public
	 */
	var $cmd_sudo = '/usr/bin/sudo';

	/**
	 * Command to convert xml to wbxml
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $cmd_xml2wbxml = '/usr/bin/xml2wbxml';

	/**
	 * Command to convert wbxml to xml
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $cmd_wbxml2xml = '/usr/bin/wbxml2xml';

	/**
	 * Command to unpack winmail.dat files
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $cmd_tnef = '/usr/bin/tnef';

	/**
	 * Command to execute the php command line interface
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $cmd_php = 'php';


	/**
	 * Length of the password generated when a user uses the lost password option
	 *
	 * @var int
	 */
	var $default_password_length=6;

	/**
	 * Required length of passwords.
	 *
	 * @var boolean
	 */
	var $password_validate=true;

	/**
	 * Required length of passwords.
	 *
	 * @var int
	 */
	var $password_min_length=6;

	/**
	 * Require an uppercase char
	 *
	 * @var boolean
	 */
	var $password_require_uc=true;

	/**
	 * Require a lowercase char
	 *
	 * @var boolean
	 */
	var $password_require_lc=true;

	/**
	 * Require numbers
	 *
	 * @var boolean
	 */
	var $password_require_num=true;

	/**
	 * Require a special char
	 *
	 * @var boolean
	 */
	var $password_require_sc=true;

	/**
	 * Required unique chars
	 *
	 * @var int
	 */
	var $password_require_uniq=3;

	/**
	 * Automatically log a user out after n seconds of inactivity
	 *
	 * @var int
	 */
	var $session_inactivity_timeout = 0;

	/**
	 * Callto: link template
	 */

	var $callto_template='tel://{phone}';

	/**
	 * Open a new new window when a phone number is clicked
	 * 
	 * @var bool
	 */
	var $callto_open_window = false;

	/**
	 * Disable security check for cross domain forgeries
	 *
	 * @var <type>
	 */

	var $disable_security_token_check=false;

	/**
	 * The number of items displayed in the navigation panels (Calendars, addressbooks etc.)
	 * Don't set this number too high because it may slow the browser and server down.
	 *
	 * @var type
	 */

	var $nav_page_size=50;


	/**
	 * If you are behind a proxy you can set it here for all CURL operations Group-Office performs.
	 *
	 * This curl function will be used:
	 * curl_setopt($ch, CURLOPT_PROXY, "http://proxy.com:8080");
	 *
	 * @var StringHelper
	 */
	var $curl_proxy="";
	
	
	var $calendar_tasklist_show = 0;


//	/**
//	 * Enable logging of slow requests
//	 *
//	 * @var boolean
//	 */
//	public $log_slow_requests=false;
//
//	/**
//	 * Slow request time in seconds
//	 *
//	 * @var float
//	 */
//	public $log_slow_requests_trigger=1;
//
//	/**
//	 * Path of the log file
//	 *
//	 * @var string
//	 */
//	public $log_slow_requests_file="/home/groupoffice/slow-requests.log";

	/*//////////////////////////////////////////////////////////////////////////////
	 //////////      Variables that are not touched by the installer   /////////////
	 //////////////////////////////////////////////////////////////////////////////*/



	/**
	 * Enable zlib compression for faster downloading of scripts and css
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $zlib_compress = true;

	/**
	 * Default list page size
	 *
	 * @var int
	 */
	var $default_max_rows_list = 30;

	/**
	 * Product name. If changed all Group-Office references will disappear.
	 * @var StringHelper
	 */

	var $product_name='GroupOffice';


		/* The permissions mode to use when creating files
	 *
	 * @var     string
	 * @access  public
	 */
	var $file_create_mode = '0644';

	/* The permissions mode to use when creating folders
	 *
	 * @var     string
	 * @access  public
	 */
	var $folder_create_mode = '0755';

	/* New files and folders will be chown'd to this group.
	 *
	 * @var     string
	 * @access  public
	 */
	var $file_change_group = '';

	/*////////////////////////////////////////////////
	 * Variables below this should not be changed
	 *////////////////////////////////////////////////

	#group configuration
	/**
	 * The administrator user group ID
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $group_root = '1';
	/**
	 * The everyone user group ID
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $group_everyone = '2';

	/**
	 * The internal user group ID
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $group_internal = '3';

	/**
	 * Date formats to be used. Only Y, m and d are supported.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $date_formats = array(
	'dmY',
	'mdY',
	'Ymd'
	);

	/**
	 * Date separators to be used.
	 *
	 * @var     StringHelper
	 * @access  public
	 */

	var $date_separators = array(
	'-',
	'.',
	'/'
	);
	/**
	 * Time formats to be used.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $time_formats = array(
	'H:i',
	'h:i a'
	);

	/**
	 * Relative path to the modules directory with no slash at start and end
	 *
	 * @var     StringHelper
	 * @access  private
	 */
	var $module_path = 'modules';
	/**
	 * Relative URL to the administrator directory with no slash at start and end
	 *
	 * @var     StringHelper
	 * @access  private
	 */

	var $configuration_url = 'configuration';


	/**
	 * The link or e-mail address in menu help -> support.
	 *
	 * No menu item is generated if left empty.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $support_link = 'https://www.group-office.com/support/';

	/**
	 * The link or e-mail address in menu help -> report bug.
	 *
	 * No menu item is generated if left empty.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $report_bug_link = 'http://sourceforge.net/tracker/?group_id=76359&atid=547651';
	
	/**
	 * Relative path to the classes directory with no slash at start and end
	 *
	 * @var     StringHelper
	 * @access  private
	 */
	var $class_path = 'classes';
	/**
	 * Relative path to the controls directory with no slash at start and end
	 *
	 * @var     StringHelper
	 * @access  private
	 */
	var $control_path = 'controls';
	/**
	 * Relative URL to the controls directory with no slash at start and end
	 *
	 * @var     StringHelper
	 * @access  private
	 */
	var $control_url = 'controls';
	/**
	 * Relative path to the themes directory with no slash at start and end
	 *
	 * @var     StringHelper
	 * @access  private
	 */
	var $theme_path = 'themes';

	/**
	 * Relative URL to the themes directory with no slash at start and end
	 *
	 * @var     StringHelper
	 * @access  private
	 */
	var $theme_url = 'themes';

	/**
	 * Relative path to the language directory with no slash at start and end
	 *
	 * @var     StringHelper
	 * @access  private
	 */
	var $language_path = 'language';

	/**
	 * Original tmpdir. The user_id is appended (/tmp/1/) to the normal tmpdir.
	 * In some cases you don't want that.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $orig_tmpdir = '';

	/**
	 * Path with trailing slash where cached scripts are generated.
	 * Defaults to $this->tmpdir/cache/
	 *
	 * @var StringHelper
	 */
	var $cachefolder='';

	/**
	 * Database object
	 *
	 * @var     object
	 * @access  private
	 */
	var $db;

	/**
	 * The amount of seconds before Group-Office will check for new mail or
	 * other notifications.
	 *
	 * @var int
	 */
	var $checker_interval=120;

	
	/**
	 * Full URL to the Group-Office assets folder with trailing slash
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $assets_url = '';

	/**
	 * Full Path to the Group-Office assets folder with trailing slash
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $assets_path = '';

	/**
	 * Enables the quicklink option in the message panel of an email message.
	 * [] Link email conversation to contact
	 * [] Link email conversation to company
	 *
	 * @var Boolean
	 */
	var $allow_quicklink = true;


	/**
	 * Automatically opens the file select dialog when opening the upload dialog.
	 *
	 * @var boolean
	 */
	public $upload_quickselect = true;

	/**
	 * EXPERIMENTAL! Minifies JS and CSS on the fly.
	 * Doesn't seem to make much difference when gzip is used.
	 *
	 * @var boolean
	 */
	public $minify = false;

	/**
	 * Allow creation of tickets without the need of specify an email-address
	 *
	 * @var boolean
	 */
	public $tickets_no_email_required = false;

	/**
	 * Enable encoding of the special characters in the phone number of the callto links
	 * Defaults to false.
	 *
	 * @var boolean
	 */
	public $encode_callto_link = false;
	
	/**
	 * Show the addressbook property in the files tree
	 * Defaults to true
	 * 
	 * @var boolean 
	 */
	public $files_show_addressbooks = true;
	
	/**
	 * Show the projects property in the files tree
	 * Defaults to true
	 * 
	 * @var boolean 
	 */
	public $files_show_projects = true;
	
	/**
	 * The maximum filesize of files that may be zipped.
	 * Defaults to 256MB
	 * 
	 * @var int 
	 */
	public $zip_max_file_size = 256000000;
	
	/**
	 * Show the "remember login" checkbox on the login form
	 * 
	 * @var boolean 
	 */
	public $remember_login = true;	
	
	/**
	 * The amount of days before a password change is forced to the user
	 * When set to 0, this function is disabled
	 * 
	 * @var int 
	 */
	public $force_password_change = 0;	
	
	/**
	 * Let Group-Office check if you are already logged in on an other location.
	 * When enabled (true), you can only be logged in to one location. (sync will still work)
	 * 
	 * @var boolean 
	 */
	public $use_single_login = false;
	
	
	/**
	 * This set the swift email body to base64
	 * 
	 * //Override qupted-prinatble encdoding with base64 because it uses much less memory on larger bodies. See also:
	 * //https://github.com/swiftmailer/swiftmailer/issues/356
	 * 
	 * @var bool 
	 */
	public $swift_email_body_force_to_base64 = false;
	
	
	/**
	 * Will link the event to every participants except for the organizer
	 * 
	 * WARNING: This caused major slowdown with lots of participants
	 * @var bool 
	 */
	var $calendar_autolink_participants = false;

	/**
	 * Stores the original values that are stored in the config.php file.
	 * So without the modifications that this class adds to it.
	 * 
	 * @var array 
	 */
	private $_original_config = array();
	
	/**
	 * Get a value from the original config.php file.
	 * These are the values that are set in the file and not processed by this class
	 * 
	 * @param string $key
	 * @return string
	 */
	public function getOriginalValue($key){
		
		if(!isset($this->_original_config[$key])){
			return null;
		}
		
		return $this->_original_config[$key];
	}
	
	//ignore errors
	public function __get($name) {
		
		if(method_exists($this, "get" . $name)) {
			return $this->{"get" . $name}();
		}
		
		return null;
	}
	
	public function __isset($name) {
		if(method_exists($this, "get" . $name)) {
			$var =  $this->{"get" . $name}();
			return isset($var);
		}
		
		return false;
	}
	
	private function getGlobalConfig() {
		$globalConfigFile = '/etc/groupoffice/globalconfig.inc.php';
		try {
			if (file_exists($globalConfigFile)) {
				require($globalConfigFile);
			}
		}catch(\Exception $e) {
			//ignore open_basedir error
		}

		return $config ?? [];
	}

	private function getInstanceConfig() {
		$config_file = $this->get_config_file();

		if($config_file)
			include($config_file);

		$config['configPath'] = $config_file;

		return $config;
	}
		
	
	
	/**
	 * Constructor. Initialises all public variables.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
		$config = array();

		$this->root_path = str_replace('\\','/',dirname(dirname(dirname(__FILE__)))).'/';

		$config = array_merge($this->getGlobalConfig(), $this->getInstanceConfig());
		
		//auto host
		if(empty($config['host'])) {
			$config['host'] = dirname($_SERVER['SCRIPT_NAME']);
		} 
		$config['host'] = trim($config['host'], '/');
		$config['host'] = empty($config['host']) ? '/' : '/' . $config['host'] . '/';		
		$config['root_path'] = \go\core\Environment::get()->getInstallFolder()->getPath() . '/';
		
		$this->_original_config = $config;
		
		foreach($config as $key=>$value) {
			if(!method_exists($this, "get".$key)) {
				$this->$key=$value;
			}
		}
		
		$this->file_storage_path = rtrim($this->file_storage_path, '/').'/';
		$this->tmpdir = rtrim($this->tmpdir, '/').'/';

//		if($this->info_log=="")
//			$this->info_log =$this->file_storage_path.'log/info.log';

		//this can be used in some cases where you don't want the dynamically
		//determined full URL. This is done in set_full_url below.
		//$this->orig_full_url = $this->full_url;

		$this->orig_tmpdir=$this->tmpdir;

		if(empty($this->db_user)) {
		//Detect some default values for installation if root_path is not set yet
			$this->host = dirname($_SERVER['SCRIPT_NAME']);
			$basename = basename($this->host);
			while($basename=='install' || $basename == 'api') {
        $this->host = dirname($this->host);
        $basename = basename($this->host);
      }

			if(substr($this->host,-1) != '/') {
				$this->host .= '/';
			}

			$this->db_host='localhost';

			if(Util\Common::isWindows()) {
				$this->file_storage_path = substr($this->root_path,0,3).'groupoffice/';
				$this->tmpdir=substr($this->root_path,0,3).'temp';

				$this->cmd_zip=$this->root_path.'controls/win32/zip.exe';
				$this->cmd_unzip=$this->root_path.'controls/win32/unzip.exe';
				$this->cmd_xml2wbxml=$this->root_path.'controls/win32/libwbxml/xml2wbxml.exe';
				$this->cmd_wbxml2xml=$this->root_path.'controls/win32/libwbxml/wbxml2xml.exe';

				$this->convert_utf8_filenames_to_ascii=true;
			}

			if(empty($config['tmpdir']) && function_exists('sys_get_temp_dir')) {
				$this->tmpdir = rtrim(str_replace('\\','/', sys_get_temp_dir()),'/').'/groupoffice/';
			}
			
		}

//		// path to classes
//		$this->class_path = $this->root_path.$this->class_path.'/';
//
//		// path to themes
//		$this->theme_path = $this->root_path.$this->theme_path.'/';
//
//		// URL to themes
//		$this->theme_url = $this->host.$this->theme_url.'/';
//
//		// path to controls
//		$this->control_path = $this->root_path.$this->control_path.'/';
//
//		// url to controls
//		$this->control_url = $this->host.$this->control_url.'/';
//
//		// path to modules
//		$this->module_path = $this->root_path.$this->module_path.'/';
//
//		// url to user configuration apps
//		$this->configuration_url = $this->host.$this->configuration_url.'/';


		if($this->debug)
			$this->debug_log=true;

//		if($this->debug_log){// || $this->log_slow_requests) {
//
//			list ($usec, $sec) = explode(" ", microtime());
//			$this->loadstart = ((float) $usec + (float) $sec);
//
////			$dat = getrusage();
////			define('PHP_TUSAGE', microtime(true));
////			define('PHP_RUSAGE', $dat["ru_utime.tv_sec"]*1e6+$dat["ru_utime.tv_usec"]);
//		}

//		if(is_string($this->file_create_mode)) {
//			$this->file_create_mode=octdec($this->file_create_mode);
//		}
//
//		if(is_string($this->folder_create_mode)) {
//			$this->folder_create_mode=octdec($this->folder_create_mode);
//		}

		if($this->debug_log) {
			$this->log=true;
		}

		//$this->set_full_url();

		if(!$this->support_link && $this->isProVersion()){
			$this->support_link = "https://www.group-office.com/support";
		}

	}
	
	/**
	 * The no-reply e-mail which will be used to send system messages
	 * Check if the noreply_email variable is set in the config.php file.
	 * If it is not set, then use noreply@ {webmaster_email domain name}
	 * When the webmaster email is not set, then this will be noreply@example.com
	 * 
	 * @return     StringHelper
	 */
	public function getNoreply_email(){

		$wmdomain = 'example.com';

		if(!empty($this->webmaster_email)){
			$extractedEmail = explode('@',$this->webmaster_email);
			if(isset($extractedEmail[1]))
				$wmdomain = $extractedEmail[1];
		}

		return 'noreply@'.$wmdomain;
	}
	
	public function getVersion() {
		return go()->getVersion();
	}
	
	public function getMtime() {
		return go()->getVersion();
	}
	
	public function getTitle() {
		return go()->getSettings()->title;
	}
	
	public function getlanguage() {
		return go()->getSettings()->language;
	}
	
	public function getwebmaster_email() {
		return go()->getSettings()->systemEmail;
	}
	
	public function getsmtp_server() {
		return go()->getSettings()->smtpHost;
	}
	
	public function getsmtp_port() {
		return go()->getSettings()->smtpPort;
	}
	
	public function getsmtp_username() {
		return go()->getSettings()->smtpUsername;
	}
	
	public function getsmtp_password() {
		return go()->getSettings()->decryptSmtpPassword();
	}
	
	public function getsmtp_encryption() {
		return go()->getSettings()->smtpEncryption;
	}
	
	public function getdebug_email() {
		return !empty(go()->getConfig()['debugEmail']) ? go()->getConfig()['debugEmail'] : null;
	}
	
	public function gepassword_min_length() {
		return go()->getSettings()->passwordMinLength;
	}
	
	public function getlogin_message() {
		return go()->getSettings()->loginMessageEnabled ? go()->getSettings()->loginMessage : null;
	}
	
	public function getfull_url() {
		return rtrim(go()->getSettings()->URL, '/') . '/';
	}
	
	public function getorig_full_url() {
		return rtrim(go()->getSettings()->URL, '/') . '/';
	}

	public function getMajorVersion(){
		return substr($this->version,0,3);;
	}

	/**
	 * Get the temporary files folder.
	 *
	 * @return Fs\Folder
	 */
	public function getTempFolder($autoCreate=true){
		$user_id = \GO::user() ? \GO::user()->id : 0;

		$path = $this->orig_tmpdir;

		if(PHP_SAPI=='cli'){
			$path .= 'cli/';
		}
		$path .= $user_id;

		$folder = new Fs\Folder($path);

		if($autoCreate)
			$folder->create(0777);

		return $folder;
	}

	/**
	 * Get the cache folder for cached scripts.
	 *
	 * @return \Fs\Folder
	 */
	public function getCacheFolder($autoCreate=true){

		if(empty($this->cachefolder)){
			$this->cachefolder=$this->file_storage_path.'cache/';
		}

		$folder = new Fs\Folder($this->cachefolder);

		if($autoCreate)
			$folder->create(0777);
		return $folder;
	}

	/**
	 * Check if the pro package is available.
	 *
	 * @return boolean
	 */
	public function isProVersion(){
		return is_dir($this->root_path.'modules/professional');
	}



//	function __destruct() {
//		if($this->debug_log) {
//			//\GO::debug('Performed '.$GLOBALS['query_count'].' database queries', $this);
//
//			\GO::debug('Page load took: '.(Util\Date::getmicrotime()-$this->loadstart).'ms', $this);
//			\GO::debug('Peak memory usage:'.round(memory_get_peak_usage()/1048576,2).'MB', $this);
//
//		}
////		$this->_logSlowRequest();
//	}

//	private function _logSlowRequest(){
//		if($this->log_slow_requests){
//			$time = Util\Date::getmicrotime()-$this->loadstart;
//			if($time>$this->log_slow_requests_trigger){
//
//				$logStr = "URI: ";
//
//				if(isset($_SERVER['HTTP_HOST']))
//					$logStr .= $_SERVER['HTTP_HOST'];
//
//				if(isset($_SERVER['REQUEST_URI']))
//					$logStr .= $_SERVER['REQUEST_URI'];
//
//				$logStr .= '; ';
//
//				$logStr .= 'r: '.\GO::router()->getControllerRoute().';';
//
//				$logStr .= 'time: '.$time.';'."\n";
//
//
//				file_put_contents($this->log_slow_requests_file, $logStr,FILE_APPEND);
//			}
//		}
//	}

	function use_zlib_compression(){

		if(!isset($this->zlib_support_tested)){
			$this->zlib_support_tested=true;
			$this->zlib_compress=$this->zlib_compress && extension_loaded('zlib') && !ini_get('zlib.output_compression');
		}
		return $this->zlib_compress;
	}


	public function getCompleteDateFormat(){
		return $this->default_date_format[0].
						$this->default_date_separator.
						$this->default_date_format[1].
						$this->default_date_separator.
						$this->default_date_format[2];
	}

	/**
	 * Get's the location of a configuration file.
	 * Group-Office searches two locations:
	 *	1. /etc/Group-Office/APACHE SERVER NAME/subdir/to/groupoffice/config.php
	 *	2. /path/to/groupoffice/config.php
	 *
	 * The first location is more secure because the sensitive information is kept
	 * outside the document root.
	 *
	 * @return StringHelper Path to configuration file
	 */

	public function get_config_file() {		
		return \go\core\App::findConfigFile();
	}

	/**
	 * Sets Full URL to reach Group-Office with slash on end
	 *
	 * This function checks wether or not Group-Office runs on a
	 * default http or https port and stores the full url in a variable
	 */
	public function set_full_url() {
		//full_url may be configured permanent in config.php. If not then
		//autodetect it and put it in the session. It can be used by wordpress for
		//example.

		//this used to use HTTP_HOST but that is a client controlled value which can be manipulated and is unsafe.
		if(empty($this->full_url)){
			if(isset($_SERVER["SERVER_NAME"])) {
				if(!isset($_SESSION['GO_SESSION']['full_url']) && isset($_SERVER["SERVER_NAME"])) {
					$https = (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on" || $_SERVER["HTTPS"] == "1")) || !empty($_SERVER["HTTP_X_SSL_REQUEST"]);
					$_SESSION['GO_SESSION']['full_url'] = 'http';
					if ($https) {
						$_SESSION['GO_SESSION']['full_url'] .= "s";
					}
					$_SESSION['GO_SESSION']['full_url'] .= "://".$_SERVER["SERVER_NAME"];
					if ((!$https && $_SERVER["SERVER_PORT"] != "80") || ($https && $_SERVER["SERVER_PORT"] != "443"))
						$_SESSION['GO_SESSION']['full_url'] .= ":".$_SERVER["SERVER_PORT"];

					$_SESSION['GO_SESSION']['full_url'] .= $this->host;
				}
				$this->full_url=$_SESSION['GO_SESSION']['full_url'];
			}else
			{
				$_SESSION['GO_SESSION']['full_url']=$this->full_url;
			}
			if(empty($this->orig_full_url))
				$this->orig_full_url=$this->full_url;
		}
	}


	/**
	 * Gets a custom saved setting from the database
	 *
	 * @param StringHelper $name Configuration key name
   * @param integer $user_id Id of the user you want to get a setting from - defaults to 0 for the default setting,
	 * @param mixed $default The default value that will be returned when the setting cannot be found
	 *
	 * @return mixed Configuration value
	 */
	public function get_setting($name, $user_id=0,$default=null) {
		$attributes['name']=$name;
    $attributes['user_id']=$user_id;

		$setting = Model\Setting::model()->findSingleByAttributes($attributes);
		if ($setting) {
			return $setting->value;
		}
		return $default;
	}

	/**
	 * Get multiple settings at once
	 * @param array $keys
	 * @param int $user_id Optional leave empty for global settings
	 *
	 * @return array Key value array('setting name'=>'value');
	 */
	public function getSettings($keys, $user_id=0){
		$findParams = Db\FindParams::newInstance()->select();

		$findParams->getCriteria()
						->addCondition('user_id', $user_id)
						->addInCondition('name', $keys);

		$stmt = Model\Setting::model()->find($findParams);

		$return = array();
		foreach($keys as $key){
			$return[$key]=null;
		}

		foreach($stmt as $setting){
			$return[$setting->name]=$setting->value;
		}

		return $return;
	}

    /**
     * Get all settings with the same key for the settings table
     * @param string $name the key of the setting
     * @return array all settings in user_id value pairs
     *
    public function get_settings($name) {
      $params = Db\FindParams::newInstance()->select('*');
      $params->getCriteria()->addCondition('name',$name);
      return Model\Setting::model()->find($params)->fetchAll();
    }
     *
     */

	/**
	 * Saves a custom setting to the database
	 *
	 * @param 	StringHelper $name Configuration key name
	 * @param 	StringHelper $value Configuration key value
	 * @param integer $user_id Id of user you want to load the setting for
     * defaults to 0 for the default setting (not user specific)
	 * @return bool Returns true on succes
	 */
	public function save_setting( $name, $value, $user_id=0) {

		$attributes['name']=$name;
		$attributes['user_id']=$user_id;

		$setting = Model\Setting::model()->findSingleByAttributes($attributes);
		if(!$setting){
			$setting = new Model\Setting();
			$setting->setAttributes($attributes);
		}

		$setting->value=$value;
		return $setting->save();
	}

	/**
	 * Deletes a custom setting from the database
	 *
	 * @param 	StringHelper $name Configuration key name
     * @params integer $user_id The is of the user you want to delete a setting from
     * defaults to 0 for the default setting,
     * if set to false settings for every user inclusing default will be deleted
	 * @access public
	 * @return bool Returns true on succes
	 */
	function delete_setting( $name , $user_id=0) {
		$attributes['name']=$name;
        if($user_id!==false)
          $attributes['user_id']=$user_id;

		$setting = Model\Setting::model()->findSingleByAttributes($attributes);
		return $setting ? $setting->delete() : true;
	}



	/**
	 * Save the current configuraton to the config.php file.
	 *
	 * @return boolean
	 */
	public function save($extraConfig=array()) {

		$values = get_object_vars(\GO::config());
		$config=array();

		require($this->get_config_file());

		foreach($values as $key=>$value)
		{
			if($key == 'full_url')
			break;

			if(!is_object($value))
			{
				$config[$key]=$value;
			}
		}
		$config = array_merge($config, $extraConfig);

		return Util\ConfigEditor::save(new Fs\File(\GO::config()->get_config_file()), $config);
	}
}
