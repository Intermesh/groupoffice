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
 * @author Merijn Schering <mschering@intermesh.nl>
 * @author Wilmar van Beusekom <wilmar@intermesh.nl>
 */

namespace GO\Addressbook\Model;
use Sabre;

/**
 * @property String $photo Full path to photo
 * @property String $photoURL URL to photo
 * 
 * @property String $name Full name of the contact
 * @property int $go_user_id
 * @property int $files_folder_id
 * @property boolean $email_allowed
 * @property string $salutation
 * @property int $mtime
 * @property int $muser_id
 * @property int $ctime
 * @property string $comment
 * @property string $address_no
 * @property string $zip
 * @property string $city
 * @property string $state
 * @property string $country
 * @property string $cellular
 * @property string $cellular2
 * @property string $work_fax
 * @property string $fax
 * @property string $work_phone
 * @property string $home_phone
 * @property string $function
 * @property string $department
 * @property int $company_id
 * @property string $email3
 * @property string $email2
 * @property string $email
 * @property string $birthday
 * @property string $sex
 * @property string $suffix
 * @property string $title
 * @property string $initials
 * @property string $last_name
 * @property string $middle_name
 * @property string $first_name
 * @property int $addressbook_id
 * @property int $user_id
 * @property int $id
 * @property int $age
 * @property int $action_date
 * 
 * @property string $firstEmail Automatically returns the first filled in e-mail address.
 * @property Addressbook $addressbook
 * @property Company $company
 * @property string $homepage
 * @property string $uuid
 * @property string $url_linkedin
 * @property string $url_facebook
 * @property string $url_twitter
 * @property string $skype_name
 * @property int $last_email_time
 * @property string $color
 */
class Contact extends \GO\Base\Db\ActiveRecord {
		
	/**
	 * if user typed in a new company name manually we set this attribute so a new company will be autocreated.
	 * 
	 * @var StringHelper 
	 */
	public $company_name;
	
	
	public $skip_user_update=false;
	
	
	private $_photoFile;
		
	/**
	 * This property is used to temporary store the photo file object when removing the photo from the contact model.
	 * 
	 * @var File object / Boolean false 
	 */
	private $_removePhotoFile = false;
	
	
	public function getUri() {
		if(isset($this->_setUri)) {
			return $this->_setUri;
		}
		
		return str_replace('/','+',$this->uuid).'-'.$this->id;
	}
	
	private $_setUri;
	
