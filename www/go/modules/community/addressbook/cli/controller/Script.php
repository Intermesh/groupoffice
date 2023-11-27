<?php
namespace go\modules\community\addressbook\cli\controller;


use go\core\Controller;

class Script extends Controller
{

	/**
	 * Reset's country text to current locale from iso codes
	 *
	 * ./cli.php community/addressbook/Script/fixCountries
	 */
  public function fixCountries() {
  	$countries = go()->t("countries");

  	foreach($countries as $iso => $text) {
  		echo $iso . ': '. $text ."\n";
  		$sql = "UPDATE addressbook_address set country = :text where countryCode = :code";
  		$stmt = go()->getDbConnection()->getPDO()->prepare($sql);
  		$stmt->execute(['code' => $iso, 'text' => $text]);
	  }

  	echo "Done\n";
  }
}