go.systemsettings.EmailPanel = Ext.extend(Ext.form.FormPanel, {
	initComponent: function () {
		Ext.apply(this,{
			title:t('Email'),
			autoScroll:true,
			iconCls: 'ic-email',
			layout:'column',
			items: [this.serverSettings()]
		});
		
		go.systemsettings.EmailPanel.superclass.initComponent.call(this);
	},
	
	serverSettings: function() {
		return new FieldSet({
			title:t('Server'),
			items: [
				{
					xtype: 'textfield',
					name : 'smtpHost',
					fieldLabel: t('SMTP host'),
				},{
					xtype: 'numberfield',
					name : 'smtpPort',
					boxLabel: t('SMTP port')
				},{
					xtype: 'textfield',
					name: 'smtpUsername',
					fieldLabel: t('SMTP username')
				},{
					xtype:'textfield',
					name: 'smtpPassowrd',
					fieldLabel: t('SMTP password')
				},{
					xtype: 'combobox',
					name: 'smtpEncryption',
					fieldLabel: t('SMTP encryption'),
					mode: 'local',
					store: new Ext.data.ArrayStore({
						fields: [
							'value',
							'display'
						],
						data: [['tls', 'TLS'], ['ssl', 'SSL'], [null, 'None']]
					}),
					valueField: 'value',
					displayField: 'display'
				}
			]
		});
	},
	
	submit : function(cb, scope) {
		go.Jmap.request({
			url: "go/modules/core/core/Settings/set"
		});
	},
	
	load : function() {
		go.Jmap.request({
			url: "go/modules/core/core/Settings/get"
		});
	}
	
	
});

//GO.mainLayout.onReady(function(){
//	go.systemSettingsDialog.addPanel('email', go.systemsettings.EmailPanel);
//});

