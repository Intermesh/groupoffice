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

		go.systemsettings.AuthenticationPanel.superclass.initComponent.call(this);
	},

	submit: function (cb, scope) {
		go.Jmap.request({
			method: "core/core/Settings/set",
			params: this.getForm().getFieldValues(),
			callback: function (options, success, response) {
				cb.call(scope, success);
			},
			scop: scope
		});
	},

	load: function (cb, scope) {
		go.Jmap.request({
			method: "core/core/Settings/get",
			callback: function (options, success, response) {
				this.getForm().setValues(response);

				cb.call(scope, success);
			},
			scope: this
		});
	}


});


