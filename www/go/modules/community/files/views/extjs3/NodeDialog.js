go.modules.community.files.NodeDialog = Ext.extend(go.form.Dialog, {
	stateId: 'files-nodeDialog',
	title: t("Rename"),
	entityStore: go.Stores.get("Node"),
	width: 300,
	height: 150,
	
	initFormItems: function () {
		var items = [
			{
				xtype: 'fieldset',
				autoHeight: true,
				items: [
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						allowBlank: false
					}
				]
			}
		];
		return items;
	}
});