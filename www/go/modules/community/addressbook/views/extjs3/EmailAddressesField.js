(function () {
	var store = new Ext.data.ArrayStore({
		xtype: "arraystore",
		idIndex: 0,
		fields: [
			'value',
			'display'
		],
		data: go.modules.community.addressbook.typeStoreData('emailTypes')
	});

	go.modules.community.addressbook.EmailAddressesField = Ext.extend(go.form.FormGroup, {
		hideLabel: true,
		xtype: "formgroup",
		name: "emailAddresses",
		addButtonIconCls: 'ic-email',
		addButtonText: t("Add e-mail address"),
		itemCfg: {
			anchor: "100%",
			items: [{
				anchor: "100%",
				xtype: "compositefield",
				hideLabel: true,
				items: [
					{
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
						value: "work"
					},
					{
						flex: 1,
						xtype: "textfield",
						allowBlank: false,
						vtype: 'emailAddress',
						name: "email",
						setFocus: true
					}]
			}]
		}
	}
	);
})();