<?php

namespace GO\Addressbook\Model;

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
 * @author WilmarVB <wilmar@intermesh.nl>
 * @property int $files_folder_id
 * @property boolean $email_allowed
 * @property int $mtime
 * @property int $muser_id
 * @property int $ctime
 * @property string $crn Company registration number
 * @property string $iban
 * @property string $vat_no
 * @property string $bank_no
 * @property string $bank_bic
 * @property string $comment
 * @property string $homepage
 * @property string $email
 * @property string $fax
 * @property string $phone
 * @property string $post_zip
 * @property string $post_country
 * @property string $post_state
 * @property string $post_city
 * @property string $post_address_no
 * @property string $post_address
 * @property string $country
 * @property string $state
 * @property string $city
 * @property string $zip
 * @property string $address
 * @property string $address_no
 * @property string $name2
 * @property string $name
 * @property int $addressbook_id
 * @property int $user_id
 * @property int $id
 * @property int $link_id
 * @property string $invoice_email
 * 
 * @property String $photo Full path to photo
 * @property String $photoURL URL to photo
 * @property string $color
 */
class Company extends \GO\Base\Db\ActiveRecord {
	
	/**
	 * Check the VAT number with the VIES service.
	 * 
	 * @var boolean
	 */
	public $checkVatNumber=false;
	

	private $_photoFile;
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Company 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	protected function getLocalizedName() {
		return \GO::t("Company", "addressbook");
	}
	
	public function aclField(){
		return 'addressbook.acl_id';	
	}
	
	public function tableName(){
		return 'ab_companies';
	}
	
	public function hasFiles(){
		return true;
	}
	
	public function hasLinks() {
		return true;
	}
	
	public function customfieldsModel() {
		
		return "GO\Addressbook\Customfields\Model\Company";
	}
	
	public function defaultAttributes() {
		$ab = \GO::user() ? Addressbook::model()->getDefault(\GO::user()) : null;
		return array(
				'addressbook_id' => $ab ? $ab->id : null,
//				'country'=>\GO::config()->default_country,
//				'post_country'=>\GO::config()->default_country
		);
	}
	
	public function attributeLabels() {
		$labels = parent::attributeLabels();
		
		$labels['postAddressIsEqual']="Post address is equal to visit address";
		$labels['link_id'] = \GO::t("Link");
		$labels['invoice_email'] = \GO::t("E-mail");
		$labels['photo'] = \GO::t("Photo", "addressbook");
		
		return $labels;
	}
	
	public function validate() {
		if(!empty($this->vat_no) && \GO\Base\Util\Validate::isEuCountry($this->post_country)){
			
			if(substr($this->vat_no,0,2)!=$this->post_country)			
				$this->vat_no = $this->post_country.' '.$this->vat_no;
			
			if($this->checkVatNumber && ($this->isModified('vat_no') || $this->isModified('post_country')) && !\GO\Base\Util\Validate::checkVat($this->post_country, $this->vat_no))
				$this->setValidationError('vat_no', 'European VAT (Country:'.$this->post_country.', No.:'.$this->vat_no.') number is invalid according to VIES. Please click <a target="_blank" href="http://ec.europa.eu/taxation_customs/vies/" target="_blank">here</a> to check it on their website.');
		}
		
		return parent::validate();
	}
	
	protected function init() {
		$this->columns['addressbook_id']['required']=true;
		$this->columns['email']['regex']=\GO\Base\Util\StringHelper::get_email_validation_regex();
		$this->columns['invoice_email']['regex']=\GO\Base\Util\StringHelper::get_email_validation_regex();
		
//		
//		$this->columns['phone']['gotype']='phone';
//		$this->columns['fax']['gotype']='phone';
		$this->columns['color']['gotype']='color';
		
		return parent::init();
	}

	public function relations(){
		return array(
			'addressbook' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Addressbook\Model\Addressbook', 'field'=>'addressbook_id'),
			'contacts' => array('type'=>self::HAS_MANY, 'model'=>'GO\Addressbook\Model\Contact', 'field'=>'company_id', 'delete'=>false),
			'addresslists' => array('type'=>self::MANY_MANY, 'model'=>'GO\Addressbook\Model\Addresslist', 'field'=>'company_id', 'linkModel' => 'GO\Addressbook\Model\AddresslistCompany'),
		);
	}


