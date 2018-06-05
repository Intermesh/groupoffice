<?php
$GO_SCRIPTS_JS .= "GO.mainLayout.on('authenticated', function(){";
$findParams = \GO\Base\Db\FindParams::newInstance()->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('behave_as_module', 1));

$stmt = \GO\Bookmarks\Model\Bookmark::model()->find($findParams);

while($bookmark = $stmt->fetch()){
	if (strlen($bookmark->name) > 30) {
		$name = substr($bookmark->name, 0, 28) . '..';
	} else {
		$name = $bookmark->name;
	}
	$GO_SCRIPTS_JS .= 'GO.moduleManager._addModule(\'bookmarks-id-' . $bookmark->id . '\', GO.panel.IFrameComponent, {title : \'' . \GO\Base\Util\StringHelper::escape_javascript($name) . '\', url : \'' . \GO\Base\Util\StringHelper::escape_javascript($bookmark->content) . '\',iconCls: \'go-tab-icon-bookmarks\'});';
}

// Load the bookmark categories for the start menu
$categoryFindParams = \GO\Base\Db\FindParams::newInstance()->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('show_in_startmenu', 1));
$categoryStmt = \GO\Bookmarks\Model\Category::model()->find($categoryFindParams);

while($category = $categoryStmt->fetch()){
	
	if (strlen($category->name) > 30) {
		$categoryName = substr($category->name, 0, 28) . '..';
	} else {
		$categoryName = $category->name;
	}
	
	$bookmarks = $category->bookmarks;
	
	while($bookmark = $bookmarks->fetch()){
		
		if (strlen($bookmark->name) > 30) {
			$name = substr($bookmark->name, 0, 28) . '..';
		} else {
			$name = $bookmark->name;
		}
		
		$GO_SCRIPTS_JS .= 'GO.moduleManager._addModule(\'bookmarks-id-' . $bookmark->id . '\', GO.panel.IFrameComponent, {title : \'' . \GO\Base\Util\StringHelper::escape_javascript($name) . '\', url : \'' . \GO\Base\Util\StringHelper::escape_javascript($bookmark->content) . '\',iconCls: \'go-tab-icon-bookmarks\'},{title:\''.\GO\Base\Util\StringHelper::escape_javascript($categoryName).'\',iconCls: \'go-menu-icon-bookmarks\'});';
	}

}

$GO_SCRIPTS_JS .= "});";
