<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: config.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.basic
 */

/**
 * This class holds the main configuration options of Group-Office
 * Don't modify this file. The values defined here are just default values.
 * They are overwritten by the configuration options in /config.inc.php or
 * /etc/groupoffice/{HOSTNAME}/config.inc.php
 *
 * To edit these options use install.php.
 *
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: config.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @package go.basic
 *
 * @uses db
 */

class GO_CONFIG {
#FRAMEWORK VARIABLES

/**
 * Enable this Group-Office installation?
 *
 * @var     StringHelper
 * @access  public
 */
	var $enabled = true;

	/**
	 * The Group-Office server ID
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $id = 'groupoffice';

	/**
	 * Enable debugging mode
	 *
	 * @var     bool
	 * @access  public
	 */
	var $debug = false;

	/**
	 * Just enable the debug log.
	 * @var bool
	 */
	var $debug_log = false;
	
	/**
	 * Info log location. If empty it will be in <file_storage_path>/log/info.log
	 * @var bool
	 */
	var $info_log = "";

	/**
	 * Output errors in debug mode
	 *
	 * @var     bool
	 * @access  public
	 */
	var $debug_display_errors=true;

	/**
	 * Enable syslog
	 *
	 * @var     bool
	 * @access  public
	 */

	var $log = false;

	/**
	 * Default language
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $language = 'en';

	/**
	 * Default country
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $default_country = "NL";

	/**
	 * Default timezone
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $default_timezone = 'Europe/Amsterdam';

	/**
	 * Default language
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $default_currency='€';

	/**
	 * Default date format
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $default_date_format='dmY';

	/**
	 * Default date separator
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $default_date_separator='-';

	/**
	 * Default time format
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $default_time_format='G:i';

	/**
	 * Default name formatting and sorting. Can be last_name or first_name
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $default_sort_name = "last_name";


	/**
	 * Default first day of the week 0=sunday 1=monday
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $default_first_weekday=1;

	/**
	 * Default decimal separator for numbers
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $default_decimal_separator=',';

	/**
	 * Default thousands separator for numbers
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $default_thousands_separator='.';

	/**
	 * Default theme
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $theme = 'Default';

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
	 * Set to true to show all user groups as recipients in the mail module.
	 *
	 * @var     bool
	 * @access  public
	 */
	var $show_all_user_groups_in_mail = false;

	/**
	 * Enable user registration by everyone
	 *
	 * @var     bool
	 * @access  public
	 */
	var $allow_registration = false;

	/**
	 * Enabled fields for the user registration form
	 *
	 * @var     bool
	 * @access  public
	 */
	var $registration_fields = 'title_initials,sex,birthday,address,home_phone,fax,cellular,company,department,function,work_address,work_phone,work_fax,homepage';


	/**
	 * Enabled fields for the user registration form
	 *
	 * @var     bool
	 * @access  public
	 */
	var $required_registration_fields = 'company,address';

	/**
	 * Allow e-mail address more then once
	 *
	 * @var     bool
	 * @access  public
	 */
	var $allow_duplicate_email = false;

	/**
	 * Activate self regstered accounts?
	 *
	 * @var     bool
	 * @access  public
	 */
	var $auto_activate_accounts = false;

	/**
	 * Notify webmaster of user signup?
	 *
	 * @var     bool
	 * @access  public
	 */
	var $notify_admin_of_registration = true;

	/**
	 * Grant read permissions for these modules to new self-registered users.
	 * Module names are separated by a comma.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $register_modules_read = '';

	/**
	 * Grant write permissions for these modules to new self-registered users.
	 * Module names are separated by a comma.
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $register_modules_write = '';

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
	var $register_visible_user_groups = 'Everyone';

	/**
	 * Relative hostname with slash on both start and end
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $host = '/groupoffice/';
	
	/**
	 * Useful to force https://your.host:433 or something like that
	 * 
	 * @var bool
	 * @access  public
	 */

