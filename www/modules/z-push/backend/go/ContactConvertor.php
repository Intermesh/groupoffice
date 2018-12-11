<?php

use GO\Base\Util\StringHelper;
use go\core\fs\Blob;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\Date;
use go\modules\community\addressbook\model\PhoneNumber;
use go\modules\community\addressbook\model\Url;

class ContactConvertor {
	
	private $phoneMapping;
	private $dateMapping;
	private $addressMapping;
	private $urlMapping;
	
	private $simpleMapping = [
		'name' => 'fileas',
		'jobTitle' => 'jobtitle',
		'firstName'	=> 'firstname',
		'lastName'	=> 'lastname',
		'middleName'	=> 'middlename',
		'suffixes'	=> 'suffix',
		'prefixes'	=> 'title'		
	];

	public function __construct() {

		$this->phoneMapping = [
				PhoneNumber::TYPE_WORK => [
						["number" => "businessphonenumber"],
						["number" => "business2phonenumber"],
						["number" => "companymainphone"]
				],
				PhoneNumber::TYPE_FAX => [
						["number" => "businessfaxnumber"]
				],
				PhoneNumber::TYPE_MOBILE => [
						["number" => "mobilephonenumber"],
						["number" => "carphonenumber"]
				],
				PhoneNumber::TYPE_HOME => [
						["number" => "homephonenumber"],
						["number" => "home2phonenumber"]
				],
		];

		$this->dateMapping = [
				Date::TYPE_BIRTHDAY => [["date" => "birthday"]],
				Date::TYPE_ANNIVERSARY => [["date" => "anniversary"]]
		];
		
		$this->urlMapping = [
			Url::TYPE_HOMEPAGE => [["url" => "webpage"]]				
		];

		$this->addressMapping = [
				Address::TYPE_POSTAL => [
						[
								"zipCode" => "otherpostalcode",
								"street" => function($i, $message) {
									$message->otherstreet .= trim($i->street . ' ' . $i->street2);
								},
								"city" => 'othercity',
								"state" => "otherstate",
								"country" => "othercountry"
						]
				],
				Address::TYPE_HOME => [
						[
								"zipCode" => "homepostalcode",
								"street" => function($i, $message) {
									$message->homestreet .= trim($i->street . ' ' . $i->street2);
								},
								"city" => 'homecity',
								"state" => "homestate",
								"country" => "homecountry"
						]
				],
				Address::TYPE_WORK => [
						[
								"zipCode" => "businesspostalcode",
								"street" => function($i, $message) {
									$message->businessstreet .= trim($i->street . ' ' . $i->street2);
								},
								"city" => 'businesscity',
								"state" => "businessstate",
								"country" => "businesscountry"
						]
				],
				Address::TYPE_VISIT => [
						[
								"zipCode" => "homepostalcode",
								"street" => function($i, $message) {
									$message->homestreet .= trim($i->street . ' ' . $i->street2);
								},
								"city" => 'homecity',
								"state" => "homestate",
								"country" => "homecountry"
						],
						[
								"zipCode" => "otherpostalcode",
								"street" => function($i, $message) {
									$message->otherstreet .= trim($i->street . ' ' . $i->street2);
								},
								"city" => 'othercity',
								"state" => "otherstate",
								"country" => "othercountry"
						]],
		];
	}

