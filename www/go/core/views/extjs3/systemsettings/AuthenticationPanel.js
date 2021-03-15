go.systemsettings.AuthenticationPanel = Ext.extend(go.systemsettings.Panel, {
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
							width: dp(72)
						},
						this.domainCombo,
						{
							xtype:"numberfield",
							fieldLabel: t("Logout when inactive"),
							hint: t("Logout users when inactive for more than this number of seconds. This will also disable the 'Remember my login' checkbox in the login dialog. 0 disables this setting."),
							name: "logoutWhenInactive",
							decimals: 0,
							value: 0,
							width: dp(72)
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
				}

			]
		});

		go.systemsettings.AuthenticationPanel.superclass.initComponent.call(this);
	}

});


