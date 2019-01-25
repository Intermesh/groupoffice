<?php

namespace go\modules\community\pages\controller;

use go\core\jmap\EntityController;
use go\modules\community\pages\model;

/**
 * The controller for the Site entity
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Site extends EntityController {

    /**
     * The class name of the entity this controller is for.
     * 
     * @return string
     */
    protected function entityClass() {
	return model\Site::class;
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

	$site = model\Site::findBySlug($p['slug']);

	if ($site) {
	    $list = [$site];
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

    public function getFirstPage($params) {
	$p = $this->paramsGet($params);

	if (isset($p['slug'])) {
	    $siteId = model\Site::findBySlug($p['slug']);
	    if (isset($siteId->id)) {
		$name = model\Page::findFirstSitePage($siteId->id);

		$result = [
		    'accountId' => $p['accountId'],
		    'state' => $this->getState(),
		    'list' => [$name],
		    'notFound' => []
		];
	    } else {
		$result = [
		    'accountId' => $p['accountId'],
		    'state' => $this->getState(),
		    'list' => [],
		    'notFound' => [$p['slug'], $siteId]
		];
	    }
	} else {
	    $result = [
		'accountId' => $p['accountId'],
		'state' => $this->getState(),
		'list' => [],
		'notFound' => [$params]
	    ];
	}


	\go\core\jmap\Response::get()->addResponse($result);
    }

}
