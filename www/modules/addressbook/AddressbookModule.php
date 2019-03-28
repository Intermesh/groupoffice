<?php


namespace GO\Addressbook;

use Exception;
use GO;

use GO\Addressbook\Model\Addressbook;
use GO\Addressbook\Model\Addresslist;
use GO\Addressbook\Model\UserSettings;
use GO\Addressbook\Model\Template;

use go\modules\core\users\model\User;
use go\core\orm\Mapping;
use go\core\orm\Property;

class AddressbookModule extends \GO\Base\Module{

	public function author() {
		return 'Merijn Schering';
	}
	
	public function authorEmail() {
		return 'mschering@intermesh.nl';
	}
	
	
	// New framework
	public static function defineListeners() {
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
	}
	
	public static function onMap(Mapping $mapping) {
		$mapping->addRelation('addressbookSettings', UserSettings::class, ['id' => 'user_id'], false);
		return true;
	}
	// End new framework
	
	
	public static function initListeners() {
		\GO\Base\Model\User::model()->addListener('delete', "GO\Addressbook\AddressbookModule", "deleteUser");
	}
	
//	// Load the settings for the "Addresslists" tab in the Settings panel
//	public static function loadSettings($settingsController, &$params, &$response, $user) {
//
//		$findParams = \GO\Base\Db\FindParams::newInstance()
//						->joinCustomFields();
//		
//		$contact = $user->contact($findParams);
//		if($contact){
//			
//			// If there are customfields then load them too in the settings panel
//			$contactCfs = $contact->getCustomfieldsRecord();			
//			if($contactCfs)
//				$response['data'] = array_merge($response['data'],$contactCfs->getAttributes());
//			
//			$response['data']['email_allowed'] = $contact->email_allowed;
//		
//			$addresslists = $contact->addresslists();
//			foreach($addresslists as $addresslist){
//				$response['data']['addresslist_'.$addresslist->id]=1;
//			}
//		}
//		
//		self::_loadPhoto($response, $contact, $params);
//			
//		$settings = Settings::model()->getDefault($user);
//		$response['data']=array_merge($response['data'], $settings->getAttributes());
//		
//		$addressbook = $settings->addressbook;
//		
//		if($addressbook) {
//			$response['data']['default_addressbook_id']=$addressbook->id;
//			$response['remoteComboTexts']['default_addressbook_id']=$addressbook->name;
//		}
//		
//		return parent::loadSettings($settingsController, $params, $response, $user);
//	}
//	
//	// Save the settings for the "Addresslists" tab in the Settings panel
//	public static function submitSettings($settingsController, &$params, &$response, $user) {
//		$contact = $user->contact;
//		// Only do this when the setting "globalsettings_show_tab_addresslist" is enabled.
//		$tabEnabled = GO::config()->get_setting('globalsettings_show_tab_addresslist');
//		if($tabEnabled){
//		
//			
//
//			if($contact){
//
//				$addresslists = Addresslist::model()->find(\GO\Base\Db\FindParams::newInstance()->permissionLevel(\GO\Base\Model\Acl::READ_PERMISSION));
//				foreach($addresslists as $addresslist){
//					$linkModel = $addresslist->hasManyMany('contacts', $contact->id);
//					$mustHaveLinkModel = isset($params['addresslist_' . $addresslist->id]);
//					if ($linkModel && !$mustHaveLinkModel) {
//						$linkModel->delete();
//					}
//					if (!$linkModel && $mustHaveLinkModel) {
//						$addresslist->addManyMany('contacts',$contact->id);
//					}
//				}	
//			}
//		}
//		if($contact){
//			GO::$ignoreAclPermissions = true;
//			self::_savePhoto($response, $contact, $params);
//			GO::$ignoreAclPermissions = false;
//		}
//		
//		$settings = Settings::model()->getDefault($user);		
//		$settings->default_addressbook_id=$params['default_addressbook_id'];
//		$settings->save();
//		
//		return parent::submitSettings($settingsController, $params, $response, $user);
//	}
	
//	private static function _loadPhoto(&$response, &$model, &$params) {
//		$response['data']['photo_url']=$model->photoThumbURL;		
//		$response['data']['original_photo_url']=$model->photoURL;
//	}
//	
//	private static function _savePhoto(&$response, &$model, &$params) {
//		if(!empty($params['delete_photo'])){
//			$model->removePhoto();
//			$model->save();
//		}
//		if (isset($_FILES['image']['tmp_name'][0]) && is_uploaded_file($_FILES['image']['tmp_name'][0])) {
//		
//			
//			$destinationFile = new \GO\Base\Fs\File(\GO::config()->getTempFolder()->path().'/'.$_FILES['image']['name'][0]);
//			
//			move_uploaded_file($_FILES['image']['tmp_name'][0], $destinationFile->path());
//			
//			$model->setPhoto($destinationFile);
//			$model->save();
//			$response['photo_url'] = $model->photoThumbURL;
//			$response['original_photo_url'] = $model->photoURL;
//		}elseif(!empty($params['download_photo_url'])){
//			
//			$file = \GO\Base\Fs\File::tempFile();	
//			$c = new \GO\Base\Util\HttpClient();
//			
//			if(!$c->downloadFile($params['download_photo_url'], $file))
//				throw new Exception("Could not download photo from: '".$params['download_photo_url']."'");
//						
//			$model->setPhoto($file);
//			$model->save();					
//			$response['photo_url'] = $model->photoThumbURL;
//			$response['original_photo_url'] = $model->photoURL;
//		}
//	}
//	
	/**
	 * When a user is created, updated or logs in this function will be called.
	 * The function can check if the default calendar, addressbook, notebook etc.
	 * is created for this user.
	 * 
	 */
	public static function firstRun(){
		parent::firstRun();
	}
	
