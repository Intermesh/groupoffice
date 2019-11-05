(function () {
	var store = new Ext.data.ArrayStore({
		xtype: "arraystore",
		idIndex: 0,
		fields: [
			'value',
			'display'
		],
		data: go.modules.community.addressbook.typeStoreData('dateTypes')
	});

	go.modules.community.addressbook.DatesField = Ext.extend(go.form.FormGroup, {

		name: "dates",
		addButtonText: t("Add date"),
		addButtonIconCls: 'ic-event',
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
					store: store,
					valueField: 'value',
					displayField: 'display',
					width: dp(140),
					mobile: {
						width: dp(100)
					},
					value: "birthday"
				}, {
					flex: 1,
					xtype: "datefield",
					allowBlank: false,
					name: "date",
					setFocus: true
				}]
			}]
		}
	}
	);
})();