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
						store: {
							xtype: "arraystore",
							id: 0,
							fields: [
								'value',
								'display'
							],
							data: go.modules.community.addressbook.typeStoreData("dateTypes")
						},
						valueField: 'value',
						displayField: 'display',
						width: dp(140),
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