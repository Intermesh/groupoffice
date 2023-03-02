
/* global go, Ext */

go.modules.community.addressbook.SystemSettingsPanel = Ext.extend(go.systemsettings.Panel, {

	title: t("Address book"),
	iconCls: 'ic-contacts',
	labelWidth: 125,
	initComponent: function () {

		//The account dialog is an go.form.Dialog that loads the current User as entity.
		this.items = [{
			xtype: "fieldset",
			items: [
				{
					hideLabel: true,
					xtype: "checkbox",
					boxLabel: t("Create personal address book for each user"),
					name: "createPersonalAddressBooks"
				// },
				// {
				// 	hideLabel: true,
				// 	xtype: "checkbox",
				// 	boxLabel: t("Automatically link e-mail to contacts"),
				// 	name: "autoLinkEmail",
				// 	disabled: !GO.savemailas,
				// 	hint: t("Warning: this will copy e-mails to the Group-Office storage and will therefore increase disk space usage. The e-mail will be visible to all people that can view the contact too.")
				},new (Ext.extend(go.form.RadioGroup, {
					setValue(v) {
						this.toggleSibling(v);
						this.supr().setValue.call(this, v);
					},
					toggleSibling(v) {
						switch(v) {
							case 'off':
							case 'on':
								this.nextSibling().hide();
								break;
							case 'excl':
							case 'incl':
								this.nextSibling().show();
						}
					}
				}))({
					columns:1,
					fieldLabel: t("Automatic linking"),
					name:"autoLink",
					disabled: !GO.savemailas,
					hint: t("Warning: this will copy e-mails to the Group-Office storage and will therefore increase disk space usage. The e-mail will be visible to all people that can view the contact too."),
					listeners: {
						'change': (me,v) => {
							me.toggleSibling(v.inputValue);
						}
					},
					items: [
						{boxLabel: t("Don't link automatically to contacts"), inputValue: 'off'},
						{boxLabel: t("Link to all contacts"), inputValue: 'on'},
						{boxLabel: t("Exclude contacts from the address books below"), inputValue: 'excl'},
						{boxLabel: t("Only link to the contacts from the address books below"), inputValue: 'incl'}
					]

				}), {
				xtype:'container',
					hidden:true,
					disabled: !GO.savemailas,
					items: [
						new go.form.Chips({
							name:'autoLinkAddressBookIds',
							fieldLabel: t("Address book"),
							entityStore: "AddressBook"
						})
					]
				}]
			// As per 6.6, this permission has been dropped in favor of the module permission mayExportContacts
				// ,{
				// 	hideLabel: true,
				// 	xtype: "checkbox",
				// 	boxLabel: t("Restrict export to administrators"),
				// 	name: "restrictExportToAdmins"
				// }]
		}];

		go.modules.community.addressbook.SystemSettingsPanel.superclass.initComponent.call(this);

	},

});