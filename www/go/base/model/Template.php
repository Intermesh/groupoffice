<?php
/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @package GO.modules.addressbook.model
 * @version $Id: example.php 7607 20120101Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */
 
/**
 * The Template model
 *
 * @package GO.modules.addressbook.model
 * @property string $extension
 * @property string $content
 * @property int $acl_id
 * @property string $name
 * @property int $type
 * @property int $user_id
 * @property int $id
 * @property int $acl_write
 */


namespace GO\Base\Model;


use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\Date;

class Template extends \GO\Base\Db\ActiveRecord{
	
	const TYPE_EMAIL=0;
	
	const TYPE_DOCUMENT=1;
	
	public static $trimOnSave = false;
	
	public $htmlSpecialChars=true;
	
	private $_defaultTags;
	private $_lineBreak;
	
	
	public static $attributesFormat = 'formatted';
	
		/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Template 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	// TODO : move language from mailings module to addressbook module
	protected function getLocalizedName() {
		return \GO::t("template");
	}
	
	protected function init() {
		$this->columns['content']['required']=true;
		
//		$this->addDefaultTag('contact:salutation', \GO::t("Dear sir/madam"));
		$this->addDefaultTag('salutation', \GO::t("Dear sir/madam"));
		$this->addDefaultTag('date', \GO\Base\Util\Date::get_timestamp(time(), false));
		
		return parent::init();
	}

	private static $dateFormat;

  /**
   * Get the user's date format
   *
   * @return string
   */
	private static function getDateFormat() {
	  if(!isset(self::$dateFormat)) {
	    $user = go()->getAuthState()->getUser(['dateFormat', 'timeFormat']);
      self::$dateFormat = isset($user) ? $user->dateFormat : 'd-m-Y';
      self::$timeFormat = isset($user) ? $user->timeFormat : 'H:i';
    }

	  return self::$dateFormat;
  }

  private static $timeFormat;

  /**
   * Get the user's time format
   *
   * @return string
   */
  private static function getTimeFormat() {
    if(!isset(self::$timeFormat)) {
      self::getDateFormat();
    }

    return self::$timeFormat;
  }
	
	protected function getPermissionLevelForNewModel() {
		return \GO\Base\Model\Acl::MANAGE_PERMISSION;
	}
	
	/**
	 * Add a default tag value.
	 * 
	 * @param StringHelper $key
	 * @param StringHelper $value 
	 */
	public function addDefaultTag($key, $value){
		$this->_defaultTags[$key]=$value;
	}
	
	public function setLineBreak($lb){
		$this->_lineBreak=$lb;
	}
	
	
	public function aclField(){
		return 'acl_id';	
	}
	
	public function tableName(){
		return 'go_templates';
	}
	
	private static function _addTagPrefixAndRemoveEmptyValues($attributes, $tagPrefix){
		$newAttributes = [];
		if(!empty($tagPrefix)){
			foreach($attributes as $key=>$value){
				if(!empty($value))
					$newAttributes[$tagPrefix.$key]=$value;
			}
			$attributes=$newAttributes;
		}
		return $attributes;
	}
	
