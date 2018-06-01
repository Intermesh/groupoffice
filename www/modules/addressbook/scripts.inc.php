<?php

$GO_SCRIPTS_JS .= 'GO.lang.legacy.addressbook.defaultSalutationExpression="'.\GO\Base\Util\StringHelper::escape_javascript(\GO::t("Default salutation", "addressbook")).'";';


$export_acl_id = \GO::config()->get_setting('go_addressbook_export', 0);
if(!$export_acl_id)
{
	$acl = new \GO\Base\Model\Acl();
	$acl->usedIn='go_settings';
	$acl->ownedBy = 1;
	$acl->save();
	
	$export_acl_id = $acl->id;
	\GO::config()->save_setting('go_addressbook_export', $acl->id, 0);
}
$GO_SCRIPTS_JS .= 'GO.addressbook.export_acl_id="'.$export_acl_id.'";';

$acl_level = \GO\Base\Model\Acl::getUserPermissionLevel($export_acl_id, \GO::user()->id);
$GO_SCRIPTS_JS .= 'GO.addressbook.exportPermission="'.(($acl_level) ? 1 : 0).'";';


if(\GO::modules()->customfields){
	$GO_SCRIPTS_JS .=  '

	GO.customfields.settingsPanels={
		name: "'.\GO\Addressbook\Model\Contact::model()->localizedName.'",
		panels: []
	};'."\n";

	$stmt = \GO\Users\Model\CfSettingTab::model()->getSettingTabs();
	while($category = $stmt->fetch()){

		$fields = array();
		$fstmt = $category->fields();
		while($field = $fstmt->fetch()){
			$fields[]=$field->toJsonArray();
		}

		// Makes global, client-side, editable form panels for every customfield category
		$GO_SCRIPTS_JS .= "\n\n".'GO.customfields.settingsPanels.panels.push({xtype : "customformpanel", itemId:"cf-panel-'.$category->id.'", category_id: '.$category->id.', title : "'.htmlspecialchars($category->name,ENT_QUOTES, 'UTF-8').'", customfields : '.json_encode($fields).'});'."\n";
	}
}
