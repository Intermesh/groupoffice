go.modules.community.multi_instance.InstanceDialog = Ext.extend(go.form.Dialog, {
	stateId: 'multi_instance-InstanceDialog',
	title: t('Instance'),
	entityStore: "Instance",
	autoHeight: false,
	height: dp(900),
	closeOnSubmit: false,
	initFormItems: function () {

		this.addPanel(this.allowedModulesPanel = new go.modules.community.multi_instance.AllowedModulesPanel({
			disabled: true
		}));

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
						xtype: "checkbox",
						boxLabel: "This is a trial (will be deactivated automatically after 30 days)",
						name: "isTrial"
					}, {
						xtype: "numberfield",
						name: "storageQuota",
						serverFormats: false,
						fieldLabel: t("Storage quota"),
						multiplier: 1 / (1024 * 1024 * 1024),
						hint: t("Size in GB")						
					}, {
						serverFormats: false,
						xtype: "numberfield",
						name: "usersMax",
						decimals: 0,
						fieldLabel: t("Maximum number of users")
					}, {
						xtype: 'hidden',
						name: 'allowedModules'
					}]
			}
		];
	},

	submit : function() {
		var me = this;
		return this.supr().submit.call(this) . then (function() {
			if(me.allowedModulesPanel.disabled) {
				// setTimeout(function(){
					me.onLoad(me.formPanel.entity);
					me.tabPanel.setActiveTab(me.allowedModulesPanel);
				// });
			} else
			{
				me.close();
			}
		});
	},
	
	onLoad : function(entityValues) {
		go.modules.community.multi_instance.InstanceDialog.superclass.onLoad.call(this);

		this.allowedModulesPanel.setDisabled(false);
		
		if(this.currentId) {
			this.formPanel.getForm().findField("hostname").setDisabled(true);
		}

		this.allowedModulesPanel.store.loadData(entityValues, false);
		this.allowedModulesPanel.store.sort([{
			field: 'localizedPackage',
			direction: 'ASC'
		}, {
			field: 'title',
			direction: 'ASC'
		}]);
	},

	onBeforeSubmit: function() {
		var m = [];
		this.allowedModulesPanel.store.each(function(record) {
			if (record.data.allowed) {
				m.push(record.data.package + "/" + record.data.module)
			}
		}, this);
		this.formPanel.getForm().findField('allowedModules').setValue(m);

		return true;
	}
});

