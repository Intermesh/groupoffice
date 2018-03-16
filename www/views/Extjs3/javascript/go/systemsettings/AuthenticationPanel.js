go.systemsettings.AuthenticationPanel = Ext.extend(Ext.Panel, {
	initComponent: function () {
		Ext.apply(this, {
			title: t('Authentication'),
			autoScroll: true,
			iconCls: 'ic-lock'			
		});

		go.systemsettings.AuthenticationPanel.superclass.initComponent.call(this);
	},

	submit: function (cb, scope) {
//		go.Jmap.request({
//			method: "core/core/Settings/set",
//			params: this.getForm().getFieldValues(),
//			callback: function (options, success, response) {
//				cb.call(scope, success);
//			},
//			scop: scope
//		});
	},

	load: function (cb, scope) {
//		go.Jmap.request({
//			method: "core/core/Settings/get",
//			callback: function (options, success, response) {
//				this.getForm().setValues(response);
//
//				cb.call(scope, success);
//			},
//			scope: this
//		});
	}


});


