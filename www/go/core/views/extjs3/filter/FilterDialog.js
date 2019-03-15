go.filter.FilterDialog = Ext.extend(go.form.Dialog, {
	title: t('Filter'),
	entityStore: "EntityFilter",
	entity: null,
	autoScroll: true,
	height: dp(400),
	width: dp(1000),
	initFormItems: function () {

		return [{
				xtype: 'fieldset',
				items: [{
						xtype: "hidden",
						name: 'entity',
						value: this.entity
				},
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
				entity: this.entity
			}

		];
	}
});
