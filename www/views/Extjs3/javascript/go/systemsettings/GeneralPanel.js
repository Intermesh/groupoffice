go.systemsettings.GeneralPanel = Ext.extend(Ext.form.FormPanel, {
	initComponent: function () {
		Ext.apply(this, {
			title: t('General'),
			autoScroll: true,
			iconCls: 'ic-description',
			items: [{
					xtype: "fieldset",
					items: [
						{
							xtype: 'textfield',
							name: 'title',
							fieldLabel: t('Title')							
						},
						this.languageCombo = new Ext.form.ComboBox({
							fieldLabel: t('Language'),
							name: 'language',
							store: new Ext.data.SimpleStore({
								fields: ['id', 'language'],
								data: GO.Languages
							}),
							displayField: 'language',
							valueField: 'id',
							hiddenName: 'language',
							mode: 'local',
							triggerAction: 'all',
							editable: false,
							selectOnFocus: true,
							forceSelection: true,
							hint: t("The language is automatically detected from the browser. If the language is not available then this language will be used.")
						})
					]
				}]
		});

		go.systemsettings.NotificationsPanel.superclass.initComponent.call(this);
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

