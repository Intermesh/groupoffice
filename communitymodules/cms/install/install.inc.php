<?php
$module = $this->get_module('cms');
global $GO_SECURITY, $GO_LANGUAGE, $lang, $GO_CONFIG;
require($GLOBALS['GO_LANGUAGE']->get_language_file('cms'));


$addressbook_name = 'Website contacts';
if(isset($this->modules['addressbook'])) {
	require_once($this->modules['addressbook']['class_path'].'addressbook.class.inc.php');
	$ab = new addressbook();

	$ab->get_user_addressbooks(1);
	$addressbook = $ab->next_record();

	$addressbook_name=$addressbook['name'];
}

require_once($module['class_path'].'cms.class.inc.php');
$cms = new cms();


$site['domain']='example.com';
$site['webmaster']='webmaster@example.com';
$site['language']=$GO_LANGUAGE->language;
$site['template']='Example';
$site['name']='Example website';
$site['user_id']=1;
$site['acl_write']=$GO_SECURITY->get_new_acl('site');

$site_id = $cms->add_site($site);

$file['name']='Home';
$file['content']='<h1>Demo site<br /></h1>
<p>Just showing off some features here! Press the "View" button to preview this site. In a real production site the CMS will generate friendly URL\'s like http://www.example.com/Home.</p>
<p>&nbsp;</p>';
$file['type']='default';
$file['auto_meta']='1';
$file['folder_id']=$site['root_folder_id'];

$cms->add_file($file, $site);

unset($file);
$file['name']='Contact';
$file['content']='';
$file['type']='contact';
$file['auto_meta']='1';
$file['folder_id']=$site['root_folder_id'];
$file['option_values']='<?xml version="1.0"?>
<template_options><option name="addressbook" value="'.$cms->escape($addressbook_name).'"/></template_options>';

$cms->add_file($file, $site);

$folder['site_id']=$site['id'];
$folder['type']='default';
$folder['name']='Member area';
$folder['disabled']='0';
$folder['parent_id']=$site['root_folder_id'];
$member_folder_id = $cms->add_folder($folder);

unset($file);
$file['option_values']='';
$file['name']='Photoalbum';
$file['content']='';
$file['type']='photoalbum';
$file['folder_id']=$member_folder_id;
$file['auto_meta']='1';
$cms->add_file($file, $site);
$foto_album_files_folder_id = isset($file['files_folder_id']) ? $file['files_folder_id'] : 0;

unset($file);
$file['name']='Guestbook';
$file['content']='';
$file['type']='guestbook';
$file['folder_id']=$member_folder_id;
$file['auto_meta']='1';

$cms->add_file($file, $site);


unset($file);
$file['name']='Portfolio';
$file['content']='<h1>Portfolio</h1>
<p>This page demonstrates how it can sum up other pages and use some custom fields with that.</p>';
$file['type']='portfolio';
$file['folder_id']=$site['root_folder_id'];
$file['auto_meta']='1';

$cms->add_file($file, $site);


unset($folder);
$folder['type']='default';
$folder['name']='data';
$folder['disabled']='1';
$folder['site_id']=$site['id'];
$folder['parent_id']=$site['root_folder_id'];
$data_folder_id = $cms->add_folder($folder);

unset($file);
$file['name']='formSuccess';
$file['content']='<h1>Thank you!</h1>
<p>Your information was recieved.</p>
<p>&nbsp;</p>';
$file['type']='default';
$file['folder_id']=$data_folder_id;
$cms->add_file($file, $site);

unset($folder);
$folder['type']='default';
$folder['name']='guestbook';
$folder['disabled']='1';
$folder['site_id']=$site['id'];
$folder['parent_id']=$data_folder_id;
$cms->add_folder($folder);


unset($folder);
$folder['type']='portfolio';
$folder['name']='portfolio';
$folder['disabled']='1';
$folder['site_id']=$site['id'];
$folder['parent_id']=$data_folder_id;
$portfolio_folder_id = $cms->add_folder($folder);


unset($file);
$file['name']='Calendar';
$file['content']='<p>In a corporate environment a calendar can\'t be missed. This calendar allows you to plan all sorts of recurring events and set reminders for them. The easy to use interface will never let you miss an event. It\'s easy to set up multiple calendars and share them with other users. The calendar supports the import and export of the popular iCalendar standard. This makes it possible to synchronise the Group-Office calendar with other calendar software that support the iCalendar protocol.</p>';
$file['type']='portfolio';
$file['folder_id']=$portfolio_folder_id;
$file['option_values']='<?xml version="1.0"?>
<template_options><option name="image" value="public/cms/Example website/data/portfolio/Calendar/calendar.jpg"/></template_options>';
$cms->add_file($file, $site);

unset($file);
$file['name']='E-mail';
$file['content']='<p>The flexible e-mail module integrates in all other modules. You can access your e-mail everywhere in the world. With the templates you can create professional signatures and send newsletters to keep your customers up-to-date with your latest news!</p>';
$file['type']='portfolio';
$file['folder_id']=$portfolio_folder_id;
$file['option_values']='<?xml version="1.0"?>
<template_options><option name="image" value="public/cms/Example website/data/portfolio/E-mail/email.jpg"/></template_options>';
$cms->add_file($file, $site);

unset($file);
$file['name']='CRM';
$file['content']='<p>Keep in touch with your prospects and customers in an easy way. The addressbook keeps track of all the customers related notes, e-mail, files etc. With the ticket system you will be reminded of important events so you will never forget a customer.</p>';
$file['type']='portfolio';
$file['folder_id']=$portfolio_folder_id;
$file['option_values']='<?xml version="1.0"?>
<template_options><option name="image" value="public/cms/Example website/data/portfolio/CRM/crm.jpg"/></template_options>';
$cms->add_file($file, $site);


if(!empty($foto_album_files_folder_id))
{
	require_once($this->modules['files']['class_path'].'files.class.inc.php');
	$files = new files();

	$path = $GO_CONFIG->file_storage_path.$files->build_path($foto_album_files_folder_id);

	$fs = new filesystem();
	$fs->copy($module['path'].'install/photoalbum/Sunny highlands.jpg', $path.'/Sunny highlands.jpg');
	$fs->copy($module['path'].'install/photoalbum/Wish you were here.jpg', $path.'/Wish you were here.jpg');

	//$files->sync_folder($foto_album_files_folder_id);

	$fs = new filesystem();
	$fs->copy($module['path'].'install/portfolio/calendar.jpg', $GO_CONFIG->file_storage_path.'public/cms/Example website/data/portfolio/Calendar/calendar.jpg');
	$fs->copy($module['path'].'install/portfolio/crm.jpg', $GO_CONFIG->file_storage_path.'public/cms/Example website/data/portfolio/CRM/crm.jpg');
	$fs->copy($module['path'].'install/portfolio/email.jpg', $GO_CONFIG->file_storage_path.'public/cms/Example website/data/portfolio/E-mail/email.jpg');
	

	
}