go.customfields.FieldDialog = Ext.extend(go.form.Dialog, {	
	title: t('Field'),
	entityStore: "Field",
	height: dp(400),
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
					},{
						xtype: 'textfield',
						name: 'databaseName',
						fieldLabel: t("Database name"),
						anchor: '100%',
						allowBlank: false
					},{
						xtype: "textfield",
						name: "hint",
						fieldLabel: t("Hint text"),
						anchor: "100%"
					},{
						xtype: "textfield",
						name: "prefix",
						fieldLabel: t("Prefix"),
						anchor: "100%"
					},{
						xtype: "textfield",
						name: "suffix",
						fieldLabel: t("Suffix"),
						anchor: "100%"
					},{
						xtype: "checkbox",
						name: "unique",
						boxLabel: t("Unique values"),
						hideLabel: true
					}
				]
			}
		];
	}
});