	var $force_login_url = false;

	/**
	 * Full URL to reach Group-Office with slash on end. This value is determined
	 * automatically if not set in config.php
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $full_url = '';

	/**
	 * Title of Group-Office
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $title = '';

	/**
	 * The e-mail of the webmaster
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $webmaster_email = 'webmaster@example.com';

	/**
	 * The link in menu help -> contents
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $help_link = 'http://www.group-office.com/wiki/';


	/**
	 * The path to the root of Group-Office with slash on end
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $root_path = '';

	/**
	 * The path to store temporary files with a slash on end
	 * Leave to ../ for installation
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $tmpdir = '/tmp/';

	/**
	 * The maximum number of users
	 *
	 * @var     int
	 * @access  public
	 */
	var $max_users = 0;

	/**
	 * The maximum number KB this Group-Office installation may use. 0 will allow unlimited usage of disk space.
	 *
	 * @var     int
	 * @access  public
	 */
	var $quota = 0;
	
	/**
	 * Limit the maximum results for user to a fixed number. When enabled a search query must be entered to get results.
	 * 
	 * @var int 
	 */
	var $limit_usersearch=0;


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
	 * The maximum file size the filebrowser attempts to upload. Be aware that
	 * the php.ini file must be set accordingly (http://www.php.net).
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $max_file_size = '10000000';


	#email variables
	/**
	 * The E-mail mailer type to use. Valid options are: smtp, qmail, sendmail, mail
	 *
	 * @var     int
	 * @access  public
	 */
	//var $mailer = 'smtp';
	/**
	 * The SMTP host to use when using the SMTP mailer
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $smtp_server = 'localhost';
	/**
	 * The SMTP port to use when using the SMTP mailer
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $smtp_port = '25';

	/**
	 * The SMTP username for authentication (Empty for no authentication)
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $smtp_username = '';

	/**
	 * The SMTP password for authentication
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $smtp_password = '';

	/**
	 * Leave blank or set to tls or ssl
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $smtp_encryption = '';


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
	 * The maximum size of e-mail attachments the browser attempts to upload.
	 * Be aware that the php.ini file must be set accordingly (http://www.php.net).
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $max_attachment_size = '10000000';


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
	 * If this URL is set and PhpMyAdmin is configured to allow authentication
	 * with signon. You can edit the database in the admin tools module.
	 *
	 * Example phpmyadmin configuration:
	 *
	 * $cfg['Servers'][$i]['auth_type'] = 'signon';
	 * $cfg['Servers'][$i]['SignonSession'] = 'groupoffice';
	 * $cfg['Servers'][$i]['SignonURL']='http://localhost/phpmyadmin/';
	 *
	 * @var unknown_type
	 */
	var $phpMyAdminUrl='';

	/**
	 * Comma separated list of scripts that are unsafe for whatever reason.
	 * For example: A form on a website that will add a contact to an addressbook.
	 * It can add addressbook entries without authentication but can still be very useful
	 *
	 * Scripts can be separated with a comma: modules/addressbook/cms.php,modules/cms/example.php
	 *
	 * @var StringHelper
	 */

	var $allow_unsafe_scripts='';

	/**
	 * Length of the password generated when a user uses the lost password option
	 *
	 * @var int
	 */
	var $default_password_length=6;
	
	/**
	 * Automatically log a user out after n seconds of inactivity
	 *
	 * @var int
	 */

	var $session_inactivity_timeout = 0;

	/**
	 * Callto: link template
	 */

	var $callto_template='callto:{phone}';


	/**
	 * Don't use flash to upload. In some cases it doesn't work like when
	 * using a self-signed certificate.
	 */
	var $disable_flash_upload=false;

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


	/*//////////////////////////////////////////////////////////////////////////////
	 //////////      Variables that are not touched by the installer   /////////////
	 //////////////////////////////////////////////////////////////////////////////*/

	/**
	 * The Group-Office version number
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $version = '4.0.91';


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

	/* The permissions mode to use when creating folders
	 *
	 * @var     string
	 * @access  public
	 */
	var $file_change_group = '';

	/**
	 * Modification date
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $mtime = '20120411';

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
	'G:i',
	'g:i a'
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
	var $theme_path = 'views/Extjs3/themes';

	/**
	 * Relative URL to the themes directory with no slash at start and end
	 *
	 * @var     StringHelper
	 * @access  private
	 */
	var $theme_url = 'views/Extjs3/themes';

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
	 * Database object
	 *
	 * @var     object
	 * @access  private
	 */
	var $db;

	/**
	 * Enable zlib compression for faster downloading of scripts and css
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $zlib_compress = true;


	var $product_name='Group-Office';

	/**
	 * Full original URL to reach Group-Office with slash on end
	 *
	 * @var     StringHelper
	 * @access  public
	 */
	var $orig_full_url = '';

	/**
	 * Constructor. Initialises all public variables.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
		$config = array();

		$this->root_path = str_replace('\\','/',dirname(dirname(dirname(__FILE__)))).'/';

		//suppress error for open_basedir warnings etc
		if(@file_exists('/etc/groupoffice/globalconfig.inc.php')) {
			require('/etc/groupoffice/globalconfig.inc.php');
		}

		$config_file = $this->get_config_file();

		if($config_file)
			include($config_file);

		foreach($config as $key=>$value) {
			$this->$key=$value;
		}
		
		if($this->info_log=="")
			$this->info_log =$this->file_storage_path.'log/info.log';

		//this can be used in some cases where you don't want the dynamically
		//determined full URL. This is done in set_full_url below.
		$this->orig_full_url = $this->full_url;

		$this->orig_tmpdir=$this->tmpdir;

		if(empty($this->db_user)) {
		//Detect some default values for installation if root_path is not set yet
			$this->host = dirname(dirname($_SERVER['PHP_SELF']));
			
			if(substr($this->host,-1) != '/') {
				$this->host .= '/';
			}

			$this->db_host='localhost';

			if(is_windows()) {
				$this->file_storage_path = substr($this->root_path,0,3).'groupoffice/';
				$this->tmpdir=substr($this->root_path,0,3).'temp';

				$this->cmd_zip=$this->root_path.'controls/win32/zip.exe';
				$this->cmd_unzip=$this->root_path.'controls/win32/unzip.exe';
				$this->cmd_xml2wbxml=$this->root_path.'controls/win32/libwbxml/xml2wbxml.exe';
				$this->cmd_wbxml2xml=$this->root_path.'controls/win32/libwbxml/wbxml2xml.exe';
			}

			if(empty($config['tmpdir']) && function_exists('sys_get_temp_dir')) {
				$this->tmpdir = str_replace('\\','/', sys_get_temp_dir());
			}
		}
		



		// path to classes
		$this->class_path = $this->root_path.'go3compat/'.$this->class_path.'/';

		// path to themes
		$this->theme_path = $this->root_path.'go3compat/'.$this->theme_path.'/';

		// URL to themes
		$this->theme_url = $this->host.$this->theme_url.'/';

		// path to controls
		$this->control_path = $this->root_path.'go3compat/'.$this->control_path.'/';

		// url to controls
		$this->control_url = $this->host.'go3compat/'.$this->control_url.'/';

		// path to modules
		$this->module_path = $this->root_path.$this->module_path.'/';

		// url to user configuration apps
		$this->configuration_url = $this->host.$this->configuration_url.'/';

		
		if($this->debug)
			$this->debug_log=true;

		if($this->debug_log) {			
			list ($usec, $sec) = explode(" ", microtime());
			$this->loadstart = ((float) $usec + (float) $sec);
		}

		// database class library
		require_once($this->class_path.'database/base_db.class.inc.php');
		require_once($this->class_path.'database/'.$this->db_type.'.class.inc.php');

		$this->db = new db($this);

		if(is_string($this->file_create_mode)) {
			$this->file_create_mode=octdec($this->file_create_mode);
		}

		if(is_string($this->folder_create_mode)) {
			$this->folder_create_mode=octdec($this->folder_create_mode);
		}

		if($this->debug_log) {
			$this->log=true;
		}
		
		$this->set_full_url();

		if(isset($this->session_config_file))
			go_debug('Used config file from session', $this);

	}

	/**
	 * This function sets some default session variables. When a user logs in
	 * they are overridden by the user settings.
	 */
	public function set_default_session(){

		if(!isset($_SESSION['GO_SESSION']['timezone']))
		{
			$_SESSION['GO_SESSION']['decimal_separator'] = $this->default_decimal_separator;
			$_SESSION['GO_SESSION']['thousands_separator'] = $this->default_thousands_separator;
			$_SESSION['GO_SESSION']['date_separator'] = $this->default_date_separator;
			$_SESSION['GO_SESSION']['date_format'] = Date::get_dateformat( $this->default_date_format, $_SESSION['GO_SESSION']['date_separator']);
			$_SESSION['GO_SESSION']['time_format'] = $this->default_time_format;
			$_SESSION['GO_SESSION']['currency'] = $this->default_currency;
			$_SESSION['GO_SESSION']['timezone'] = $this->default_timezone;
			$_SESSION['GO_SESSION']['country'] = $this->default_country;
			$_SESSION['GO_SESSION']['sort_name'] = 'last_name';
			$_SESSION['GO_SESSION']['auth_token']=String::random_password('a-z,1-9', '', 30);
			//some url's require this token to be appended
			
			if(!isset($_SESSION['GO_SESSION']['security_token']))
				$_SESSION['GO_SESSION']['security_token']=String::random_password('a-z,1-9', '', 20);

			go_debug('Setup new session '.$_SESSION['GO_SESSION']['security_token']);
		}

		
	}

	function __destruct() {
		if($this->debug_log && !class_exists('GO')) {
			go_debug('Performed '.$GLOBALS['query_count'].' database queries', $this);

			go_debug('Page load took: '.(getmicrotime()-$this->loadstart).'ms', $this);

			go_debug('Peak memory usage:'.round(memory_get_peak_usage()/1048576,2).'MB', $this);
			go_debug("--------------------\n", $this);
		}
	}

	function use_zlib_compression(){

		if(!isset($this->zlib_support_tested)){
			$this->zlib_support_tested=true;
			$this->zlib_compress=$this->zlib_compress && extension_loaded('zlib') && !ini_get('zlib.output_compression');
		}
		return $this->zlib_compress;
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
	 * @access public
	 * @return StringHelper Path to configuration file
	 */

	function get_config_file() {
		if(defined('CONFIG_FILE'))
			return CONFIG_FILE;

		//on start page always search for config
		if(basename($_SERVER['PHP_SELF'])=='index.php'){
			unset($_SESSION['GO_SESSION']['config_file']);
		}

		if(isset($_SESSION['GO_SESSION']['config_file'])) {
			$this->session_config_file=true;
			return $_SESSION['GO_SESSION']['config_file'];
		}else {
			$config_dir = $this->root_path;
			$config_file = $config_dir.'config.php';
			if(@file_exists($config_file)) {
				$_SESSION['GO_SESSION']['config_file']=$config_file;
				return $config_file;
			}

			$count = 0;

			//use SCRIPT_FILENAME in apache mode because it will use a symlinked
			//directory
			$script = php_sapi_name()=='cli' ? __FILE__ : $_SERVER['SCRIPT_FILENAME'];

			$config_dir = dirname($script).'/';

			/*
			 * z-push also has a config.php. Don't detect that.
			 */
			$pos = strpos($config_dir, 'modules/z-push');
			if($pos){
				$config_dir = substr($config_dir, 0, $pos);
			}

			//openlog('[Group-Office]['.date('Ymd G:i').']', LOG_PERROR, LOG_USER);
			
			while(!isset($_SESSION['GO_SESSION']['config_file'])){
				$count++;
				$config_file = $config_dir.'config.php';
				//syslog(LOG_NOTICE,$config_file);
			
				if(@file_exists($config_file)) {
					$_SESSION['GO_SESSION']['config_file']=$config_file;
					return $config_file;
				}
				$config_dir=dirname($config_dir);

				if($count==10 || dirname($config_dir) == $config_dir){
					break;
				}
				$config_dir .= '/';			
			}
			
			/*if(isset($_SERVER['SCRIPT_FILENAME']) && isset($_SERVER['PHP_SELF'])) {
				$config_file = dirname(substr($_SERVER['SCRIPT_FILENAME'], 0 ,-strlen($_SERVER['PHP_SELF']))).'/config.php';
				if(@file_exists($config_file)) {
					$_SESSION['GO_SESSION']['config_file']=$config_file;
					return $config_file;
				}
			}*/
			if(!empty($_SERVER['SERVER_NAME'])){
				$config_file = '/etc/groupoffice/'.$_SERVER['SERVER_NAME'].'/config.php';
				if(@file_exists($config_file)) {
					$_SESSION['GO_SESSION']['config_file']=$config_file;
					return $config_file;
				}
			}
			$config_file = '/etc/groupoffice/config.php';
			if(@file_exists($config_file)) {
				$_SESSION['GO_SESSION']['config_file']=$config_file;
				return $config_file;
			}else
			{
				return false;
			}
		}
	}

	/**
	 * Sets Full URL to reach Group-Office with slash on end
	 *
	 * This function checks wether or not Group-Office runs on a
	 * default http or https port and stores the full url in a variable
	 *
	 * @access public
	 */
	function set_full_url() {
		//full_url may be configured permanent in config.php. If not then 
		//autodetect it and put it in the session. It can be used by wordpress for
		//example.
		if(isset($_SERVER["HTTP_HOST"])) {
			if(!isset($_SESSION['GO_SESSION']['full_url']) && isset($_SERVER["HTTP_HOST"])) {
				$https = (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on" || $_SERVER["HTTPS"] == "1")) || !empty($_SERVER["HTTP_X_SSL_REQUEST"]);
				$_SESSION['GO_SESSION']['full_url'] = 'http';
				if ($https) {
					$_SESSION['GO_SESSION']['full_url'] .= "s";
				}
				/*$url .= "://";
				if ((!$https && $_SERVER["SERVER_PORT"] != "80") || ($https && $_SERVER["SERVER_PORT"] != "443")) {
					$url .= $_SERVER["HTTP_HOST"].":".$_SERVER["SERVER_PORT"].$this->host;
				} else {
					$url .= $_SERVER["HTTP_HOST"].$this->host;
				}*/

				$_SESSION['GO_SESSION']['full_url'] .= '://'.$_SERVER["HTTP_HOST"].$this->host;
			}
			$this->full_url=$_SESSION['GO_SESSION']['full_url'];
		}else
		{
			$_SESSION['GO_SESSION']['full_url']=$this->full_url;
		}
		if(empty($this->orig_full_url))
			$this->orig_full_url=$this->full_url;
	}


	/**
	 * Gets a custom saved setting from the database
	 *
	 * @param  StringHelper $name Configuration key name
	 * @access public
	 * @return StringHelper Configuration key value
	 */
	function get_setting($name, $user_id=0) {
		$this->db->query("SELECT * FROM go_settings WHERE name='".$this->db->escape($name)."' AND user_id=".$this->db->escape($user_id));
		if ( $this->db->next_record() ) {
			return $this->db->f('value');
		}
		return false;
	}

	/**
	 * Gets all custom saved user settings from the database
	 *
	 * @param  user_id The user ID to get the settings for.
	 * @access public
	 * @return array Configurations with key and value
	 */
	function get_settings($user_id) {
		$settings=array();
		$this->db->query("SELECT * FROM go_settings WHERE user_id=".$this->db->escape($user_id));
		while($this->db->next_record()) {
			$settings[$this->db->f('name')]=$this->db->f('value');
		}
		return $settings;
	}

	/**
	 * Saves a custom setting to the database
	 *
	 * @param 	StringHelper $name Configuration key name
	 * @param 	StringHelper $value Configuration key value
	 * @access public
	 * @return bool Returns true on succes
	 */
	function save_setting( $name, $value, $user_id=0) {
		if ( $this->get_setting($name, $user_id) === false ) {
			return $this->db->query("INSERT INTO go_settings (name, value, user_id) VALUES ('".$this->db->escape($name)."', '".$this->db->escape($value)."', ".intval($user_id).")");
		} else {
			return $this->db->query("UPDATE go_settings SET value='".$this->db->escape($value)."' WHERE name='".$this->db->escape($name)."' AND user_id='".$this->db->escape($user_id)."'");
		}
	}

	/**
	 * Deletes a custom setting from the database
	 *
	 * @param 	StringHelper $name Configuration key name
	 * @access public
	 * @return bool Returns true on succes
	 */
	function delete_setting( $name ) {
		return $this->db->query("DELETE FROM go_settings WHERE name='".$this->db->escape($name)."'");
	}

	function save_state($user_id, $name, $value) {
		$state['user_id']=$user_id;
		$state['name']=$name;
		$state['value']=$value;

		return $this->db->replace_row('go_state',$state);
	}

	function get_state($user_id, $index) {
		$state = array();
		$sql = "SELECT * FROM go_state WHERE user_id=".$this->db->escape($user_id);
		$this->db->query($sql);

		while($this->db->next_record(DB_ASSOC)) {
			$state[$this->db->f('name')]=$this->db->f('value');
		}
		return $state;
	}



	function get_client_settings() {
		global $GO_SECURITY, $GO_MODULES, $GO_LANGUAGE;

		require_once($this->class_path.'base/theme.class.inc.php');
		$GLOBALS['GO_THEME'] = new GO_THEME();


		$response['state_index'] = 'go';

		$response['language']=$GLOBALS['GO_LANGUAGE']->language;
		$response['state']=array();
		if($GLOBALS['GO_SECURITY']->logged_in()) {
			//state for Ext components
			$response['state'] = $this->get_state($GLOBALS['GO_SECURITY']->user_id, $response['state_index']);

			$response['has_admin_permission']=$GLOBALS['GO_SECURITY']->has_admin_permission($GLOBALS['GO_SECURITY']->user_id);
		}
		foreach($_SESSION['GO_SESSION'] as $key=>$value) {
			if(!is_array($value)) {
				$response[$key]=$value;
			}
		}

		//$response['modules']=$GLOBALS['GO_MODULES']->modules;
		$response['config']['theme_url']=$GLOBALS['GO_THEME']->theme_url;
		$response['config']['theme']=$GLOBALS['GO_THEME']->theme;
		$response['config']['product_name']=$this->product_name;
		$response['config']['product_version']=$this->version;
		$response['config']['host']=$this->host;
		$response['config']['title']=$this->title;
		$response['config']['webmaster_email']=$this->webmaster_email;

		$response['config']['allow_password_change']=$this->allow_password_change;
		$response['config']['allow_themes']=$this->allow_themes;
		$response['config']['allow_profile_edit']=$this->allow_profile_edit;

		$response['config']['max_users']=$this->max_users;

		$response['config']['debug']=$this->debug;
		$response['config']['disable_flash_upload']=$this->disable_flash_upload;		

		$response['config']['max_attachment_size']=$this->max_attachment_size;
		$response['config']['max_file_size']=$this->max_file_size;
		$response['config']['help_link']=$this->help_link;
		$response['config']['nav_page_size']=intval($this->nav_page_size);


		return $response;
	}
}
