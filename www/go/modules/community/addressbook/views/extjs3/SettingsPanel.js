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
				this.addressBookCombo = {
					xtype: "addressbookcombo",
					hiddenName: "addressBookSettings.defaultAddressBookId",
					fieldLabel: t("Default address book"),
					allowBlank: true,
					id: 'defaultAddressBookId'
				},
				this.defaultAddressBookOptions = new Ext.form.RadioGroup({
					allowBlank: true,
					validationEvent: false,
					fieldLabel: t("Default behavior for address books", 'addressbook', 'community'),
					name: 'addressBookSettings.defaultAddressBookOptions',
					columns: 1,
					listeners: {
						'added': function (elm, ownerCt, index) {
							if (me.rememberLastItemHiddenCB.checked) {
								me.rememberLastItemOption.setValue(true);
							} else if (me.displayAllContactsCB.checked) {
								me.allContactsOption.setValue(true);
							} else {
								me.defaultAddressBookOption.setValue(true);
							}
						},
						'change': function (elm, rb) {
							var acbd = false, rli = false;
							if (rb.id === 'allContactsByDefault') {
								acbd = true;
							} else if (rb.id === 'rememberLastItem') {
								rli = true;
							}
							me.displayAllContactsCB.setValue(acbd);
							me.rememberLastItemHiddenCB.setValue(rli);
						}
					},
					items: [
						this.allContactsOption = new Ext.form.Radio({
							boxLabel: t("Start in All contacts", 'addressbook', 'communtiy'),
							id: 'allContactsByDefault',
							name: 'addressBookOptions',
							hint: t("Display all contacts in address book by default", 'addressbook', 'community')
						}),
						this.defaultAddressBookOption = new Ext.form.Radio({
							id: 'defaultAddressBookByDefault',
							boxLabel: t("Start in Default Address Book"),
							name: 'addressBookOptions',
							hint: t("Remember last selected address book when reopening the address book module", 'addressbook', 'community')
						}),
						this.rememberLastItemOption = new Ext.form.Radio({
							id: 'rememberLastItem',
							boxLabel: t("Remeber last selected item"),
							name: 'addressBookOptions',
							hint: t("Remember last selected address book when reopening the address book module", 'addressbook', 'community')
						})
					]
				}),
				this.rememberLastItemHiddenCB = new Ext.form.Checkbox({
					hidden: true,
					name: 'addressBookSettings.rememberLastItem',
					fieldLabel: 'rememberLastItem'
				}),
				this.displayAllContactsCB = new Ext.form.Checkbox({
					hidden: true,
					name: 'addressBookSettings.displayAllContactsByDefault',
					fieldLabel: 'displayAllContactsByDefault'
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
			]
		}
		];
		go.modules.community.addressbook.SettingsPanel.superclass.initComponent.call(this);
	},

	onSubmitStart: function (value) {
		if(value && value.addressBookSettings) {
			delete value.addressBookSettings.defaultAddressBookOptions;
		}
	},

	onLoadStart: function (userId) {

	},

	onLoadComplete: function (action) {

	},

	onSubmitComplete: function () {

	}
});

