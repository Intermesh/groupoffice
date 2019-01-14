<?php

namespace go\modules\community\pages\model;

use DOMDocument;
use DOMXPath;
use go\core\acl\model\AclItemEntity;
use go\core\orm\Query;

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
    protected $sortOrder;

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

    protected static function defineFilters() {
	return parent::defineFilters()
			->add('siteId', function(Query $query, $value, array $filter) {
			    $query->andWhere('siteId', '=', $filter['siteId']);
			});
    }

    protected static function aclEntityClass() {
	return Site::class;
    }

    protected static function aclEntityKeys() {
	return ['siteId' => 'id'];
    }

    public function getSortOrder() {
	return $this->sortOrder;
    }

    //set sortorder. generates one if the page doesnt have one yet.
    public function setSortOrder($sortOrder) {
	if (empty($this->sortOrder)) {
	    $res = (new Query)->select('sortOrder')->from('pages_page')->where(['siteId' => $this->siteId])->orderBy(['sortOrder' => 'DESC'])->single();
	    $this->sortOrder = $res['sortOrder'] + 1;
	} else {
	    $this->sortOrder = $sortOrder;
	}
    }

    public function getPageName() {
	return $this->pageName;
    }

    //set page name and generate a slug if the page doesnt have one yet.
    public function setPageName($name) {
	$this->pageName = $name;
	if (empty($this->slug)) {
	    $this->slug = $this->parseSlug($this->slugify($name, 189));
	}
    }

    //checks if a slug already exists and alters it to prevent duplicates.
    protected function parseSlug($slug) {
	$counter = 0;
	while (true) {
	    if ($counter == 0) {
		if (self::find()->where(['slug' => $slug])->single()) {
		    $counter++;
		} else {
		    break;
		}
	    } elseif (self::find()->where(['slug' => ($slug . $counter)])->single()) {
		$counter++;
	    } else {
		break;
	    }
	}
	if ($counter != 0) {
	    return $slug . $counter;
	} else {
	    return $slug;
	}
    }

    public function getContent() {
	return $this->content;
    }

    //set content and plainContent
    public function setContent($content) {
	//parse content to remove or add relevant tag id's.
	$this->content = $this->parseContent($content);
	//keep header tags to navigate while searching?
	$this->plainContent = strip_tags($content, '<h1><h2>');
    }

    //fixes <p> id's, loops through the headers and generates id's for them.
    protected function parseContent($content) {
	$counter;

	$doc = new DOMDocument();

	//ignore duplicate id errors so cleanIds() fix them.
	libxml_use_internal_errors(true);

	//prevent loadHTML from adding doctype or tags like head and body.
	//Also adds the right encoding.
	$doc->loadHTML('<?xml encoding="utf-8" ?>' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
	$doc = $this->cleanIds($doc);
	libxml_clear_errors();
	libxml_use_internal_errors(false);


	$xpath = new DOMXPath($doc);
	$headers = $xpath->evaluate('//h1 | //h2');
	foreach ($headers as $element) {
	    $counter = 0;
	    $headerSlug = '#'. $this->slugify($element->nodeValue, 100);
	    //$newElement = $doc->createElement($element->nodeName);
	    $newElement = $element->cloneNode(true);
	    while (true) {
		if ($counter == 0) {
		    if (is_null($doc->getElementById($headerSlug))) {
			break;
		    }
		    $counter++;
		} elseif (!is_null($doc->getElementById($headerSlug . $counter))) {
		    $counter++;
		} else {
		    break;
		}
	    }
	    if ($counter != 0) {
		$newElement->setAttribute('id', $headerSlug . $counter);
	    } else {
		$newElement->setAttribute('id', $headerSlug);
	    }
	    $element->parentNode->replaceChild($newElement, $element);
	}
	//The replace is used to remove the encoding to prevent duplicates.
	//Adding the encoding once doesnt work since it can be messed with inside the html editor. Even without source edit enabled.
	
	return str_replace('<?xml encoding="utf-8" ?>', '', $doc->saveHTML());
    }

    //Removes id's from tags. 
    protected function cleanIds($DOMdocument) {
	$doc = $DOMdocument;
	$xpath = new DOMXPath($doc);
	$tags = $xpath->evaluate('//h1 | //h2 | //p');
	foreach ($tags as $element) {
	    $element->nodeValue = str_replace("\xE2\x80\x8B", "", $element->nodeValue);
	    if($element->nodeValue == ''){
		$element->parentNode->removeChild($element);
	    }elseif ($element->hasAttribute('id')) {
		$element->removeAttribute('id');
	    }
	    
	}
	return $doc;
    }

    //Removes dangerous url characters, replaces spaces with _ and limits the amount of characters.
    //default character limit is 100, max character limit for most slugs in the database is 190.
    public function slugify($input, $charLimit = 100) {
	return strtolower(preg_replace('/[ ]/', '_', mb_substr(preg_replace('/[\'\"\&\#\(\)\[\]\{\}\$\+\,\.\/\\\:\;\=\?\@\^\<\>\!\*\|\%]/', '_', $input), 0, $charLimit)));
    }

    /**
     * 
     * @param string $slug
     * @return self
     */
    public static function findBySlug($params) {
	if (isset($params['siteId'])) {
	    $whereParams = ['slug' => $params['slug'], 'siteId' => $params['siteId']];
	} else {
	    $whereParams = ['slug' => $params['slug']];
	}
	return self::find()->where($whereParams)->single();
    }

    public static function findFirstSitePage($siteId) {
	return (new Query)->select('slug')->from('pages_page')->where(['siteId' => $siteId])->orderBy(['sortOrder' => 'ASC'])->single();
    }

}
