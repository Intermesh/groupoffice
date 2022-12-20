(function () {
	var store = new Ext.data.ArrayStore({
		xtype: "arraystore",
		idIndex: 0,
		fields: [
			'value',
			'display'
		],
		data: go.modules.community.addressbook.typeStoreData('urlTypes')
	});

	go.modules.community.addressbook.UrlsField = Ext.extend(go.form.FormGroup, {
		xtype: "formgroup",
		name: "urls",
		addButtonText: t("Add online url"),
		addButtonIconCls: 'ic-home',
		itemCfg: {
			items: [{
				anchor: "100%",
				layout: "form",
				xtype: "container",
				cls: "go-hbox",
				items: [{
					xtype: 'combo',
					name: 'type',
					mode: 'local',
					editable: false,
					triggerAction: 'all',
					store: store,
					valueField: 'value',
					displayField: 'display',
					width: dp(140),
					mobile: {
						width: dp(100)
					},
					value: "homepage",
					fieldLabel: t("Type"),
				}, {
					flex: 1,
					xtype: "textfield",
					allowBlank: false,
					name: "url",
					setFocus: true,
					fieldLabel: "URL",
				}]
			}]
		}
	});
})();