	public function GO2AS(Contact $contact, $contentParameters) {
		$message = new SyncContact();
				
		$bpReturnType = GoSyncUtils::getBodyPreferenceMatch($contentParameters->GetBodyPreference());

		if (Request::GetProtocolVersion() >= 12.0) {
			$message->asbody = GoSyncUtils::createASBodyForMessage($contact,'notes',$bpReturnType);
		} else {
			$message->body = StringHelper::normalizeCrlf($contact->comment);
			$message->bodysize = strlen($message->body);
			$message->bodytruncated = 0;
		}
		
		foreach($this->simpleMapping as $goProp => $asProp) {
			$message->$asProp = $contact->$goProp;
		}
		
		$this->hasManyToFlat($contact->phoneNumbers, $message, $this->phoneMapping);		
		$this->hasManyToFlat($contact->dates, $message, $this->dateMapping);		
		
		foreach($contact->emailAddresses as $e) {
			if(!isset($message->email1address)) {
				$message->email1address = $e->email;
			} elseif(!isset($message->email2address)) {
				$message->email2address = $e->email;
			} elseif(!isset($message->email3address)) {
				$message->email3address = $e->email;
			}
		}

		
		if(!$contact->isOrganization) {
			$companies = Contact::find()
							->withLink($contact)
							->andWhere('isOrganization', '=', true)
							->selectSingleValue('name')
							->all();
			
			$message->companyname = implode(',', $companies);
		} else
		{
			$message->companyname = $contact->name;
		}
		
		$this->hasManyToFlat($contact->addresses, $message, $this->addressMapping);		
		$this->hasManyToFlat($contact->urls, $message, $this->urlMapping);		

		$blob = isset($contact->photoBlobId) ? Blob::findById($contact->photoBlobId) : false;
		if($blob && $blob->getFile()->exists()) {		
			$pic = base64_encode($blob->getFile()->getContents());			
			$message->picture = $pic;			
		}
		
		return $message;
	}
	
	/**
	 * Convert GO has many properties with a type to the syncmessage names.
	 * 
	 * @param type $items
	 * @param SyncContact $message
	 * @param array $mapping
	 * @param string $propName
	 * @return type
	 */
	private function hasManyToFlat($items, SyncContact $message, array $mapping) {				
		foreach($items as $i) {
			$this->applyItem($i, $message, $mapping);
		}				
	}
	
	private function applyItem($i, $message, $mapping) {
//		ZLog::Write(LOGLEVEL_DEBUG, "Applying ". var_export($i->toArray(), true));
		
		if(!isset($mapping[$i->type])) {
			return false;
		}			
		$m = $mapping[$i->type];		 
		
		foreach($m as $syncProps) {
			$firstPropName = array_values($syncProps)[0];
			if(empty($message->$firstPropName)) {
//				ZLog::Write(LOGLEVEL_DEBUG, "Applying to ". $firstPropName);
				foreach($syncProps as $goProp => $asProp) {
					if(is_callable($asProp)) {
						call_user_func($asProp, $i, $message);
					} else
					{
						$message->$asProp = $i->$goProp;
					}
				}
				return true;
			} else
			{
				ZLog::Write(LOGLEVEL_DEBUG, "Already set: ". $firstPropName);
			}
		}
		
		return false;
	}
	
	
	private function flatToHasMany($items, $message, $mapping) {
		
		//create array by type
		//['work' => [['number' => 123]]]
		$values = [];
		foreach($mapping as $type => $typeMapping) {
			$values[$type] = [];
			foreach($typeMapping as $m){
				$v = $this->buildHasManyValues($m, $message);
				if($v) {
					$values[$type][] = $v;
				}
			}
		}		
		
		foreach($values as $type => $valuesOfType) {
			$items = $this->patchType($items, $type, $valuesOfType, $mapping[$type]);
		}
		
		return $items;
	}
	
	private function buildHasManyValues($m, $message) {		
		$v = [];
		$firstPropName = array_values($m)[0];
		if(empty($message->$firstPropName)) {
			return false;
		}

		foreach($m as $goProp => $asProp) {
			$v[$goProp] = $message->$asProp;					
		}
		return $v;		
	}
	
	private function patchType($items, $type, $values, $mapping) {
		
		$maxValues = count($mapping);
		$typeIndex = 0;
		for($i = 0, $c = count($items); $i < $c; $i++) {
			if($items[$i]->type != $type) {
				//other type. Ignore
				continue;
			}
			
			if(isset($values[$typeIndex])) {
				$items[$i]->setValues($values[$typeIndex]);
				$typeIndex++;
			} elseif($typeIndex <= $maxValues)
			{
				array_splice($items, $i, 1);
				$i--;
				$c--;
			}
		}		
		
		return $items;
	}
	
	public function AS2GO(SyncContact $message, Contact $contact, $contentParameters) {
		foreach($this->simpleMapping as $goProp => $asProp) {
			$contact->$goProp = $message->$asProp;
		}
		
		$contact->phoneNumbers = $this->flatToHasMany($contact->phoneNumbers ?? [], $message, $this->phoneMapping);		
		
		return $contact;
	}

}
