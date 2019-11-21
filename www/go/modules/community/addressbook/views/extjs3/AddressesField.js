(function () {
	var store = new Ext.data.ArrayStore({
		xtype: "arraystore",
		idIndex: 0,
		fields: [
			'value',
			'display'
		],
		data: go.modules.community.addressbook.typeStoreData('addressTypes')
	});

	go.modules.community.addressbook.AddressesField = Ext.extend(go.form.FormGroup, {
		hideLabel: true,
		xtype: "formgroup",
		name: "addresses",
		addButtonText: t("Add street address"),
		addButtonIconCls: 'ic-add-location',
		pad: true,
		itemCfg: {
			labelWidth: dp(140),
			items: [{
				anchor: "100%",
				fieldLabel: t("Type"),
				xtype: 'combo',
				name: 'type',
				mode: 'local',
				editable: false,
				triggerAction: 'all',
				store: store,
				valueField: 'value',
				displayField: 'display',
				value: "work"
			}, {
				xtype: "textfield",
				fieldLabel: t("Street"),
				name: "street",
				anchor: "100%",
				setFocus: true
			}, {
				xtype: "textfield",
				fieldLabel: t("Street 2"),
				name: "street2",
				anchor: "100%"
			}, {
				xtype: "textfield",
				fieldLabel: t("ZIP code"),
				name: "zipCode",
				anchor: "100%"
			}, {
				xtype: "textfield",
				fieldLabel: t("City"),
				name: "city",
				anchor: "100%"
			}, {
				xtype: "textfield",
				fieldLabel: t("State"),
				name: "state",
				anchor: "100%"
			}, {
				xtype: "selectcountry",
				fieldLabel: t("Country"),
				hiddenName: "countryCode",
				name: "country",
				anchor: "100%"
			}]
		}
	}
	);
})();