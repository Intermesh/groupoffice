<?php
/**
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @package GO.modules.Site
 * @version $Id: Content.php 7607 2013-03-27 15:36:16Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 */
 
/**
 * The Content model
 *
 * @package GO.modules.Site
 * @version $Id: Content.php 7607 2013-03-27 15:36:16Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 *
 * @property int $id
 * @property int $user_id
 * @property int $ctime
 * @property int $mtime
 * @property String $title
 * @property String $slug
 * @property String $meta_title
 * @property String $meta_description
 * @property String $meta_keywords
 * @property String $content
 * @property int $status
 * @property int $parent_id
 * @property int $site_id
 * @property int $sort_order
 * @property int $ptime
 * @property string $default_child_template
 * 
 * @method Content model()
 */
namespace GO\Site\Model;

use GO;

require_once (GO::config()->root_path.'modules/site/components/Site.php');


use Michelf\MarkdownExtra;
use GO\Base\Util\TagParser;
use GO\Site\Model\Site As SiteModel;

class Content extends \GO\Base\Db\ActiveRecord{
	
	use \go\core\orm\CustomFieldsTrait;

//	private $_cf=array();	
	
//	private static $fields;
	
//	public $parentslug;
//	public $baseslug;

	
//	protected function afterLoad() {
//	
//		$this->_loadSlug();
//		
//		return parent::afterLoad();
//	}
	
//	protected function afterCreate() {
//		$this->_loadSlug();
//		return parent::afterCreate();
//	}
	
//	private function _loadSlug(){
//		if($this->isNew && $this->parent){
//			$this->parentslug=$this->parent->slug.'/';
//			$this->baseslug="";
//		}  else {
//			
//		
//			if(($pos = strrpos($this->slug, "/"))){
//				$this->parentslug=substr($this->slug,0, $pos+1);
//			}else {
//				$this->parentslug="";
//			}
//		
//			$this->baseslug=basename($this->slug);
//		}
//	}
	
	public function getParentslug(){
		if($this->isNew){
			return $this->parent && !empty($this->parent->slug) ? $this->parent->slug.'/' : '';
		}  else {		
			if(($pos = strrpos($this->slug, "/"))){
				return substr($this->slug,0, $pos+1);
			}else {
				return "";
			}
		}
	}
	
	public function getBaseslug(){
		return basename($this->slug);
	}
	
	public function setBaseslug($slug){
	
		$this->slug = $this->parentslug.$slug;
	}
	
//	private function _loadCf(){
//			//load cf
//		if(!isset(self::$fields)){
//			$fields = \GO\Customfields\Model\Field::model()->findByModel('GO\Site\Model\Content', false);
//			self::$fields=array();
//			foreach($fields as $field){
//				self::$fields[$field->name]= $field;
//			}
//		}		
//	}
	
//	public function __get($name) {
//		
//		$isAttr = isset($this->columns[$name]);
//		
//		if(!$isAttr)
//			$this->_loadCf();
//		
//		if(!$isAttr && isset(self::$fields[$name])){
//			return $this->getCustomFieldValueByName($name);
//		}  else {
//			return parent::__get($name);
//		}
//
//	}
	
//	public function __isset($name) {
//		
//		$isAttr = isset($this->columns[$name]);
//		
//		if(!$isAttr)
//			$this->_loadCf();
//		
//		if(!$isAttr && isset(self::$fields[$name])){
//			$var= $this->getCustomFieldValueByName($name);
//			return isset($var);
//		}  else {
//			return parent::__get($name);
//		}
//	}
	
//	
//	/*
//	 * Attach the customfield model to this model.
//	 */
//	public function customfieldsModel() {
//		return 'GO\Site\Customfields\Model\Content';
//	}
//	
	protected function init() {
		$this->columns['slug']['unique'] = array('site_id');
		parent::init();
	}
	
	/**
	 * Returns the table name
	 */
	 public function tableName() {
		 return 'site_content';
	 }

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	 public function relations() {
		 return array(
			'children' => array(
					'type' => self::HAS_MANY, 
					'model' => 'GO\Site\Model\Content', 
					'field' => 'parent_id', 
					'delete' => self::DELETE_RESTRICT, 
					'findParams' =>\GO\Base\Db\FindParams::newInstance()->select('*')->order(array('sort_order','ptime'))
			),
			'site'=>array('type'=>self::BELONGS_TO, 'model'=>"GO\Site\Model\Site", 'field'=>'site_id'),
			'parent'=>array('type'=>self::BELONGS_TO, 'model'=>"GO\Site\Model\Content", 'field'=>'parent_id','findParams' =>\GO\Base\Db\FindParams::newInstance()->select('*'))
		 );
	 }
	 
