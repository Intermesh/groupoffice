go.customfields.FieldSetDialog = Ext.extend(go.form.Dialog, {
	stateId: 'custom-field-set-dialog',
	title: t('Field set'),
	entityStore: "FieldSet",
	width: dp(1000),
	height: dp(800),
	autoScroll: true,
	initFormItems: function () {
		this.addPanel(new go.permissions.SharePanel());
		return [{
				xtype: 'fieldset',
				items: [{
						xtype: "hidden",
						name: "entity"
					},
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						allowBlank: false
					}, {
						xtype: "checkbox",
						name: 'isTab',
						hideLabel: true,
						boxLabel: t("Show as tab")
					}, {
						xtype: "textarea",
						name: "description",
						fieldLabel: t("Description"),
						anchor: "100%",
						grow: true,
						hint: t("This description will show in the edit form")
					}	,{
						xtype:'gonumberfield',
						name: 'columns',
						fieldLabel: t("Columns"),
						value: 2,
						decimals: 0
					}
				]
			}
		];
	}
});