	public function setUri($uri) {
		$this->_setUri = $uri;					
	}
	
	
	public function getETag() {
		return '"' . date('Ymd H:i:s', $this->mtime). '-'.$this->id.'"';
	}
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Contact 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}

	public function aclField(){
		return 'addressbook.acl_id';	
	}
	
	public function tableName(){
		return 'ab_contacts';
	}
	
	public function hasFiles(){
		return true;
	}
	
	public function hasLinks() {
		return true;
	}
	
	public function attributeLabels() {
		
		$labels = parent::attributeLabels();
		
		$labels['url_facebook'] = \GO::t("Facebook URL", "addressbook");
		$labels['url_linkedin'] = \GO::t("LinkedIn URL", "addressbook");
		$labels['url_twitter'] = \GO::t("Twitter URL", "addressbook");
		$labels['skype_name'] = \GO::t("Skype name", "addressbook");
		$labels['photo'] = \GO::t("Photo", "addressbook");
		$labels['action_date'] = \GO::t("Action date", "addressbook");
		
		return $labels;
	}
	
	public function defaultAttributes() {
		
		$ab = false;
		if(\GO::user() && \GO::user()->contact){
			$ab = Addressbook::model()->getDefault(\GO::user());
		}
		
		return array(
				'addressbook_id' => $ab ? $ab->id : null
//				'country'=>\GO::config()->default_country
		);
	}
	
	protected function init() {
		
		$this->columns['addressbook_id']['required']=true;
		$this->columns['email']['regex']=\GO\Base\Util\StringHelper::get_email_validation_regex();
		$this->columns['email2']['regex']=\GO\Base\Util\StringHelper::get_email_validation_regex();
		$this->columns['email3']['regex']=\GO\Base\Util\StringHelper::get_email_validation_regex();
		
//		$this->columns['home_phone']['gotype']='phone';
//		$this->columns['work_phone']['gotype']='phone';
//		$this->columns['cellular']['gotype']='phone';
//		$this->columns['cellular2']['gotype']='phone';
//		$this->columns['fax']['gotype']='phone';
		$this->columns['color']['gotype']='color';
		
		$this->columns['action_date']['gotype'] = 'unixtimestamp';
		
		return parent::init();
	}
	
	public function getFindSearchQueryParamFields($prefixTable = 't', $withCustomFields = true) {
		$fields = parent::getFindSearchQueryParamFields($prefixTable, $withCustomFields);
		$fields[]="CONCAT(t.first_name,' ',t.middle_name,' ',t.last_name)";	
		return $fields;
	}
	
	public function customfieldsModel() {
		
		return "GO\Addressbook\Customfields\Model\Contact";
	}

	public function relations(){
		return array(
			'goUser' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Base\Model\User', 'field'=>'go_user_id'),
			'addressbook' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Addressbook\Model\Addressbook', 'field'=>'addressbook_id'),
			'company' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Addressbook\Model\Company', 'field'=>'company_id'),
			'addresslists' => array('type'=>self::MANY_MANY, 'model'=>'GO\Addressbook\Model\Addresslist', 'field'=>'contact_id', 'linkModel' => 'GO\Addressbook\Model\AddresslistContact'),
			'vcardProperties' => array('type'=>self::HAS_MANY, 'model'=>'GO\Addressbook\Model\ContactVcardProperty', 'field'=>'contact_id', 'delete'=> true)
		);
	}
	
	public function getAttributes($outputType = 'formatted') {
		
		$attr = parent::getAttributes($outputType);
		$attr['name']=$this->getName();
		
		return $attr;
	}


	
	/**
	 *
	 * @return String Full formatted name of the user
	 */
	public function getName($sort_name=false){
		
		if(!$sort_name){
			if(\GO::user()){
				$sort_name = \GO::user()->sort_name;
			}else
			{
				$sort_name = 'first_name';
			}
		}
		
		return \GO\Base\Util\StringHelper::format_name($this->last_name, $this->first_name, $this->middle_name,$sort_name);
	}
	
	/**
	 * Get the full address formatted according to the country standards.
	 * 
	 * @return StringHelper
	 */
	public function getFormattedAddress()
	{
		return \GO\Base\Util\Common::formatAddress(
						$this->country, 
						$this->address, 
						$this->address_no,
						$this->zip, 
						$this->city, 
						$this->state
						);
	}

	protected function getCacheAttributes() {
		
		$name = $this->name;
		if($this->company)
			$name .= ' ('.$this->company->name.')';

		if($this->addressbook)
			$name .= ' ('.$this->addressbook->name.')';
			
		return array(
				'name' => $name
		);
	}
	
	protected function getLocalizedName() {
		return \GO::t("Contact", "addressbook");
	}

	/**
	 * The files module will use this function.
	 */
	public function buildFilesPath() {
		
		if(!$this->addressbook)
			return false;
		
		$new_folder_name = \GO\Base\Fs\Base::stripInvalidChars($this->name).' ('.$this->id.')';
		$last_part = empty($this->last_name) ? '' : \GO\Addressbook\Utils::getIndexChar($this->last_name);
		$new_path = $this->addressbook->buildFilesPath().'/contacts';
		if(!empty($last_part)) {
			$new_path .= '/'.$last_part;
		}else {
			$new_path .= '/0 no last name';
		}
					
		$new_path .= '/'.$new_folder_name;
		return $new_path;
	}
	
	public function beforeDelete() {
		
		if($this->goUser())			
			throw new \Exception("This contact belongs to a user account. Please delete this account first.");
		
		return parent::beforeDelete();
	}
	
	protected function afterDelete() {
		if($this->getPhotoFile()->exists())
			$this->getPhotoFile()->delete();
		
		return parent::afterDelete();
	}
	
	protected function beforeSave() {
		
		if(!empty($this->homepage))
			$this->homepage = \GO\Base\Util\Http::checkUrlForHttp($this->homepage);
		
		$this->_autoSalutation();
		
		if (strtolower($this->sex)==strtolower(\GO::t("Female", "addressbook")))
			$this->sex = 'F';
		$this->sex = $this->sex=='M' || $this->sex=='F' ? $this->sex : 'M';
		
		//Auto create company if company_id is a String and can't be found.
		if(!empty($this->company_name)){			
			$company = Company::model()->findSingleByAttributes(array(
				'addressbook_id'=>$this->addressbook_id,
				'name'=>$this->company_name
			));
			
			if(!$company)
			{
				$company = new Company();
				$company->name=$this->company_name;
				$company->addressbook_id=$this->addressbook_id;			
				$company->save();
			}			
			
			$this->company_id=$company->id;			
		}
				
		
		$this->_prefixSocialMediaLinks();
		
		if (empty($this->color))
			$this->color = "000000";
		
		if($this->isModified('location') && !empty(\GO::config()->google_api_key)) {
			$this->fetchCoords();
		}
		
		return parent::beforeSave();
	}
	
	/**
	 * Override to check if any of the location attributes are modified
	 * @param type $attributeName
	 * @return type
	 */
	public function isModified($attributeName = false) {
		if($attributeName === 'location') {
			$addressAttrs = array('address','address_no','country','city');
			$modified = false;
			foreach($addressAttrs as $attr) {
				if($this->isModified($attr)) {
					$modified = true;
					break;
				}
			}
			return $modified;
		}
		return parent::isModified($attributeName);
	}
	
	static function fetchGoogleCoords($query) {
		$key = '';
		if(!empty(\GO::config()->google_api_key)) {
			$key = 'key='.\GO::config()->google_api_key.'&';
		}
		$lang = 'language='.\GO::user()->language.'&';
		$url = "https://maps.google.com/maps/api/geocode/json?{$key}{$lang}address={$query}";
		$resp_json = @file_get_contents($url);
		return json_decode($resp_json, true);
	}
	
	public function fetchCoords() {
		$query = array();
		$addressAttrs = array('address','address_no','country','city');
		foreach($addressAttrs as $attr) {
			$val = $this->getAttribute($attr);
			if(!empty($val));
			$query[] = urlencode($val);
		}
		$resp = self::fetchGoogleCoords(implode('+', $query));
		if ($resp['status'] == 'OK') {
			$this->latitude = $resp['results'][0]['geometry']['location']['lat'];
			$this->longitude = $resp['results'][0]['geometry']['location']['lng'];
		}
		return $this;
	}
	
	private function _prefixSocialMediaLinks() {
		if ($this->isModified('url_linkedin') && !empty($this->url_linkedin) && strpos($this->url_linkedin,'http')!==0)
			$this->url_linkedin = 'http://'.$this->url_linkedin;
		if ($this->isModified('url_linkedin') && !empty($this->url_facebook) && strpos($this->url_facebook,'http')!==0)
			$this->url_facebook = 'http://'.$this->url_facebook;
		if ($this->isModified('url_linkedin') && !empty($this->url_twitter) && strpos($this->url_twitter,'http')!==0)
			$this->url_twitter = 'http://'.$this->url_twitter;
	}
	
	protected function afterDbInsert() {
		if(empty($this->uuid)){
			$this->uuid = \GO\Base\Util\UUID::create('contact', $this->id);
			return true;
		}else
		{
			return false;
		}
	}
	
	private function _autoSalutation(){
		if(empty($this->salutation)){
			$tpl = $this->addressbook->default_salutation;
			$a = $this->getAttributes();
			foreach($a as $key=>$value){
				if(is_string($value))
					$tpl = str_replace('{'.$key.'}', $value, $tpl);
			}			
			$tpl = preg_replace('/[ ]+/',' ',$tpl);
			
			preg_match('/\[([^\/]+)\/([^\]]+)]/',$tpl, $matches);
			
			if(isset($matches[0])){
				$index = $this->sex=='M' ? 1 : 2;			
				$replaceText = isset($matches[$index]) ? $matches[$index] : "";
				
				$tpl = str_replace($matches[0], $replaceText, $tpl);
			}
			
			$this->salutation=$tpl;
			
			$this->cutAttributeLength('salutation');
		}
	}
	
	protected function afterSave($wasNew) {
	
		if($wasNew && $this->addressbook->create_folder) {
			
			$c = new \GO\Files\Controller\FolderController();
			$c->checkModelFolder($this, false, true);
			
		}
		
		if(!$wasNew && $this->isModified('addressbook_id') && ($company=$this->company)){
			//make sure company is in the same addressbook.
			$company->addressbook_id=$this->addressbook_id;
			$company->save();
		}
		
		//If the _removePhotoFile property is set and the photo property is an empty string, then remove the photo file from disk.
		if(!empty($this->_removePhotoFile) && empty($this->photo)){
			$this->_removePhotoFile->delete();
		}
		
		if(!$this->skip_user_update &&  $this->isModified(array('first_name','middle_name','last_name','email')) && $this->goUser){
			$this->goUser->displayName = $this->getName('first_name');
			$this->goUser->email = $this->email;
			$this->goUser->skip_contact_update=true;
			if($this->goUser->isModified())
				$this->goUser->save(true);
		}
		
		return parent::afterSave($wasNew);
	}
	
