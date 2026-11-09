<?php
namespace go\core;

use go\core\model\Link;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;

class LanguageTest extends \PHPUnit\Framework\TestCase
{
	public function testFormatAddress() {
		$address = go()->getLanguage()->formatAddress([
			'address' => 'Veemarktkade 8',
		], "nl", false);

		$this->assertEquals('Veemarktkade 8', $address);


		$address = go()->getLanguage()->formatAddress([
			'address' => 'Veemarktkade 8'
		], "gb", false);

		$this->assertEquals("Veemarktkade 8", $address);
	}

	/**
	 * Language overrides used to be loaded with require() from the admin's home folder in the data directory. Any user
	 * who could write there could run PHP code, so the feature was removed. This file must never be executed.
	 */
	public function testLanguageOverrideInDataFolderIsNotLoaded() {
		$admin = \go\core\model\User::findById(1, ['homeDir']);
		$file = go()->getDataFolder()->getFile($admin->homeDir . '/language/community/notes/en.php');
		$existed = $file->exists();
		$this->assertFalse($existed, 'A language override file already exists, not touching it: ' . $file->getPath());

		$cacheKey = 'en-community-notes';
		unset($GLOBALS['languageOverrideLoaded']);

		// Topmost folder inside the admin's home that doesn't exist yet, so it can be removed afterwards
		$homePath = go()->getDataFolder()->getFolder($admin->homeDir)->getPath();
		$createdFolder = null;
		for ($folder = $file->getFolder(); $folder->getPath() !== $homePath && !$folder->exists(); $folder = $folder->getParent()) {
			$createdFolder = $folder;
		}

		try {
			$file->getFolder()->create();
			$file->putContents('<?php $GLOBALS[\'languageOverrideLoaded\'] = true; return [\'Notes\' => \'OVERRIDDEN\'];');
			go()->getCache()->delete($cacheKey);

			$language = new Language();
			$language->setLanguage('en');

			$this->assertNotEquals('OVERRIDDEN', $language->t('Notes', 'community', 'notes'));
			$this->assertArrayNotHasKey('languageOverrideLoaded', $GLOBALS);
		} finally {
			$file->delete();
			$createdFolder?->delete();
			go()->getCache()->delete($cacheKey);
			unset($GLOBALS['languageOverrideLoaded']);
		}
	}
}