	public static function getCompanyAttributes(\go\modules\community\addressbook\model\Contact $company, $tagPrefix = 'company:'){
		$attributes[$tagPrefix . 'salutation'] = $company->getSalutation();
		
		$attributes[$tagPrefix . 'comment'] = $company->notes;
		$attributes[$tagPrefix . 'crn'] = $company->registrationNumber;
		$attributes[$tagPrefix . 'vat_no'] = $company->vatNo;
		$attributes[$tagPrefix . 'iban'] = $company->IBAN;
		$attributes[$tagPrefix . 'homepage'] = $company->findUrlByType(\go\modules\community\addressbook\model\Url::TYPE_HOMEPAGE, false)->url ?? "";
		
		
			$a = $company->findAddressByType(\go\modules\community\addressbook\model\Address::TYPE_VISIT, true);
			if($a) {				
				$attributes[$tagPrefix . 'address'] = $a->street;
				$attributes[$tagPrefix . 'address_no'] = $a->street2;
				$attributes[$tagPrefix . 'zip'] = $a->zipCode;
				$attributes[$tagPrefix . 'country'] = $a->country;
				$attributes[$tagPrefix . 'state'] = $a->state;
				$attributes[$tagPrefix . 'city'] = $a->city;
				
				$attributes[$tagPrefix . 'formatted_address'] = $a->getFormatted();
			}
			
			$a = $company->findAddressByType(\go\modules\community\addressbook\model\Address::TYPE_POSTAL, true);
			if($a) {				
				$attributes[$tagPrefix . 'post_address'] = $a->street;
				$attributes[$tagPrefix . 'post_address_no'] = $a->street2;
				$attributes[$tagPrefix . 'post_zip'] = $a->zipCode;
				$attributes[$tagPrefix . 'post_country'] = $a->country;
				$attributes[$tagPrefix . 'post_state'] = $a->state;
				$attributes[$tagPrefix . 'post_city'] = $a->city;
				
				$attributes[$tagPrefix . 'formatted_post_address'] = $a->getFormatted();
			}

			$attributes[$tagPrefix . 'email'] = isset($company->emailAddresses[0]) ? $company->emailAddresses[0]->email :  "";
			$attributes[$tagPrefix . 'invoice_email'] = $company->findEmailByType(\go\modules\community\addressbook\model\EmailAddress::TYPE_BILLING, true);
			
			foreach($company->phoneNumbers as $p) {
				switch($p->type) {
					
					case \go\modules\community\addressbook\model\PhoneNumber::TYPE_FAX:
						$attributes[$tagPrefix . 'fax'] = $p->number;
					break;
				
					default:
						$attributes[$tagPrefix . 'phone'] = $p->number;
					break;

				}
			}
			return $attributes;
	} 
	
	public static function getContactAttributes(Contact $contact, $tagPrefix = 'contact:', $companyTagPrefix = 'company:'){
		$attributes[$tagPrefix . 'salutation'] = $contact->getSalutation();
		$attributes[$tagPrefix . 'sirmadam']=$contact->gender=="M" ? \GO::t('sir') : \GO::t('madam');
		
		$attributes[$tagPrefix . 'first_name'] = $contact->firstName;
		$attributes[$tagPrefix . 'middle_name'] = $contact->middleName;
		$attributes[$tagPrefix . 'last_name'] = $contact->lastName;
		$attributes[$tagPrefix . 'comment'] = $contact->notes;

			//$attributes = array_merge($attributes, $this->_getModelAttributes($contact, $tagPrefix . ''));

			// By default this was replaced just by M or F but now it will be replaced by the whole text Male or Female.
			$attributes[$tagPrefix . 'sex']=$contact->gender == "M" ? \GO::t('male','addressbook') : \GO::t('female','addressbook');

			if(isset($contact->addresses[0])) {
				$a = $contact->addresses[0];

				$attributes[$tagPrefix . 'address'] = $a->street;
				$attributes[$tagPrefix . 'address_no'] = $a->street2;
				$attributes[$tagPrefix . 'zip'] = $a->zipCode;
				$attributes[$tagPrefix . 'country'] = $a->country;
				$attributes[$tagPrefix . 'city'] = $a->city;
				$attributes[$tagPrefix . 'state'] = $a->state;
				$attributes[$tagPrefix . 'formatted_address'] = $a->getFormatted();
			}

			$birthday = $contact->findDateByType(Date::TYPE_BIRTHDAY, false);
      $attributes[$tagPrefix . 'birthday'] = $birthday && isset($birthday->date) ? $birthday->date->format(static::getDateFormat()) : "";

			$attributes[$tagPrefix . 'email'] = isset($contact->emailAddresses[0]) ? $contact->emailAddresses[0]->email :  "";
			$attributes[$tagPrefix . 'email2'] = isset($contact->emailAddresses[1]) ? $contact->emailAddresses[1]->email :  "";
			$attributes[$tagPrefix . 'email3'] = isset($contact->emailAddresses[2]) ? $contact->emailAddresses[2]->email :  "";

			$attributes[$tagPrefix . 'function'] = $contact->jobTitle;
			$attributes[$tagPrefix . 'department'] = $contact->department;

			foreach($contact->phoneNumbers as $p) {
				switch($p->type) {
					case \go\modules\community\addressbook\model\PhoneNumber::TYPE_HOME:
						$attributes[$tagPrefix . 'home_phone'] = $p->number;
					break;

					case \go\modules\community\addressbook\model\PhoneNumber::TYPE_WORK:
						$attributes[$tagPrefix . 'work_phone'] = $p->number;
					break;

					case \go\modules\community\addressbook\model\PhoneNumber::TYPE_FAX:
						$attributes[$tagPrefix . 'fax'] = $p->number;
					break;

					case \go\modules\community\addressbook\model\PhoneNumber::TYPE_WORK_FAX:
						$attributes[$tagPrefix . 'work_fax'] = $p->number;
					break;

					case \go\modules\community\addressbook\model\PhoneNumber::TYPE_WORK_MOBILE:
						$attributes[$tagPrefix . 'cellular2'] = $p->number;
					break;

					case \go\modules\community\addressbook\model\PhoneNumber::TYPE_MOBILE:
						$attributes[$tagPrefix . 'cellular'] = $p->number;						
					break;
				}
			}

		$attributes[$tagPrefix . 'homepage'] = $contact->findUrlByType(\go\modules\community\addressbook\model\Url::TYPE_HOMEPAGE, false)->url ?? "";

		$orgs = $contact->getOrganizationIds();
		if(count($orgs) && ($company = \go\modules\community\addressbook\model\Contact::findById($orgs[0])))
		{
			$attributes = array_merge($attributes, static::_getModelAttributes($company, $companyTagPrefix));
		}

		return $attributes;
	} 
	
	
	
