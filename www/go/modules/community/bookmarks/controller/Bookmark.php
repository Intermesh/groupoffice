<?php
namespace go\modules\community\bookmarks\controller;

use go\core\ErrorHandler;
use go\core\jmap\EntityController;
use go\modules\community\bookmarks\model;
use go\core\fs\Blob;
use go\core\util\StringUtil;
use Throwable;

/**
 * The controller for the Bookmark entity
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */ 
class Bookmark extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return model\Bookmark::class;
	}	
	
	/**
	 * Handles the Bookmark entity's Bookmark/query command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/query
	 */
	public function query($params) {
		return $this->defaultQuery($params);
	}
	
	/**
	 * Handles the Bookmark entity's Bookmark/get command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/get
	 */
	public function get($params) {
		return $this->defaultGet($params);
	}
	
	/**
	 * Handles the Bookmark entity's Bookmark/set command
	 * 
	 * @see https://jmap.io/spec-core.html#/set
	 * @param array $params
	 */
	public function set($params) {
		return $this->defaultSet($params);
	}
	
	
	/**
	 * Handles the Bookmark entity's Bookmark/changes command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes($params) {
		return $this->defaultChanges($params);
	}

	public function description($params) {
		//$url = $params['url'];

		$response = array();
		$response['title'] = '';
		$response['logo'] = '';
		$response['description'] = '';
				
		if (function_exists('curl_init')) {
			try{
				$c = new \go\core\http\Client();
				$c->setOption(CURLOPT_CONNECTTIMEOUT, 2);
				$c->setOption(CURLOPT_TIMEOUT, 5);
				$c->setOption(CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);




				$r = $c->get($params['url']);

				if(!empty($r['headers']['location'])) {
					$params['url'] = $r['headers']['location'];
				}

				go()->debug($response);

				//go_debug($html);

				$html = $r['body'];

				$html = str_replace("\r", '', $html);
				$html = str_replace("\n", ' ', $html);

				$html = preg_replace("'</[\s]*([\w]*)[\s]*>'", "</$1>", $html);

				preg_match('/<head>(.*)<\/head>/i', $html, $match);
				if (isset($match[1])) {
					$html = $match[1];

					if(isset($r['header']['content-type'])) {
						preg_match('/charset=([^"\'>]*)/i', $r['header']['content-type'], $match);

						if (isset($match[1])) {

							$charset = strtolower(trim($match[1]));
							if ($charset != 'utf-8')
								$html = StringUtil::cleanUtf8($html, $charset);
						}
					}

					preg_match_all('/<meta[^>]*>/i', $html, $matches);

					$description = '';
					foreach ($matches[0] as $match) {
						if (stripos($match, 'description')) {
							$name_pos = stripos($match, 'content');
							if ($name_pos) {
								$description = substr($match, $name_pos + 7, -1);
								$description = trim($description, '="\'/ ');
								break;
							}
						}
					}
					//replace double spaces
					$response['description'] = preg_replace('/\s+/', ' ', $description);

					preg_match('/<title>(.*)<\/title>/i', $html, $match);
					$response['title'] = $match ? preg_replace('/\s+/', ' ', trim($match[1])) : '';
				}
			}
			catch(Throwable $e){
				ErrorHandler::logException($e, "Failed to fetch description for " . $params['url'] );
			}

			try{
				$r = $c->get("https://www.google.com/s2/favicons?domain=" . $params['url']);

				$contents = $r['body'];
				
				if (!empty($contents) && $r['status'] != 404) {
					$filename = str_replace('.', '_', preg_replace('/^https?:\/\//', '', $params['url'])) . '.ico';
					$filename = rtrim(str_replace('/', '_', $filename), '_ ');
					$blob = Blob::fromString($contents);
					$blob->name = $filename;
					$blob->type = "image/x-icon";
					if(!$blob->save()) {
						$errors = $blob->getValidationErrors();
					}
					$response['logo'] = $blob->id;
				}
			}
			catch(Throwable $e){
				$response['logo'] = '';
				ErrorHandler::logException($e, "Failed to logo description for " . $params['url'] );
			}
		}

		$response['url'] = $params['url'];
		$response['title'] = StringUtil::cutString($response['title'], 64, true, "");
		$response['description'] = StringUtil::cutString($response['description'], 255, true, "");
		return $response;
	}

	/**
	 * updates all logos to a blob
	 *
	 */
	public static function updateLogos() {

		$results = go()->getDbConnection()->select("*")->from("bm_bookmarks")->where('category_id IN (select id from bookmarks_category)');

		foreach($results as $result) {
			$data = [];
			$data['id'] = $result["id"];
			$data['categoryId'] = $result["category_id"];
			$data['createdBy'] = $result["user_id"];
			$data['name'] = $result["name"];		
			$data['content'] = $result["content"];
			$data['description'] = $result["description"];
			$data['logo'] = $result["logo"];
			$data['openExtern'] = $result["open_extern"];
			$data['behaveAsModule'] = $result["behave_as_module"];
			$publicIcon = $result["public_icon"];

			if($publicIcon == 0) {
				$file = \go()->getDataFolder()->getFile($data['logo']);
			} else {
				$file = \go()->getEnvironment()->getInstallFolder()->getFile("modules/bookmarks/" . $data['logo']);
			}

			if($file->exists()) {
				$blob = Blob::fromFile($file);			
				if(!$blob->save()) {
					$errors = $blob->getValidationErrors();
				}
				$data['logo'] = $blob->id;
			} else{
				unset($data['logo']);
			}
			go()->getDbConnection()->replace('bookmarks_bookmark', $data)->execute();
		}
	}
}

