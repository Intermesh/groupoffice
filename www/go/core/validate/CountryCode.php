<?php
namespace go\core\validate;

use Exception;

class CountryCode {

	/**
	 * Validate ISO country code
	 *
	 * @param string $isoCode eg. "NL"
	 * @return bool
	 * @throws Exception
	 */
	public static function validate(string $isoCode): bool
	{
		
		if($isoCode != strtoupper($isoCode)) {
			throw new Exception("Country codes must be upper case");
		}
		
		$countries = go()->t('countries');
		return isset($countries[$isoCode]);
	}
}
