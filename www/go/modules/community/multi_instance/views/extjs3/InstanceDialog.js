go.modules.community.multi_instance.InstanceDialog = Ext.extend(go.form.Dialog, {
	stateId: 'multi_instance-InstanceDialog',
	title: t('Instance'),
	entityStore: "Instance",
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
					}, {
						xtype: "numberfield",
						name: "storageQuota",
						fieldLabel: t("Storage quota"),
						multiplier: 1 / (1024 * 1024 * 1024),
						hint: t("Size in GB")						
					}, {
						xtype: "numberfield",
						name: "usersMax",
						fieldLabel: t("Maximum number of users")
					}]
			}
		];
	},
	
	onLoad : function() {
		go.modules.community.multi_instance.InstanceDialog.superclass.onLoad.call(this);
		
		if(this.currentId) {
			this.formPanel.getForm().findField("hostname").setDisabled(true);
		}
	}
});

