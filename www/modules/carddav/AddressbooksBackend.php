<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author WilmarVB <wilmar@intermesh.nl>
 */

namespace GO\CardDAV;
use Sabre;

class AddressbooksBackend extends \Sabre\CardDAV\Backend\AbstractBackend {
	
	/**
	 * Get the user model by principal URI
	 * 
	 * @param StringHelper $principalUri
	 * @return \GO\Base\Model\User 
	 */
	private function _getUser($principalUri) {
		$username = basename($principalUri);
		return \GO\Base\Model\User::model()->findSingleByAttribute('username', $username);
	}
	
	private function _modelToDAVAddressbook(\GO\Addressbook\Model\Addressbook $addressbookModel, $principalUri){
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
			->select('max(mtime) as mtime, count(*) as count')
			->single()
			->criteria(\GO\Base\Db\FindCriteria::newInstance()
				->addModel(\GO\Addressbook\Model\Contact::model())
				->addModel(\GO\Addressbook\Model\Company::model())
				->addCondition('addressbook_id', $addressbookModel->id));
		
		$model = \GO\Addressbook\Model\Contact::model()->find($findParams);
	
		$ctag = $model->count . ':' . $model->mtime;
		
//		if(\GO::config()->debug)
//			$ctag=time();

		return array(
			'id' => $addressbookModel->id,
			'uri' => $addressbookModel->getUri(),
			'principaluri' => $principalUri,
//			'size'=> $model->count,
//			'mtime'=>$model->mtime,
			'{DAV:}displayname' => $addressbookModel->name,
			'{http://calendarserver.org/ns/}getctag' => $ctag,
			'{' . Sabre\CardDAV\Plugin::NS_CARDDAV . '}supported-address-data' => new Sabre\CardDAV\Xml\Property\SupportedAddressData(),
			'{' . Sabre\CardDAV\Plugin::NS_CARDDAV . '}addressbook-description' => 'User addressbook'
		);

	}
	
//	private function _convert(\GO\Addressbook\Model\Contact $contactModel,$currentUri) {
//		
//		$data = ($contactModel->mtime==$contactModel->client_mtime && !empty($contactModel->data)) ? $contactModel->data : $contactModel->toVObject()->serialize();
//		
//		return array(
//				'id' => $contactModel->id,
//				'carddata' => $data,
//				'uri' => $currentUri,
//				'lastmodified' => date('Ymd H:i:s', $contactModel->mtime)
//			);
//	}
	
	private $_cachedAddressbooks;
	
