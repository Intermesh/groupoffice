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
				{
					xtype: "fieldset",
					title: t("Allowed groups"),
					items: [
						{
							xtype: "box",
							autoEl: "p",
							html: t("Define which groups are allowed to login from which IP addresses. You can use '*' to match any charachters and '?'" +
								" to match any single character. eg. '192.168.1?.*'. <strong>Be careful!</strong> You can lock yourself out!")
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


