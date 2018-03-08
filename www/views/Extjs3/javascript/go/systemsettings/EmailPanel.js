go.systemsettings.EmailPanel = Ext.extend(Ext.Panel, {

	initComponent: function () {

		Ext.apply(this,{
			title:t('Email'),
			autoScroll:true,
			iconCls: 'ic-email',
			layout:'column',
			items: [
				{
					columnWidth: .5,//left
					items:[this.serverSettings()]
				},{
					columnWidth: .5,//right
					items: [this.linkLabelSettings()]
				}
			]
		});
		
		go.systemsettings.EmailPanel.superclass.initComponent.call(this);
	},
	
	serverSettings: function() {
		return new FieldSet({
			title:t('Server'),
			items: [
				{
					xtype: 'textfield',
					name : 'smtp_server',
					fieldLabel: t('SMTP host'),
				},{
					xtype: 'numberfield',
					name : 'smtp_port',
					boxLabel: t('SMTP port')
				},{
					xtype: 'textfield',
					name: 'smtp_username',
					fieldLabel: t('SMTP username')
				},{
					xtype:'textfield',
					name: 'smtp_password',
					fieldLabel: t('SMTP password')
				},{
					xtype: 'combobox',
					name: 'smtp_encryption',
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
				},{
					xtype: 'textfield',
					name: 'smtp_local_domain',
					fieldLabel: t('The Swift mailer component auto detects the domain you are connecting from. In some cases it fails and uses an invalid IPv6 IP like ::1. You can override it here.')
				},{
					xtype: 'xcheckbox',
					name: 'swift_qp_dot_escape',
					boxLabel: t('A special Swift preference to escape dots. For some buggy SMTP servers this is necessary.')
				},{
					xtype: 'xcheckbox',
					name: 'email_disable_aliases',
					boxLabel: t('Set to true to prevent users from changing their e-mail aliases in the email module.')
				},{
					xtype: 'textfield',
					name: 'disable_imap_capabilities',
					fieldLabel: t('We stumbled upon a dovecot server that crashed when sending a command using LIST-EXTENDED. With this option we can workaround that issue.')
				},{
					xtype: 'textfield',
					name: 'restrict_smtp_hosts',
					fieldLabel: t('A comma separated list of smtp server IP addresses that you want to restrict. E.g. "213.207.103.219:10,127.0.0.1:10"; will restrict those IP\'s to 10 e-mails per day.')
				},{
					xtype: 'numberfield',
					name: 'max_attachment_size',
					fieldLabel: t('Attachment size'),
					tooltip: t('The maximum summed size of e-mail attachments in a message in bytes Group-Office will accept. Please be aware that the SMTP server has size limits too.')
				},{
					xtype: 'textfield',
					name: 'smime_root_cert_location',
					fieldLabel: 'SMIME certificate',
					tooltip: t('Specifies the name of a file containing a bunch of extra certificates to include in the signature which can for example be used to help the recipient to verify the certificate that you used')
				},{
					xtype: 'textfield',
					name: 'smime_sign_extra_certs',
					fieldLabel: 'Extra SMIME certificates',
					tooltip: t('Include extra certificates for signing')
				}
			]
		});
	},
	
	linkLabelSettings : function() {
		return new FieldSet({
			title:t('Server'),
			items: [
				{
					xtype:'xcheckbox',
					name: 'email_autolink_contacts',
					boxLabel: t('Will link ALL e-mails to contacts automatically when sending mails.')
				},{
					xtype:'xcheckbox',
					name: 'email_autolink_companies',
					boxLabel: t('Will link ALL e-mails to also to contact->company automatically when sending mails.')
				},{
					xtype:'xcheckbox',
					name: 'email_enable_labels',
					boxLabel: t('Enable labels in email')
				}
			]
		});
	}
	
	
});

GO.mainLayout.onReady(function(){
	go.systemSettingsDialog.addPanel('system-general', go.systemsettings.GeneralPanel);
});

