<?php

namespace GO\Bookmarks;


class BookmarksModule extends \GO\Base\Module{
	public function autoInstall() {
		return true;
	}
	
	public static function initListeners() {
		$c = new \GO\Core\Controller\AuthController();
		$c->addListener('head', "GO\Bookmarks\BookmarksModule", "head");
	}
	
	public static function head(){
		echo '<style>';

		$findParams = \GO\Base\Db\FindParams::newInstance()
			->joinRelation('category')
			->criteria(\GO\Base\Db\FindCriteria::newInstance()
				->addCondition('behave_as_module', 1)
				->addCondition('show_in_startmenu', 1,'=','category',false)
			);

		$stmt = Model\Bookmark::model()->find($findParams);
		while ($bookmark = $stmt->fetch()) {			
			echo '.go-menu-icon-bookmarks-id-'.$bookmark->id.'{background-image:url('.$bookmark->thumbUrl.')}';			
		}

		echo '</style>';
	}
	
	public function install() {
		parent::install();
		
		$category = new Model\Category();
		$category->name=\GO::t('general','bookmarks');		
		$category->save();
		$category->acl->addGroup(\GO::config()->group_internal, \GO\Base\Model\Acl::READ_PERMISSION);
	}
}
