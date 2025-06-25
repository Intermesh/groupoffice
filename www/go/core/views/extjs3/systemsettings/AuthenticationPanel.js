go.systemsettings.AuthenticationPanel = Ext.extend(go.systemsettings.Panel, {
	hasPermission: function() {
		return go.User.isAdmin;
	},
	itemId: "authentication", //makes it routable
	initComponent: function () {
		this.domainCombo = GO.SystemSettingsDomainCombo = new go.login.DomainCombo({
			fieldLabel: t("Default domain"),
			// hidden: go.User.session.auth.domains.authenticationDomains.length === 0,
			hiddenName: "defaultAuthenticationDomain",
			hint: t("Users can login without this domain behind the username. Note that if the user exists in the Group-Office database it will take precedence.")
		});		
		
		Ext.apply(this, {
			title: t('Authentication'),
			autoScroll: true,
			iconCls: 'ic-lock',
			items: [{
					xtype:"fieldset",
					title: t("Password"),
					items: [
						{
							xtype:"numberfield",
							fieldLabel: t("Minimum length"),
							name: "passwordMinLength",
							decimals: 0,
							value: 6,
							width: dp(184)
						},
						this.domainCombo,
						{
							xtype:"numberfield",
							fieldLabel: t("Logout when inactive"),
							hint: t("Logout users when inactive for more than this number of seconds. This will also disable the 'Remember my login' checkbox in the login dialog. 0 disables this setting."),
							name: "logoutWhenInactive",
							decimals: 0,
							value: 0,
							width: dp(184)
						},
						{
							xtype:"textfield",
							fieldLabel: t("Lost password URL"),
							hint: t("You can set an URL to handle lost passwords in an alternative way"),
							name: "lostPasswordURL",
							anchor: "100%"
						}
					]
				},
				{
					xtype: "fieldset",
					title: t("Allowed groups"),
					items: [
						{
							xtype: "box",
							autoEl: "p",
							html: t("Define which groups are allowed to login from which IP addresses. You can use '*' to match any charachters and '?'" +
								" to match any single character. eg. '192.168.1?.*'. Be careful, You can lock yourself out!")
						},
						new go.systemsettings.AuthAllowGroupGrid({
							border: true
						})
					]
				},{
					xtype: "fieldset",
					title: t("Synchronization"),
					items: [
						{
							xtype: "checkbox",
							hideLabel: true,
							boxLabel: t("Enable 2-Factor authentication for ActiveSync devices"),
							name: "activeSyncEnable2FA"
						},{
							xtype: "checkbox",
							hideLabel: true,
							boxLabel: t("ActiveSync devices can connect by default."),
							hint: t("When disabled the administrator has to allow each new device manually"),
							name: "activeSyncCanConnect"
						}
					]

				},
				{
					xtype: "fieldset",
					title: t("API settings"),
					items: [
						{
							xtype: "box",
							autoEl: "p",
							html: t("Allow Cross Origin Requests from these origins")
						},
						{
							xtype: "formgroup",
							hideLabel: false,
							fieldLabel: t("CORS origins"),

							name: "corsAllowOrigin",
							itemCfg: {
								xtype: "textfield",
								hideLabel: true,
								placeholder: 'eg. https://example.com'
							},
							anchor: "100%"
						}, {
							xtype: "checkbox",
							hideLabel: true,
							boxLabel: t("Allow creation of users through the API"),
							hint: t("When enabled, you should restrict access for the 'Everyone' group as much as possible. Use with caution."),
							name: "allowRegistration"
						}
					]

				},

			]
		});

		go.systemsettings.AuthenticationPanel.superclass.initComponent.call(this);
	}

});


