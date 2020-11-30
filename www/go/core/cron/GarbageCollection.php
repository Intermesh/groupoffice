<?php
namespace go\core\cron;

use Error;
use ErrorException;
use Exception;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\model\OauthAccessToken;
use go\core\util\DateTime;
use go\core\model\CronJob;
use function GO;
use go\core\db\Query;
use go\core\event\EventEmitterTrait;
use go\core\orm\EntityType;
use GO\Base\Db\ActiveRecord;
use go\core\model\Token;
use go\core\jmap\Entity;
use go\core\orm\Query as GoQuery;
use Throwable;

/**
 * This cron job cleans up garbage
 * 
 * It should run once a day and it cleans:
 * 
 * - BLOB storage
 * - core_change sync changelog
 * 
 */
class GarbageCollection extends CronJob {

	use EventEmitterTrait;

	const EVENT_RUN = 'run';
	
	public function run() {
		$this->blobs();
		$this->change();
		$this->links();

		Token::collectGarbage();
		OauthAccessToken::collectGarbage();
		$this->tmpFiles();

		$this->fireEvent(self::EVENT_RUN);
	}

	private function tmpFiles() {
		$garbage =go()->getTmpFolder()->find(
			[
				'older' => new DateTime("-1 day"),
				'empty' => true
			]
		);

		foreach($garbage as $item) {
			$item->delete();
		}
	}

	private function blobs() {
		go()->debug("Cleaning up BLOB's");
		
		Blob::delete((new GoQuery)->where('staleAt', '<=', new DateTime()));
	
		go()->debug("Deleted stale blobs");
	}

	private function change() {

		go()->debug("Cleaning up changes");
		$date = new DateTime();
		$date->modify('-' .go()->getSettings()->syncChangesMaxAge.' days');

		go()->getDbConnection()->delete('core_change', (new Query)->where('createdAt', '<', $date))->execute();
		go()->debug("Done");
	}

	private function links() {

		go()->debug("Cleaning up links");
		// $classFinder = new ClassFinder();
		// $entities = $classFinder->findByTrait(SearchableTrait::class);
		$types = EntityType::findAll();
		foreach($types as $type) {

			try {
				if($type->getName() == "Link" || $type->getName() == "Search" || !is_a($type->getClassName(), Entity::class, true)) {
					continue;
				}

				go()->debug("Cleaning ". $type->getName());

				$cls = $type->getClassName();

				if(is_a($cls,  ActiveRecord::class, true)) {
					$tableName = $cls::model()->tableName();
				} else {
					$tableName = $cls::getMapping()->getPrimaryTable()->getName();
				}

				$query = (new Query)->select('sub.id')->from($tableName);

				$stmt = go()->getDbConnection()->delete('core_search', (new Query)
					->where('entityTypeId', '=', $cls::entityType()->getId())
					->andWhere('entityId', 'NOT IN', $query)
				);
				$stmt->execute();

				go()->debug("Deleted ". $stmt->rowCount() . " cached search results for $cls");

				$stmt = go()->getDbConnection()->delete('core_link', (new Query)
					->where('fromEntityTypeId', '=', $cls::entityType()->getId())
					->andWhere('fromId', 'NOT IN', $query)
				);
				$stmt->execute();

				go()->debug("Deleted ". $stmt->rowCount() . " links from $cls");

				$stmt = go()->getDbConnection()->delete('core_link', (new Query)
					->where('toEntityTypeId', '=', $cls::entityType()->getId())
					->andWhere('toId', 'NOT IN', $query)
				);
				$stmt->execute();

				go()->debug("Deleted ". $stmt->rowCount() . " links to $cls");
			}catch(Exception $e) {
				ErrorHandler::logException($e);
			}

		}
	}
}

