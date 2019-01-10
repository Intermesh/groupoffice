<?php

namespace go\modules\community\pages\controller;

use go\core\jmap\EntityController;
use go\modules\community\pages\model;
use go\core\db\Query;

/**
 * The controller for the Page entity
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Page extends EntityController {

    /**
     * The class name of the entity this controller is for.
     * 
     * @return string
     */
    protected function entityClass() {
	return model\Page::class;
    }

    public function get($params) {

	if (isset($params['slug'])) {
	    $this->getBySlug($params);
	} else {
	    parent::get($params);
	}
    }

    private function getBySlug($params) {

	$p = $this->paramsGet($params);

	$page = model\Page::findBySlug($p);
	if ($page) {
	    $list = [$page];
	    $notFound = [];
	} else {
	    $list = [];
	    $notFound = [$params['slug']];
	}
	$result = [
	    'accountId' => $p['accountId'],
	    'state' => $this->getState(),
	    'list' => $list,
	    'notFound' => $notFound
	];

	\go\core\jmap\Response::get()->addResponse($result);
    }

    protected function paramsGet(array $params) {
	$p = parent::paramsGet($params);

	if (!isset($p['slug'])) {
	    $p['slug'] = [];
	}

	return $p;
    }

    public function getHeaders($params) {
	//todo: error's afvangen
	$r['items'] = [];
	$pageSlug = $params['pageSlug'];
	$query = (new Query)->select()->from('pages_page')->where(['slug' => $pageSlug])->single();
	$currentHeader;
	$doc = new \DOMDocument();
	$counter = 1;
	$currentHeader = null;
	$doc->loadHTML('<?xml encoding="utf-8" ?>' . $query['content'] , LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
	$xpath = new \DOMXPath($doc);
	$headers = $xpath->evaluate('//h1 | //h2');

	foreach ($headers as $element) {

	    if ($element->tagName == 'h1') {
		if (isset($currentHeader)) {
		    $r['items'][] = $currentHeader;
		}
		$currentHeader = ['name' => $element->nodeValue, 'slug' => $this->parseSlug($element, $pageSlug), 'isPage' => false];
		$counter++;
	    } else {
		if (isset($currentHeader)) {
		    $currentHeader['items'][] = ['name' => $element->nodeValue, 'slug' => $this->parseSlug($element, $pageSlug), 'isPage' => false];
		} else {
		    $r['items'][] = ['name' => $element->nodeValue, 'slug' => $this->parseSlug($element, $pageSlug), 'isPage' => false];
		}
		$counter++;
	    };
	};
	if (isset($currentHeader)) {
	    $r['items'][] = $currentHeader;
	};
	\go\core\jmap\Response::get()->addResponse($r);
    }

    private function parseSlug($element, $pageSlug) {
	//Get the slug based on the header id and remove zero width spaces.
	$slug = str_replace("\xE2\x80\x8B", "", $element->getAttribute('id'));
	$slug = $pageSlug . '/' . $slug;
	
	return $slug;
    }

}
