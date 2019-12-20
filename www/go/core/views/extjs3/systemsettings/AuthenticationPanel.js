go.systemsettings.AuthenticationPanel = Ext.extend(go.systemsettings.Panel, {
	initComponent: function () {
		this.domainCombo = GO.SystemSettingsDomainCombo = new go.login.DomainCombo({
			fieldLabel: t("Default domain"),
			hidden: GO.authenticationDomains.length === 0,
			hiddenName: "defaultAuthenticationDomain"
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
							width: dp(48)
						},
						this.domainCombo
					]
			},

				new go.systemsettings.AuthAllowGroupGrid()
			]
		});

		go.systemsettings.AuthenticationPanel.superclass.initComponent.call(this);
	}

});


