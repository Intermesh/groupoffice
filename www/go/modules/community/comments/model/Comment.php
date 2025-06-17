<?php
namespace go\modules\community\comments\model;

use Exception;
use GO\Base\Exception\AccessDenied;
use go\core\acl\model\AclItemEntity;
use go\core\fs\Blob;
use go\core\model\Acl;
use go\core\orm\exception\SaveException;
use go\core\model\Search;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\jmap\Entity;
use go\core\orm\SearchableTrait;
use go\core\util\ArrayObject;
use go\core\util\DateTime;
use go\core\orm\EntityType;
use GO\Base\Db\ActiveRecord;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\core\db\Criteria;

class Comment extends AclItemEntity {

	public $id;
	
	public $text;
	public $entityId;
	protected $entity;
	
	public $entityTypeId;
	
	/** @var DateTime */
	public $createdAt;
	/** @var DateTime */
	public $modifiedAt;
	public $createdBy;
	public $modifiedBy;
	/** @var DateTime */
	public $date;

	public $validateXSS = true;

	/**
	 * Label ID's
	 * 
	 * @var int[]
	 */
	public $labels;
	
	/**
	 * By default the section is NULL. This property can be used to create multiple comment blocks per entity. 
	 * This works with a 'section' filter that defaults to NULL.
	 * 
	 * @var string
	 */
	public $section;

	/**
	 *
	 * @var string[]
	 */
	protected $images = [];

	/**
	 * @var CommentAttachment[]
	 */
	public $attachments = [];


	/**
	 * The MIME message ID from the outgoing or incoming email (used in support module)
	 *
	 * @var string
	 */
	public $mimeMessageId;

