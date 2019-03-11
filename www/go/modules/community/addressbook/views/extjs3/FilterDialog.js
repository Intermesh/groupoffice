go.modules.community.addressbook.FilterDialog = Ext.extend(go.form.Dialog, {	
	title: t('Filter'),
	entityStore: "Filter",
	autoScroll: true,
	height: dp(400),
	width: dp(1000),
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
						xtype: 'filterconditions',
						fields: [{
								name: 'city',
								title: t("City"),
								type: 'string'
						},{
								name: 'country',
								title: t("Country"),
								type: 'string'
						}]
					}]
			}
		];
	}
});
