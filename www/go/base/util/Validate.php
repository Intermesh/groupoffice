<?php

namespace GO\Base\Util;


class Validate {
	
/**
	* Checks the given Ip is valid (ipv4 and ipv6).
	* * Needs PHP 5.2 or higher
	* 
	* @param StringHelper $ip
	* @return boolean $isValid
	*/
	public static function ip($ip){
		$isValid = false;
		 
		if(filter_var($ip, FILTER_VALIDATE_IP)) {
			$isValid = true;
		}

		return $isValid;
	}
	
	/**
	 * Check if an email adress is in a valid format
	 *
	 * @param	StringHelper $email E-mail address
	 * @return bool
	 */
	public static function email($email) {
		return preg_match(StringHelper::get_email_validation_regex(), $email);
	}
	
/**
	* Checks the given Ip if it is an internal one or not (ipv4 and ipv6).
	* * Needs PHP 5.2 or higher
	* 
	* @param StringHelper $ip
	* @return boolean $isInternal
	*/
	public static function internalIp($ip){
		$isInternal = false;

		if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
				$isInternal = true;
		}

		return $isInternal && Validate::ip($ip);
	}
	
/**
	* Checks the given Ip is an ipV6 address.
	* * Needs PHP 5.2 or higher
	* 
	* @param StringHelper $ip
	* @return boolean $isIpV6
	*/
	public static function ipV6($ip){
		$isIpV6 = false;
		 
		if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			$isIpV6 = true;
		}

		return $isIpV6;
	}
	