	protected function getCacheAttributes() {
		
		$name = !empty($this->name2) ? $this->name.' '.$this->name2 : $this->name;
		
		if($this->addressbook)
			$name .= ' ('.$this->addressbook->name.')';
		
		return array(
				'name' => $name
		);
	}
	/**
	 * The files module will use this function.
	 */
	public function buildFilesPath() {
		
		$new_folder_name = \GO\Base\Fs\Base::stripInvalidChars($this->name).' ('.$this->id.')';
		$new_path = $this->addressbook->buildFilesPath().'/companies';
		

		$char = \GO\Addressbook\Utils::getIndexChar($new_folder_name);
			
		$new_path .= '/'.$char.'/'.$new_folder_name;
		return $new_path;
	}
	
	protected function afterSave($wasNew) {
		
		if($wasNew && $this->addressbook->create_folder) {
			
			$c = new \GO\Files\Controller\FolderController();
			$c->checkModelFolder($this, false, true);
			
		}
		if(!$wasNew){
			
			//also update time stamp of contact for carddav
			//make sure contacts and companies are in the same addressbook.
			$whereCriteria = \GO\Base\Db\FindCriteria::newInstance()
							->addCondition('company_id', $this->id);
							
			
			$findParams = \GO\Base\Db\FindParams::newInstance()
							->ignoreAcl()
							->criteria($whereCriteria);			
			
			if($this->isModified(['name','addressbook_id', 'address', 'address_no', 'city','state','zip','country', 'post_address', 'post_address_no', 'post_city','post_state','post_zip','post_country'])) {
				$stmt = Contact::model()->find($findParams);			
				while($contact = $stmt->fetch()){
					$contact->addressbook_id=$this->addressbook_id;
					$contact->mtime = time();
					$contact->save();
				}
			}
		}		
		return parent::afterSave($wasNew);
	}
	
	protected function afterMergeWith(\GO\Base\Db\ActiveRecord $model) {
		
		//move company employees to this model too.
		if($model->className()==$this->className()){
			$stmt = $model->contacts();
			while($contact = $stmt->fetch())
			{
				$contact->company_id=$this->id;
				$contact->save();
			}
		}
		
		if(\GO::modules()->isInstalled("projects2")) {
			
			$findParms = \GO\Base\Db\FindParams::newInstance()
							->criteria(\GO\Base\Db\FindCriteria::newInstance()
								->addCondition('company_id', $model->id)
							);
			
			$stmt = \GO\Projects2\Model\Project::model()->find($findParms);
			
			foreach ($stmt as $projet) {
				$projet->company_id = $this->id;
				$projet->customer = $this->name;
				
				$projet->save();
			}
			
		}
		
		
		
		return parent::afterMergeWith($model);
	}
	
	protected function beforeSave() {
		if(!empty($this->homepage))
			$this->homepage = \GO\Base\Util\Http::checkUrlForHttp($this->homepage);
		
		if (empty($this->color))
			$this->color = "000000";
		
		if(!empty(\GO::config()->google_api_key)) {
			if($this->isLocationModified()) {
				$this->fetchCoords();
			}
			if($this->isLocationModified('post_')) {
				$this->fetchCoords('post_');
			}
		}
		
		return parent::beforeSave();
	}
	
	public function fetchCoords($type = '') {
		$query = array();
		$addressAttrs = array("{$type}address","{$type}address_no","{$type}country","{$type}city");
		foreach($addressAttrs as $attr) {
			$val = $this->getAttribute($attr);
			if(!empty($val));
			$query[] = urlencode($val);
		}
		$resp = Contact::fetchGoogleCoords(implode('+', $query));
		if ($resp['status'] == 'OK') {
			$this->setAttribute("{$type}latitude",$resp['results'][0]['geometry']['location']['lat']);
			$this->setAttribute("{$type}longitude", $resp['results'][0]['geometry']['location']['lng']);
		}
		return $this;
	}
	
	public function isLocationModified($type = '') {

		$addressAttrs = array("{$type}address","{$type}address_no","{$type}country","{$type}city");

		$modified = false;
		foreach($addressAttrs as $attr) {
			if($this->isModified($attr)) {
				$modified = true;
				break;
			}
		}
		return $modified;
		
	}
	
