<?php /** @noinspection PhpUndefinedFieldInspection */

/** @noinspection PhpUnused */

namespace go\modules\community\addressbook;

use Exception;
use Faker\Generator;
use go\core\db\Query;
use go\core\exception\Forbidden;
use go\core\http\Response;
use go\core;
use go\core\model\Permission;
use go\core\orm\exception\SaveException;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\modules\community\addressbook\convert\VCard;
use go\modules\community\addressbook\model\Address;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\EmailAddress;
use go\modules\community\addressbook\model\PhoneNumber;
use go\modules\community\addressbook\model\UserSettings;
use go\core\model\Link;
use go\core\model\User;
use go\modules\community\addressbook\model\AddressBook;
use go\core\model\Group;
use go\core\model\Acl;
use go\core\model\Module as GoModule;
use GO\Files\Model\Folder;
use go\modules\community\addressbook\model\Settings;
use go\modules\community\comments\Module as CommentsModule;
use GO\Savemailas\SavemailasModule;

/**						
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 * 
 * @todo 
 * Merge
 * Deduplicate
 * 
 */
class Module extends core\Module
{

	/**
	 * The development status of this module
	 * @return string
	 */
	public function getStatus() : string{
		return self::STATUS_STABLE;
	}

	public function getAuthor(): string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}

	protected function rights(): array
	{
		return [
			'mayChangeAddressbooks', // allows AddressBook/set (hide ui elements that use this)
			'mayExportContacts', // Allows users to export contacts
		];
	}

	public function autoInstall(): bool
	{
		return true;
	}

	/**
	 * Default sort order when installing. If null it will be auto generated.
	 * @return int|null
	 */
	public static function getDefaultSortOrder() : ?int{
		return 15;
	}


	public function defineListeners()
	{
		parent::defineListeners();

		Link::on(Link::EVENT_BEFORE_DELETE, Contact::class, 'onLinkDelete');
		Link::on(Link::EVENT_SAVE, Contact::class, 'onLinkSave');
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
		User::on(User::EVENT_BEFORE_DELETE, static::class, 'onUserDelete');
		User::on(User::EVENT_BEFORE_SAVE, static::class, 'onUserBeforeSave');
	}

	/**
	 * @throws Forbidden
	 * @throws Exception
	 */
	public function downloadVCard($contactId)
	{
		$contact = Contact::findById($contactId);
		if (!$contact->getPermissionLevel()) {
			throw new Forbidden();
		}

		$c = new VCard();

		$vcard = $c->export($contact);

		Response::get()
			->setHeader('Content-Type', 'text/vcard;charset=utf-8')
			->setHeader('Content-Disposition', 'attachment; filename="' . $contact->name . '.vcf"')
			->setHeader("Content-Length", strlen($vcard))
			->sendHeaders();

		echo $vcard;
	}


	/**
	 * @throws Exception
	 */
	public static function onMap(Mapping $mapping)
	{
		$mapping->addHasOne('addressBookSettings', UserSettings::class, ['id' => 'userId'], true);
		$mapping->addScalar('birthdayPortletAddressBooks', "addressbook_portlet_birthday", ['id' => 'userId']);
	}

	/**
	 * @throws Exception
	 */
	public static function onUserDelete(Query $query)
	{
		AddressBook::delete(['createdBy' => $query]);
	}

	public static function onUserBeforeSave(User $user)
	{
		if (!$user->isNew() && $user->isModified('displayName')) {
			$oldName = $user->getOldValue('displayName');
			$ab = AddressBook::find()->where(['createdBy' => $user->id, 'name' => $oldName])->single();
			if ($ab) {
				$ab->name = $user->displayName;
				$ab->save();
			}
		}
	}

	protected function beforeInstall(GoModule $model): bool
	{
		// Share module with Internal group
		$model->permissions[Group::ID_INTERNAL] = (new Permission($model))
			->setRights(['mayRead' => true, 'mayExportContacts' => true]);

		return parent::beforeInstall($model);
	}


	/**
	 * @throws Exception
	 */
	protected function afterInstall(GoModule $model): bool
	{
		// create Shared address book
		$addressBook = new AddressBook();
		$addressBook->name = go()->t("Shared");
		$addressBook->setAcl([
			Group::ID_INTERNAL => Acl::LEVEL_DELETE
		]);
		$addressBook->save();


		static::checkRootFolder();

		return parent::afterInstall($model);
	}

	/**
	 * @return core\Settings|null
	 */
	public function getSettings()
	{
		return Settings::get();
	}

	/**
	 * Create and check permission on the "addressbook" root folder.
	 * @throws Exception
	 */
	public static function checkRootFolder()
	{

		if (!GoModule::isInstalled('legacy', 'files')) {
			return false;
		}

		$roAclId = core\model\Module::findByName("community", "addressbook")->getShadowAclId();

		$folder = Folder::model()->findByPath('addressbook', true, ['acl_id' => $roAclId]);
		if ($folder->acl_id != $roAclId) {
			$folder->acl_id = $roAclId;
			$folder->save(true);
		}

		return $folder;
	}

	public function checkDatabase()
	{
		static::checkRootFolder();
		parent::checkDatabase(); // TODO: Change the autogenerated stub
	}

	/**
	 * @throws SaveException
	 */
	public function demoCompany(Generator $faker): Contact
	{
		$this->demo($faker);

		$index = rand(0, count($this->demoCompanies) - 1);

		return $this->demoCompanies[$index];
	}

	/**
	 * @throws SaveException
	 * @throws Exception
	 */
	private function internalDemoCompany(Generator $faker): Contact
	{
		$company = new Contact();
//			$blob = core\fs\Blob::fromTmp(new core\fs\File($faker->image));
//			$company->photoBlobId = $blob->id;
		$company->isOrganization = true;
		$company->addressBookId = self::demoAddressBook()->id;
		$company->name = $faker->company;
		$company->jobTitle = $faker->bs;


		$this->generateContactProps($company, $faker);

		$company->notes = $faker->realtext;
		if (!$company->save()) {
			throw new SaveException($company);
		}

		Link::demo($faker, $company);

		if (GoModule::isInstalled("community", "comments")) {
			CommentsModule::demoComments($faker, $company);
		}

		return $company;
	}

	private static $demoAddressBook;

	private static function demoAddressBook()
	{
		return self::$demoAddressBook ?? (self::$demoAddressBook = AddressBook::find()->single());
	}

	/**
	 * @throws SaveException
	 */
	public function demoContact(Generator $faker): Contact
	{
		$this->demo($faker);

		$index = rand(0, count($this->demoContacts) - 1);

		return $this->demoContacts[$index];
	}

	/**
	 * @throws SaveException
	 * @throws Exception
	 */
	private function internalDemoContact(Generator $faker): Contact
	{

		$contact = new Contact();
//			$blob = core\fs\Blob::fromTmp(new core\fs\File($faker->image(null, 640, 480, 'people')));
//			$company->photoBlobId = $blob->id;

		$contact->addressBookId = self::demoAddressBook()->id;
		$contact->firstName = $faker->firstName;
		$contact->lastName = $faker->lastName;

		$this->generateContactProps($contact, $faker);

		if(!$contact->save()) {
			throw new SaveException($contact);
		}

		if(GoModule::isInstalled("community", "comments")) {
			CommentsModule::demoComments($faker, $contact);
		}

		Link::demo($faker, $contact);

		if(GoModule::isInstalled("legacy", "savemailas")) {
			SavemailasModule::get()->demoMail($faker, $contact);
		}

		return $contact;
	}

	private $demoCompanies;
	private $demoContacts;


	/**
	 * @throws SaveException
	 * @throws Exception
	 */
	public function demo(Generator $faker) {
		if(!isset($this->demoCompanies)) {

			$companies = Contact::find()
				->where('isOrganization', '=', true)
				->orderBy(['id' => 'DESC'])
				->limit(10)
				->all();

			if(count($companies) == 10) {
				$this->demoCompanies = $companies;
				$this->demoContacts = 	Contact::find()
					->where('isOrganization', '=', false)
					->orderBy(['id' => 'DESC'])
					->limit(10)
					->all();
			} else{
				for ($n = 0; $n < 10; $n++) {
					echo ".";

					$company = $this->internalDemoCompany($faker);
					$this->demoCompanies[] = $company;
					$contact = $this->internalDemoContact($faker);
					$this->demoContacts[] = $contact;

					Link::create($contact, $company, null, true);
				}
			}

		}
	}

	/**
	 * @param Contact $company
	 * @param Generator $faker
	 * @return void
	 */
	private function generateContactProps( Contact $company, Generator $faker): void
	{
		$count = $faker->numberBetween(0, 3);
		for ($i = 0; $i < $count; $i++) {
			$company->phoneNumbers[$i] = (new PhoneNumber($company))->setValues(['number' => $faker->phoneNumber, 'type' => PhoneNumber::TYPE_MOBILE]);
		}
		$count = $faker->numberBetween(0, 3);
		for ($i = 0; $i < $count; $i++) {
			$company->emailAddresses[$i] = (new EmailAddress($company))->setValues(['email' => $faker->email, 'type' => EmailAddress::TYPE_HOME]);
		}

		$company->addresses[0] = $a = new Address($company);

		$a->address = $faker->streetName .' '.$faker->streetAddress;
		$a->city = $faker->city;
		$a->zipCode = $faker->postcode;
		$a->state = $faker->state;
		$a->country = $faker->country;
	}
}