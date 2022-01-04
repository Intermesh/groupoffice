go.smtp.AccountDialog = Ext.extend(go.form.Dialog, {
	title: t('Server profile', 'imapauth'),
	entityStore: "SmtpAccount",
	width: dp(800),
	height: dp(600),
	autoScroll: true,
	initFormItems: function () {

		this.addPanel(new go.permissions.SharePanel());

		return [{
			xtype: 'fieldset',
			defaults: {
				anchor: '100%'
			},
			items: [{
				xtype: 'textfield',
				name: 'fromName',
				fieldLabel: t('From name'),
				allowBlank: false
			}, {
				xtype: 'textfield',
				name: 'fromEmail',
				fieldLabel: t('From e-mail'),
				allowBlank: false
			}, {
				xtype: 'textfield',
				name: 'hostname',
				fieldLabel: t('Hostname'),
				allowBlank: false
			}, {
				xtype: 'numberfield',
				name: 'port',
				fieldLabel: t('Port'),
				decimals: 0,
				value: 587,
				allowBlank: false
			}, 
			// {
			// 	submit: false,
			// 	xtype: 'checkbox',
			// 	hideLabel: true,
			// 	boxLabel: t('Use authentication'),
			// 	listeners: {
			// 		check: function (checkbox, checked) {
			// 			this.formPanel.getForm().findField('username').setDisabled(!checked);
			// 			this.formPanel.getForm().findField('password').setDisabled(!checked);
			// 		},
			// 		scope: this
			// 	}
			// }, 
			{
				xtype: 'textfield',
				name: 'username',
				fieldLabel: t('Username'),
				autocomplete: "new-password"
			}, {
				xtype: 'textfield',
				name: 'password',
				fieldLabel: t('Password'),
				inputType: "password",
				autocomplete: "new-password"
			}, {
				xtype: 'combo',
				name: 'encryption',
				fieldLabel: t('Encryption'),
				mode: 'local',
				editable: false,
				triggerAction: 'all',
				store: new Ext.data.ArrayStore({
					fields: [
						'value',
						'display'
					],
					data: [['tls', 'TLS'], ['ssl', 'SSL'], [null, 'None']]
				}),
				valueField: 'value',
				displayField: 'display',
				value: 'tls',
				listeners: {
					change: function (combo, newVal, oldVal) {
						this.formPanel.getForm().findField('verifyCertificate').setDisabled(newVal == null);
					},
					scope: this
				}
			}, {
				xtype: 'xcheckbox',
				hideLabel: true,
				boxLabel: t('Verify certificate'),
				name: 'verifyCertificate',
				checked: true
			}],
			bbar: [
				{
					text: t("Send test message"),
					handler: this.sendTestMessage,
					scope: this
				}
			]
		}
		];
	},

	sendTestMessage : function() {
		this.getEl().mask(t("Sending..."));
		var me = this;
		
		go.Jmap.request({
			method: "SmtpAccount/test",
			params: this.formPanel.getForm().getFieldValues()
		}).then(function() {
			Ext.MessageBox.alert(
				t("Success"),
				t("A message was sent successfully to {email}").replace('{email}', me.formPanel.getForm().findField('fromEmail').getValue())
			);
		}).catch(function(response) {
			var error = "";
			error = "<br /><br />" + response.message;

			Ext.MessageBox.alert(
				t("Failed"),
				t("Failed to send message to {email}").replace('{email}', me.formPanel.getForm().findField('fromEmail').getValue() + error)
			);
		}).finally(function() {
			me.getEl().unmask();
		});
		
		
	}
});