	private static function _getModelAttributes($model, $tagPrefix=''){
		$attributes = $model instanceof \GO\Base\Db\ActiveRecord ? $model->getAttributes(static::$attributesFormat) : $model->toArray();		
		
		if(method_exists($model, "getCustomFields")){
			$attributes = array_merge($attributes, $model->getCustomFields(true)->toArray());
		}

		$attributes = static::_addTagPrefixAndRemoveEmptyValues($attributes, $tagPrefix);
		
		$cls = get_class($model);
		
		switch($cls) {
			case \go\modules\community\addressbook\model\Contact::class:
				if($model->isOrganization) {
					$attributes = array_merge($attributes, static::getCompanyAttributes($model, $tagPrefix));
				} else
				{
					$attributes = array_merge($attributes, static::getContactAttributes($model, $tagPrefix));
				}
				
				break;			
		}

    $attributes = array_map(function($v) {
      if($v instanceof \DateTime) {
        //ugly but should work. If the time is not 0:00 then print it.
        $format = self::getDateFormat();

        if($v->format('Gi') > 0) {
          $format .= ' ' . self::getTimeFormat();
        }

        return $v->format($format);
      }

      return $v;
    },$attributes);
		$attributes = array_filter($attributes, "is_scalar"); 
		
		
		return $attributes;
	}
	
	private function _getUserAttributes(){
		$attributes=array();
		
		
		if(\GO::user() && ($contact = \go\modules\community\addressbook\model\Contact::findForUser(\GO::user()->id))){
			$attributes = array_merge($attributes, $contact->getCustomFields(true)->toArray());
			$attributes = $this->_addTagPrefixAndRemoveEmptyValues($attributes, 'user:');
			$attributes = array_merge($attributes, $this->getContactAttributes($contact, 'user:', 'usercompany:'));			

			$attributes['user:sirmadam']=$contact->gender=="M" ? \GO::t('Sir','addressbook', 'community') : \GO::t('Madam', 'addressbook', 'community');
			
			$company = false;
			if(isset($contact->getOrganizationIds()[0])) {
				$company = \go\modules\community\addressbook\model\Contact::findById($contact->getOrganizationIds()[0]);
			}
			if($company){
				$attributes = array_merge($attributes, $this->_getModelAttributes($company,'usercompany:'));
			}
			
			$attributes = array_merge($attributes, $this->_getModelAttributes(\GO::user(),'user:'));			
		} else if (\GO::user()) {
			$attributes = array_merge($attributes, $this->_getModelAttributes(\GO::user(),'user:'));			
		}
		return $attributes;
	}
	
