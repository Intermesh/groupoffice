go.modules.community.apikeys.KeyDialog = Ext.extend(go.form.Dialog, {
	title: t('API Key'),
	entityStore: "Key",
	autoHeight: true,
	initFormItems: function () {
		return [{
				xtype: 'fieldset',
				items: [
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',						
						allowBlank: false
					}]
			}
		];
	}
});

