<?php
namespace go\modules\community\notes\cli\controller;

use go\core\Controller;
use go\core\jmap\Response;
use go\core\jmap\Router;
use go\core\orm\LoggingTrait;
use go\core\util\JSON;

class NoteBook extends Controller {

	/**
	 * ./cli.php community/notes/NoteBook/export --noteBookId=1 --format=csv
	 */
	public function export($params) {

		extract($this->checkParams($params, ['noteBookId', 'format'=>'csv']));
		$json = <<<JSON
[
  [
    "Note/query", {
      "filter": {
        "noteBookId": [$noteBookId]
      }
    },
    "call-2"
  ],
  [
    "Note/export", {
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
	 * ./cli.php community/notes/NoteBook/delete --noteBookId=1
	 */
	public function delete($params) {

		extract($this->checkParams($params, ['noteBookId']));

		$json = <<<JSON
[
  [
    "NoteBook/set", {
      "destroy": [$noteBookId]
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