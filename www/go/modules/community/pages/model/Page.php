<?php

namespace go\modules\community\pages\model;

use go\core\acl\model\AclItemEntity;

/**
 * Page model
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Page extends AclItemEntity {

    /**
     * 
     * @var int
     */
    public $siteId;

    /**
     * 
     * @var int
     */
    public $id;

    /**
     * 
     * @var int
     */
    public $createdBy;

    /**
     * 
     * @var int
     */
    public $modifiedBy;

    /**
     * 
     * @var \IFW\Util\DateTime
     */
    public $createdAt;

    /**
     * 
     * @var \IFW\Util\DateTime
     */
    public $modifiedAt;

    /**
     * 
     * @var string
     */
    protected $pageName = 'page';

    /**
     * 
     * @var string
     */
    protected $content;

    /**
     * 
     * @var int
     */
    public $sortOrder;

    /**
     * 
     * @var string
     */
    public $plainContent;

    /**
     * 
     * @var string
     */
    public $slug;

    protected static function defineMapping() {
	return parent::defineMapping()
			->addTable("pages_page");
    }

    public static function filter(\go\core\db\Query $query, array $filter) {
	if (isset($filter['siteId'])) {
	    $query->andWhere('siteId', '=', $filter['siteId']);
	}

	return parent::filter($query, $filter);
    }

    protected static function aclEntityClass() {
	return Site::class;
    }

    protected static function aclEntityKeys() {
	return ['siteId' => 'id'];
    }

    public function getPageName() {
	return $this->pageName;
    }

    public function setPageName($name) {
	$this->pageName = $name;
	if (empty($this->slug)) {
	    
	//todo: char limit op de slug zetten
	    $this->slug = $this->slugify($name, 160);
	}
    }

    public function getContent() {
	return $this->content;
    }
    public function setContent($content) {
	$this->content = $this->parseContent($content);
	if (empty($this->plainContent)) {
	    //keep header tags to navigate while searching.
	    $this->plainContent = strip_tags($content, '<h1><h2>');
	}
    }
    
    protected function parseContent($content){
	$cleanedContent = $this->cleanTextIds($content);
	$path = $this->getSlugPath();
	$counter;
	
	$doc = new \DOMDocument();
	$doc->loadHTML($cleanedContent);
	$xpath = new \DOMXPath($doc);
	$headers = $xpath->evaluate('//h1 | //h2');
	foreach($headers as $element){
	    if(!$element->hasAttribute('id')){
	    $counter = 0;
	    $headerSlug = $this->slugify($element->nodeValue, 100);
	    $newElement = $doc->createElement($element->nodeName);
	    while(true){
		if($counter == 0){
		    if(is_null($doc->getElementById($headerSlug))){
			break;
		    }
		    $counter++;
		}elseif (!is_null($doc->getElementById($headerSlug . $counter))) {
		    $counter++;
		}else{
		    break;
		}
	    }
	    if($counter != 0){
	    $newElement->setAttribute('id',$path . $headerSlug . $counter);
	    }else{
	    $newElement->setAttribute('id',$path . $headerSlug);
	    }
	    $newElement->nodeValue = $element->nodeValue;
	    $element->parentNode->replaceChild($newElement, $element);
	}
	}
	return $doc->saveHTML();
    }
    //Removes id's from <p> tags. 
    //Changing a <h> to a <p> does not remove the id.
    //This method is used to fix that.
    protected function cleanTextIds($content){
	$doc = new \DOMDocument();
	$doc->loadHTML($content);
	$text = $doc->getElementsByTagName('p');
	foreach($text as $element){
	    if($element->hasAttribute('id')){
		$element->removeAttribute('id');
		$changedElement = $element;
		$element->parentNode->replaceChild($changedElement, $element);
	    }
	}
	return $doc->saveHTML();
    }
    
    //Generates The current page url based on the slug of the site and page.
    //used to generate linkable id's for page headers.
    protected function getSlugPath(){
	$siteSlug = (new \go\core\db\Query)->select('slug')->from('pages_site')->where(['id' => $this->siteId])->single();
	$slugPath = $siteSlug['slug'] . '/view/'. $this->slug . '/#';
	return $slugPath;
    }
    
    //Removes dangerous url characters, replaces spaces with _ and limits the amount of characters.
    //default character limit is 100, max character limit for most slugs in the database is 190.
    protected function slugify($input, $charLimit = 100){
	return strtolower(preg_replace('/[ ]/', '_', 
		mb_substr(preg_replace('/[\&\#\(\)\[\]\{\}\$\+\,\.\/\\\:\;\=\?\@\^\<\>\!\*\|\%]/', '', $input),0,$charLimit)));
    }

    /**
     * 
     * @param string $slug
     * @return self
     */
    public static function findBySlug($slug) {
	return self::find()->single();
    }

}