	use SearchableTrait;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("comments_comment", 'c')
			->addScalar('labels', 'comments_comment_label', ['id' => 'commentId'])
			->addScalar('images', 'comments_comment_image', ['id' => 'commentId'])
			->addArray('attachments', CommentAttachment::class, ['id' => 'commentId'])
			->addQuery(
				(new Query())
					->select("e.clientName AS entity")
					->join('core_entity', 'e', 'e.id = c.entityTypeId')
		);
	}

	/**
	 * Set the entity type
	 *
	 * @param mixed $entity "note", Entity $note or Entitytype instance
	 * @throws Exception
	 *
	 * @return self
	 */
	public function setEntity($entity): Comment
	{

		if($entity instanceof Entity || $entity instanceof ActiveRecord) {
			$this->entityTypeId = $entity->entityType()->getId();
			$this->entity = $entity->entityType()->getName();
			$this->entityId = $entity->id;
			return $this;
		}
		
		if(!($entity instanceof EntityType)) {
			$entity = EntityType::findByName($entity);
		}	
		$this->entity = $entity->getName();
		$this->entityTypeId = $entity->getId();

		return $this;
	}

	public function getEntity() {
		return $this->entity;
	}
	
	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('entityId', function(Criteria $criteria, $value) {
				$criteria->where('c.entityId', '=', $value);
			})
			
			->add('entity', function(Criteria $criteria, $value) {
				$criteria->where(['e.clientName' => $value]);	
			})

			->add('section', function(Criteria $criteria, $value){
				$criteria->where(['c.section' => $value]);
			}, null);
	}
	
	public static function sort(Query $query, ArrayObject $sort): Query
	{
		if(!count($sort)) {
			$sort['c.date'] = 'ASC';
		}

		return parent::sort($query, $sort);		
	}

	private $relatedEntity;

	/**
	 * Find the entity this comment belongs to.
	 *
	 * @return Entity|ActiveRecord
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function findEntity() {

		if(!isset($this->relatedEntity)) {
			$e = EntityType::findById($this->entityTypeId);
			$cls = $e->getClassName();
			if (is_a($cls, ActiveRecord::class, true)) {
				$this->relatedEntity = $cls::model()->findByPk($this->entityId, false, true);
			} else {
				$this->relatedEntity = $cls::findById($this->entityId);
			}
		}

		return $this->relatedEntity;
	}

	public function findAclEntity()
	{
		return $this->findEntity();
	}

	/**
	 * Find comments for a given entity
	 *
	 * Note: this method finds all comments regardless of the {@see $section}
	 *
	 *
	 * @example To filter by section:
	 * ```
	 * $comments = Comment::findForEntity($entity)->where('section', '=', 'foo');
	 * ```
	 *
	 * @param ActiveRecord|Entity $entity
	 * @param array $properties
	 * @return Query<$this>
	 * @throws Exception
	 */
	public static function findForEntity(ActiveRecord|Entity $entity, array $properties = []) : Query {
		$entityTypeId = $entity->entityType()->getId();
		$entityId = $entity->id;

		return self::find($properties)->where(['entityTypeId' => $entityTypeId, 'entityId' => $entityId]);
	}

	/**
	 * Get the permission level of the current user
	 *
	 * @return int
	 */
	protected function internalGetPermissionLevel(): int
	{

		if(go()->getAuthState()->isAdmin()) {
			return Acl::LEVEL_MANAGE;
		}

		if($this->isNew()) {
			return $this->findEntity()->getPermissionLevel() ? Acl::LEVEL_WRITE : false;
		}

		if($this->createdBy == go()->getAuthState()->getUserId()) {
			return Acl::LEVEL_MANAGE;
		}

		return $this->findEntity()->hasPermissionLevel(Acl::LEVEL_READ) ? Acl::LEVEL_CREATE : false;

	}

	/**
	 * Applies conditions to the query so that only entities with the given permission level are fetched.
	 *
	 * @param Query $query
	 * @param int $level
	 * @param int|null $userId Leave to null for the current user
	 * @param array|null $groups
	 * @return Query $query;
	 */
	public static function applyAclToQuery(Query $query, int $level = Acl::LEVEL_READ, int|null $userId = null, array|null $groups = null): Query
	{
		return $query;
	}
	

	protected function internalValidate()
	{
		if($this->isModified(['text']) && !empty($this->text)) {
			$this->text = StringUtil::sanitizeHtml($this->text, false);

			if ($this->validateXSS && StringUtil::detectXSS($this->text, false)) {
				$this->setValidationError('text', ErrorCode::INVALID_INPUT, "You're not allowed to use scripts in the content");
			}
		}

		if(!isset($this->date)) {
			$this->date = new DateTime();
		}
		parent::internalValidate();
	}

	protected function internalSave(): bool
	{
		$this->images = Blob::parseFromHtml($this->text, true);

		if(!parent::internalSave()) {
			return false;
		}

		if($this->isNew()) {
			$entity = $this->findEntity();
			if(method_exists($entity, 'onCommentAdded')) {
				$entity->onCommentAdded($this);
			}elseif(property_exists($entity, 'lastContactAt')) {
				$entity->lastContactAt = new DateTime();
				$entity->save();
			}
		} else {
			$entity = $this->findEntity();
		}

		if(property_exists($entity, 'modifiedAt')) {
			if($entity->modifiedAt < $this->modifiedAt && method_exists($entity, 'saveSearch')) {
				//if entity wasn't saved in 'onCommentAdded' then save the search so it can be found
				$entity->saveSearch();
			}
		} else if($entity instanceof ActiveRecord && $entity->hasAttribute('mtime')) {
			if($entity->mtime < $this->modifiedAt->format("U") ) {
				//if entity wasn't saved in 'onCommentAdded' then save the search so it can be found
				$entity->cacheSearchRecord();
			}
		}



		return true;
	}

	public function title(): string
	{
		$entity = $this->findEntity();

		if($entity) {
			return $entity->title();

		} else {
			return $this->id;
		}
	}

	protected static function aclEntityClass(): string
	{
		return Search::class;
	}

	protected static function aclEntityKeys(): array
	{
		return ['entityId' => 'entityId', 'entityTypeId' => 'entityTypeId'];
	}


	/**
	 * Copy comments from one entity to another.
	 *
	 * @param Entity|ActiveRecord $from
	 * @param Entity|ActiveRecord  $to
	 * @return bool
	 * @throws SaveException
	 */
	public static function copyTo($from, $to): bool
	{
		go()->getDbConnection()->beginTransaction();
		try {
			foreach (Comment::findForEntity($from) as $comment) {
				$copy = $comment->copy();
				if (!$copy->setEntity($to)->save()) {
					throw new SaveException($copy);
				}
			}
		} catch(Exception $e) {
			go()->getDbConnection()->rollBack();
			throw $e;
		}

		return go()->getDbConnection()->commit();
	}


	protected function getSearchDescription(): string
	{
		return StringUtil::cutString($this->getAsText(), 100);
	}

	private function getAsText() : string {
		if(!isset($this->asText)) {
			$this->asText = preg_replace("/<style>.*<\/style>/usi", "", $this->text);
			$this->asText = strip_tags($this->asText);
		}
		return $this->asText;
	}

	private $asText;

	protected function getSearchKeywords(): ?array
	{
		return [$this->getAsText()];
	}
}