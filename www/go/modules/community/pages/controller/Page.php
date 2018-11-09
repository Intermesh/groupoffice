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
	
	public function getTree($siteId){
	    $r = [];
	    $query = (new Query)->select()->from('pages_page')->where(['siteId' => $siteId['siteId']])->orderBy(['sortOrder' => 'ASC'])->all();
	    $currentPage;
	    $currentHeader;
	    $doc = new \DOMDocument();
	    $counter = 1;
	    foreach ($query as $page) {
		$currentHeader = null;
		$currentPage = ['id' => $counter, 'name' => $page['pageName']];
		$counter++;
		$doc->loadHTML($page['content']);
		$xpath = new \DOMXPath($doc);
		$headers = $xpath->evaluate('//h1 | //h2');

		foreach($headers as $element){

		    if ($element->tagName == 'h1') {
			if(isset($currentHeader)){
			    $currentPage['items'][] = $currentHeader;
			}
			$currentHeader = ['id' => $counter, 'name' => $element->nodeValue];
			$counter++;
		    } else {
			if(isset($currentHeader)){
			    $currentHeader['items'][] = ['id' => $counter, 'name' => $element->nodeValue];
			}else{
			    $currentPage['items'][] = ['id' => $counter, 'name' => $element->nodeValue];
			}
			$counter++;
		    };
		};
		if(isset($currentHeader)){
			    $currentPage['items'][] = $currentHeader;
		}
		$r[] = $currentPage;
	    };
	    \go\core\jmap\Response::get()->addResponse(json_encode($r));
	}
	}

