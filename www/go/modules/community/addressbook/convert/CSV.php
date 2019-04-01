<?php

namespace go\modules\community\addressbook\convert;

use go\core\data\convert;
use go\core\orm\Entity;

class CSV extends convert\CSV {	

	
	/**
	 * List headers to exclude
	 * @var string[]
	 */
	public static $excludeHeaders = ['addressBookId', 'goUserId', 'vcardBlobId', 'uri'];
	
	protected function internalGetHeaders($entityCls) {
		$headers = parent::internalGetHeaders($entityCls);
		$headers[] = 'organizations';
		return $headers;
	}
	
	protected function getValue(Entity $entity, $header) {
		
		switch($header) {
			case 'organizations':
				
				if($entity->isOrganization) {
					return "";
				}
				
				return implode($this->multipleDelimiter, $entity->findOrganizations()->selectSingleValue('name')->all());
				
				break;
			
			default:
					return parent::getValue($entity, $header);
		}
		
	
	}

}