	 /**
	  * Find a content item by it's slug (and siteId)
	  * 
	  * @param StringHelper $slug
	  * @param int $siteId
	  * @return Content
	  * @throws \GO\Base\Exception\NotFound
	  */
	 public static function findBySlug($slug, $siteId=false){
		 
		 if(!$siteId)
			$siteId = \Site::model()->id;
		 
//		 if(!$siteId)
//			$model = self::model()->findSingleByAttribute('slug', $slug);
//		 else
			$model = self::model()->findSingleByAttributes(array('slug'=>$slug,'site_id'=>$siteId));
		 
		 if(!$model)
			 return false;
			 //Throw new \GO\Base\Exception\NotFound('There is no page found with the slug: '.$slug);
		 
		 return $model;
	 }
	 
	 /**
	  * Get the url to this content item.
	  * 
	  * @param StringHelper $route parameter can be set when you have "special" 
	  * controller actions to handle your content
	  * @return StringHelper
	  */
	 public function getUrl($route='site/front/content'){
		 
		// var_dump($this->slug);
		 if(empty($this->slug)){
			return \Site::urlManager()->createUrl($route); 
		 }else{
			return \Site::urlManager()->createUrl($route,array('slug'=>$this->slug));
		 }
	 }
	 
	 /**
	  * Return the first child or false if it doesn't have one.
	  * 
	  * @return \GO\Site\Model\Site
	  */
	 public function firstChild(){
		 return $this->children(\GO\Base\Db\FindParams::newInstance()->single());
	 }
	 
	 /**
	  * Check if this content item has children
	  * 
	  * @return boolean
	  */
	 public function hasChildren(){
		 $child = $this->firstChild();
		 return !empty($child); 
	 }
	 
	 /**
	  * Check if the given content model is an ancestor of this content model
	  * 
	  * @param Content $parent
	  * @return boolean
	  */
	 public function isChildOf(Content $parent){
		 return strpos($this->slug, $parent->slug)===0;
	 }
	 
	 /**
	  * Check if this contentitem has a parent
	  * 
	  * @return boolean
	  */
	 public function hasParent(){
		 return !empty($this->parent_id);
	 }
	 
	 public function setDefaultTemplate() {
		 if(empty($this->template) && !empty($this->parent->default_child_template)){
			$this->template = $this->parent->default_child_template;
		 }else{
			$config = new \GO\Site\Components\Config($this->site);
			$this->template = $config->getDefaultTemplate();
		 }
	 }
	 
	 
	 public function getTreeNodeAttributes(){
		 
		 $hasChildren = $this->hasChildren();
		 
		 return array(
					'id' => $this->site_id.'_content_'.$this->id,
					'site_id'=>$this->site_id,
					'content_id'=>$this->id,
					'slug'=>$this->slug,
					'cls' => 'site-node-content',
					'iconCls' => 'go-model-icon-GO_Site_Model_Content', 
					//'expanded' => !$hasChildren,
					'expanded' => !$hasChildren || \GO\Site\Model\Site::isExpandedNode($this->site_id.'_content_'.$this->id),
					'hasChildren' => $hasChildren,
					'children'=> $hasChildren ? $this->getChildrenTree() : array(),
					'text' => $this->title,
					'qtip'=>'Drag me to the editor to create a link.'
			);
	 }
	 
	 /**
	  * # Backend Functionality
	  * 
	  * Get the tree array for the children of the current item
	  * 
	  * @return array
	  */
	 public function getChildrenTree(){
		 $tree = array();
		 $children = $this->children;
		 		 	 
		 foreach($children as $child){		 
			$tree[] = $child->getTreeNodeAttributes();
		 }
		 
		 return $tree;
	 }
	 
//	 public function getCustomFieldValueByName($cfName){
//		 
//		if(!key_exists($cfName, $this->_cf)){
//			
////			$column = $this->getCustomfieldsRecord()->getColumn(self::$fields[$cfName]->columnName());
////			if(!$column)
////				return null;
//
//			$value = $this->getCustomfieldsRecord()->{self::$fields[$cfName]->columnName()};
//
//			$this->_cf[$cfName]=$value;
//
//		}
//
//		return $this->_cf[$cfName];
//	 }
	 
