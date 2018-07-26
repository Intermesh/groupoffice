go.modules.community.ldapauthenticator.ServerForm = Ext.extend(go.form.Dialog, {
	title: t('Server profile', 'ldapauth'),
	entityStore: go.Stores.get("LdapAuthServer"),
	width: dp(600),
	height: dp(600),
	autoScroll: true,
	
	onLoad : function() {
				
		this.createEmailCheckbox.setValue(!GO.util.empty(this.formPanel.getForm().findField('imapHostname').getValue()));
		console.log(this.formPanel.getForm().findField('username').getValue());
		this.formPanel.getForm().findField('ldapUseAuthentication').setValue(!GO.util.empty(this.formPanel.getForm().findField('username').getValue()));
		
		go.modules.community.ldapauthenticator.ServerForm.superclass.onLoad.call(this);
	},
	initFormItems: function () {


		return [{
				title: 'LDAP Server',
				xtype: 'fieldset',
				defaults: {
					anchor: '100%'
				},
				items: [{

						hint: t("Enter the domains this ldap server should be used to authenticate. Users must login with their e-mail address and if the domain matches this profile it will be used.", "ldapauthenticator"),
						xtype: "gridfield",
						name: "domains",
						store: new Ext.data.JsonStore({
							autoDestroy: true,
							root: "records",
							fields: [
								'id',
								'name'
							]
						}),
						fieldLabel: "Domains",

						autoExpandColumn: "name",
						columns: [
							{
								id: 'name',
								header: t('Name'),
								sortable: false,
								dataIndex: 'name',
								hideable: false,
								draggable: false,
								menuDisabled: true,
								editor: new Ext.form.TextField({
									allowBlank: false
								})
							}
						],
					},

					{
						xtype: 'textfield',
						name: 'hostname',
						fieldLabel: t("Hostname", "ldapauthenticator"),
						required: true
					}, {
						xtype: 'numberfield',
						decimals: 0,
						name: 'port',
						fieldLabel: t("Port", "ldapauthenticator"),
						required: true,
						value: 389
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
						value: 'tls'
					},
					{
						xtype: 'xcheckbox',
						submit: false,
						hideLabel: true,
						boxLabel: t('Use authentication', 'imapauthenticator'),
						name: 'ldapUseAuthentication',
						hint: t("Enable this if the LDAP server requires autentication to lookup users or groups"),
						listeners: {
							check: function (checkbox, checked) {
								this.formPanel.getForm().findField('username').setDisabled(!checked);
								this.formPanel.getForm().findField('password').setDisabled(!checked);
							},
							scope: this
						}
					}, {
						xtype: 'textfield',
						name: 'username',
						disabled: true,
						fieldLabel: t('Username')
					}, {
						xtype: 'textfield',
						name: 'password',
						disabled: true,
						fieldLabel: t('Password'),
						inputType:"password"
					},
					
					]
			}, this.usersFieldSet = new Ext.form.FieldSet({
				title: t("Users"),
				items: [
					{
						xtype: 'textfield',
						name: 'usernameAttribute',
						fieldLabel: t("Username attribute", "ldapauthenticator"),
						value: "uid",
						required: true
					}, {
						xtype: 'textfield',
						name: 'peopleDN',
						fieldLabel: "peopleDN",
						value: "ou=people,dc=example,dc=com	",
						required: true
					}, {
						xtype: 'textfield',
						name: 'groupsDN',
						fieldLabel: "groupsDN",
						value: "ou=groups,dc=example,dc=com	",
						required: true
					},this.createEmailCheckbox = new Ext.form.Checkbox({
						xtype:"checkbox",
						submit: false,
						hideLabel: true,
						boxLabel: t("Create e-mail account for users", "ldapauthenticator"),
						listeners: {
							check: function (checkbox, checked) {
								this.imapFieldSet.setVisible(checked);
								this.smtpFieldSet.setVisible(checked);
							},
							scope: this
						}
					})
				]
			}), this.imapFieldSet = new Ext.form.FieldSet({
				hidden: true,
				hideMode: "offsets",
				title: 'IMAP Server',
				xtype: 'fieldset',
				defaults: {
					anchor: '100%'
				},
				items: [{
						xtype: 'textfield',
						name: 'imapHostname',
						fieldLabel: t("Hostname", "imapauthenticator"),
						required: true
					}, {
						xtype: 'numberfield',
						decimals: 0,
						name: 'imapPort',
						fieldLabel: t("Port", "imapauthenticator"),
						required: true,
						value: 143
					}, {
						xtype: 'combo',
						name: 'imapEncryption',
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
						value: 'tls'
					}, {
						xtype: 'xcheckbox',
						checked: true,
						hideLabel: true,
						boxLabel: t('Validate certificate'),
						name: 'imapValidateCertificate'
					}, {
						xtype: 'xcheckbox',
						hideLabel: true,
						boxLabel: t('Remove domain from username', 'imapauthenticator'),
						name: 'removeDomainFromUsername',
						hint: t("Users must login with their full e-mail adress. Enable this option if the IMAP excepts the username without domain.")
					}]
			}), this.smtpFieldSet = new Ext.form.FieldSet({
				hidden: true,
				hideMode: "offsets",
				title: 'SMTP Server',
				xtype: 'fieldset',
				defaults: {
					anchor: '100%'
				},
				items: [{
						xtype: 'textfield',
						name: 'smtpHostname',
						fieldLabel: t('Hostname'),
					}, {
						xtype: 'numberfield',
						name: 'smtpPort',
						fieldLabel: t('Port'),
						decimals: 0,
						value: 587
					}, {
						xtype: 'xcheckbox',
						hideLabel: true,
						boxLabel: t('Use user credentials', 'imapauthenticator'),
						name: 'smtpUseUserCredentials',
						hint: t("Enable this if the SMTP server credentials are identical to the IMAP server.", "imapauthenticator"),
						listeners: {
							check: function (checkbox, checked) {
								this.formPanel.getForm().findField('smtpUsername').setDisabled(checked);
								this.formPanel.getForm().findField('smtpPassword').setDisabled(checked);
							},
							scope: this
						}
					}, {
						xtype: 'textfield',
						name: 'smtpUsername',
						fieldLabel: t('Username')
					}, {
						xtype: 'textfield',
						name: 'smtpPassword',
						fieldLabel: t('Password')
					}, {
						xtype: 'combo',
						name: 'smtpEncryption',
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
						value: 'tls'
					}, {
						xtype: 'xcheckbox',
						hideLabel: true,
						boxLabel: t('Validate certificate'),
						name: 'smtpValidateCertificate',
						checked: true
					}]
			}), {
				xtype: 'fieldset',
				title: t("User options", "ldapauthenticator"),
				items: [
					new go.form.multiselect.Field({
						hint: t("Users will automatically be added to these groups", "ldapauthenticator"),
						name: "groups",
						idField: "groupId",
						displayField: "name",
						entityStore: go.Stores.get("Group"),
						
						fieldLabel: t("Groups"),
						storeBaseParams:{
							filter: {"includeUsers" : false}
						}
					})
				]
			}
		];
	}
});