	/**
	 * Function to let this model copy the visit address to the post address.
	 * After this function is called, you need to call the save() function to 
	 * actually save this model. 
	 * 
	 */
	public function setPostAddressFromVisitAddress(){
		$this->post_address=$this->address;
		$this->post_address_no=$this->address_no;
		$this->post_zip=$this->zip;
		$this->post_city=$this->city;
		$this->post_country=$this->country;
		$this->post_state=$this->state;
	}
	
	
	/**
	 * Used on website registration form
	 * @return boolean
	 */
	protected function getPostAddressIsEqual(){
		return ($this->post_address==$this->address &&
		$this->post_address_no==$this->address_no &&
		$this->post_zip==$this->zip &&
		$this->post_city==$this->city &&
		$this->post_country==$this->country &&
		$this->post_state==$this->state);
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
	
	/**
	 * Get the full post address formatted according to the country standards.
	 * 
	 * @return StringHelper
	 */
	public function getFormattedPostAddress()
	{
		return \GO\Base\Util\Common::formatAddress(
						$this->post_country, 
						$this->post_address, 
						$this->post_address_no,
						$this->post_zip, 
						$this->post_city, 
						$this->post_state
						);
	}

	
	public function removePhoto(){
		$this->getPhotoFile()->delete();
		$this->photo="";
	}
	
	/**
	 * Set new photo file. The file will be converted into JPEG and resized to fit
	 * a 480x640 pixel box
	 * 
	 * @param \GO\Base\Fs\File $file
	 */
	public function seOLDPhoto(\GO\Base\Fs\File $file){
		
		if($this->isNew)
			throw new \Exception("Cannot save a photo on a new company that is not yet saved.");
		
		$this->getPhotoFile()->delete();
				
		$photoPath = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path.'company_photos/'.$this->addressbook_id.'/');
		$photoPath->create();		
		
		
//		if(strtolower($file->extension())!='jpg'){
//		$filename = $photoPath->path().'/'.$this->id.'.jpg';
//		$img = new \GO\Base\Util\Image();
//		if(!$img->load($file->path())){
//			throw new \Exception(\GO::t("The image you uploaded is not supported. Only gif, png and jpg images are supported.", "addressbook"));
//		}
//		
//		//resize it to small image so we don't get in trouble with sync clients
//		$img->fitBox(240,320);
//		
//		if(!$img->save($filename, IMAGETYPE_JPEG)){
//			throw new \Exception("Could not save photo!");
//		}
//		$file = new \GO\Base\Fs\File($filename);
//		}else
//		{		
			$file->move($photoPath, $this->id.'.'.strtolower($file->extension()));
//		}
	
		
		$this->photo=$file->stripFileStoragePath();
		
	}
	public function setPhoto(\GO\Base\Fs\File $file){
		
		if($this->isNew)
			Throw new \Exception("Cannot save a photo on a new contact that is not yet saved.");
		
		$this->getPhotoFile()->delete();
				
		$photoPath = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path.'addressbook/photos/'.$this->addressbook_id.'/');
		$photoPath->create();		
		
		
//		if(strtolower($file->extension())!='jpg'){
		$filename = $photoPath->path().'/com_'.$this->id.'.jpg';
		$img = new \GO\Base\Util\Image();
		if(!$img->load($file->path())){
			throw new \Exception(\GO::t("The image you uploaded is not supported. Only gif, png and jpg images are supported.", "addressbook"));
		}
		
		$aspectRatio = $img->getHeight() > $img->getWidth()
				? $img->getHeight() / $img->getWidth()
				: $img->getWidth() / $img->getHeight();
		
		//resize it to small image so we don't get in trouble with sync clients
		if ($img->getHeight() > $img->getWidth()) {
			$img->fitBox(320/$aspectRatio,320);
		} else {
			$img->fitBox(320,320/$aspectRatio);
		}
		
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
						? \GO::url('addressbook/company/photo', array('id'=>$this->id,'mtime'=>$this->photoFile->mtime())) 
						: null;
	}
	
	public function getPhotoThumbURL($urlParams=array("lw"=>280,"pw"=>210,"zc"=>0)) {
		
		if(!$this->getPhotoFile()->exists()){
			return null;
		}
		$urlParams['filemtime']=$this->getPhotoFile()->mtime();
		$urlParams['src']=$this->getPhotoFile()->stripFileStoragePath();
		return \GO::url('core/thumb', $urlParams);	

	}
	
}
