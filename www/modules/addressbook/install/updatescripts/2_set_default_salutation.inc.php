<?php
	$module = $GLOBALS['GO_MODULES']->get_module('addressbook');
	global $GO_LANGUAGE, $lang;
	require($GLOBALS['GO_LANGUAGE']->get_language_file('addressbook'));

	//require_once($module['class_path'].'addressbook.class.inc.php');
	//$ab = new addressbook();

	$default_salutation = $lang['common']['dear'].' ['.$lang['common']['sirMadam']['M'].'/'.$lang['common']['sirMadam']['F'].'] {middle_name} {last_name}';
	$default_language = $GLOBALS['GO_CONFIG']->default_country;
	if(!$GLOBALS['GO_LANGUAGE']->get_address_format_by_iso($default_language))
		$default_language = 'US';

	$sql = "UPDATE ab_addressbooks SET default_iso_address_format = \"$default_language\", default_salutation = \"$default_salutation\"";
	$db->query($sql);

	$sql = "UPDATE ab_contacts SET iso_address_format = \"$default_language\"";
	$db->query($sql);

	$sql = "UPDATE ab_companies SET iso_address_format = \"$default_language\", post_iso_address_format = \"$default_language\"";
	$db->query($sql);
?>
