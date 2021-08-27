/* global go, Ext */

go.modules.community.addressbook.SettingsPanel = Ext.extend(Ext.Panel, {
	title: t("Address book"),
	iconCls: 'ic-contacts',
	labelWidth: 125,
	layout: "form",
	initComponent: function () {
		var me = this;
		//The account dialog is an go.form.Dialog that loads the current User as entity.
		this.items = [{
			xtype: "fieldset",
			title: t("Display options for address books", "addressbook", "community"),
			items: [
				{
					xtype: "addressbookcombo",
					hiddenName: "addressBookSettings.defaultAddressBookId",
					fieldLabel: t("Default address book"),
					allowBlank: true,
				},
				this.defaultAddressBookOptions = new go.form.RadioGroup({
					allowBlank: false,

					fieldLabel: t("Start in"),
					name: 'addressBookSettings.startIn',
					columns: 1,

					items: [
						{
							boxLabel: t("All contacts"),
							inputValue: 'allcontacts'
						},
						{
							boxLabel: t("Starred"),
							inputValue: 'starred'
						},
						{
							boxLabel: t("Default address book"),
							inputValue: 'default'
						},
						{
							inputValue: 'remember',
							boxLabel: t("Last selected address book")
						}
					]
				}),


				// , {
				// 	xtype: 'combo',
				// 	name: 'addressBookSettings.sortBy',
				// 	fieldLabel: t('Sort name by'),
				// 	mode: 'local',
				// 	editable: false,
				// 	triggerAction: 'all',
				// 	store: new Ext.data.ArrayStore({
				// 		fields: [
				// 			'value',
				// 			'display'
				// 		],
				// 		data: [['name', t('First name')], ['lastName', 'Last name']]
				// 	}),
				// 	valueField: 'value',
				// 	displayField: 'display',
				// 	value: 'name'
				// }
			]
		}
		];
		go.modules.community.addressbook.SettingsPanel.superclass.initComponent.call(this);
	}
});

