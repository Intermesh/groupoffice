<?php

namespace GO\Carddav;


class CarddavModule extends \GO\Base\Module{
	public function depends() {
		return array("dav","sync","addressbook");
	}
	
	public static function initListeners() {
		
		\GO\Addressbook\Model\Contact::model()->addListener("delete", "GO\Carddav\CarddavModule", "deleteContact");

		
	}
	
	public static function deleteContact(\GO\Addressbook\Model\Contact $contact){
		\GO\CardDAV\Model\DavContact::model()->deleteByAttribute('id', $contact->id);
	}
	
}