//	/**
//	 * Set the photo
//	 * 
//	 * @param String $srcFileName The source image file name.
//	 */
//	public function setPhoto($srcFileName){
//		
//		if(!$this->id)
//			throw new \Exception("Contact must be saved before you can set a photo");
//
//		$destination = \GO::config()->file_storage_path.'contacts/contact_photos/'.$this->id.'.jpg';
//		
//		if(empty($srcFileName))
//		{
//			$file = new \GO\Base\Fs\File($this->_getPhotoPath());
//			return !$file->exists() || $file->delete();
//		}else
//		{		
//
//			$f = new \GO\Base\Fs\Folder(dirname($this->_getPhotoPath()));
//			$f->create();
//
//
//			$img = new \GO\Base\Util\Image();
//			if(!$img->load($srcFileName)){
//				throw new \Exception(\GO::t("The image you uploaded is not supported. Only gif, png and jpg images are supported.", "addressbook"));
//			}
//
//			$img->zoomcrop(90,120);
//			if(!$img->save($destination, IMAGETYPE_JPEG))
//				throw new \Exception("Could not save photo at ".$destination." from ".$srcFileName);
//		}
//	}
	
//	private function _getPhotoPath(){
//		return \GO::config()->file_storage_path.'contacts/contact_photos/'.$this->id.'.jpg';
//	}
//	
//	protected function getPhoto(){
//		if(file_exists($this->_getPhotoPath()))
//			return $this->_getPhotoPath();
//		else
//			return '';
//	}
	
	/**
	 * Get the photo file object. It always returns a file even though it doesn't
	 * exist. Use $contact->photoFile->exists() to detect that.
	 * 
	 * @return \GO\Base\Fs\File
	 */
	public function getPhotoFile(){
		if(!isset($this->_photoFile)){
			if(empty($this->photo))
				$this->photo=$this->id.'.jpg';
		
			$this->_photoFile = new \GO\Base\Fs\File(\GO::config()->file_storage_path.$this->photo);
		}
		
		return $this->_photoFile;
	}
	
	/**
	 * Get the URL to the original photo.
	 * 
	 * @return StringHelper
	 */
	public function getPhotoURL(){
		return $this->photoFile->exists() 
			? \GO::url('addressbook/contact/photo', array('id'=>$this->id,'mtime'=>$this->photoFile->mtime())) 
			: null;
	}
	
	public function getPhotoThumbURL($urlParams=array("w"=>300, "h"=>300, "zc"=>1)) {
		
		if(!$this->getPhotoFile()->exists()){
			return null;
		}
		$urlParams['filemtime']=$this->getPhotoFile()->mtime();
		$urlParams['src']=$this->getPhotoFile()->stripFileStoragePath();
		return \GO::url('core/thumb', $urlParams);	
	}
	

	
	/**
	 * Set new photo file. The file will be converted into JPEG and resized to fit
	 * a 480x640 pixel box
	 * 
	 * @param \GO\Base\Fs\File $file
	 */
	public function setPhoto(\GO\Base\Fs\File $file){
		
		if($this->isNew)
			Throw new \Exception("Cannot save a photo on a new contact that is not yet saved.");
		
		$this->getPhotoFile()->delete();
				
		$photoPath = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path.'addressbook/photos/'.$this->addressbook_id.'/');
		$photoPath->create();		
		
		
