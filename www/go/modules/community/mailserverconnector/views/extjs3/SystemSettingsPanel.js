go.modules.community.mailserverconnector.SystemSettingsPanel = Ext.extend(Ext.form.FormPanel, {
	iconCls: 'ic-lock',
	initComponent: function () {
		this.title = t("Mailserver");		
		
		this.items = [{
				xtype: 'fieldset',
				items: [{
					xtype: "textfield",
					name: "apiKey",
					fieldLabel: t("API Key"),
					anchor: "100%"
			}]
		}];
		
		go.modules.community.mailserverconnector.SystemSettingsPanel.superclass.initComponent.call(this);
		
		
		this.on('render', function () {
			go.Jmap.request({
				method: "community/mailserverconnector/Settings/get",
				callback: function (options, success, response) {
					this.getForm().setValues(response);
				},
				scope: this
			});
		}, this);
	},
	onSubmit: function (cb, scope) {
		go.Jmap.request({
			method: "community/mailserverconnector/Settings/set",
			params: this.getForm().getFieldValues(),
			callback: function (options, success, response) {
				cb.call(scope, this, success);
			},
			scop: scope
		});
	}
});
