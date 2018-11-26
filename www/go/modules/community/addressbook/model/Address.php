<?php
namespace go\modules\community\addressbook\model;
						
use go\core\orm\Property;
						
/**
 * Address model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Address extends Property {
	
	
	const TYPE_POSTAL = "postal";
	const TYPE_VISIT = "visit";
	const TYPE_WORK = "work";
	const TYPE_HOME = "home";
	
	/**
	 * 
	 * @var int
	 */							
	public $id;

	/**
	 * 
	 * @var int
	 */							
	public $contactId;

	/**
	 * 
	 * @var string
	 */							
	public $type;

	/**
	 * 
	 * @var string
	 */							
	public $street = '';
	
	/**
	 * 
	 * @var string
	 */							
	public $street2 = '';

	/**
	 * 
	 * @var string
	 */							
	public $zipCode = '';

	/**
	 * 
	 * @var string
	 */							
	public $city = '';

	/**
	 * 
	 * @var string
	 */							
	public $state = '';

	/**
	 * 
	 * @var string
	 */							
	public $country;
	
	/**
	 * ISO 3601 2 char country code. eg. "NL".
	 * @var string
	 */
	public $countryCode;
	
	public $latitude;
	public $longitude;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("addressbook_address");
	}
	
	protected function internalValidate() {
		if($this->isModified('countryCode') && isset($this->countryCode)) {
			$this->countryCode = strtoupper($this->countryCode);
			$countries = GO()->t('countries');
			if(!isset($countries[$this->countryCode])) {
				$this->setValidationError('countryCode', \go\core\validate\ErrorCode::INVALID_INPUT, "Unknown ISO 3601 2 char country code provided");
			}
		}
		return parent::internalValidate();
	}
	
	protected function internalSave() {		
		if($this->isModified('countryCode')) {			
			if(isset($this->countryCode)) {
				$countries = GO()->t('countries');
				$this->country = $countries[$this->countryCode];
			}
		} elseif($this->isModified('country')) {
			$countryCodes = array_flip(GO()->t('countries'));
			if(isset($countryCodes[$this->country])) {
				$this->countryCode = $countryCodes[$this->country] || null;
			}
		}
		
	
		
		return parent::internalSave();
	}
	
	public function getFormatted() {
			
		if(empty($this->street) && empty($this->city) && empty($this->state)){
			return "";
		}
		require(\go\core\Environment::get()->getInstallFolder() . '/language/addressformats.php');

		$format = isset($af[$this->countryCode]) ? $af[$this->countryCode] : $af['default'];

		$format= str_replace('{address}', $this->street, $format);
		$format= str_replace('{address_no}', $this->street2, $format);
		$format= str_replace('{city}', $this->city, $format);
		$format= str_replace('{zip}', $this->zipCode, $format);
		$format= str_replace('{state}', $this->state, $format);
		$format= str_replace('{country}', $this->country, $format);
		
		return preg_replace("/(\r\n)+|(\n|\r)+/", "\n", $format);
	}

}