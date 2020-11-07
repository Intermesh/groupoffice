go.modules.community.multi_instance.InstanceDialog = Ext.extend(go.form.Dialog, {
	stateId: 'multi_instance-InstanceDialog',
	title: t('Instance'),
	entityStore: "Instance",
	modulesStore: new Ext.data.JsonStore({
		fields: ['id', 'package', 'module', 'title', 'icon', 'allowed', 'localizedPackage'],
		root: 'availableModules',
		id: 'id',
		remoteSort: false
	}),
	autoHeight: false,
	height: dp(900),
	initFormItems: function () {
		this.moduleColumns = [
			new GO.grid.CheckColumn({
				header: t('Allowed'),
				dataIndex: 'allowed',
			}),
			{
				header: 'Package',
				dataIndex: 'localizedPackage',
				width: dp(300)
			}, {
				header: 'Module',
				dataIndex: 'title',
				id: 'title',
				width: dp(500),
				renderer: function(name, cell, record) {
					return '<div class="mo-title" ' +
						'style="background-image:url(' + go.Jmap.downloadUrl('core/moduleIcon/'+(record.data.package || "legacy")+'/'+record.data.module) + ')">'
						+ record.data.title +'</div>';
				}
			}
		];
		this.addPanel(new go.grid.GridPanel({
			title: t('Modules'),
			store: this.modulesStore,
			columns: this.moduleColumns,
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
						xtype: 'textfield',
						hidden: true,
						name: 'availableModules'
					}]
			}
		];
	},
	
	onLoad : function(entityValues) {
		go.modules.community.multi_instance.InstanceDialog.superclass.onLoad.call(this);
		
		if(this.currentId) {
			this.formPanel.getForm().findField("hostname").setDisabled(true);
		}

		this.modulesStore.loadData(entityValues, false);
		this.modulesStore.sort([{
			field: 'localizedPackage',
			direction: 'ASC'
		}, {
			field: 'title',
			direction: 'ASC'
		}]);
	},

	onBeforeSubmit: function() {
		var string = '';
		this.modulesStore.each(function(record) {
			if (record.data.allowed) {
				string = string + record.data.package + "/" + record.data.module + ","
			}
		}, this);
		this.formPanel.getForm().findField('availableModules').setValue(string);

		return true;
	}
});