	 public function beforeValidate() {
		 parent::beforeValidate();
		 if(empty($this->ptime)){
			 $this->ptime = time();
		 }
		 
//		 if(!empty($this->parent_id)){
//			$this->slug = $this->parent->slug.'/'.$this->baseslug;
//		 }else{
//			$this->slug = $this->baseslug;
//		 }
		 
	 }
	 
	 
	 /**
	  * Get a short text from this contentitem
	  * 
	  * @param int $length
	  * @param boolean $cutwords
	  * @param StringHelper $append
	  * @return StringHelper
	  */
	 public function getShortText($length=100,$cutwords=false,$append='...'){
		 
//		 $text = \GO\Base\Util\StringHelper::html_to_text($this->content);
		 
		 $text = \GO\Base\Util\StringHelper::cut_string($this->content,$length,!$cutwords,$append);
		 $html = MarkdownExtra::defaultTransform($text);
		 return strip_tags($html);
	 }
	 
	 protected function afterSave($wasNew) {
		 
//		 if($this->isModified('slug')){
			 foreach($this->children as $child){
					$slugArray = explode('/',$child->slug);
					$ownSlug = array_pop($slugArray);

					$child->slug = $child->parent->slug;
					
//					GO::debug("Parent SLUG: ".$child->slug);
					
					if(!empty($child->slug)){
						$child->slug .= '/';
					}
					
					$child->slug .= $ownSlug;
					
//					GO::debug("SLUG new: ".$child->slug);
					
					$child->save();
			 }
//		 }
		
		 return parent::afterSave($wasNew);
	 }
	 
	 public function setAttribute($name, $value, $format = false) {
		 
		 parent::setAttribute($name, $value, $format);
		 
//		 if($name=='parent_id' && !$this->loadingFromDatabase){
//			 $this->_loadSlug();
//		 }
		 
	 }
	 
	 
	 
	 protected function beforeSave() {
		 
		 if($this->isNew){
			$this->sort_order=$this->count();	
		 }
		 
		 return parent::beforeSave();
	 }
	 
	 public function getHtml() {
		$html = self::replaceContentTags($this->content);


		$site_id = $this->site_id;
		$html = preg_replace_callback('/slug:\/\/([a-z0-9\-\/]+)/', function($matches) use ($site_id) {

			$content = Content::model()->findBySlug($matches[1], $site_id);

			return $content ? $content->getUrl() : '/404-not-found';
		}, $html);

		$html = preg_replace_callback('/file:\/\/(.*\.[a-z0-9]{3,4})/', function($matches) use ($site_id) {


			return \Site::file($matches[1], false);
		}, $html);


		

		$html = MarkdownExtra::defaultTransform($html);
		
		//temp fix for tables		 
		$html = str_replace('<table>', '<table class="table table-striped">', $html);


		$html = $this->replaceMarkdownContentTags($html);


		$html = $this->replaceAutoLinks($html);

		return $html;
	}

	private function replaceAutoLinks($html){
		 
		 $al = \Site::config()->autolinks;
		 
		 $replacer = new GO\Base\Util\HtmlReplacer();
		 
		 
		 if(!empty($al)){
			 foreach($al as $word=>$slug){
				 
				 if(isset($_REQUEST['slug']) && $slug!=$_REQUEST['slug']){				 
					$model = Content::model()->findBySlug($slug, $this->site_id);

					if($model)
						$html = $replacer->replace($word, '<a href="'.$model->url.'">'.$word.'</a>', $html);
				 }
			 }
		 }
		 
		 return $html;
	 }
	 
	 private function replaceMarkdownContentTags($html) {

		$tagParser = new TagParser();
		$tagParser->tagStart = '{site:';
		$tagParser->tagEnd = '}';

		$tags = $tagParser->getTags($html);

		foreach ($tags as $tag) {
			
			$class = "GO\\Site\\Tag\\".ucfirst($tag['tagName']);
			
			if(class_exists($class)){
				$tagHtml = $class::render($tag['params'], $tag, $this);
			}else
			{
				$tagHtml = 'Error: unsuppoted tag: "'.$tag['tagName'].'".';
			}
			
			$html = str_replace($tag['outerText'], $tagHtml, $html);
		}

		return $html;
	}
	
	
	

	public static function replaceContentTags($content=''){
		 
		 $tagParser = new TagParser();
		 $tagParser->tagStart='<site:';
		 $tagParser->tagEnd='>';
		 
		 $tags = $tagParser->getTags($content);
		 
//		 var_dump($tags);
		 foreach($tags as $tag){
			 
			 switch($tag['tagName']){
				 case 'link':
						$template = self::processLink($tag['params'],$tag['outerText']);
						$content = str_replace($tag['outerText'], $template, $content);
					break;
				
				 case 'img':
					 $template = self::processImage($tag['params']);
						$content = str_replace($tag['outerText'], $template, $content);
					 break;
			 }
		 }
		
		 return $content;
	 }
	 
