go.modules.core.users.SystemSettingsUserDefaults = Ext.extend(Ext.form.FormPanel, {
	initComponent: function () {
		Ext.apply(this, {
			title: t('User defaults'),
			autoScroll: true,
			iconCls: 'ic-description',
			items: [{
					xtype: "fieldset",
					title: t("Regional"),
					labelWidth: dp(160),
					items: [
						new Ext.form.ComboBox({
							fieldLabel: t("Timezone", "users"),
							name: 'defaultTimezone',
							store: new Ext.data.SimpleStore({
								fields: ['timezone'],
								data: go.TimeZones
							}),
							displayField: 'timezone',
							mode: 'local',
							triggerAction: 'all',
							selectOnFocus: true,
							forceSelection: true
						}), new Ext.form.ComboBox({
							fieldLabel: t('Date format'),

							store: new Ext.data.SimpleStore({
								fields: ['id', 'dateformat'],
								data: [
									['d-m-Y', t("Day-Month-Year", "users")],
									['m/d/Y', t("Month/Day/Year", "users")],
									['d/m/Y', t("Day/Month/Year", "users")],
									['d.m.Y', t("Day.Month.Year", "users")],
									['Y-m-d', t("Year-Month-Day", "users")],
									['Y.m.d', t("Year.Month.Day", "users")]
								]
							}),
							displayField: 'dateformat',
							valueField: 'id',
							hiddenName: 'defaultDateFormat',
							mode: 'local',
							triggerAction: 'all',
							editable: false,
							selectOnFocus: true,
							forceSelection: true
						}),
						new Ext.form.ComboBox({
							fieldLabel: t("Time Format", "users"),
							store: new Ext.data.SimpleStore({
								fields: ['id', 'format'],
								data: [
									['G:i', t("24 hour format", "users")],
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
				},

				{
					xtype: "fieldset",
					labelWidth: dp(240),
					defaults: {width: dp(50)},
					title: t('Formatting'),
					items: [
						{
							xtype: 'textfield',
							fieldLabel: t("List separator", "users"),
							name: 'defaultListSeparator',							
						}, {
							xtype: 'textfield',
							fieldLabel: t("Text separator", "users"),
							name: 'defaultTextSeparator'
						}, {
							xtype: 'textfield',
							fieldLabel: t("Thousand Seperator", "users"),
							name: 'defaultThousandSeparator'
						},
						{
							xtype: 'textfield',
							fieldLabel: t("Decimal Seperator", "users"),
							name: 'defaultDecimalSeparator'
						}, {
							xtype: "textfield",
							name: "defaultCurrency",
							value: "€",
							fieldLabel: t("Currency")
						}
					]
				}, {
					title: t("Other"),
					xtype: "fieldset",
					items: [
						new go.form.multiselect.Field({
							hint: t("Users will automatically be added to these groups", "users"),
							name: "defaultGroups",
							idField: "groupId",
							displayField: "name",
							entityStore: go.Stores.get("Group"),

							fieldLabel: t("Groups"),
							storeBaseParams: {
								filter: [{"includeUsers": false}]
							}
						})]
				}


			]
		});

		go.modules.core.users.SystemSettingsUserDefaults.superclass.initComponent.call(this);
		
		
		this.on('render', function() {
			go.Jmap.request({
				method: "core/users/Settings/get",
				callback: function (options, success, response) {
					this.getForm().setValues(response);
				},
				scope: this
			});
		}, this);
	},

	onSubmit: function (cb, scope) {
		go.Jmap.request({
			method: "core/users/Settings/set",
			params: this.getForm().getFieldValues(),
			callback: function (options, success, response) {
				cb.call(scope, this, success);
			},
			scop: scope
		});
	}

});



