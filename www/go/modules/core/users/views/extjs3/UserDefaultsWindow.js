
/* global Ext, go */

go.modules.core.users.UserDefaultsWindow = Ext.extend(go.Window, {
	title: t("User defaults"),
	autoScroll: true,
	modal: true,
	width: dp(800),
	height: dp(600),
	initComponent: function () {

		this.formPanel = new Ext.form.FormPanel({
			items: [
				{
					layout: "hbox",
					items: [
						{
							flex: 1,
							xtype: "fieldset",
							title: t("Regional"),
							labelWidth: dp(160),
							items: [
								new Ext.form.ComboBox({
									fieldLabel: t("Timezone"),
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
											['d-m-Y', t("Day-Month-Year")],
											['m/d/Y', t("Month/Day/Year")],
											['d/m/Y', t("Day/Month/Year")],
											['d.m.Y', t("Day.Month.Year")],
											['Y-m-d', t("Year-Month-Day")],
											['Y.m.d', t("Year.Month.Day")]
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
									fieldLabel: t("Time Format"),
									store: new Ext.data.SimpleStore({
										fields: ['id', 'format'],
										data: [
											['G:i', t("24 hour format")],
											['h:i a', t("12 hour format")]
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
									fieldLabel: t("First weekday"),
									store: new Ext.data.SimpleStore({
										fields: ['id', 'day'],
										data: [
											[0, t("Sunday")],
											[1, t("Monday")]
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
							flex: 1,
							xtype: "fieldset",
							labelWidth: dp(240),
							defaults: {width: dp(50)},
							title: t('Formatting'),
							items: [
								{
									xtype: 'textfield',
									fieldLabel: t("List separator"),
									name: 'defaultListSeparator'
								}, {
									xtype: 'textfield',
									fieldLabel: t("Text separator"),
									name: 'defaultTextSeparator'
								}, {
									xtype: 'textfield',
									fieldLabel: t("Thousand Seperator"),
									name: 'defaultThousandSeparator'
								},
								{
									xtype: 'textfield',
									fieldLabel: t("Decimal Seperator"),
									name: 'defaultDecimalSeparator'
								}, {
									xtype: "textfield",
									name: "defaultCurrency",
									value: "€",
									fieldLabel: t("Currency")
								}

							]
						}


					]
				},
				{
					title: t("Other"),
					xtype: "fieldset",
					items: [
						new go.form.multiselect.Field({
							valueIsId: true,
							hint: t("Users will automatically be added to these users"),
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

		this.items = [this.formPanel];

		this.bbar = ['->', {
				text: t("Save"),
				handler: function () {
					this.submit();
				},
				scope: this
			}];

		go.modules.core.users.UserDefaultsWindow.superclass.initComponent.call(this);


		this.on('render', function () {
			go.Jmap.request({
				method: "core/users/Settings/get",
				callback: function (options, success, response) {
					this.formPanel.getForm().setValues(response);
				},
				scope: this
			});
		}, this);
	},

	submit: function (cb, scope) {

		this.getEl().mask();
		go.Jmap.request({
			method: "core/users/Settings/set",
			params: this.formPanel.getForm().getFieldValues(),
			callback: function (options, success, response) {
				this.getEl().unmask();
				this.close();
			},
			scope: this
		});
	}

});



