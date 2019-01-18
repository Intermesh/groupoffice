
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
			]}
		];

		go.modules.community.addressbook.SettingsPanel.superclass.initComponent.call(this);
	}

});

