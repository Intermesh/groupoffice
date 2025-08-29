go.modules.community.ldapauthenticator.ServerForm = Ext.extend(go.form.Dialog, {
	title: t('Server profile', 'ldapauth'),
	entityStore: "LdapAuthServer",
	width: dp(600),
	height: dp(600),
	autoScroll: true,

	initComponent: function () {
		this.supr().initComponent.call(this);

		this.formPanel.on("beforesubmit", function (form, values) {
			if (!this.formPanel.getForm().findField('ldapUseAuthentication').getValue()) {
				values.username = "";
				values.password = "";
			}

			if(!this.createEmailCheckbox.getValue()) {
				values.imapHostname = "";
			}
		}, this);
	},

	onLoad: function () {

		this.createEmailCheckbox.setValue(!GO.util.empty(this.formPanel.getForm().findField('imapHostname').getValue()));
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

				hint: t("Enter the domains this ldap server should be used to authenticate. Users must login with their e-mail address and if the domain matches this profile it will be used."),
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
					fieldLabel: t("Hostname"),
					required: true
				}, {
					xtype: 'numberfield',
					decimals: 0,
					name: 'port',
					fieldLabel: t("Port"),
					required: true,
					value: 389
				}, {
					xtype: 'selectfield',
					name: 'encryption',
					fieldLabel: t('Encryption'),
					options: [['tls', 'TLS'], ['ssl', 'SSL'], [null, 'None']],
					value: 'tls'
				}, {
					xtype: "checkbox",
					hideLabel: true,
					name: 'ldapVerifyCertificate',
					boxLabel: t("Verify SSL certicate"),
					checked: true
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
					fieldLabel: t('Username'),
					autocomplete: "new-password",
					hint: "cn=Administrator,dc=com"
				}, {
					xtype: 'textfield',
					name: 'password',
					disabled: true,
					fieldLabel: t('Password'),
					inputType: "password",
					autocomplete: "new-password"
				}, {
					xtype: "checkbox",
					fieldLabel: t("Follow referrals"),
					name: 'followReferrals',
					checked: true,
					hint: t("For older Microsoft ActiveDirectory installation this has to be disabled")
				}
			]
		}, this.usersFieldSet = new Ext.form.FieldSet({
			title: t("Users"),
			defaults: {
				anchor: '100%'
			},
			items: [
				{
					xtype: 'textfield',
					name: 'usernameAttribute',
					fieldLabel: t("Username attribute"),
					value: "uid",
					required: true,
					hint: t("Use 'samaccountname' for Microsoft ActiveDirectory.")
				},
				{
					xtype: "checkbox",
					hideLabel: true,
					name: 'loginWithEmail',
					boxLabel: t("Login with e-mail address")
				},
				{
					xtype: 'textarea',
					grow: true,
					name: 'syncUsersQuery',
					fieldLabel: t("User query"),
					required: true,
					value: "(objectClass=InetOrgPerson)",
					hint: t("For Microsoft ActiveDirectory use '(objectCategory=InetOrgPerson)'")
				},
				{
					xtype: 'textfield',
					name: 'peopleDN',
					fieldLabel: "peopleDN",
					value: "ou=people,dc=example,dc=com",
					hint: t("For Microsoft ActiveDirectory it's typically 'cn=Users,dc=example,dc=com'."),
					required: true
				}, {
					xtype: 'textfield',
					name: 'groupsDN',
					fieldLabel: "groupsDN",
					value: "ou=people,dc=example,dc=com",
					hint: t("For Microsoft ActiveDirectory it's typically 'cn=Groups,dc=example,dc=com'."),
					required: true
				}, this.createEmailCheckbox = new Ext.form.Checkbox({
					xtype: "checkbox",
					submit: false,
					hideLabel: true,
					boxLabel: t("Create e-mail account for users"),
					name: 'createUserEmail',
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
				xtype: 'checkbox',
				hideLabel: true,
				boxLabel: t("Use e-mail instead of LDAP username as IMAP username"),
				name: 'imapUseEmailForUsername'
			}, {
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
				xtype: 'checkbox',
				checked: true,
				hideLabel: true,
				boxLabel: t('Validate certificate'),
				name: 'imapValidateCertificate'
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
				xtype: 'checkbox',
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
				xtype: 'checkbox',
				hideLabel: true,
				boxLabel: t('Validate certificate'),
				name: 'smtpValidateCertificate',
				checked: true
			}]
		}), {
			xtype: 'fieldset',
			title: t("User options"),
			items: [
				new go.form.multiselect.Field({
					hint: t("Users will automatically be added to these groups"),
					name: "groups",
					idField: "groupId",
					displayField: "name",
					entityStore: "Group",

					fieldLabel: t("Groups"),
					storeConfig: {filters: {default: {hideUsers: true}}}
				})
			]
		}, {
			xtype: "fieldset",
			title: t("Synchronization"),
			defaults: {
				anchor: '100%'
			},
			items: [
				{
					xtype: 'checkbox',
					checked: false,
					hideLabel: true,
					boxLabel: t('Synchronize users'),
					name: 'syncUsers',
					listeners: {
						scope: this,
						check: function (cb, checked) {
							const cb2 = this.formPanel.form.findField('syncUsersDelete');
							cb2.setValue(false);
							cb2.setVisible(checked);
						}
					}
				}, {
					hidden: true,
					xtype: 'checkbox',
					checked: false,
					hideLabel: true,
					boxLabel: t('Delete users'),
					name: 'syncUsersDelete',
					listeners: {
						scope: this,
						check: function (cb, checked) {
							this.formPanel.form.findField('syncUsersMaxDeletePercentage').setVisible(checked)
						}
					}
				}, {
					hidden: true,
					xtype: "gonumberfield",
					name: 'syncUsersMaxDeletePercentage',
					fieldLabel: t("Max delete percentage"),
					value: 5,
					decimals: 0
				},{

					xtype: 'checkbox',
					checked: false,
					hideLabel: true,
					boxLabel: t('Synchronize groups'),
					name: 'syncGroups',
					listeners: {
						scope: this,
						check: function (cb, checked) {
							const cb2 = this.formPanel.form.findField('syncGroupsDelete');
							cb2.setValue(false);
							cb2.setVisible(checked);
						}
					}
				}, {
					hidden: true,
					xtype: 'checkbox',
					checked: false,
					hideLabel: true,
					boxLabel: t('Delete groups'),
					name: 'syncGroupsDelete',
					listeners: {
						scope: this,
						check: function (cb, checked) {
							this.formPanel.form.findField('syncGroupsMaxDeletePercentage').setVisible(checked)
						}
					}
				},{
					hidden: true,
					xtype: "gonumberfield",
					name: 'syncGroupsMaxDeletePercentage',
					fieldLabel: t("Max delete percentage"),
					value: 5,
					decimals: 0
				}, {
					xtype: 'textarea',
					grow: true,
					name: 'syncGroupsQuery',
					fieldLabel: t("Group query"),
					required: true,
					value: "(objectClass=Group)",
					hint: t("For Microsoft ActiveDirectory use '(objectCategory=group)'")
				}
			]
		}
		];
	}
});

