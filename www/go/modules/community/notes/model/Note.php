<?php
namespace go\modules\community\notes\model;

use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\fs\Blob;
use go\core\model\EmailTemplateAttachment;
use go\core\orm\Query;
use go\core\orm\CustomFieldsTrait;
use go\core\orm\SearchableTrait;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\modules\community\notes\convert\Spreadsheet;

class Note extends AclItemEntity {

	/**
	 * The Entity ID
	 * 
	 * @var int
	 */
	public $id;

	public $name;
	public $content;
	public $noteBookId;
	
	/**
	 *
	 * @var DateTime
	 */
	public $createdAt;
	
	/**
	 *
	 * @var DateTime
	 */
	public $modifiedAt;
	public $createdBy;
	public $modifiedBy;
	public $filesFolderId;
	public $password;
	
	use CustomFieldsTrait;
	
	use SearchableTrait;


	/**
	 *
	 * @var string[]
	 */
	protected $images = [];
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("notes_note", "n")
						->addScalar('images', 'notes_note_image', ['id' => 'noteId']);
	}

	protected static function aclEntityClass() {
		return NoteBook::class;
	}

	protected static function aclEntityKeys() {
		return ['noteBookId' => 'id'];
	}

	protected function getSearchDescription() {
		return preg_replace("/\s+/", " ", strip_tags(str_replace(">", "> ",$this->content)));
	}

	public function title() {
		return $this->name;
	}

	/**
	 * Return columns to search on with the 'text' filter. {@see filter()}
	 * 
	 * @return string[]
	 */
	protected static function textFilterColumns() {
		return ['name', 'content'];
	}
	
	protected static function defineFilters() {
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
		if($this->isModified(['content']) && StringUtil::detectXSS($this->content)) {
			$this->setValidationError('content', ErrorCode::INVALID_INPUT, "You're not allowed to use scripts in the content");
		}
		return parent::internalValidate();
	}


	protected function internalSave()
	{
		$this->images = Blob::parseFromHtml($this->content);
		return parent::internalSave();
	}


	/**
	 * @inheritDoc
	 */
	public static function converters()
	{
		return array_merge(parent::converters(), [Spreadsheet::class]);
	}
}
