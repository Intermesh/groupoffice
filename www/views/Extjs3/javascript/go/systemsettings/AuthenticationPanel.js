go.systemsettings.AuthenticationPanel = Ext.extend(Ext.form.FormPanel, {
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
			}]
		});
		
			

		go.systemsettings.AuthenticationPanel.superclass.initComponent.call(this);
		
		this.on('render', function() {
			go.Jmap.request({
				method: "core/core/Settings/get",
				callback: function (options, success, response) {
					this.getForm().setValues(response);
				},
				scope: this
			});
		}, this);
	},

	onSubmit: function (cb, scope) {
		go.Jmap.request({
			method: "core/core/Settings/set",
			params: this.getForm().getFieldValues(),
			callback: function (options, success, response) {
				cb.call(scope, this, success);
			},
			scop: scope
		});
	}


});