	public static function deleteUser($user){
		Addresslist::model()->deleteByAttribute('user_id', $user->id);
		Template::model()->deleteByAttribute('user_id', $user->id);		
	}
	
	public function autoInstall() {
		return true;
	}
	
	public function install() {
		parent::install();
		
		$default_language = \GO::config()->default_country;
		if (empty($default_language))
			$default_language = 'US';

		$addressbook = new Addressbook();
		$addressbook->setAttributes(array(
				'user_id' => 1,
				'name' => \GO::t("Prospects", "addressbook")
		));
		$addressbook->save();
		$addressbook->acl->addGroup(\GO::config()->group_internal,\GO\Base\Model\Acl::WRITE_PERMISSION);

		$addressbook = new Addressbook();
		$addressbook->setAttributes(array(
				'user_id' => 1,
				'name' => \GO::t("Suppliers", "addressbook")
		));
		$addressbook->save();
		$addressbook->acl->addGroup(\GO::config()->group_internal,\GO\Base\Model\Acl::WRITE_PERMISSION);

		if (!is_dir(\GO::config()->file_storage_path.'contacts/contact_photos'))
			mkdir(\GO::config()->file_storage_path.'contacts/contact_photos',0755, true);

		$addressbook = new Addressbook();
		$addressbook->setAttributes(array(
			'user_id' => 1,
			'name' => \GO::t("Customers", "addressbook")
		));
		$addressbook->save();
		$addressbook->acl->addGroup(\GO::config()->group_internal,\GO\Base\Model\Acl::WRITE_PERMISSION);
		
		
		$message = new \GO\Base\Mail\Message();
		$message->setHtmlAlternateBody('{salutation},<br />
<br />
{body}<br />
<br />
'.\GO::t("Best regards", "addressbook").'<br />
<br />
<br />
{user:name}<br />
{usercompany:name}<br />');
		
		$template = new Template();
		$template->setAttributes(array(
			'content' => $message->toString(),
			'name' => \GO::t("Default"),
			'type' => Template::TYPE_EMAIL,
			'user_id' => 1
		));
		$template->save();
		$template->acl->addGroup(\GO::config()->group_internal);
		
		
		$dt = Template::model()->findSingleByAttribute('name', 'Letter');
		if (!$dt) {
			$dt = new Template();	
			$dt->type = Template::TYPE_DOCUMENT;
			$dt->content = file_get_contents(\GO::modules()->addressbook->path . 'install/letter_template.docx');
			$dt->extension = 'docx';
			$dt->name = 'Letter';
			$dt->save();
			
			$dt->acl->addGroup(\GO::config()->group_internal);
		}
		
		
		$this->setFolderPermissions();
		
	}
	
	public function setFolderPermissions(){
		if(\GO::modules()->isInstalled('files')){
			$folder = \GO\Files\Model\Folder::model()->findByPath('addressbook', true);
			if($folder){
				$folder->acl_id=\GO\Base\Model\Acl::model()->getReadOnlyAcl()->id;
				$folder->readonly=1;
				$folder->save();
			}			
			
			$folder = \GO\Files\Model\Folder::model()->findByPath('addressbook/photos', true);
			if($folder && !$folder->acl_id){
				$folder->setNewAcl(1);
				$folder->readonly=1;
				$folder->save();
			}			
			
		  //hide old contacts folder if it exists
			$folder = \GO\Files\Model\Folder::model()->findByPath('contacts');
			if($folder){
				if(!$folder->getAcl()){
					$folder->setNewAcl(1);
					$folder->readonly=1;
					$folder->save();
				}  else {
					
					$folder->getAcl()->clear();
					
				}
			}		
		}
		
	}
	
	
	public function setFolderPermissions2(){
		if(\GO::modules()->isInstalled('files')){
			\GO\Base\Fs\Folder::createFromPath(\GO::config()->file_storage_path.'company_photos');
			$folderModel = \GO\Files\Model\Folder::model()->findByPath('company_photos', true);
			if($folderModel && !$folderModel->acl_id){
				$folderModel->setNewAcl(1);
				$folderModel->readonly=1;
				$folderModel->save();
			}			
		}
		
	}
	
	
	public function checkDatabase(&$response) {
		
		$this->setFolderPermissions();
		
		return parent::checkDatabase($response);
	}

}
