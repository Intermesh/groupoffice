<?php
namespace go\core\orm;

use Exception;
use go\core\db\Statement;
use go\core\ErrorHandler;
use go\core\model\Principal;
use function go;

trait PrincipalTrait {

	/**
	 * Map of principal attrs to entity attrs eg.
	 * [
	 *   'email'=>$this->email,
	 *   'name'=>$this->title, // required
	 *   'description'=> 'Some string', // required
	 *   'avatarId'=> $this->profilePictureBlobId,
	 *   'timeZone'=> null
	 * ]
	 * 
	 * @return string
	 */
	abstract protected function principalAttrs(): array;
	abstract protected function principalType(): string;

	protected function isPrincipalModified(): bool
	{
		// override this method to only change the principal when a used property is modified
		return $this->isModified() !== false;
	}

	protected function isPrincipal(): bool
	{
		return true;
	}

	public bool $savePrincipal = true;

	/**
	 * Save principal
	 *
	 * @param bool $checkExisting If certain there's no existing record then this can be set to false
	 * @return bool Returns true if was created and false if it was not needed.
	 * @throws Exception
	 */
	public function savePrincipal(bool $checkExisting = true): bool
	{
		if(!$this->savePrincipal || !$this->isPrincipal() || !$this->isFetchedComplete()) {
			return false;
		}
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
			$principal->type = $this->principalType();
			$principal->setEntityType(static::entityType(), $this->id);
		}

		foreach($this->principalAttrs() as $key => $value) {
			$principal->{$key} = $value;
		}

		$principal->setAclId($this->findAclId());

		$principal->cutPropertiesToColumnLength();

		if(!$principal->internalSave()) {
			throw new Exception("Could not save principal cache: " . var_export($principal->getValidationErrors(), true));
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
	protected static function queryMissingPrincipals(int $offset = 0): Query
	{
		
		$limit = 1000;

		/* @var $query Query */
		$query = static::find();
		$query
			->join("core_principal", "principal",
				"principal.entityId = ".$query->getTableAlias() . ".id AND principal.entityTypeId = " . static::entityType()->getId(), "LEFT")
			->andWhere('principal.entityId IS NULL')
			->limit($limit)
			->offset($offset);

//		echo $query ."\n";

		return $query;
	}

	/**
	 * @throws Exception
	 */
	public static function rebuildPrincipalForEntity(): void
	{
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
		echo "Deleted ". $stmt->rowCount() . " entries\n";

		//In small batches to keep memory low
		$stmt = static::queryMissingPrincipals()->execute();
		$offset = 0;
		
		//In small batches to keep memory low	
		while($stmt->rowCount()) {
			if(ob_get_level() > 0) ob_flush();
			flush();

			while ($m = $stmt->fetch()) {

				try {
					if(ob_get_level() > 0) ob_flush();
					flush();

					if(!$m->savePrincipal(false)) {
						echo "S";
						$offset++;
					} else {
						echo ".";
					}

				} catch (Exception $e) {
					echo "Error: " . $m->id() . ' '. $m->title() ." : " . $e->getMessage() ."\n";
					ErrorHandler::logException($e);

					$offset++;
				}
			}
			echo "\n";
			$stmt = static::queryMissingPrincipals($offset)->execute();
		}

	}
}