	 public static function processLink($linkAttr, $completeXml) {

		$html = '<a';

		switch ($linkAttr['linktype']) {
			case 'content':
				// $linkAttr['contentid'] = '1';

				if (empty($linkAttr['contentid']))
					$linkAttr['contentid'] = '0';

				$content = Content::model()->findByPk((int) $linkAttr['contentid']);

				if ($content)
					$url = $content->url;
				else
					$url = '#';

				$html .= ' href="' . $url . '"';
				break;
			case 'file':
				// $linkAttr['path'] = 'public/site/1/files/1/contract.png';

				if (empty($linkAttr['path']))
					$linkAttr['path'] = '#';

				$html .= ' href="' . \Site::file($linkAttr['path'], false) . '"';
				break;
			case 'manual':
				// $linkAttr['url'] = 'www.google.nl';

				if (empty($linkAttr['url']))
					$linkAttr['url'] = '#';

				$html .= ' href="' . $linkAttr['url'] . '"';
				break;
		}

		if (isset($linkAttr['target']))
			$html .= ' target="_blank"';

		if (!empty($linkAttr['title']))
			$html .= ' title="' . $linkAttr['title'] . '"';

		$html .= '>';

		preg_match('/(<a[^>]*>)(.*?)(<\/a>)/i', $completeXml, $matches);

		if (isset($matches[2]))
			$html .= $matches[2];
		else
			$html .= 'LINK';

		$html .= '</a>';

		return $html;
	}
	 
	 public static function processImage($imageAttr){
		 
		//		if(key_exists('width', $imageAttr)){}
		//		if(key_exists('height', $imageAttr)){}
		//		if(key_exists('zoom', $imageAttr)){}
		//		if(key_exists('crop', $imageAttr)){}
		//		if(key_exists('alt', $imageAttr)){}
		//		if(key_exists('a', $imageAttr)){}
		//		if(key_exists('path', $imageAttr)){}
		//		if(key_exists('align', $imageAttr)){}		 
		 
		$html = '';
		
		if(key_exists('path', $imageAttr)){
			if( key_exists('width', $imageAttr) && key_exists('height', $imageAttr)){
				if(key_exists('crop', $imageAttr))
					$thumb = \Site::thumb($imageAttr['path'],array("lw"=>$imageAttr['width'], "ph"=>$imageAttr['height'], "zc"=>1));
				else
					$thumb = \Site::thumb($imageAttr['path'],array("lw"=>$imageAttr['width'], "ph"=>$imageAttr['height'], "zc"=>0));
				if(isset($imageAttr['link_to_original'])){
					$imageAttr['href'] = \Site::file($imageAttr['path'],false); // Create an url to the original image
					$imageAttr['data-lightbox']=$imageAttr['path'];
				}
				
			} else {
				$thumb = \Site::file($imageAttr['path']);
			}
			
			$html .= '<img src="'.$thumb.'"';
			
			if(key_exists('alt', $imageAttr))
				$html .= ' alt="'.$imageAttr['alt'].'"';
			
			if(key_exists('align', $imageAttr))
				$html .= ' style="'.$imageAttr['align'].'"';
			else
				$html .= ' style="display:inline-block;"';
			
			$html .= ' />';
			
			if(key_exists('href', $imageAttr)){
				$target='';
				if(isset($imageAttr['target'])){
					$target = ' target="'.$imageAttr['target'].'"';
				}
				
				$a = '<a ';
				
				if(isset($imageAttr['data-lightbox'])){
					$a .= 'data-lightbox="'.$imageAttr['data-lightbox'].'"';
				}
				
				$a .= 'href="%s"'.$target.'>%s</a>';
				
			 $html = sprintf($a,$imageAttr['href'],$html);
			}
		}
		 return $html;
	 }
	 
	 /**
	  * Get the meta title of this content item.
	  * 
	  * @return StringHelper
	  */
	 public function getMetaTitle(){
		 if(!empty($this->meta_title))
			 return $this->meta_title;
		else
			return $this->title;
	 }
	 
	  public static function setTreeSort($extractedParent,$sortOrder,$allowedTypes){
		 
		 $sort = 0;
		 
		 foreach($sortOrder as $sortItem){
			 
			 $extrChild = \GO\Site\SiteModule::extractTreeNode($sortItem);
			 
			 if(in_array($extrChild['type'],$allowedTypes)){
				 
				 $modelName = \GO\Site\SiteModule::getModelNameFromTreeNodeType($extrChild['type']);
				 
				 $model = $modelName::model()->findByPk($extrChild['modelId']);
				 $model->parent_id = !empty($extractedParent['modelId'])?$extractedParent['modelId']:NULL;
				 $model->sort_order = $sort;
				 if($model->save())				 
					$sort++;
			 }
		 }
		 
		 return array("success"=>true);
	 }
	 
}
