<?php
namespace go\modules\community\notes\model;

use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\orm\Query;
use go\core\orm\CustomFieldsTrait;
use go\core\orm\LoggingTrait;
use go\core\orm\SearchableTrait;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;

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
	
	use LoggingTrait;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("notes_note", "n");
	}

	protected static function aclEntityClass() {
		return NoteBook::class;
	}

	protected static function aclEntityKeys() {
		return ['noteBookId' => 'id'];
	}

	protected function getSearchDescription() {
		return $this->getExcerpt();
	}

	protected function getSearchName() {
		return $this->name;
	}
	
	public function getExcerpt() {
		$text = preg_replace("/\s+/", " ", strip_tags(str_replace(">", "> ",$this->content)));
		return StringUtil::cutString($text, 200);
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

}