	/**
		* Returns the list of addressbooks for a specific user.
		*
		* Every addressbook should have the following properties:
		*   id - an arbitrary unique id
		*   uri - the 'basename' part of the url
		*   principaluri - Same as the passed parameter
		*
		* Any additional clark-notation property may be passed besides this. Some
		* common ones are :
		*   {DAV:}displayname
		*   {urn:ietf:params:xml:ns:carddav}addressbook-description
		*   {http://calendarserver.org/ns/}getctag
		*
		* @param StringHelper $principalUri
		* @return array
		*/
	public function getAddressBooksForUser($principalUri) {
		\GO::debug('a:getAddressbooksForUser('.$principalUri.')');
		
		if(!isset($this->_cachedAddressbooks[$principalUri])){
			$user = $this->_getUser($principalUri);
			$findParams = \GO\Base\Db\FindParams::newInstance()
							->joinModel(array(
									'model' => 'GO\Sync\Model\UserAddressbook',
									'localTableAlias' => 't', //defaults to "t"
									'localField' => 'id', //defaults to "id"
									'foreignField' => 'addressbook_id', //defaults to primary key of the remote model
									'tableAlias' => 'l', //Optional table alias
							))
//							->permissionLevel(\GO\Base\Model\Acl::DELETE_PERMISSION, $user->id)
							->ignoreAcl()
//							->debugSql()
							->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('user_id', $user->id, '=', 'l'));
			$stmt = \GO\Addressbook\Model\Addressbook::model()->find($findParams);
			$this->_cachedAddressbooks[$principalUri] = array();
			while ($addressbookModel = $stmt->fetch()) {
				$this->_cachedAddressbooks[$principalUri][] = $this->_modelToDAVAddressbook($addressbookModel, $principalUri);
			}
		}
		return $this->_cachedAddressbooks[$principalUri];
	}

	/**
		* Updates an addressbook's properties
		*
		* See Sabre\DAV_IProperties for a description of the mutations array, as
		* well as the return value.
		*
		* @param mixed $addressbookId
		* @param array $mutations
		* @see Sabre\DAV_IProperties::updateProperties
		* @return bool|array
		*/
	public function updateAddressBook($addressbookId, Sabre\DAV\PropPatch  $mutations) {
		throw new Sabre\DAV\Exception\Forbidden();
	}

	/**
		* Creates a new address book
		*
		* @param StringHelper $principalUri
		* @param StringHelper $url Just the 'basename' of the url.
		* @param array $properties
		* @return void
		*/
	public function createAddressBook($principalUri, $url, array $properties) {
		throw new Sabre\DAV\Exception\Forbidden();
	}

	/**
		* Deletes an entire addressbook and all its contents
		*
		* @param mixed $addressbookId
		* @return void
		*/
	public function deleteAddressBook($addressbookId) {
		throw new Sabre\DAV\Exception\Forbidden();
	}

	/**
		* Returns all cards for a specific addressbook id.
		*
		* This method should return the following properties for each card:
		*   * carddata - raw vcard data
		*   * uri - Some unique url
		*   * lastmodified - A unix timestamp
		*
		* It's recommended to also return the following properties:
		*   * etag - A unique etag. This must change every time the card changes.
		*   * size - The size of the card in bytes.
		*
		* If these last two properties are provided, less time will be spent
		* calculating them. If they are specified, you can also ommit carddata.
		* This may speed up certain requests, especially with large cards.
		*
		* @param mixed $addressbookId
		* @return array
		*/
	public function getCards($addressbookId) {
		\GO::debug('a:getCards('.$addressbookId.')');
		
			//Get the calendar object and check if the user has delete permission.
		$addressbook = \GO\Addressbook\Model\Addressbook::model()->findByPk($addressbookId);
//		if(!$addressbook->checkPermissionLevel(\GO\Base\Model\Acl::DELETE_PERMISSION))
//			throw new Sabre\DAV\Exception\Forbidden();
		
//				VAR_DUMP($addressbookId);
		$contactsStmt = \GO\Addressbook\Model\Contact::model()->find(
			\GO\Base\Db\FindParams::newInstance()
				->select('t.*')->debugSql()->ignoreAcl()
				->criteria(\GO\Base\	Db\FindCriteria::newInstance()
					->addCondition('addressbook_id',$addressbookId)
				)
			);
		
		\GO::debug("Found ".$contactsStmt->rowCount()." contacts");
		
		$cards = array();
		
		/**
		 * Making sure the necessary DavContact records are here 
		 */
		while ($contactModel = $contactsStmt->fetch()) {
			$davContact = Model\DavContact::model()->findByPk($contactModel->id);
			
			if(empty($contactModel->uuid)){
				//because uuid's were not saved for contacts before ensure it exists here
				$contactModel->uuid=\GO\Base\Util\UUID::create('contact', $contactModel->id);
				$contactModel->save();
			}

			if(!$davContact){
				$davContact = new Model\DavContact();				
				$davContact->mtime=$contactModel->mtime;
				$davContact->id=$contactModel->id;
				$davContact->data=$contactModel->toVObject()->serialize();
				$davContact->uri=$contactModel->uuid.'-'.$contactModel->id;
				$davContact->save();
			}else if($davContact->mtime!=$contactModel->mtime) {
				
//				$davContact->data;
				
				$vcard = \GO\Base\VObject\Reader::read($davContact->data);
				
				$davContact->mtime=$contactModel->mtime;				
				$davContact->data=$contactModel->toVObject($vcard)->serialize();
				$davContact->save();				
			}
				

			$cards[] = array(
					'id' => $contactModel->id,
					'uri' => $davContact->uri,
					'carddata' => $davContact->data,
					'lastmodified' => $contactModel->mtime,
					'etag'=>$contactModel->getEtag()
			);
		}
		
//		var_dump($cards);
		
		return $cards;
	}

	/**
		* Returns a specfic card.
		*
		* The same set of properties must be returned as with getCards. The only
		* exception is that 'carddata' is absolutely required.
		*
		* @param mixed $addressbookId
		* @param StringHelper $cardUri
		* @return array
		*/
	public function getCard($addressbookId, $cardUri) {
		\GO::debug('a:getCard('.$addressbookId.','.$cardUri.')');
		
		// Checking if the contact can be retrieved using the CardDAV uri.
		$contactModel = \GO\Addressbook\Model\Contact::model()->find(
			\GO\Base\Db\FindParams::newInstance()
				->select('t.*,d.uri, d.mtime AS client_mtime, d.data')
				->single()
				->join(
					Model\DavContact::model()->tableName(),
					\GO\Base\Db\FindCriteria::newInstance()
						->addRawCondition('t.id', 'd.id'),
					'd')
				->criteria(
					\GO\Base\Db\FindCriteria::newInstance()
						->addCondition('uri',$cardUri,'=','d')
						->addCondition('addressbook_id', $addressbookId)
				)
		);
		
		if (!empty($contactModel)) {
			\GO::debug('Contact found on server.');
			
//			if($contactModel->mtime==$contactModel->client_mtime && !empty($contactModel->data)) {
//				$data = $contactModel->data;
//			}else
//			{
				$vcard = !empty($contactModel->data) ? \GO\Base\VObject\Reader::read($contactModel->data) : null;
				
				$data = $contactModel->toVObject($vcard)->serialize();
//			}			
			
//			\GO::debug($contactModel->mtime==$contactModel->client_mtime ? "Returning client data (mtime)" : "Returning server data (mtime)");
//			
//			\GO::debug($data);

			$object = array(
					'id' => $contactModel->id,
					'uri' => $contactModel->uri,
					'carddata' => $data,
					'lastmodified' => date('Ymd H:i:s', $contactModel->mtime),
					'etag'=>$contactModel->getETag()
			);
			//\GO::debug($object);
			return $object;
	
		} else {
			throw new Sabre\DAV\Exception\NotFound('File not found');
		}
	}

	/**
	 * Creates a new card.
	 *
	 * The addressbook id will be passed as the first argument. This is the
	 * same id as it is returned from the getAddressbooksForUser method.
	 *
	 * The cardUri is a base uri, and doesn't include the full path. The
	 * cardData argument is the vcard body, and is passed as a string.
	 *
	 * @param mixed $addressbookId
	 * @param StringHelper $cardUri
	 * @param StringHelper $cardData VCard data string.
	 * @return null|string when string the etag is returned
	 */
	public function createCard($addressbookId, $cardUri, $cardData) {
		\GO::debug('a:createCard('.$addressbookId.','.$cardUri.',[data])');
		
		\GO::debug($cardData);
		
		$file = new \GO\Base\Fs\File($cardUri);
		$contactModel = new \GO\Addressbook\Model\Contact();
		$contactModel->importVObject(
			\GO\Base\VObject\Reader::read($cardData),
			array('addressbook_id'=>$addressbookId,'uuid'=>$file->nameWithoutExtension())
		);

		if (!$contactModel)
			return false;	
		
		if($contactModel->hasValidationErrors()) {
			throw new \Exception("couldn't save contact: ".var_export($contactModel->getValidationErrors(), true));
		}

		//store calendar data because we need to reply with the exact client data
		$davContact = new Model\DavContact();
		$davContact->id=$contactModel->id;
		$davContact->uri=$cardUri;
		$davContact->data=$cardData;
		if(!$davContact->save()) {
			throw new \Exception("Couldn't save vcard data: ".var_export($davContact->getValidationErrors(), true));
		}
		
		//see http://sabre.io/dav/building-a-carddav-client/
		//Don't return E-tag if server has modified the contact by finding a company id
		if(!$contactModel->company || $contactModel->company->ctime == time()){
			return $contactModel->getETag();
		}
		
	}

	/**
		* Updates a card.
		*
		* The addressbook id will be passed as the first argument. This is the
		* same id as it is returned from the getAddressbooksForUser method.
		*
		* The cardUri is a base uri, and doesn't include the full path. The
		* cardData argument is the vcard body, and is passed as a string.
		*
		* It is possible to return an ETag from this method. This ETag should
		* match that of the updated resource, and must be enclosed with double
		* quotes (that is: the string itself must contain the actual quotes).
		*
		* You should only return the ETag if you store the carddata as-is. If a
		* subsequent GET request on the same card does not have the same body,
		* byte-by-byte and you did return an ETag here, clients tend to get
		* confused.
		*
		* If you don't return an ETag, you can just return null.
		*
		* @param mixed $addressbookId
		* @param StringHelper $cardUri
		* @param StringHelper $cardData
		* @return StringHelper|null
		*/
	public function updateCard($addressbookId, $cardUri, $cardData) {
		\GO::debug('a:updateCard('.$addressbookId.','.$cardUri.',[data])');
		
		\GO::debug($cardData);
		
		$contactModel = $this->_getContactModelByUri($cardUri);
		
		$oldCompanyId = $contactModel->company_id;
		
		$davContactModel = Model\DavContact::model()->findByPk($contactModel->id);
		$davContactModel->data = $cardData;
		if(!$davContactModel->save()) {
			throw new \Exception("Couldn't save vcard data: ".var_export($davContactModel->getValidationErrors(), true));
		}
		
		$contactModel->importVObject(
			\GO\Base\VObject\Reader::read($cardData),
			array('addressbook_id'=>$addressbookId)
		);
		
		if($contactModel->hasValidationErrors()) {
			throw new \Exception("couldn't save contact: ".var_export($contactModel->getValidationErrors(), true));
		}
		
		//see http://sabre.io/dav/building-a-carddav-client/
		//Don't return E-tag if server has modified the contact by finding a company id
		
		if($oldCompanyId == $contactModel->company_id || empty($contactModel->company_id)){
			return $contactModel->getETag();
		}
		
	}

	/**
		* Deletes a card
		*
		* @param mixed $addressbookId
		* @param StringHelper $cardUri
		* @return bool
		*/
	public function deleteCard($addressbookId, $cardUri) {
		\GO::debug('a:deleteCard('.$addressbookId.','.$cardUri.')');
		$contactModel = $this->_getContactModelByUri($cardUri);
		$contactModel->delete();
//		$davContactModel = Model\DavContact::model()->findByPk($contactModel->id);
//		$davContactModel->delete();
		return true;
	}
	
	private function _getContactModelByUri($uri) {
		$davContactModel = Model\DavContact::model()
			->findSingleByAttribute('uri',$uri);
		if (empty($davContactModel))
			throw new \Exception('CardDAV entry not found for uri '.$uri);
		
		return \GO\Addressbook\Model\Contact::model()->findByPk($davContactModel->id);
	}
}
