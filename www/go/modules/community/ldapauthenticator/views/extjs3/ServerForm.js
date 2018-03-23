go.modules.community.ldapauthenticator.ServerForm = Ext.extend(go.form.FormWindow, {
	title: t('Server profile', 'ldapauth'),
	entityStore: go.stores.LdapAuthServer,
	width: dp(400),
	height: dp(600),
	autoScroll: true,
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
						xtype: 'textfield',
						name: 'usernameAttribute',
						fieldLabel: t("Username attribute", "ldapauthenticator"),
						value: "uid",
						required: true
					},{
						xtype: 'textfield',
						name: 'peopleDN',
						fieldLabel: "peopleDN",
						value: "ou=people,dc=example,dc=com	",
						required: true
					},{
						xtype: 'textfield',
						name: 'groupsDN',
						fieldLabel: "groupsDN",
						value: "ou=groups,dc=example,dc=com	",
						required: true
					}]
			}
		];
	}
});

