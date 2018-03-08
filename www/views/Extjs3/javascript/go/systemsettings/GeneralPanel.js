go.systemsettings.GeneralPanel = Ext.extend(Ext.Panel, {

	initComponent: function () {

		Ext.apply(this,{
			title:t('Look & feel'),
			autoScroll:true,
			iconCls: 'ic-style',
			layout:'column',
			items: [
				{
					columnWidth: .5,//left
					items:[this.debugSettings()]
				},{
					columnWidth: .5,//right
					items: []
				}
			]
		});
		
		go.systemsettings.GeneralPanel.superclass.initComponent.call(this);
	},
	
	debugSettings: function() {
		return new FieldSet({
			title:t('Debugging and logging'),
			items: [
				{
					xtype: 'xcheckbox',
					name : 'debug',
					boxLabel: t('Run Group-Office in debug mode. This will log much info to /home/groupoffice/log/debug.log and will use uncompressed javascripts. You can also enable this as admin in Group-Office by pressing CTRL+F7. It may dramatically slow down your system, observed with /home/groupoffice mounted via NFS.')
				},{
					xtype: 'xchecgbox',
					name : 'debug_log',
					boxLabel: t('Just enable the debug log. See debug.log in your Group-Office log folder.')
				},{
					xtype: 'textfield',
					name: 'debug_email',
					fieldLabel: t('Option to make sure all outgoing emails will be send to the given email address. This is useful when debugging Group-Office and when you don\'t want to send unwanted emails to active customers. Example: "info@test.dev"')
				},{
					xtype:'textfield',
					name: 'debug_usernames',
					fieldLabel: t('Comma separated list of usernames, e.g.: \'admin,john,mary\'. For these users, debug mode will be on.')
				},{
					xtype: 'numberfield',
					name: 'log_max_days',
					fieldLabel: t('Set the number of days the database log will contain until it will be dumped to a CSV file on disk. The log module must be installed.')
				},{
					xtype: 'xcheckbox',
					name: 'firephp',
					boxLabel: t('	Enable FirePhp')
				},{
					xtype: 'textfield',
					name: 'info_log',
					fieldLabel: t('Info log location. eg: "<file_storage_path>/log/info.log" This logs all logins and logouts. If set empty ("") it will disable this log.')
				},{
					xtype: 'textfield',
					name: 'file_log',
					fieldLabel: t('file_log todo.. keep this?')
				}
			]
		});
	},
	
	
});

GO.mainLayout.onReady(function(){
	go.systemSettingsDialog.addPanel('system-general', go.systemsettings.GeneralPanel);
});

