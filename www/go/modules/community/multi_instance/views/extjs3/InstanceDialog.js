go.modules.community.multi_instance.InstanceDialog = Ext.extend(go.form.Dialog, {
	stateId: 'multi_instance-InstanceDialog',
	title: t('Instance'),
	entityStore: go.Stores.get("Instance"),
	autoHeight: true,
	initFormItems: function () {
		return [{
				xtype: 'fieldset',
				items: [
					{
						xtype: 'textfield',
						name: 'hostname',
						fieldLabel: t("Hostname"),
						anchor: '100%',
						required: true
					}]
			}
		];
	}
});

