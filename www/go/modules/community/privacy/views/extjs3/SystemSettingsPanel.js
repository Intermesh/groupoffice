
/* global go, Ext */

go.modules.community.privacy.SystemSettingsPanel = Ext.extend(go.systemsettings.Panel, {
	hasPermission: function () {
		return go.User.isAdmin;
	},
	title: t("Privacy Settings", "privacy", "community"),
	iconCls: 'ic-security',
	labelWidth: 125,
	itemId: "privacy", //makes it routable

	initComponent: function () {
		this.items = [{
			xtype: "fieldset",
			items: [{
				xtype: "gonumberfield",
				minValue: 0,
				decimals: 0,
				fieldLabel: t("Inactivity period"),
				hint: t("Move inactive contacts to trash after the configured number of months"),
				name: "trashAfterXMonths",
				allowBlank: false
			}, {
				xtype: "gonumberfield",
				minValue: 0,
				decimals: 0,
				fieldLabel: t("Warning period"),
				hint: t("Warn configured number of days before moving contact to trash"),
				name: "warnXDaysBeforeDeletion",
				allowBlank: false
			}, this.monChips = new go.form.Chips({
				fieldLabel: t("Address books"),
				hint: t("These address books are to be monitored for inactive contacts"),
				entityStore: "AddressBook",
				allowBlank: false,
				listeners: {
					change: function (combo, newVal, oldVal) {
						this.trashCombo.store.setFilter("default", {
							exclude: newVal.length ? newVal : null
						}).reload();
						this.hiddenABFld.setValue(newVal.join(","));
					},
					scope: this
				}
			}), this.trashCombo = new go.modules.community.addressbook.AddresBookCombo({ /* TODO: exclude monitorAddressBooks */
					hiddenName: 'trashAddressBook',
					fieldLabel: 'Trash address book',
					hint: t("Expired contacts will be moved into this address book"),
					allowBlank: false,
					listeners: {
						change: function(combo, newVal, oldVal) {
							this.monChips.store.setFilter("default", {
								exclude: go.util.empty(newVal) ? null : [newVal]
							}).reload();
						},
						scope: this
					}
				}), this.hiddenABFld = new Ext.form.Hidden( {
					id: 'monitorAddressBooks',
					name: 'monitorAddressBooks'
				})
			]
		}];

		go.modules.community.privacy.SystemSettingsPanel.superclass.initComponent.call(this);
	},

	onSubmit: function (cb, scope) {
	 	this.monChips.disable();
		go.modules.community.privacy.SystemSettingsPanel.superclass.onSubmit.call(this,cb,scope);
	},
	loadSettings: function () {

		go.modules.community.privacy.SystemSettingsPanel.superclass.loadSettings.call(this);
		const module = go.Modules.get(this.package, this.module),
			v = module.settings,
			arAB = v.monitorAddressBooks.split(",");

		this.monChips.setValue(arAB)

	}
});
