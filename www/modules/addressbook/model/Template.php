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


namespace GO\Addressbook\Model;


class Template extends \GO\Base\Db\ActiveRecord{
	
	const TYPE_EMAIL=0;
	
	const TYPE_DOCUMENT=1;
	
	public static $trimOnSave = false;
	
	public $htmlSpecialChars=true;
	
	private $_defaultTags;
	private $_lineBreak;
	
	
	public $attributesFormat = 'formatted';
	
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
		return \GO::t("template", "addressbook");
	}
	
	protected function init() {
		$this->columns['content']['required']=true;
		
//		$this->addDefaultTag('contact:salutation', \GO::t("Dear Mr / Ms"));
		$this->addDefaultTag('salutation', \GO::t("Dear Mr / Ms"));
		$this->addDefaultTag('date', \GO\Base\Util\Date::get_timestamp(time(), false));
		
		return parent::init();
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
		return 'ab_email_templates';
	}
	
	private function _addTagPrefixAndRemoveEmptyValues($attributes, $tagPrefix){
		if(!empty($tagPrefix)){
			foreach($attributes as $key=>$value){
				if(!empty($value))
					$newAttributes[$tagPrefix.$key]=$value;
			}
			$attributes=$newAttributes;
		}
		return $attributes;
	}
	
	private function _getModelAttributes($model, $tagPrefix=''){
		$attributes = $model instanceof \GO\Base\Db\ActiveRecord ? $model->getAttributes($this->attributesFormat) : $model->toArray();		
		
		
		
		if(method_exists($model, 'getFormattedAddress')){
			$attributes['formatted_address']=$model->getFormattedAddress();
		}
		
		if(method_exists($model, 'getFormattedPostAddress')){
			$attributes['formatted_post_address']=$model->getFormattedPostAddress();
		}
		
		if(method_exists($model, "getCustomFields")){
			$attributes = array_merge($attributes, $model->getCustomFields());
		
			$attributes = array_map(function($a) {
				return $a instanceof \DateTime ? $a->format(GO()->getAuthState()->getUser()->getDateTimeFormat()) : $a;
			}, $attributes);
		} else	if($model->customfieldsRecord){
			$attributes = array_merge($attributes, $model->customfieldsRecord->getAttributes($this->attributesFormat));
			
			// For multiselect fields, replace the | with a ,
			$cfCols = $model->customfieldsRecord->getColumns();
			
			foreach($cfCols as $cfColName => $cfCol){
				if(isset($cfCol['customfield'])){
					$cfType = $cfCol['customfield']->customfieldtype;

					if($cfType instanceof \GO\Customfields\Customfieldtype\Select){

						$isMultiSelect = $cfType->getField()->getOption('multiselect');

						if($isMultiSelect && isset($attributes[$cfColName])){
							$attributes[$cfColName] = str_replace('|', ', ', $attributes[$cfColName]);
						}
					}
				}		
			}
		}
		$attributes = array_filter($attributes, "is_scalar"); 
		
		$attributes = $this->_addTagPrefixAndRemoveEmptyValues($attributes, $tagPrefix);
		
		return $attributes;
	}
	
	private function _getUserAttributes(){
		$attributes=array();
		
		if(\GO::user() && \GO::user()->contact){
			$attributes = array_merge($attributes, $this->_getModelAttributes(\GO::user()->contact,'user:'));
			$attributes['user:sirmadam']=\GO::user()->contact->sex=="M" ? \GO::t("sir", "addressbook") : \GO::t("madam", "addressbook");
			if(\GO::user()->contact->company){
				$attributes = array_merge($attributes, $this->_getModelAttributes(\GO::user()->contact->company,'usercompany:'));
			}
			
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
	public function replaceContactTags($content, Contact $contact, $leaveEmptyTags=false){
		
		if(\GO::modules()->customfields)
			\GO\Customfields\Model\AbstractCustomFieldsRecord::$formatForExport=true;
		
		$attributes = $leaveEmptyTags ? array() : $this->_defaultTags;
		
		if(!empty($contact->salutation))
			$attributes['salutation']=$contact->salutation;
		
		$attributes['contact:sirmadam']=$contact->sex=="M" ? \GO::t("Sir") : \GO::t("Madam");
		
		$attributes = array_merge($attributes, $this->_getModelAttributes($contact, 'contact:'));
		
		// By default this was replaced just by M or F but now it will be replaced by the whole text Male or Female.
		$attributes['contact:sex']=$contact->sex=="M" ? \GO::t("Male", "addressbook") : \GO::t("Female", "addressbook");
		
		if($contact->company)
		{
			$attributes = array_merge($attributes, $this->_getModelAttributes($contact->company, 'company:'));
		}
		
		$attributes = array_merge($attributes, $this->_getUserAttributes());
				
		if(\GO::modules()->customfields)
			\GO\Customfields\Model\AbstractCustomFieldsRecord::$formatForExport=false;
		
		return $this->_parse($content, $attributes, $leaveEmptyTags);
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
		
		if(\GO::modules()->customfields)
			\GO\Customfields\Model\AbstractCustomFieldsRecord::$formatForExport=true;
		
		$attributes = $leaveEmptyTags ? array() : $this->_defaultTags;
		
		$attributes = array_merge($attributes, $this->_getModelAttributes($model, $tagPrefix));
		
		$attributes = array_merge($attributes, $this->_getUserAttributes());
		
		if($model instanceof \GO\Base\Db\ActiveRecord) {
			$content = $this->_replaceRelations($content, $model, $tagPrefix, $leaveEmptyTags);
		}
		
		if(\GO::modules()->customfields)
			\GO\Customfields\Model\AbstractCustomFieldsRecord::$formatForExport=false;
	
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
		if(\GO::modules()->customfields)
			\GO\Customfields\Model\AbstractCustomFieldsRecord::$formatForExport=true;
		
		$attributes = $leaveEmptyTags ? array() : $this->_defaultTags;
		
		$attributes = array_merge($attributes, $this->_getUserAttributes());
		
		//$attributes['contact:salutation']=\GO::t("Dear Mr / Ms");
		
		if(\GO::modules()->customfields)
			\GO\Customfields\Model\AbstractCustomFieldsRecord::$formatForExport=false;
		
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