	/**
	 * Replaces all contact, company and user tags in a string.
	 * 
	 * Tags look like this:
	 * 
	 * {contact:modelAttributeName}
	 * 
	 * {company:modelAttributeName}
	 * 
	 * {user:modelAttributeName}
	 * 
	 * @param StringHelper $content Containing the tags
	 * @param Contact $contact
	 * @param boolean $leaveEmptyTags Set to true if you don't want unreplaced tags to be cleaned up.
	 * @return StringHelper 
	 */
	public function replaceContactTags($content, \go\modules\community\addressbook\model\Contact $contact, $leaveEmptyTags=false){		
		return $this->replaceModelTags($content, $contact, 'contact:', $leaveEmptyTags);
	}
	
	
	/**
	 * Replaces all tags of a model.
	 * 
	 * Tags look like this:
	 * 
	 * {$tagPrefix:modelAttributeName}
	 * 
	 * @param StringHelper $content Containing the tags
	 * @param \GO\Base\Db\ActiveRecord $model
	 * @param StringHelper $tagPrefix
	 * @param boolean $leaveEmptyTags Set to true if you don't want unreplaced tags to be cleaned up.
	 * @return StringHelper 
	 */
	public function replaceModelTags($content, $model, $tagPrefix='', $leaveEmptyTags=false){
		$attributes = $leaveEmptyTags ? array() : $this->_defaultTags;
		
		$attributes = array_merge($attributes, $this->_getModelAttributes($model, $tagPrefix));
		
		$attributes = array_merge($attributes, $this->_getUserAttributes());
		
		if($model instanceof \GO\Base\Db\ActiveRecord) {
			$content = $this->_replaceRelations($content, $model, $tagPrefix, $leaveEmptyTags);
		}
		
		return $this->_parse($content, $attributes, $leaveEmptyTags);		
	}
	
	/**
	 * 
	 * Replaces relations if found in the template.
	 * eg. {project:responsibleUser:name}
	 * 
	 * @param type $content
	 * @param type $model
	 * @param type $tagPrefix
	 * @param type $leaveEmptyTags 
	 */
	private function _replaceRelations($content, $model, $tagPrefix='', $leaveEmptyTags=false){
		
		$relations = $model->getRelations();
		$pattern = '/'.preg_quote($tagPrefix,'/').'([^:]+):[^\}]+\}/';
		if(preg_match_all($pattern,$content, $matches)){
			foreach($matches[1] as $relation){
				if(isset($relations[$relation])){
					$relatedModel = $model->$relation;	

					if($relatedModel){

						$content = $this->replaceModelTags($content, $relatedModel, $tagPrefix.$relation.':', $leaveEmptyTags);
					}
				}
			}
		}
		return $content;
	}
	
	private function _parse($content, $attributes, $leaveEmptyTags){
		
		$attributes = array_merge($this->_defaultTags, $attributes);
		
		if($this->htmlSpecialChars){
			foreach($attributes as $key=>$value)
				$attributes[$key]=htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		}
		
		if(isset($this->_lineBreak)){
			foreach($attributes as $key=>$value)
				$attributes[$key]=str_replace("\n", $this->_lineBreak, $attributes[$key]);
		}
		
		$templateParser = new \GO\Base\Util\TemplateParser();
		return $templateParser->parse($content, $attributes, $leaveEmptyTags);
	}
	
