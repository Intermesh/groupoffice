<?php
namespace go\core\cron;

use Exception;
use go\core\fs\Blob;
use go\core\util\DateTime;
use go\core\model\CronJob;
use function GO;
use go\core\db\Query;
use go\core\event\EventEmitterTrait;
use go\core\orm\EntityType;
use GO\Base\Db\ActiveRecord;

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

		$this->fireEvent(self::EVENT_RUN);
	}

	private function blobs() {
		go()->debug("Cleaning up BLOB's");
		$blobs = Blob::find()->where('staleAt', '<=', new DateTime())->execute();

		foreach($blobs as $blob)
		{
			if(!$blob->delete()) {
				throw new Exception("Could not delete blob!");
			}
		}		
			
		go()->debug("Deleted ". $blobs->rowCount() . " stale blobs");
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

			if($type->getName() == "Link" || $type->getName() == "Search") {
				continue;
			}

			go()->debug("Cleaning ". $type->getName());

			$cls = $type->getClassName();

			if(is_a($cls,  ActiveRecord::class, true)) {
				$tableName = $cls::model()->tableName();
			} else{
				$tableName = array_values($cls::getMapping()->getTables())[0]->getName();
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

		}
	}
}

