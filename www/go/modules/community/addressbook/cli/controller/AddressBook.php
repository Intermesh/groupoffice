<?php
namespace go\modules\community\addressbook\cli\controller;

use go\core\Controller;
use go\core\jmap\Response;
use go\core\jmap\Router;
use go\core\orm\LoggingTrait;
use go\core\util\JSON;

class AddressBook extends Controller {

	/**
	 * ./cli.php community/addressbook/AddressBook/export --addressBookId=1 --format=csv
	 */
	public function export($addressBookId, $format = 'csv') {
		$json = <<<JSON
[
  [
    "Contact/query", {
      "filter": {
        "addressBookId": [$addressBookId]
      }
    },
    "call-2"
  ],
  [
    "Contact/export", {
      "#ids": {
        "path": "/ids",
        "resultOf": "call-2"
      },
      "extension": "$format"
    },
    "call-3"
  ],
  [
    "core/System/blob", {
    "#id": {
      "path": "/blob/id",
      "resultOf": "call-3"
    }
  },
    "call-3"
  ]
]
JSON;

		$requests = JSON::decode($json, true);

		Response::get()->jsonOptions = JSON_PRETTY_PRINT;

		$router = new Router();
		$router->run($requests);

	}

	/**
	 * /cli.php community/addressbook/AddressBook/delete --addressBookId=1
	 */
	public function delete($addressBookId) {
		$json = <<<JSON
[
  [
    "AddressBook/set", {
      "destroy": [$addressBookId]
    },
    "call-1"
  ]
]
JSON;

		$requests = JSON::decode($json, true);

		Response::get()->jsonOptions = JSON_PRETTY_PRINT;

		$router = new Router();
		$router->run($requests);

	}



}