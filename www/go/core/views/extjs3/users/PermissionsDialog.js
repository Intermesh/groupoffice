/**
 * var aclId = 1;
 * var dlg = new go.permissions.ShareWindow();
 * dlg.load(aclId).show();
 */
go.users.PermissionsDialog = Ext.extend(go.form.Dialog, {
	title: t('User permissions'),
	entityStore: "Group",
	height: dp(600),
	width: dp(1000),
	formPanelLayout: "fit",

	initFormItems : function() {
		return new go.permissions.SharePanel({
			title: null,
			hideLabel: true,
			name: 'acl'
		});
	}
});
