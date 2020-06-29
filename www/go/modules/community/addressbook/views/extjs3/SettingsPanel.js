
/* global go, Ext */

go.modules.community.addressbook.SettingsPanel = Ext.extend(Ext.Panel, {
	title: t("Address book"),
	iconCls: 'ic-contacts',
	labelWidth: 125,
	layout: "form",
	initComponent: function () {

		//The account dialog is an go.form.Dialog that loads the current User as entity.
		this.items = [{
			xtype: "fieldset",
			items: [{
					xtype: "addressbookcombo",
					hiddenName: "addressBookSettings.defaultAddressBookId",
					fieldLabel: t("Default address book"),
					allowBlank: true
				}

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
			]}
		];

		go.modules.community.addressbook.SettingsPanel.superclass.initComponent.call(this);
	}

});

