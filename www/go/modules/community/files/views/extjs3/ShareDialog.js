go.modules.community.files.ShareDialog = Ext.extend(go.form.FormWindow, {
	stateId: 'files-shareDialog',
	title: t("Share"),
	entityStore: go.Stores.get("Node"),
	width: 600,
	height: 600,
	
	initFormItems: function () {
		var items = [{
				xtype: 'fieldset',
				title: "External users",
				autoHeight: true,
				items: []
			}
		];

		return items;
	}
});