	/**
	 * Replaces all tags of the current user.
	 * 
	 * Tags look like this:
	 * 
	 * {user:modelAttributeName}
	 * 
	 * @param StringHelper $content Containing the tags
	 * @param boolean $leaveEmptyTags Set to true if you don't want unreplaced tags to be cleaned up.
	 * @return StringHelper 
	 */
	public function replaceUserTags($content, $leaveEmptyTags=false){
		$attributes = $leaveEmptyTags ? array() : $this->_defaultTags;
		
		$attributes = array_merge($attributes, $this->_getUserAttributes());
		
		//$attributes['contact:salutation']=\GO::t("Dear sir/madam");
		
		return $this->_parse($content, $attributes, $leaveEmptyTags);
	}
	
	/**
	 * Replaces customtags
	 * 
	 * Tags look like this:
	 * 
	 * {$key}
	 * 
	 * @param StringHelper $content Containing the tags
	 * @param array $attributes
	 * @param boolean $leaveEmptyTags Set to true if you don't want unreplaced tags to be cleaned up.
	 * @return StringHelper 
	 */
	public function replaceCustomTags($content, $attributes, $leaveEmptyTags=false){
		return $this->_parse($content, $attributes, $leaveEmptyTags);
	}

//	/**
//	 * @return \GO\Email\Model\SavedMessage
//	 */
//	private function _getMessage(){
//		if(!isset($this->_message)){
//			
//			//todo getFromMimeData
//			$this->_message = \GO\Email\Model\SavedMessage::model()->createFromMimeData($this->content);
//
//		}
//		return $this->_message;
//	}
//	protected function getBody(){
//		return $this->_getMessage()->getHtmlBody();
//	}
	
	
	/**
	 * Replace the {link} tag with a div
	 * 
	 * @param StringHelper $content
	 * @param \GO\Email\Model\SavedMessage $message
	 * @return StringHelper
	 */
	public function replaceLinkTag($content, $message){
		
		$content = str_replace('{link}', '<span class="go-composer-link"></span>', $content);

		return $content;
	}
	
	/**
	 * Search for an image inside the ODF document with the given tag.
	 * When the tag is found, then replace the image inside the ODF file with the 
	 * replacement image.
	 *  
	 * @param StringHelper $content		The XML content file of the ODF.
	 * @param \GO\Base\Fs\Folder $extractedOdfFolder	The folder object of the extracted ODF file
	 * @param StringHelper $tag		The tag to search for in the content
	 * @param \GO\Base\Fs\File $replacementImage		The image that needs to be replaced
	 * @return StringHelper The XML content file of the ODF.
	 */
	public function replaceODFImage($content, \GO\Base\Fs\Folder $extractedOdfFolder, $tag, \GO\Base\Fs\File $replacementImage){
			
		if(!$replacementImage->exists()){
			\GO::debug('Contact has no image set for the tag: '.$tag);
			return $content;
		}
		
		// Find an xml element with the attribute draw:name="$tag"
		// If the attribute is found, then search for the attribute
		// <draw:image xlink:href="" and get the path that is stored with it.
		$reader = new \DOMDocument('1.0', 'UTF-8');
		
		$reader->preserveWhiteSpace  = false;
		$reader->loadXML($content);
		$frames = $reader->getElementsByTagName('frame');
		
		$replaceImages = array();
		
		foreach($frames as $frame){
			
			$name = $frame->getAttributeNode('draw:name');
			
			if(!empty($name) && $name->value===$tag){
				$images = $frame->getElementsByTagName('image');
				foreach($images as $image){
					$href = $image->getAttributeNode('xlink:href');

					if(!empty($href) && !empty($href->value)){
						$replaceImages[] = $href->value;
					}
				}	
			}			
		}
		
		$picturesFolder = $extractedOdfFolder->child('Pictures');
		
		foreach($replaceImages as $replaceImage){
			
			/* @var $picture \GO\Base\Fs\File */
			$picture = $picturesFolder->child(basename($replaceImage));
			
			if($picture){
				//If the image is a .jpg (or .JPG) then replace the content of $picture 
				//with the $replacementImage content (Both are \GO\Base\Fs\File objects)
				if(strtolower($picture->extension()) == 'jpg'){
					$picture->putContents($replacementImage->getContents());
				}
			}
		}

		return $content;

	}	
}
