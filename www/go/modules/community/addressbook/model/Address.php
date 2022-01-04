<?php
namespace go\modules\community\addressbook\model;
						
use go\core\model\User;
use go\core\orm\Mapping;
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
	const TYPE_DELIVERY = "delivery";

	/**
	 * 
	 * @var int
	 */							
	protected $contactId;

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

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable("addressbook_address");
	}
	
	protected function internalValidate() {
		$this->validateCountry();		
		return parent::internalValidate();
	}
	
	private function validateCountry() {

		if(isset($this->countryCode) && empty($this->countryCode)) {
			$this->countryCode = null;
		}
		
		if($this->isModified('countryCode')) {			
			if(isset($this->countryCode)) {
				$countries = go()->t('countries');
				if(!isset($countries[$this->countryCode])) {
					$this->setValidationError('countryCode', \go\core\validate\ErrorCode::INVALID_INPUT, "Unknown ISO 3601 2 char country code provided: " . $this->countryCode);
					return false;
				}
				$this->country = $countries[$this->countryCode];
			}
		} elseif($this->isModified('country')) {
			$countryCodes = array_flip(go()->t('countries'));
			if(isset($countryCodes[$this->country])) {
				$this->countryCode = $countryCodes[$this->country] ?? null;
			}
		}
	}

	
	public function getFormatted()
	{
		return go()->getLanguage()->formatAddress($this->countryCode, $this->toArray(['street','street2', 'city', 'zipCode', 'state']));
	}
	
	public function getCombinedStreet() {
		return trim($this->street . ' ' . $this->street2);
	}
	
	public function setCombinedStreet($v) {
		$lastSpace = strrpos($v, ' ');
		if($lastSpace === false) {
			$lastSpace = strrpos($v, "\n");
		}
		if($lastSpace === false) {
			$this->street = $v;
			$this->street2 = null;
		} else
		{
			$this->street = substr($v, 0, $lastSpace);
			$this->street2 = substr($v, $lastSpace + 1);
		}
	}

}