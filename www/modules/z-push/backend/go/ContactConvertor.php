<?php

use GO\Base\Util\StringHelper;
use go\core\fs\Blob;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\Date;
use go\modules\community\addressbook\model\EmailAddress;
use go\modules\community\addressbook\model\PhoneNumber;
use go\modules\community\addressbook\model\Url;

/**
 * Contact convertor class
 * 
 * From ActiveSync to Group-Office and vice versa
 */
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
								"combinedStreet" => "otherstreet",
								"city" => 'othercity',
								"state" => "otherstate",
								"country" => "othercountry"
						]
				],
				Address::TYPE_HOME => [
						[
								"zipCode" => "homepostalcode",
								"combinedStreet" => "homestreet",
								"city" => 'homecity',
								"state" => "homestate",
								"country" => "homecountry"
						]
				],
				Address::TYPE_WORK => [
						[
								"zipCode" => "businesspostalcode",
								"combinedStreet" => "businessstreet",
								"city" => 'businesscity',
								"state" => "businessstate",
								"country" => "businesscountry"
						]
				]
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
		
		//Loop through all items (eg. phoneMumbers)
		foreach($items as $i) {
			$this->applyItem($i, $message, $mapping);
		}				
	}
	
	/**
	 * Applies phone number to the SyncContact object
	 * 
	 * @param PhoneNumber $i
	 * @param SyncContact $message
	 * @param array $mapping
	 * @return boolean
	 */
	private function applyItem($i, $message, $mapping) {
//		ZLog::Write(LOGLEVEL_DEBUG, "Applying ". var_export($i->toArray(), true));
		
		if(!isset($mapping[$i->type])) {
			//If the type is not mapped then don't sync it.
			return false;
		}			
		$m = $mapping[$i->type];		 
		
		//The mapping object maps multiple items to different properties. For example the first "home" phonenumber maps to homephone and the second to home2phone.
		foreach($m as $syncProps) {
			//Check if the first mapped property is not set yet. If not the set the properties.
			$firstPropName = array_values($syncProps)[0];
			if(empty($message->$firstPropName)) {			
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
	
	/**
	 * Maps AS properties to has many item with a "type" property. For example phone numbers.
	 *
	 * @param PhoneNumber[] $items
	 * @param SyncContact $message
	 * @param array $mapping
	 * @param string $cls
	 * @return PhoneNumber[]
	 */
	private function flatToHasMany($items, $message, $mapping, $cls) {
		ZLog::Write(LOGLEVEL_DEBUG, "flatToHasMany");
		//create array of values by type:
		//[
		//  'work' => [['number' => 123], ['number' => 123]],
		//  'home' => [['number' => 123], ['number' => 123]]
		//]
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
			$items = $this->patchType($items, $type, $valuesOfType, $mapping[$type], $cls);
		}	
		
		return $items;
	}
	
	/**
	 * Uses the mapping to convert AS properties to has many array values.
	 * 
	 * eg. "homephone" -> ['type' => 'home', 'number' => 1234']
	 * @param type $m
	 * @param type $message
	 * @return boolean
	 */
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
	
	/**
	 * Patches the phonenumbers by type. Leaving numbers that couldn't be synced untouched,
	 * 
	 * @param PhoneNumber[] $items
	 * @param string $type eg. "home"
	 * @param array $values
	 * @param array $mapping
	 * @param string $cls
	 * @return type
	 */
	private function patchType($items, $type, $values, $mapping, $cls) {
		
		//The max number of phone numbers AS supports for this type.
		$maxValues = count($mapping);
		$count = 0;
		
		//Loop through existing items.
		for($i = 0, $c = count($items); $i < $c; $i++) {
			if($items[$i]->type != $type) {
				//other type. Ignore
				continue;
			}
			//Get the first value for this type and apply it.
			$value = array_pop($values);
			if($value) {				
				$items[$i]->setValues($value);
			} elseif($count < $maxValues)
			{
				//Remove the value from the array if we're not passed the max supported numbers.
				array_splice($items, $i, 1);
				$i--;
				$c--;
			}
			
			$count++;
		}		
		
		//Add new phone numbers that are left over from the numbers that were sent to the device.
		foreach($values as $value) {
			$items[] = (new $cls)->setValues($value)->setValues(['type' => $type]);
		}
		
		return $items;
	}
	
	/**
	 * Convert AS SyncContact message to Group-Office Contact
	 * 
	 * @param SyncContact $message
	 * @param Contact $contact
	 * @param type $contentParameters
	 * @return Contact
	 */
	public function AS2GO(SyncContact $message, Contact $contact, $contentParameters) {
		foreach($this->simpleMapping as $goProp => $asProp) {
			$contact->$goProp = $message->$asProp;
		}
		
		$contact->phoneNumbers = $this->flatToHasMany($contact->phoneNumbers ?? [], $message, $this->phoneMapping, PhoneNumber::class);		
		$contact->dates = $this->flatToHasMany($contact->dates ?? [], $message, $this->dateMapping, Date::class);
		$contact->urls = $this->flatToHasMany($contact->urls ?? [], $message, $this->urlMapping, Url::class);
		$contact->addresses = $this->flatToHasMany($contact->addresses ?? [], $message, $this->addressMapping, Address::class);
		
		$this->setEmailAddresses($message, $contact);
		
		$contact->notes = GoSyncUtils::getBodyFromMessage($message);
		
		ZLog::Write(LOGLEVEL_DEBUG,var_export($message->picture, true) );
		if (!empty($message->picture)) {
			$pictureString = base64_decode($message->picture);					
			$blob = Blob::fromString($pictureString);
			$blob->type = 'image/jpeg';
			$blob->name = $contact->name . '.jpg';
			$blob->save();
			$contact->photoBlobId = $blob->id;
			
			ZLog::Write(LOGLEVEL_DEBUG, "New picture set: ".$contact->photoBlobId );
		} else {
			$contact->photoBlobId = null;
		}
		
		return $contact;
	}
	
	private function setEmailAddresses(SyncContact $message, Contact $contact) {
		$max = 3;
		
		//AS support 3 email addresses. So if there are more than store these to add later so they will remain untouched.
		$count = count($contact->emailAddresses);
		if($count > $max) {
			
			$keep = array_slice($contact->emailAddresses, $max);
			$contact->emailAddresses = array_slice($contact->emailAddresses, 0, $max);
		} else {
			$keep = [];
		}		
		
		$clientEmails = [];
		if(!empty($message->email1address)) {
			$clientEmails[] = $message->email1address;
		}
		if(!empty($message->email2address)) {
			$clientEmails[] = $message->email2address;
		}
		if(!empty($message->email3address)) {
			$clientEmails[] = $message->email3address;
		}
		
		for($i = 0, $c = count($clientEmails); $i < $c; $i++) {
			if(isset($contact->emailAddresses[$i])) {
				$contact->emailAddresses[$i]->email = $clientEmails[$i];
			} else
			{
				$contact->emailAddresses[$i] = (new EmailAddress())->setValues(['type' => 'work', 'email' => $message->email1address]);
			}		
		}
		
		//remove others in range of AS.
		$contact->emailAddresses = array_slice($contact->emailAddresses, 0, $c);
	
		//add addresses out of range
		$contact->emailAddresses = array_merge($contact->emailAddresses, $keep);		
	}
}
