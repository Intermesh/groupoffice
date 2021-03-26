
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
			title:t(" Disylay options for address books", "tasks"),
			items: [
				this.allContactsCB = new Ext.form.Checkbox({
					name: "addressBookSettings.displayAllContactsByDefault",
					fieldLabel: t("All contacts", 'addressbook', 'communtiy'),
					allowBlank: true,
					id: 'allContactsByDefault',
					hint: t("Display all contacts in address book by default", 'addressbook', 'community'),
					// listeners: {
					// 	check: function (checkbox, checked) {
					// 		if(checked) {
					// 			Ext.getCmp('rememberLastItem').setChecked(!checked).disable();
					// 		} else {
					// 			Ext.getCmp('rememberLastItem').setChecked(!checked).enable();
					// 		}
					// 	}
					// }
				}),
				this.addressBookCombo = {
					xtype: "addressbookcombo",
					hiddenName: "addressBookSettings.defaultAddressBookId",
					fieldLabel: t("Default address book"),
					allowBlank: true,
					id: 'defaultAddressBookId',
					hint: t("Default address book to be opened unless overridden")
				},
				this.rememberLastItemCB = new Ext.form.Checkbox({
					id: 'rememberLastItem',
					name: "addressBookSettings.rememberLastItem",
					fieldLabel: t("Remember last selected item"),
					allowBlank: true,
					hint: t("Remember last selected address book when reopening the address book module", 'addressbook', 'community'),
					// listeners: {
					// 	check: function (checkbox, checked) {
					// 		if(checked) {
					// 			Ext.getCmp('allContactsByDefault').setChecked(!checked).disable();
					// 		} else {
					// 			Ext.getCmp('allContactsByDefault').setChecked(!checked).enable();
					// 		}
					// 	}
					// }
				})

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
	},

	onLoadStart: function (userId) {

	},

	onLoadComplete : function(action) {

	},

	onSubmitComplete : function(){

	}

});

