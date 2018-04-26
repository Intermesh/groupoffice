go.modules.community.files.RenameDialog = Ext.extend(go.form.FormWindow, {
	stateId: 'files-renameDialog',
	title: t("Rename"),
	entityStore: go.Stores.get("Node"),
	width: 600,
	height: 600,
	
	initFormItems: function () {
		var items = [];

		return items;
	}
});