<?php

/**
 * @property String thumbURL the url to the thumbnail of the bookmark
 * @property boolean $behave_as_module
 * @property boolean $open_extern
 * @property boolean $public_icon
 * @property string $logo
 * @property string $description
 * @property string $content
 * @property string $name
 * @property int $user_id
 * @property int $category_id
 * @property int $id
 */

namespace GO\Bookmarks\Model;


class Bookmark extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Bookmark
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'bm_bookmarks';
	}
	
	public function aclField() {
		return 'category.acl_id';
	}

	public function relations() {
		return array(
					'category' => array('type' => self::BELONGS_TO, 'model' => 'GO\Bookmarks\Model\Category', 'field' => 'category_id')
				);
	}
	
	protected function init() {
		$this->columns['content']['gotype']='raw';
//		$this->columns['content']['gotype']='html';
		return parent::init();
	}

	protected function getThumbURL() {

		if ($this->logo!='') {
			if ($this->public_icon == '1') {
				return \GO::modules()->host .'modules/bookmarks/'.$this->logo;
			} else {
				return \GO::url('core/thumb', array('src'=>$this->logo, 'w'=>16,'h'=>16));
			}
		} else {
			return false;
		}
	}
}
