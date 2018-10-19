/* global Ext, go, GO */

/**
 * 
 * @type |||
 */
go.modules.community.addressbook.ContactCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Contact"),
	hiddenName: 'contactId',
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	allowBlank: false,
	/**
	 * Set to true to show organizations, set to null to show both.
	 */
	isOrganization : false,
	initComponent: function () {
		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: ['id', 'name'],
				entityStore: go.Stores.get("Contact"),
				baseParams: {
					filter: {
						addressBookId: this.addressBookId,
						permissionLevel: this.permissionLevel || GO.permissionLevels.write,						
					}
				}
			})
		});
		
		if(Ext.isDefined(this.isOrganization)) {
			this.store.baseParams.filter.isOrganization = this.isOrganization;
		}

		go.modules.community.addressbook.ContactCombo.superclass.initComponent.call(this);

	}
});

Ext.reg("contactcombo", go.modules.community.addressbook.ContactCombo);
