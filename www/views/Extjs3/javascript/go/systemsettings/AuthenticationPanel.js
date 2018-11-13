go.systemsettings.AuthenticationPanel = Ext.extend(Ext.form.FormPanel, {
	initComponent: function () {
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
						}
					]
			}]
		});
		
		if (GO.authenticationDomains.length) {
			this.domainCombo = new go.login.DomainCombo({
				fieldLabel: t("Default domain"),
				hiddenName: "defaultAuthenticationDomain",
				listeners: {
					scope: this,
					beforequery: function() {
						Ext.Ajax.request({
							method: "GET",
							jsonData: {},
							url: go.User.apiUrl,
							callback: function(options, success, response) {
								var result = Ext.decode(response.responseText);								
								this.domainCombo.setDomains(result.auth.domains);		
								this.domainCombo.expand();
							},
							scope: this
						});
						
						return false;
					}
				}
			});
			
			this.items[0].items.push(this.domainCombo);
		}

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


