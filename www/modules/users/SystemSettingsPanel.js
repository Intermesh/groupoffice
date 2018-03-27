GO.users.SystemSettingsPanel = Ext.extend(Ext.form.FormPanel, {
	initComponent: function () {
		Ext.apply(this, {
			title: t('User defaults'),
			autoScroll: true,
			iconCls: 'ic-description',
			items: [{
					xtype: "fieldset",
					items: [{
							xtype: "selectcountry",
							name: "defaultCountry",
							value: "NL",
							fieldLabel: t("Country")
						},
						new Ext.form.ComboBox({
							fieldLabel: t("Timezone"),
							name: 'timezone',
							store: new Ext.data.SimpleStore({
								fields: ['timezone'],
								data: GO.users.TimeZones
							}),
							displayField: 'timezone',
							mode: 'local',
							triggerAction: 'all',
							selectOnFocus: true,
							forceSelection: true
						}), {
							xtype: "textfield",
							name: "defaultCurrency",
							value: "€",
							fieldLabel: t("Currency")
						}, {
							xtype: "textfield",
							name: "defaultDateFormat",
							value: "d-m-Y",
							fieldLabel: t("Date format")
						},
						new Ext.form.ComboBox({
							fieldLabel: t("Time Format", "users"),
							store: new Ext.data.SimpleStore({
								fields: ['id', 'format'],
								data: [
									['H:i', t("24 hour format", "users")],
									['h:i a', t("12 hour format", "users")]
								]
							}),
							displayField: 'format',
							valueField: 'id',
							hiddenName: 'defaultTimeFormat',
							mode: 'local',
							triggerAction: 'all',
							editable: false,
							selectOnFocus: true,							
							forceSelection: true
						}),
						new Ext.form.ComboBox({
							fieldLabel: t("First weekday", "users"),							
							store: new Ext.data.SimpleStore({
								fields: ['id', 'day'],
								data: [
									[0, t("Sunday", "users")],
									[1, t("Monday", "users")]
								]
							}),
							displayField: 'day',
							valueField: 'id',
							hiddenName: 'defaultFirstWeekday',
							mode: 'local',
							triggerAction: 'all',
							editable: false,
							selectOnFocus: true,
							forceSelection: true,
							value: 1
						})
					]
				}]
		});

		go.systemsettings.NotificationsPanel.superclass.initComponent.call(this);
	},

	submit: function (cb, scope) {
		go.Jmap.request({
			method: "core/users/Settings/set",
			params: this.getForm().getFieldValues(),
			callback: function (options, success, response) {
				cb.call(scope, success);
			},
			scop: scope
		});
	},

	load: function (cb, scope) {
		go.Jmap.request({
			method: "core/users/Settings/get",
			callback: function (options, success, response) {
				this.getForm().setValues(response);

				cb.call(scope, success);
			},
			scope: this
		});
	}


});


