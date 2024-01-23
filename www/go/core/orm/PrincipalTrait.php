<?php
namespace go\core\orm;

use DateTime;
use Exception;
use go\core\db\Criteria;
use go\core\db\Query as OrmQuery;
use go\core\db\Statement;
use go\core\ErrorHandler;
use go\core\model\Principal;
use function go;

trait PrincipalTrait {


	public static $updatePrincipal = true;


	
	/**
	 * Map of principal attrs to entity attrs eg.
	 * [
	 *   'email'=>$this->email,
	 *   'name'=>$this->title,
	 *   'avatarId'=> $this->profilePictureBlobId,
	 *   'description'=> 'Some string',
	 *   'timeZone'=> null
	 * ]
	 * 
	 * @return string
	 */
	abstract protected function principalAttrs(): array;
	abstract protected function principalType(): string;

	protected function isPrincipalModified() {
		// override this method to only change the principal when a used property is modified
		return $this->isModified();
	}

	/**
	 * Save entity to search cache
	 *
	 * @param bool $checkExisting If certain there's no existing record then this can be set to false
	 * @return bool
	 * @throws Exception
	 */
	public function savePrincipal(bool $checkExisting = true): bool
	{



		$principal = $checkExisting ?
			Principal::find()
				->where('entityTypeId','=', static::entityType()->getId())
				->andWhere('entityId', '=', $this->id)->single()
			: false;

		if($principal && !$this->isPrincipalModified()) {
			return true;
		}

		if(!$principal) {
			$principal = new Principal();
			$principal->setEntityType(static::entityType(), $this->id);
		}

		foreach($this->principalAttrs() as $key => $value) {
			$principal->{$key} = $value;
		}
		$principal->type = $this->principalType();
		$principal->setAclId($this->findAclId());

		$principal->cutPropertiesToColumnLength();

		$isNew = $principal->isNew();
		if(!$principal->internalSave()) {
			throw new Exception("Could not save principal cache: " . var_export($principal->getValidationErrors(), true));
		}

		if(!$isNew) {
			$principal->change(true);
		}

		return true;
	}

	public static function deletePrincipal(Query $query): bool
	{

		if(!Principal::delete(
			(new Query)
				->where(['entityTypeId' => static::entityType()->getId()])
				->andWhere('entityId', 'IN', $query)
		)) {
			return false;
		}
		
		return true;
	}


	/**
	 *
	 * @param int $offset
	 * @return Statement
	 * @throws Exception
	 */
	private static function queryMissingPrincipalCache(int $offset = 0): Statement
	{
		
		$limit = 1000;


		/* @var $query OrmQuery */
		$query = static::find();
		$query
			->join("core_principal", "principal", "principal.entityId = ".$query->getTableAlias() . ".id AND principal.entityTypeId = " . static::entityType()->getId(), "LEFT")
			->andWhere('principal.entityId IS NULL')
			->limit($limit)
			->offset($offset);

		return $query->execute();
	}

	/**
	 * @throws Exception
	 */
	public static function rebuildPrincipalForEntity() {
		$cls = static::class;
		echo $cls."\n";

		if(ob_get_level() > 0) ob_flush();
		flush();

		echo "Deleting old principals\n";

		$stmt = go()->getDbConnection()->delete('core_principal', (new Query)
			->where('entityTypeId', '=', $cls::entityType()->getId())
			->andWhere('entityId', 'NOT IN', $cls::find()->selectSingleValue($cls::getMapping()->getPrimaryTable()->getAlias() . '.id'))
		);

		$stmt->execute();

		go()->getDbConnection()->exec("commit");

		echo "Deleted ". $stmt->rowCount() . " entries\n";

		//In small batches to keep memory low
		$stmt = static::queryMissingPrincipalCache();
		
		$offset = 0;
		
		//In small batches to keep memory low	
		while($stmt->rowCount()) {
			if(ob_get_level() > 0) ob_flush();
			flush();

			while ($m = $stmt->fetch()) {

				try {
					if(ob_get_level() > 0) ob_flush();
					flush();

					$m->savePrincipal(false);
					echo ".";

				} catch (Exception $e) {
					echo "Error: " . $m->id() . ' '. $m->title() ." : " . $e->getMessage() ."\n";
					ErrorHandler::logException($e);

					$offset++;
				}
			}
			echo "\n";
			go()->getDbConnection()->exec("commit");

			$stmt = static::queryMissingPrincipalCache($offset);
		}


		go()->getDbConnection()->exec("commit");


	}

}
