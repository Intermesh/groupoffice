<?php
namespace go\modules\community\addressbook\controller;

use go\core\fs\Blob;
use go\core\http\Exception;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\model\Acl;
use go\core\util\ArrayObject;
use GO\Email\Model\Account;
use go\modules\community\addressbook\convert\VCard;
use go\modules\community\addressbook\model;

/**
 * The controller for the Contact entity
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */ 
class Contact extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\Contact::class;
	}

	//Allow access without access to the module so users can set their profile in their settings
	protected function authenticate()
	{
		if (!go()->getAuthState()->isAuthenticated()) {
			throw new Exception(401, "Unauthorized");
		}
	}



//	protected function transformSort($sort) {
//		$sort = parent::transformSort($sort);
//
//		//merge sort on start to beginning of array
//		return array_merge(['s.starred' => 'DESC'], $sort);
//	}

	/**
	 * Handles the Foo entity's Foo/query command
	 *
	 * @param array $params
	 * @return ArrayObject
	 * @throws InvalidArguments
	 * @see https://jmap.io/spec-core.html#/query
	 */
	public function query(array $params): ArrayObject
	{
		return $this->defaultQuery($params);
	}

	/**
	 * Handles the Foo entity's Foo/get command
	 *
	 * @param array $params
	 * @return ArrayObject
	 * @throws \Exception
	 * @see https://jmap.io/spec-core.html#/get
	 */
	public function get(array $params) : ArrayObject
	{
		return $this->defaultGet($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/set command
	 * 
	 * @see https://jmap.io/spec-core.html#/set
	 */
	public function set(array $params): ArrayObject
	{
		return $this->defaultSet($params);
	}


	/**
	 * Handles the Foo entity's Foo/changes command
	 *
	 * @param array $params
	 * @return ArrayObject
	 * @throws InvalidArguments
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes(array $params): ArrayObject
	{
		return $this->defaultChanges($params);
	}

	/**
	 * @throws InvalidArguments
	 */
	public function export(array $params): ArrayObject
	{
		return $this->defaultExport($params);
	}

	/**
	 *
	 * @param array $params
	 * @return ArrayObject
	 * @throws Exception
	 */
	public function import(array $params): ArrayObject
	{
		if ($this->checkPermissionsForAddressBook($params)) {
			return $this->defaultImport($params);
		}
		return new ArrayObject([]);
	}

	/**
	 * @param $params
	 * @return ArrayObject
	 */
	public function exportColumns($params): ArrayObject
	{
		return $this->defaultExportColumns($params);
	}
	
	public function importCSVMapping(array $params): ArrayObject
	{
		return $this->defaultImportCSVMapping($params);
	}

	public function merge($params): ArrayObject
	{
		return $this->defaultMerge($params);
	}

	public function labels($params): array
	{
		$tpl = <<<EOT
{{contact.name}}
[assign address = contact.addresses | filter:type:"postal" | first]
[if !{{address}}]
[assign address = contact.addresses | first]
[/if]
{{address.formatted}}
EOT;

		$labels = new model\Labels($params['unit'] ?? 'mm', $params['pageFormat'] ?? 'A4');

		$labels->rows = $params['rows'] ?? 8;
		$labels->cols = $params['columns'] ?? 2;
		$labels->labelTopMargin = $params['labelTopMargin'] ?? 10;
		$labels->labelRightMargin = $params['labelRightMargin'] ?? 10;
		$labels->labelBottomMargin = $params['labelBottomMargin'] ?? 10;
		$labels->labelLeftMargin = $params['labelLeftMargin'] ?? 10;

		$labels->pageTopMargin = $params['pageTopMargin'] ?? 10;
		$labels->pageRightMargin = $params['pageRightMargin'] ?? 10;
		$labels->pageBottomMargin = $params['pageBottomMargin'] ?? 10;
		$labels->pageLeftMargin = $params['pageLeftMargin'] ?? 10;

		$labels->SetFont($params['font'] ?? 'dejavusans', '', $params['fontSize'] ?? 10);

		$tmpFile = $labels->render($params['ids'], $params['tpl'] ?? $tpl);

		$blob = Blob::fromFile($tmpFile);
		$blob->save();

		return ['blobId' => $blob->id];

	}

	/**
	 * Import one or more VCards from an email attachment
	 * @param array $params
	 * @return array
	 * @throws \Exception
	 */
	public function loadVCF( array $params) :array
	{
		if(!empty($params['fileId'])) {
			$file = \GO\Files\Model\File::model()->findByPk($params['fileId']);
			$data = $file->fsFile->getContents();
		} else {
			$account = Account::model()->findByPk($params['account_id']);
			$imap = $account->openImapConnection($params['mailbox']);
			$data = $imap->get_message_part_decoded($params['uid'], $params['number'], $params['encoding'], false, true, false);
		}

		$vcard = \GO\Base\VObject\Reader::read($data);
		$importer = new VCard();
		$contact = new model\Contact();
		$contact->addressBookId = go()->getAuthState()->getUser(['addressBookSettings'])->addressBookSettings->getDefaultAddressBookId();;
		$contact = $importer->import($vcard, $contact);
		return ['success' => true, 'contactId' => $contact->id];

	}

	/**
	 * Match address book ID with permissions
	 *
	 * @param array $params
	 * @return bool
	 * @throws Exception
	 */
	private function checkPermissionsForAddressBook(array $params)
	{
		if(isset($params['values']) && isset($params['values']['addressBookId'])) {
			$addressBookId = $params['values']['addressBookId'];
			$addressBook = model\AddressBook::findById($addressBookId);
			if(!$addressBook || $addressBook->getPermissionLevel() < Acl::LEVEL_CREATE) {
				throw new Exception('You do not have create permissions for this address book');
			}
		}
		return true;
	}
}