//		if(strtolower($file->extension())!='jpg'){
		$filename = $photoPath->path().'/con_'.$this->id.'.jpg';
		$img = new \GO\Base\Util\Image();
		\GO::debug($file->path());
		if(!$img->load($file->path())){
			throw new \Exception(\GO::t("The image you uploaded is not supported. Only gif, png and jpg images are supported.", "addressbook"));
		}
		
		//resize it to small image so we don't get in trouble with sync clients
		$img->fitBox(240,320);
		
		if(!$img->save($filename, IMAGETYPE_JPEG)){
			throw new \Exception("Could not save photo!");
		}
		$file = new \GO\Base\Fs\File($filename);
//		}else
//		{		
//			$file->move($photoPath, $this->id.'.'.strtolower($file->extension()));
//		}
	
		
		$this->photo=$file->stripFileStoragePath();
	}
	
	
	public function removePhoto(){
		$this->getPhotoFile()->delete();
		$this->photo="";
	}
	
	/**
	 * Import a contact (with or without company) from a VObject 
	 * 
	 * @param Sabre\VObject\Component $vobject
	 * @param array $attributes Extra attributes to apply to the contact. Raw values should be past. No input formatting is applied.
	 * @return Contact
	 */
	public function importVObject(Sabre\VObject\Component $vobject, $attributes=array(),$saveToDb=true,$ignoreInvalidProperties=true) {
		//$event = new \GO\Calendar\Model\Event();
		$companyAttributes = array();
//		if (!empty($attributes['addressbook_id'])) {
//			$companyAttributes['addressbook_id'] = $attributes['addressbook_id'];
//		} 
		
		$uid = (string) $vobject->uid;
		if(!empty($uid) && empty($attributes['uuid']))
			$attributes['uuid'] = $uid;
		
		$emails = array();
		
		// Is the PHOTO attribute set as Vcard property?
		$photoAttrSet = false;
		
		//clear all supported attributes and only set them when found.
		$attributes['work_phone']=
						$attributes['cellular']=
						//$attributes['cellular2']=
						$attributes['fax']=
						$attributes['work_fax']=
						$attributes['home_phone']=
						
						$attributes['last_name']=
						$attributes['first_name']=
						$attributes['middle_name']=
						$attributes['suffix']=
						$attributes['title']=
						$attributes['department']=
						$attributes['address']=
						$attributes['address_no']=
						$attributes['city']=
						$attributes['zip']=
						$attributes['state']=
						$attributes['function']=
						$attributes['birthday']=
						$attributes['homepage']=
						$attributes['email']=
						$attributes['email2']=
						$attributes['email3']=
						null;
		
		
		foreach ($vobject->children() as $vobjProp) {
			
			// Set this variable to true when the PHOTO attribute is set.
			if($vobjProp->name == 'PHOTO'){
				$photoAttrSet = true;
			}
			
			switch ($vobjProp->name) {
				case 'PHOTO':					
					if($vobjProp->getValue()){
						if($vobjProp->getValueType() === 'URI') { //vCard 4.0 uses URI type with base64 (no binary)
							$data = $vobjProp->getValue();
							$data = str_replace('data:image/jpeg;base64,','',$data); // Todo: work for other formats
							
							$photoFile = \GO\Base\Fs\File::tempFile('','jpg');
							$photoFile->putContents(base64_decode($data));
						} else {
							$photoFile = \GO\Base\Fs\File::tempFile('','jpg');
							$photoFile->putContents($vobjProp->getValue());
						}
					}
					break;
				case 'N':
					$nameArr = explode(';',$vobjProp->getValue());
					if(isset($nameArr[0]))
						$attributes['last_name'] = $nameArr[0];
					if(isset($nameArr[1]))
						$attributes['first_name'] = $nameArr[1];
					
					
					
					$attributes['middle_name'] = !empty($nameArr[2]) ? $nameArr[2] : '' ;
					$attributes['suffix'] = !empty($nameArr[4]) ? $nameArr[4] : '' ;
					$attributes['title'] = !empty($nameArr[3]) ? $nameArr[3] : '' ;
					break;
				case 'ORG':
					$companyAttributes['name'] =  null;
					if ($vobjProp->getValue()) {
						$compNameArr = explode(';',$vobjProp->getValue());
						if (!empty($compNameArr[0]))
							$companyAttributes['name'] = $compNameArr[0];
						if (!empty($compNameArr[1]))
							$attributes['department'] = $compNameArr[1];
						if (!empty($compNameArr[2]))
							$companyAttributes['name2'] = $compNameArr[2];
					}
					break;
//				case 'TITLE':
//					$attributes['title'] = $vobjProp->getValue() ? $vobjProp->getValue() : null;
//					break;
				case 'TEL':

					
					if($vobjProp->getValue()){
						$types = array();
						foreach ($vobjProp->parameters as $param) {
							if ($param->name=='TYPE'){
								$types = explode(',',strtolower($param->getValue()));							
							}
						}
						
						if(in_array('work',$types) && ( in_array('voice',$types) || count($types)==1 || in_array('pref',$types)) ) {
							$attributes['work_phone'] = $vobjProp->getValue();
							$companyAttributes['phone'] = $vobjProp->getValue();
						}
						if(in_array('cell',$types) && ( in_array('voice',$types) || count($types)==1 || in_array('pref',$types)) ) {
							if (empty($attributes['cellular']))
								$attributes['cellular'] = $vobjProp->getValue();
							elseif (empty($attributes['cellular2']))
								$attributes['cellular2'] = $vobjProp->getValue();
						}
						if(in_array('fax',$types) && !in_array('work',$types))
							$attributes['fax'] = $vobjProp->getValue();
						if(in_array('fax',$types) && in_array('work',$types)) {
							$companyAttributes['fax'] = $vobjProp->getValue();
							$attributes['work_fax'] = $vobjProp->getValue();
						}
						if(in_array('home',$types) && ( in_array('voice',$types) || count($types)==1 || in_array('pref',$types)) )
							$attributes['home_phone'] = $vobjProp->getValue();
						
						
						if(empty($types) || in_array('pref',$types)) {
							// TODO: if $types unknown add phone number to field in priority order or by X-ABLabel
							//$label = $vobject->{$vobjProp->group . 'X-ABLABEL'};
	
							$phoneFields = ['cellular','home_phone','cellular2','work_phone','fax','work_fax'];
							foreach($phoneFields as $field) {
								if(!isset($attributes[$field])) {
									$attributes[$field] = $vobjProp->getValue();
									break;
								}
							}
						}
						
					}
//					foreach ($vobjProp->parameters as $param) {
//						if ($param['name']=='TYPE') {
//							switch (susbstr($param['value'],0,4)) {
//								case 'work':
//									$attributes['work_phone'] = $vobjProp->getValue();
//									break;
//								default:
//									$attributes['home_phone'] = $vobjProp->getValue();
//									break;
//							}
//						}
//					}
					break;
//				case 'LABEL':
				case 'ADR':
					$types = array();
					
					
					foreach ($vobjProp->parameters as $param) {
						if ($param->name=='TYPE')
							$types = explode(',',strtolower($param->getValue()));			
					}
					

					
					if(in_array('work',$types)) {
						$addrArr = explode(';',$vobjProp->getValue());
						if(isset($addrArr[2]))
							$companyAttributes['address'] = $addrArr[2];
						if(isset($addrArr[3]))
							$companyAttributes['city'] = $addrArr[3];
						if(isset($addrArr[4]))
							$companyAttributes['state'] = $addrArr[4];
						if(isset($addrArr[5]))
							$companyAttributes['zip'] = $addrArr[5];						
						if(isset($addrArr[6]))
							$companyAttributes['country'] = $addrArr[6];
					}
					if(in_array('home',$types)) {						
					
						$addrArr = explode(';',$vobjProp->getValue());	
						if(isset($addrArr[2]))
							$attributes['address'] = $addrArr[2];
						if(isset($addrArr[3]))
							$attributes['city'] = $addrArr[3];
						if(isset($addrArr[4]))
							$attributes['state'] = $addrArr[4];
						if(isset($addrArr[5]))
							$attributes['zip'] = $addrArr[5];
						if(isset($addrArr[6]))
							$attributes['country'] = $addrArr[6];
					}
					
					
					
					if(empty($types)){
						$addrArr = explode(';',$vobjProp->getValue());
						if(isset($addrArr[2]))
							$companyAttributes['post_address'] = $addrArr[2];
						if(isset($addrArr[3]))
							$companyAttributes['post_city'] = $addrArr[3];
						if(isset($addrArr[4]))
							$companyAttributes['post_state'] = $addrArr[4];
						if(isset($addrArr[5]))
							$companyAttributes['post_zip'] = $addrArr[5];						
						if(isset($addrArr[6]))
							$companyAttributes['post_country'] = $addrArr[6];
					}
					break;
				case 'EMAIL':
//					foreach ($vobjProp->parameters as $param) {
//						if ($param->name=='TYPE')
//							$types = explode(',',strtolower($param->getValue()));
//						else
//							$types = array();
//					}
//					if(in_array('pref',$types)) {
//						$attributes['email'] = $vobjProp->getValue();
//					} elseif(in_array('home',$types)) {
//						$attributes['email2'] = $vobjProp->getValue();
//					} elseif(in_array('work',$types)) {
//						$attributes['email3'] = $vobjProp->getValue();
//					} else {
//						$attributes['email'] = $vobjProp->getValue();
//					}
					if($vobjProp->getValue())
						$emails[]=$vobjProp->getValue();
					break;
				case 'TITLE':
					$attributes['function'] = $vobjProp->getValue();
					break;
				case 'BDAY':
					if($vobjProp->getValue()) {
						// is already formatted in GO\Base\VObject\Reader::convertVCard21ToVCard30
						// $attributes['birthday'] = substr($vobjProp->getValue(),0,4).'-'.substr($vobjProp->getValue(),5,2).'-'.substr($vobjProp->getValue(),8,2);
						$attributes['birthday'] = $vobjProp->getValue();
					}
					break;			
					
				case 'URL':
					$attributes['homepage'] = $vobjProp->getValue();
					break;
				
				case 'NOTE':
					$attributes['comment'] = $vobjProp->getValue();
					break;
				
				case 'GENDER':
					$attributes['sex'] = $vobjProp->getValue() == 'F' ? 'F' : 'M';
					break;
				case 'VERSION':
				case 'LAST-MODIFIED':
					break;
				default:
					$paramsArr = array();
					foreach ($vobjProp->parameters as $param) {
						$paramsArr[] = $param->serialize();
					}
//					$remainingVcardProps[] = array('name' => $vobjProp->name, 'parameters'=>implode(';',$paramsArr), 'value'=>$vobjProp->getValue());					
					break;
			}
		}
		
		if(!$photoAttrSet){
			$this->removePhoto();
		}
		
		
		foreach($emails as $email){
			if(!isset($attributes['email']))
				$attributes['email']=$email;
			elseif(!isset($attributes['email2']))
				$attributes['email2']=$email;
			elseif(!isset($attributes['email3']))
				$attributes['email3']=$email;
		}
		
		//some attributes can be specified with multiple values like tel and email.
		//We don't know which value is going to map to which exact GO attribute because every client handles this differently.
		//Clear the values if they haven't been found at all.
		//
		// Not clearing them cause some client might not send it and this can cause data loss.
//		$attributesMultiple=array('home_phone','work_phone','fax', 'work_fax','cellular','email','email2','email3');
//		foreach($attributesMultiple as $attributeName){
//			if(!isset($attributes[$attributeName]))
//				$attributes[$attributeName]="";
//		}
		
		$attributes=array_map('trim',$attributes);
		
		$attributes = $this->_splitAddress($attributes);
		
		if(empty($attributes['last_name']) && empty($attributes['first_name']))
			$attributes['first_name']='unnamed';
		
		if($attributes['birthday'] == '')
			$attributes['birthday'] = null;

		$this->setAttributes($attributes, false);		
				
		if (isset($companyAttributes['name'])) {
			$company = Company::model()->findSingleByAttributes(array('name' => $companyAttributes['name'], 'addressbook_id' => $this->addressbook_id));
			if (!$company) {
				$company = new Company();
				$company->setAttributes($companyAttributes, false);
				$company->addressbook_id = $this->addressbook_id;
			}else {
				//Only set empty properties when updating!				
				foreach($companyAttributes as $name=>$value) {
					if(empty($company->$name)) {
						$company->$name = $value;
					}
				}
			}

			if (!empty($saveToDb))
				$company->save();

			$this->setAttribute('company_id', $company->id);
		}
		
		$this->cutAttributeLengths();
		
		if($ignoreInvalidProperties){
			$this->ignoreInvalidProperties();
		}
		
		if (!empty($saveToDb))
			$this->save();
		
		
		if (!empty($photoFile) && $saveToDb){			
			$this->setPhoto($photoFile);
			$this->save();
		}
		
		
		
//		foreach ($remainingVcardProps as $prop) {
//			if (!empty($this->id) && substr($prop['name'],0,2)=='X-') {
//				// Process encounters a custom property name in the VCard.
//				$arr = explode('-',$prop['name']);
//				$currentPropName = 'X-'.$arr[1];
//				if (!in_array($currentPropName,$deletedPropertiesPrefixes_nonGO)) {
//					// Process encounters a new custom property prefix in the VCard.
//					// Now deleting all properties with this contact that have this prefix.
//					// Because of $deletedPropertiesPrefixes_nonGO, this is only done once
//					// per sync per VCard.
//					$deletablePropertiesStmt = ContactVcardProperty::model()->find(
//						\GO\Base\Db\FindParams::newInstance()->criteria(
//							\GO\Base\Db\FindCriteria::newInstance()
//								->addCondition('contact_id',$this->id)
//								->addCondition('name',$currentPropName.'-%','LIKE')
//						)
//					);
//
//					while ($delPropModel = $deletablePropertiesStmt->fetch())
//						$delPropModel->delete();
//
//					$deletedPropertiesPrefixes_nonGO[] = $currentPropName; // Keep track of prefixes for which we have deleted the properties.
//				}
//			}
//			
//			$propModel = ContactVcardProperty::model()->find(
//				\GO\Base\Db\FindParams::newInstance()
//					->single()
//					->criteria(
//						\GO\Base\Db\FindCriteria::newInstance()
//							->addCondition('contact_id',$this->id)
//							->addCondition('name',$prop['name'])
//							->addCondition('parameters',$prop['parameters'])
//					)
//				);
//			if (empty($propModel))
//				$propModel = new ContactVcardProperty();
//			$propModel->contact_id = $this->id;
//			$propModel->name = $prop['name'];
//			$propModel->parameters = $prop['parameters'];
//			$propModel->value = $prop['value'];
//			$propModel->cutAttributeLengths();
//			$propModel->save();
//		}
		
		return $this;
	}
	
	private function _splitAddress($attributes){
		if(isset($attributes['address'])){
			$attributes['address_no']='';
			$attributes['address']=  \GO\Base\Util\StringHelper::normalizeCrlf($attributes['address'], "\n");
			$lines = explode("\n", $attributes['address']);
			if(count($lines)>1){
				$attributes['address']=$lines[0];
				$attributes['address_no']=$lines[1];
			}else
			{
				$address = $this->_getAddress($lines[0]);
				if(!empty($address)) {
					$attributes['address']=$this->_getAddress($lines[0]);
					$attributes['address_no']=$this->_getAddressNo($lines[0]);
				}
			}
		}
		
		return $attributes;
	}
	
	/**
	* Gets the street name from address.
	*
	* @param  string	$address Contains the address (street-name and house-number)
	* @access private
	* @return StringHelper
	*/
	function _getAddress($address) {
		if (!$address = substr($address, 0, strrpos($address, " "))) {
			return '';
		}

		return trim($address);
	}

	/**
	* Gets the house-number from address.
	*
	* @param  string	$address Contains the address (street-name and house-number)
	* @access private
	* @return StringHelper
	*/
	function _getAddressNo($address) {
		if (!$address_no = strrchr($address, " ")) {
			return '';
		}

		return trim($address_no);
	}

		/**
	 * Get this task as a VObject. This can be turned into a vcard file data.
	 * 
	 * @return Sabre_VObject_Component 
	 */
	public function toVObject($card=null){
		
		if(!isset($card)) {
			$card=new Sabre\VObject\Component\VCard();
			//couldn't get photo to work in 4.0.
			//see https://github.com/fruux/sabre-vobject/issues/294
			$card->version = '3.0'; // this is what is written to vCard file
			
		} else if((string) $card->version == '4.0') { //only convert v4 to v3
			$card = $card->convert(\Sabre\VObject\Document::VCARD30);
		}
				
		$card->prodid='-//Intermesh//NONSGML Group-Office '.\GO::config()->version.'//EN';		
		
		if(empty($this->uuid)){
			$this->uuid=\GO\Base\Util\UUID::create('contact', $this->id);
			$this->save(true);
		}
		
		$card->uid=$this->uuid;
		
		$card->remove('N');
		$card->remove('FN');
		
		$card->add('N',array($this->last_name,$this->first_name,$this->middle_name,$this->title,$this->suffix));
		$card->add('FN',$this->name);
		
		
		$card->remove('gender');
		$card->add('gender',$this->sex);
		
		$card->remove('email');
		if (!empty($this->email)) {
//			$p = new Sabre\VObject\Property('EMAIL',$this->email);
//			$p->add(new \GO\Base\VObject\Parameter('TYPE','INTERNET'));
//			$e->add($p);
			$card->add('email',$this->email, array('type'=>array('INTERNET')));
			
		}
		if (!empty($this->email2)) {
//			$p = new Sabre\VObject\Property('EMAIL',$this->email2);
//			$p->add(new \GO\Base\VObject\Parameter('TYPE','HOME,INTERNET'));
//			$e->add($p);
			
			$card->add('email',$this->email2, array('type'=>array('HOME','INTERNET')));
		}
		if (!empty($this->email3)) {
//			$p = new Sabre\VObject\Property('EMAIL',$this->email3);
//			$p->add(new \GO\Base\VObject\Parameter('TYPE','WORK,INTERNET'));
//			$e->add($p);
			
			$card->add('email',$this->email3, array('type'=>array('WORK','INTERNET')));
		}
		
		
		$card->remove('TITLE');
		if (!empty($this->function))
			$card->add('TITLE',$this->function);
		
		
		$card->remove('TEL');
		if (!empty($this->home_phone)) {
//			$p = new Sabre\VObject\Property('TEL',$this->home_phone);
//			$p->add(new \GO\Base\VObject\Parameter('TYPE','HOME,VOICE'));
//			$e->add($p);
			
			$card->add('TEL',$this->home_phone, array('type'=>array('HOME','VOICE')));
		}
		if (!empty($this->work_phone)) {
//			$p = new Sabre\VObject\Property('TEL',$this->work_phone);
//			$p->add(new \GO\Base\VObject\Parameter('TYPE','WORK,VOICE'));
//			$e->add($p);	
			
			$card->add('TEL',$this->work_phone, array('type'=>array('WORK','VOICE')));
		}
		if (!empty($this->work_fax)) {
//			$p = new Sabre\VObject\Property('TEL',$this->work_fax);
//			$p->add(new \GO\Base\VObject\Parameter('TYPE','WORK,FAX'));
//			$e->add($p);	
			
			$card->add('TEL',$this->work_fax, array('type'=>array('WORK','FAX')));
		}
		

		if (!empty($this->fax)) {
//			$p = new Sabre\VObject\Property('TEL',$this->fax);
//			$p->add(new \GO\Base\VObject\Parameter('TYPE','HOME,FAX'));
//			$e->add($p);	
			
			$card->add('TEL',$this->fax, array('type'=>array('HOME','FAX')));
		}
		
		if (!empty($this->cellular)) {
//			$p = new Sabre\VObject\Property('TEL',$this->cellular);
//			$p->add(new \GO\Base\VObject\Parameter('TYPE','CELL,VOICE'));
//			$e->add($p);	
			
			$card->add('TEL',$this->cellular, array('type'=>array('CELL','VOICE')));
		}
		
		
		if (!empty($this->cellular2)) {
//			$p = new Sabre\VObject\Property('TEL',$this->cellular2);
//			$p->add(new \GO\Base\VObject\Parameter('TYPE','CELL,VOICE'));
//			$e->add($p);	
			
			$card->add('TEL',$this->cellular2, array('type'=>array('CELL','VOICE')));
		}
		
		$card->remove('BDAY');
		if (!empty($this->birthday)) {
			$card->add('BDAY',$this->birthday);
		}
		
		$card->remove('URL');
		if (!empty($this->homepage)) {
			$card->add('URL',$this->homepage);
		}
		
		$card->remove('ORG');
		$card->remove('ADR');
		
		
		if (!empty($this->company)) {
//			$e->add('ORG',$this->company->name,$this->department,$this->company->name2);
//			$p = new Sabre\VObject\Property('ADR',';;'.$this->company->address.' '.$this->company->address_no,
//				$this->company->city,$this->company->state,$this->company->zip,$this->company->country);
//			$p->add('TYPE','WORK');
//			$e->add($p);
			
			$card->add('ORG',array($this->company->name,$this->department,$this->company->name2));
			$card->add('ADR',array('','',$this->company->address.' '.$this->company->address_no,$this->company->city,$this->company->state,$this->company->zip,$this->company->country),array('type'=>'WORK'));
			
//			$p = new Sabre\VObject\Property('ADR',';;'.$this->company->post_address.' '.$this->company->post_address_no,
//				$this->company->post_city,$this->company->post_state,$this->company->post_zip,$this->company->post_country);
//			$e->add($p);
			$card->add('ADR',array('','',$this->company->post_address.' '.$this->company->post_address_no,
				$this->company->post_city,$this->company->post_state,$this->company->post_zip,$this->company->post_country),array('type'=>'WORK,POSTAL'));
			
		}
		
//		$p = new Sabre\VObject\Property('ADR',';;'.$this->address.' '.$this->address_no,
//			$this->city,$this->state,$this->zip,$this->country);
//		$p->add('TYPE','HOME');
//		$e->add($p);
//		
		$card->add('ADR',array('','',$this->address.' '.$this->address_no,
			$this->city,$this->state,$this->zip,$this->country),array('type'=>'HOME'));
		
		if(!empty($this->comment)){
			$card->note=$this->comment;
		}  else {
			$card->remove('note');
		}
		
//		$mtimeDateTime = new \DateTime('@'.$this->mtime);
//		$rev = new Sabre_VObject_Element_DateTime('LAST-MODIFIED');
//		$rev->setDateTime($mtimeDateTime, Sabre_VObject_Element_DateTime::UTC);		
//		$e->add($rev);
		
		$card->rev=gmdate("Y-m-d\TH:m:s\Z", $this->mtime);
		
		$card->remove('photo');
		if($this->getPhotoFile()->exists()){
			$card->add('photo', $this->getPhotoFile()->getContents(),array('type'=>'JPEG','encoding'=>'b'));	
////			$p = new \Sabre\VObject\Property\Uri($card, 'photo');
////			$p->setValue();
//			$card->add('photo', base64_encode($this->getPhotoFile()->getContents()), array('value'=>'URI'));
			
		}

		
//		$propModels = $this->vcardProperties->fetchAll(PDO::FETCH_ASSOC);
//		
//		foreach ($propModels as $propModel) {
//			$p = new Sabre\VObject\Property($propModel['name'],$propModel['value']);
//			if(!empty($propModel['parameters'])){
//				$paramStrings = explode(';',$propModel['parameters']);
//				foreach ($paramStrings as $paramString) {
//					if(!empty($paramString)){
//						$paramStringArr = explode('=',$paramString);
//
//						$param = new \GO\Base\VObject\Parameter($paramStringArr[0]);
//						if (!empty($paramStringArr[1]))
//							$param->getValue() = $paramStringArr[1];
//						$p->add($param);
//					}
//				}
//			}
//			$e->add($p);
//		}
		
		return $card;
	}
	
	/**
	 * Find contacts by e-mail address
	 * 
	 * @param StringHelper $email
	 * @param \GO\Base\Db\FindParams $findParams Optional
	 * @return \GO\Base\Db\ActiveStatement 
	 */
	public function findByEmail($email, $findParams = false){
		
		if(!$findParams)
			$findParams = \GO\Base\Db\FindParams::newInstance();
		
		$findParams->getCriteria()->mergeWith(\GO\Base\Db\FindCriteria::newInstance()
										->addCondition('email', $email)
										->addCondition('email2', $email, '=', 't', false)
										->addCondition('email3', $email, '=', 't', false)
		);

		return Contact::model()->find($findParams);		
	}
	
	/**
	 * Find contacts by e-mail address
	 * 
	 * @param StringHelper $email
	 * @param \GO\Base\Db\FindParams $findParams Optional
	 * @return \GO\Base\Db\ActiveStatement 
	 */
	public function findByPhoneNumber($number, $findParams = false){
		
		
		$number=  '%'.substr($number,-9);
		
		if(!$findParams)
			$findParams = \GO\Base\Db\FindParams::newInstance();
		
		$findParams->debugSql();
		
		$findParams->getCriteria()->mergeWith(\GO\Base\Db\FindCriteria::newInstance()
										->addCondition('home_phone', $number, 'LIKE', 't', false)
										->addCondition('work_phone', $number, 'LIKE', 't', false)
										->addCondition('cellular', $number, 'LIKE', 't', false)
										->addCondition('cellular2', $number, 'LIKE', 't', false)
		);

		return Contact::model()->find($findParams);		
	}
	
	/**
	 * Find contacts by e-mail address
	 * 
	 * @param StringHelper $email
	 * @return \GO\Base\Db\ActiveStatement 
	 */
	public function findSingleByEmail($email, \GO\Base\Db\FindParams $findParams = null){
		
		$criteria = \GO\Base\Db\FindCriteria::newInstance()
			->addCondition('email',$email)
			->addCondition('email2', $email,'=','t',false)
			->addCondition('email3', $email,'=','t',false);
			
		$fp = \GO\Base\Db\FindParams::newInstance()->criteria($criteria)->limit(1);
		
		if(isset($findParams)){
			$fp->mergeWith($findParams);
		}
		
		$stmt = Contact::model()->find($fp);
		return $stmt->fetch();
	}
	
	protected function afterMergeWith(\GO\Base\Db\ActiveRecord $model) {
		
		//this contact becomes the new user contact
		if($this->go_user_id>0)
			$model->go_user_id=0;
		
		if(!$this->photo && $model->photo){
			rename($model->photo, $this->_getPhotoPath());
		}
		
		if(\GO::modules()->isInstalled("projects2")) {
			
			$findParms = \GO\Base\Db\FindParams::newInstance()
							->criteria(\GO\Base\Db\FindCriteria::newInstance()
								->addCondition('contact_id', $model->id)
							);

			$stmt = \GO\Projects2\Model\Project::model()->find($findParms);

			foreach ($stmt as $projet) {
				
				$projet->contact_id = $this->id;
				$projet->contact = $this->name;
				
				$projet->save();
			}

		}
		
		
		
		return parent::afterMergeWith($model);
	}
	
	
	protected function getFirstEmail(){
		if(!empty($this->email)){
			return $this->email;
		}elseif(!empty($this->email2)){
			return $this->email2;
		}elseif(!empty($this->email3)){
			return $this->email3;
		}else{
			return false;
		}
	}
	
	
	protected function getAge(){
		if(empty($this->birthday))
			return "";
		
		$date = new \DateTime($this->birthday);
		$diff = $date->diff(new \DateTime());
		
		return $diff->y;
	}
	


	public function getActionDate() {
		
		return \GO\Base\Util\Date::get_timestamp($this->action_date,false);
		
	}
	
}
