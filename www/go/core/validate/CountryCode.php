<?php
namespace go\core\validate;

class CountryCode {
	
	/**
	 * Validate ISO country code
	 * 
	 * @param string $isoCode eg. "NL"
	 * @return bool
	 */
	public static function validate($isoCode) {
		
		if($isoCode != strtoupper($isoCode)) {
			throw new \Exception("Country codes must be upper case");
		}
		
		$countries = go()->t('countries');
		return isset($countries[$isoCode]);
	}
}
