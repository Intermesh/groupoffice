go.modules.community.addressbook.UrlsField = Ext.extend(go.form.FormGroup, {
	xtype: "formgroup",
	name: "urls",
	addButtonText: t("Add online url"),
	addButtonIconCls: 'ic-home',
	itemCfg: {
		items: [{
				xtype: "compositefield",
				hideLabel: true,
				items: [{
						xtype: 'combo',
						name: 'type',
						mode: 'local',
						editable: false,
						triggerAction: 'all',
						store: {
							xtype: "arraystore",
							id: 0,
							fields: [
								'value',
								'display'
							],
							data: go.modules.community.addressbook.typeStoreData("urlTypes")
						},
						valueField: 'value',
						displayField: 'display',
						width: dp(140),
						value: "homepage"
					}, {
						flex: 1,
						xtype: "textfield",
						allowBlank: false,
						name: "url",
						setFocus: true
					}]
			}]
	}
});
