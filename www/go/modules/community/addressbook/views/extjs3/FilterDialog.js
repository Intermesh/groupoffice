go.modules.community.addressbook.FilterDialog = Ext.extend(go.form.Dialog, {
	title: t('Filter'),
	entityStore: "ContactFilter",
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
					}
				]},
			{
				xtype: 'filterfieldset',
				entity: "Contact"
			}

		];
	}
});
