<?php

namespace go\core\controller;

use go\core\event\Listeners;
use go\core\jmap\Response;
use go\core\jmap\Router;
use go\core\orm\Query;
use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase {

	/**
	 * Makes the search query slower than the time limit of the test
	 */
	public static function slowDownQuery($controller, $params, Query $query): void
	{
		$query->andWhere('SLEEP(1) = 0');
	}

	/**
	 * A search that runs longer than "searchMaxExecutionTime" must be aborted by the database and
	 * return a "queryTimeout" error instead of running on.
	 */
	public function testSearchQueryTimeout(): void
	{
		$config = go()->getConfig();
		go()->setConfig(array_merge($config, ['searchMaxExecutionTime' => 0.2]));

		Search::on(Search::EVENT_BEFORE_QUERY, self::class, 'slowDownQuery');

		try {
			$start = microtime(true);
			ob_start();
			(new Router())->run([['Search/query', ['limit' => 20], 'c1']], false);
			ob_end_clean();
			$duration = microtime(true) - $start;

			$responses = Response::get()->getData();
			$response = end($responses);

			$this->assertSame('error', $response[0], json_encode($response));
			$this->assertSame('queryTimeout', $response[1]['type']);
			$this->assertLessThan(1, $duration, "The query must be aborted before SLEEP(1) ends");
		} finally {
			go()->setConfig($config);
			// Drop the request-only listener so it doesn't leak into other tests.
			Listeners::get()->clear();
		}
	}
}
