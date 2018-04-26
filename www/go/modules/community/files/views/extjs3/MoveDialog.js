go.modules.community.files.MoveDialog = Ext.extend(go.form.FormWindow, {
	stateId: 'files-moveDialog',
	title: t("Move"),
	entityStore: go.Stores.get("Node"),
	width: 600,
	height: 600,
	
	initFormItems: function () {
		var items = [];

		return items;
	}
});