/**
	* Checks the given Ip is an ipV4 address.
	* * Needs PHP 5.2 or higher
	* 
	* @param StringHelper $ip
	* @return boolean $isIpV4
	*/
	public static function ipV4($ip){
		$isIpV4 = false;
		 
		if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$isIpV4 = true;
		}

		return $isIpV4;
	}	
	
	/**
	 * TODO: CREATE THE HOSTNAME FUNCTION
	 * @return boolean 
	 */
	public static function hostname(){
		return true;
	}
	
	/**
	 * Return array of uppercase European country codes. eg. NL, GB, DE
	 * 
	 * @return array
	 */
	public static function getEUCountries(){
		return array(
			'AT',
			'BE',
			'BG',
			'CY',
			'CZ',
			'DK',
			'EE',
			'FI',
			'FR',
			//'FX', ??
			'DE',
			'GR',
			'HU',
			'IE',
			'IT',
			'LV',
			'LT',
			'LU',
			'MT',
			'NL',
			'PL',
			'PT',
			'RO',
			'SK',
			'SI',
			'ES',
			'SE',
			'GB'
		);
	}
	
	/**
	 * Check for the given country if it is an EU country or not.
	 * 
	 * @param StringHelper $country eg. "NL" or "BE"
	 * @return boolean  
	 */
	public static function isEUCountry($country){
		return in_array(strtoupper($country), self::getEUCountries());
	}
	
	/**
	 * Check if a customer needs to pay VAT.
	 * 
	 * @param StringHelper $customerCountry eg. NL Country 
	 * @param boolean $hasVatNo Customer has a valid vat number
	 * @param StringHelper $merchantCountry eg. NL This is the country the merchant lives in. If the customer comes from the same country he should always pay VAT.
	 */
	public static function vatApplicable($customerCountry, $hasVatNo, $merchantCountry){
		return strtolower($customerCountry)==strtolower($merchantCountry) || 
						(Validate::isEUCountry($customerCountry) && !$hasVatNo);
	}
	
	/**
	 * Check if a vat number is correct.
	 * 
	 * @param StringHelper $countryCode The country code: eg. "NL" or "BE"
	 * @param StringHelper $vat The vat number
	 * @return boolean true
	 */
	public static function checkVat($countryCode, $vat) {
		
		//remove unwanted characters
		$vat = preg_replace('/[^a-z0-9]/i','',$vat);
		
		//strip country if included
		if(substr($vat,0,2)==$countryCode)
			$vat = trim(substr($vat,2));
		
		//$wsdl = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';
		$wsdl = \GO::config()->root_path.'go/vendor/wsdl/checkVatService.wsdl';

		$vies = new \SoapClient($wsdl);
		
		//lower the timeout because it can hang too long
		ini_set("default_socket_timeout", 5);

		/**
			var_dump($vies->__getFunctions());
			var_dump($vies->__getTypes());
		*/
		
		$message = new \stdClass();
		$message->countryCode = $countryCode;
		$message->vatNumber = $vat;

		try {
			$ret = $vies->checkVat($message);
		} catch (\SoapFault $e) {
			$ret = $e->faultstring;
			$regex = '/\{ \'([A-Z_]*)\' \}/';
			$n = preg_match($regex, $ret, $matches);
			if(isset($matches[1])){
				$ret = $matches[1];
				$faults = array
						(
						'INVALID_INPUT' => 'The provided CountryCode is invalid or the VAT number is empty',
						'SERVICE_UNAVAILABLE' => 'The SOAP service is unavailable, try again later',
						'MS_UNAVAILABLE' => 'The VAT Member State service is unavailable, try again later or with another Member State',
						'TIMEOUT' => 'The Member State service could not be reached in time, try again later or with another Member State',
						'SERVER_BUSY' => 'The service cannot process your request. Try again later.'
				);
				$msg=$faults[$ret];
			}else
			{
				$msg=$ret;
			}
			
			if($ret!="INVALID_INPUT")
				throw new \GO\Base\Exception\ViesDown();
			
			throw new \Exception("Could not check VAT number: ".$msg);
		}

		return $ret->valid;
	}
	
	const PASSWORD_ERROR_LEN=-1;
	const PASSWORD_ERROR_UC=-2;
	const PASSWORD_ERROR_LC=-3;
	const PASSWORD_ERROR_NUM=-4;
	const PASSWORD_ERROR_SC=-5;
	const PASSWORD_ERROR_UNIQ=-6;
	
	/**
	 * Validate a password according to the following rules:
	 * 
	 * 1. Minimum of 8 characters
	 * 2. Require at least one uppercase char
	 * 3. Require at least one lowercase char
	 * 4. Require at least one special char
	 * 5. Require at least 5 unique chars
	 * 
	 * @param StringHelper $password
	 * @return boolean
	 */
	public static function strongPassword($password){
		$minLength=\GO::config()->password_min_length;
		$requireUpperCase=\GO::config()->password_require_uc;
		$requireLowerCase=\GO::config()->password_require_lc;
		$requireNumber=\GO::config()->password_require_num;
		$requireSpecialChars=\GO::config()->password_require_sc;
		$minUniqueChars=\GO::config()->password_require_uniq;
		
		if($minLength && strlen($password)<$minLength){
			return false;
			return self::PASSWORD_ERROR_LEN;
		}
		
		if($requireUpperCase && !preg_match('/[A-Z]/', $password)){
			return false;
			return self::PASSWORD_ERROR_UC;
		}
		
		if($requireLowerCase && !preg_match('/[a-z]/', $password)){
			return false;
			return self::PASSWORD_ERROR_LC;
		}
		
		if($requireNumber && !preg_match('/[0-9]/', $password)){
			return false;
			return self::PASSWORD_ERROR_NUM;
		}
		
		if($requireSpecialChars && !preg_match('/[^\da-zA-Z]/', $password)){
			return false;
			return self::PASSWORD_ERROR_SC;
		}
		
		if($minUniqueChars){
			$arr = str_split($password);
			$arr = array_unique($arr);
			
			if(count($arr)<$minUniqueChars){
				return false;
				return self::PASSWORD_ERROR_UNIQ;
			}
		}
		
		return true;
		
	}
	
	public static function getPasswordErrorString($password){
		
		$minLength=\GO::config()->password_min_length;
		$requireUpperCase=\GO::config()->password_require_uc;
		$requireLowerCase=\GO::config()->password_require_lc;
		$requireNumber=\GO::config()->password_require_num;
		$requireSpecialChars=\GO::config()->password_require_sc;
		$minUniqueChars=\GO::config()->password_require_uniq;
		
		
		$str = \GO::t("The entered password is not strong enough. It should comply to the following rules:")."\n\n";
		
		if($minLength && strlen($password)<$minLength){
			$str .=  sprintf(\GO::t("The minimum length is %s"),$minLength)."\n";
		}
		
		if($requireUpperCase && !preg_match('/[A-Z]/', $password)){
			$str .=  \GO::t("It must contain at least one uppercase character")."\n";
		}
		
		if($requireLowerCase && !preg_match('/[a-z]/', $password)){
			$str .=  \GO::t("It must contain at least one lowercase character")."\n";
		}
		
		if($requireNumber && !preg_match('/[0-9]/', $password)){
			$str .=  \GO::t("It must contain at least one a number")."\n";
		}
		
		if($requireSpecialChars && !preg_match('/[^\da-zA-Z]/', $password)){
			$str .=  \GO::t("It must contain at least one special character")."\n";
		}
		
		if($minUniqueChars){
			$arr = str_split($password);
			$arr = array_unique($arr);
			
			if(count($arr)<$minUniqueChars){
				$str .=  sprintf(\GO::t("It must contain at least %s unique characters"),$minLength)."\n";
			}
		}
		return $str;
	}
	
}
