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
	/**
	 * Set to true to show organizations, set to null to show both.
	 */
	isOrganization : false,
	initComponent: function () {
		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: ['id', 'name', "photoBlobId", {name: 'organizations', type: "relation"}, 'goUserId', 'phoneNumbers','addresses','emailAddresses','firstName', 'middleName', 'lastName', 'gender'],
				entityStore: "Contact",
				baseParams: {
					filter: {
						addressBookId: this.addressBookId,
						permissionLevel: this.permissionLevel || go.permissionLevels.write			
					}
				}
			})
		});
		
		if(Ext.isDefined(this.isOrganization)) {
			this.store.baseParams.filter.isOrganization = this.isOrganization;
		}
		
		this.tpl = new Ext.XTemplate(
				'<tpl for=".">',
				'<div class="x-combo-list-item"><div class="user">\
					 <tpl if="!photoBlobId"><div class="avatar"></div></tpl>\\n\
					 <tpl if="photoBlobId"><div class="avatar" style="background-image:url({[go.Jmap.thumbUrl(values.photoBlobId, {w: 40, h: 40, zc: 1}) ]})"></div></tpl>\
					 <div class="wrap">\
						 <div>{name}</div><small style="color:#333;">{[values.organizations ? values.organizations.column("name").join(", ") : ""]}</small>\
					 </div>\
				 </div></div>',
				'</tpl>'
		 );

		go.modules.community.addressbook.ContactCombo.superclass.initComponent.call(this);

	}
});

Ext.reg("contactcombo", go.modules.community.addressbook.ContactCombo);
