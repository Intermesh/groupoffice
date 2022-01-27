<?php

namespace go\core\data;

use Closure;
use Countable;
use Exception;
use IteratorAggregate;
use Traversable;

/**
 * Data store object
 *
 * Create a store response with this class.
 *
 * <p>Example</p>
 * ```````````````````````````````````````````````````````````````````````````
 * public function actionStore($orderColumn='username', $orderDirection='ASC', $limit=10, $offset=0, $searchQuery=""){

  $users = User::find((new Query())
  ->orderBy([$orderColumn => $orderDirection])
  ->limit($limit)
  ->offset($offset)
  ->search($searchQuery, array('t.username','t.email'))
  );

  $store = new Store($users);


  if(isset(\go\core\App::get()->request()->post['returnProperties'])){
  $store->setReturnAttributes(\go\core\App::get()->request()->post['returnProperties']);
  }

  $store->format('specialValue', function(User $model){
  return $model->username." is special";
  });

  echo $this->view->render('store', $store);
  }
 * ```````````````````````````````````````````````````````````````````````````
 */
class Store implements IteratorAggregate, ArrayableInterface, Countable  {

	/**
	 * The traversable object in the store.
	 * 
	 * In most cases this a {@see Finder} object that contains {@see Record} models.
	 * @var Traversable
	 */
	public $traversable;
	
	private $formatters = [];


	/**
	 * Constructor of the store
	 *
	 * @param Traversable|null $traversable
	 */
	public function __construct(Traversable $traversable = null) {
		$this->traversable = $traversable;
	}

	/**
	 * Format a record attribute.
	 * 
	 * ```````````````````````````````````````````````````````````````````````````
	 * 
	 * $multiplier = 10;
	 * 
	 * $store = new Store();
	 * $store->format("multipliedAttribute", function($record) use ($multiplier) {
	 *	return $record->someAttribute * $multiplier;
	 * });
	 * 
	 * ```````````````````````````````````````````````````````````````````````````
	 *
	 * @param string $storeField Name of the field in all store records
	 * @param Closure $function The function is called with the {@see Model} as argument
	 */
	public function format(string $storeField, Closure $function) {
		$this->formatters[$storeField] = $function;
	}	

	private function formatRecord($record, $model) {
		foreach ($this->formatters as $attributeName => $function) {
			$record[$attributeName] = $function($model);
		}

		return $record;
	}


	/**
	 * Convert collection to API array
	 *
	 * {@see \go\core\data\Model::toArray()}
	 *
	 * @param array|null $properties
	 * @return array
	 */
	
	public function toArray(array $properties = null): array
	{
		$records = [];
		
		foreach ($this->getIterator() as $model) {
			if(is_array($model)){
				$record = $model;
			} else
			{
				$record = $model->toArray($properties);
			}
			$records[] = $this->formatRecord($record, $model);
		}

		return $records;
	}

	/**
	 * 
	 * @return Traversable
	 */
	public function getIterator() {
		return $this->traversable;
	}

	/**
	 * Get the next record of the store iterator
	 * 
	 * @return $this
	 * @noinspection PhpPossiblePolymorphicInvocationInspection
	 * @noinspection PhpPossiblePolymorphicInvocationInspection
	 */
	public function next(): Store
	{
		$this->getIterator()->next();
		return $this->getIterator()->current();
	}

	public function count(): int
	{
		return is_array($this->traversable) ? count($this->traversable) : iterator_count($this->traversable);
	}

}
