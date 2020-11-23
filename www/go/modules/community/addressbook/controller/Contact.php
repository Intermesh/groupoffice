<?php
namespace go\modules\community\addressbook\controller;

use go\core\exception\Forbidden;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\model\Acl;
use GO\Email\Model\Account;
use go\modules\community\addressbook\convert\VCard;
use go\modules\community\addressbook\model;
use go\modules\community\addressbook\Module;

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
	protected function entityClass() {
		return model\Contact::class;
	}	
	
	
	
	protected function transformSort($sort) {
		$sort = parent::transformSort($sort);
		
		//merge sort on start to beginning of array
		return array_merge(['s.starred' => 'DESC'], $sort);
	}
	
	/**
	 * Handles the Foo entity's Foo/query command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/query
	 */
	public function query($params) {
		return $this->defaultQuery($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/get command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/get
	 */
	public function get($params) {
		return $this->defaultGet($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/set command
	 * 
	 * @see https://jmap.io/spec-core.html#/set
	 * @param array $params
	 */
	public function set($params) {
		return $this->defaultSet($params);
	}
	
	
	/**
	 * Handles the Foo entity's Foo/changes command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes($params) {
		return $this->defaultChanges($params);
	}
	
	public function export($params) {
		$addressbookMod = Module::get();
		if($addressbookMod->getSettings()->restrictExportToAdmins && !$addressbookMod->getModel()->hasPermissionLevel(Acl::LEVEL_MANAGE)) {
			throw new Forbidden("Export has been restricted to administrators");
		}
		return $this->defaultExport($params);
	}
	
	public function import($params) {
		return $this->defaultImport($params);
	}

	/**
	 * @param $params
	 * @return array
	 */
	public function exportColumns($params) {
		return $this->defaultExportColumns($params);
	}
	
	public function importCSVMapping($params) {
		return $this->defaultImportCSVMapping($params);
	}

	public function merge($params) {
		return $this->defaultMerge($params);
	}

	public function labels($params) {

		$addressbookMod = Module::get();
		if($addressbookMod->getSettings()->restrictExportToAdmins && !go()->getAuthState()->isAdmin()) {
			throw new Forbidden("Export has been restricted to administrators");
		}

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
	public function loadVCS( array $params) :array
	{
		$account = Account::model()->findByPk($params['account_id']);
		$imap = $account->openImapConnection($params['mailbox']);
		$data = $imap->get_message_part_decoded($params['uid'], $params['number'], $params['encoding'], false, true, false);

		$vcard = \GO\Base\VObject\Reader::read($data);
		$importer = new VCard();
		$contact = new model\Contact();
		$contact->addressBookId = go()->getAuthState()->getUser(['addressBookSettings'])->addressBookSettings->getDefaultAddressBookId();;
		$contact = $importer->import($vcard, $contact);
		return ['success' => true, 'contactId' => $contact->id];

	}
}

