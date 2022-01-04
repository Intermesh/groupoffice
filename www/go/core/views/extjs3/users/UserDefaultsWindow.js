
/* global Ext, go */

go.users.UserDefaultsWindow = Ext.extend(go.Window, {
	title: t("User settings"),

	modal: true,
	width: dp(800),
	height: dp(700),
	layout: "fit",
	initComponent: function () {

		this.formPanel = new go.systemsettings.Panel({
			items: [
				{
					layout: "column",
					items: [
						{
							columnWidth: .6,
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
									fieldLabel: t('Date format', "users", "core"),

									store: go.util.Format.dateFormats,
									displayField: 'label',
									valueField: 'format',
									hiddenName: 'defaultDateFormat',
									mode: 'local',
									triggerAction: 'all',
									editable: false,
									selectOnFocus: true,
									forceSelection: true
								}),
								new Ext.form.ComboBox({
									fieldLabel: t("Time Format", "users", "core"),
									store: go.util.Format.timeFormats,
									displayField: 'label',
									valueField: 'format',
									hiddenName: 'defaultTimeFormat',
									mode: 'local',
									triggerAction: 'all',
									editable: false,
									selectOnFocus: true,
									forceSelection: true
								}),
								{
									xtype: "xcheckbox",
									name: "defaultShortDateInList",
									checked: true,
									hideLabel: true,
									boxLabel: t("Use short format for date and time in lists", 'users', 'core')
								},
								new Ext.form.ComboBox({
									fieldLabel: t("First weekday", "users", "core"),
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
							columnWidth: .4,
							xtype: "fieldset",
							labelWidth: dp(180),
							defaults: {width: dp(50)},
							title: t('Formatting'),
							items: [
								{
									xtype: 'textfield',
									fieldLabel: t("List separator", "users", "core"),
									name: 'defaultListSeparator'
								}, {
									xtype: 'textfield',
									fieldLabel: t("Text separator", "users", "core"),
									name: 'defaultTextSeparator'
								}, {
									xtype: 'textfield',
									fieldLabel: t("Thousand Seperator", "users", "core"),
									name: 'defaultThousandSeparator'
								},
								{
									xtype: 'textfield',
									fieldLabel: t("Decimal Seperator", "users", "core"),
									name: 'defaultDecimalSeparator'
								}, {
									xtype: "textfield",
									name: "defaultCurrency",
									value: "€",
									fieldLabel: t("Currency", "users", "core")
								}

							]
						}


					]
				},
				this.otherFieldSet = new Ext.form.FieldSet({
					title: t("Other"),
					xtype: "fieldset",
					items: [
						{
							xtype: 'xcheckbox',
							name: 'defaultConfirmOnMove',
							hideLabel: true,
							boxLabel: t('Show confirmation dialog on move'),
							hint: t("When this is on and items are moved by dragging, confirmation is requested")
						},
						new go.form.multiselect.Field({
							valueIsId: true,
							hint: t("Users will automatically be added to these groups", "users", "core"),
							name: "defaultGroups",
							idField: "groupId",
							displayField: "name",
							entityStore: "Group",

							fieldLabel: t("Groups"),
							storeBaseParams: {
								filter: {hideUsers: true, excludeEveryone: true}
							}
						})]
				})
			]
		});
		
		if(go.Modules.get('community', 'addressbook')) {
			this.otherFieldSet.add({
				xtype: 'addressbookcombo',
				hiddenName: 'userAddressBookId'
			});
		}
		


		this.items = [this.formPanel];

		this.bbar = ['->', {
			cls:  "primary",
			text: t("Save"),
			handler: function () {
				this.submit();
			},
			scope: this
		}];

		go.users.UserDefaultsWindow.superclass.initComponent.call(this);
	},

	submit: function () {

		this.getEl().mask();
		
		this.formPanel.onSubmit(function (options, success, response) {
			this.getEl().unmask();
			this.close();
		},
		this);
	}

});



