<?php
namespace go\modules\community\notes\model;

use GO\Base\Util\StringHelper;
use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\fs\Blob;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\orm\CustomFieldsTrait;
use go\core\orm\SearchableTrait;
use go\core\util\ArrayObject;
use go\core\util\DateTime;
use go\core\util\Markdown;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\modules\community\notes\convert\Spreadsheet;

class Note extends AclItemEntity {

	const TYPE_HTML = 'html';
	const TYPE_MARKDOWN = 'markdown';

	/**
	 * The Entity ID
	 */
	public ?string $id;

	public string $name;
	public string $content;

	/**
	 * Content type. "html" or "markdown". Markdown is rendered by the client. It can't be changed for existing notes.
	 */
	public string $type = self::TYPE_HTML;
	public string $noteBookId;

	public ?\DateTimeInterface $createdAt;

	public ?\DateTimeInterface $modifiedAt;
	public ?string $createdBy;
	public ?string $modifiedBy;
	public ?string  $filesFolderId;
	public ?string $password;
	
	use CustomFieldsTrait;
	use SearchableTrait;

	/**
	 *
	 * @var string[]
	 */
	protected array $images = [];
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable("notes_note", "n")
						->addScalar('images', 'notes_note_image', ['id' => 'noteId']);
	}

	protected static function aclEntityClass(): string
	{
		return NoteBook::class;
	}

	protected static function aclEntityKeys(): array
	{
		return ['noteBookId' => 'id'];
	}

	protected function getSearchDescription(): string
	{
		$text = $this->type == self::TYPE_MARKDOWN ? Markdown::toText($this->content) : strip_tags(str_replace(">", "> ",$this->content));
		return preg_replace("/\s+/", " ", $text);
	}

	public function title(): string
	{
		return $this->name;
	}

	/**
	 * Return columns to search on with the 'text' filter. {@see filter()}
	 * 
	 * @return string[]
	 */
	protected static function textFilterColumns(): array
	{
		return ['name', 'content'];
	}
	
	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
						->add('noteBookId', function(Criteria $criteria, $value) {
							if(!empty($value)) {
								$criteria->where(['noteBookId' => $value]);
							}
						})
						->addText('name', function(Criteria $criteria, $comparator, $value, Query $query) {
							$criteria->andWhere('name', $comparator, $value);
						})
						->addText('content', function(Criteria $criteria, $comparator, $value, Query $query) {
							$criteria->andWhere('content', $comparator, $value);
						});
	}

	

	protected function internalValidate()
	{
		if(!in_array($this->type, [self::TYPE_HTML, self::TYPE_MARKDOWN], true)) {
			$this->setValidationError('type', ErrorCode::INVALID_INPUT, "Type must be 'html' or 'markdown'");
		} else if(!$this->isNew() && $this->isModified(['type'])) {
			$this->setValidationError('type', ErrorCode::INVALID_INPUT, "The type of an existing note can't be changed");
		}

		// Markdown is rendered and sanitized by the client. Sanitizing it as HTML would break it.
		if($this->type == self::TYPE_HTML && $this->isModified(['content'])) {
			$this->content = StringHelper::sanitizeHtml($this->content, false);

			if(StringUtil::detectXSS($this->content, false)) {
				$this->setValidationError('content', ErrorCode::INVALID_INPUT, "You're not allowed to use scripts in the content");
			}
		}

		parent::internalValidate();
	}


	protected function internalSave(): bool
	{
		$this->images = Blob::parse($this->content, true);
		return parent::internalSave();
	}


	/**
	 * @inheritDoc
	 */
	public static function converters(): array
	{
		return array_merge(parent::converters(), [Spreadsheet::class]);
	}

	public static function sort(Query $query, ArrayObject $sort): Query
	{
		if (isset($sort['creator/name'])) {
			$query->join('core_user', 'creator', 'creator.id = n.createdBy', 'LEFT');
			$sort->renameKey('creator/name', 'creator.displayName');
		}

		if (isset($sort['modifier/name'])) {
			$query->join('core_user', 'modifier', 'modifier.id = n.modifiedBy', 'LEFT');
			$sort->renameKey('modifier/name', 'modifier.displayName');
		}

		return parent::sort($query, $sort);
	}
}
