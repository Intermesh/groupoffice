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
	selectOnFocus: false,
	forceSelection: true,	
	/**
	 * Set to true to show organizations, set to null to show both.
	 */
	isOrganization : false,
	initComponent: function () {

		if(this.allowNew == undefined) {
			this.allowNew = {
				isOrganization: this.isOrganization,
				addressBookId: go.User.addressBookSettings.defaultAddressBookId
			};
		}

		var comboFilter = {
			addressBookId: this.addressBookId,
			permissionLevel: this.permissionLevel || go.permissionLevels.write
		};

		if(Ext.isDefined(this.isOrganization)) {
			comboFilter.isOrganization = this.isOrganization;
		}

		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: ['id', 'name', "photoBlobId", {name: 'organizations', type: "relation"}, 'goUserId', 'phoneNumbers','addresses','emailAddresses','firstName', 'middleName', 'lastName', 'gender', 'color'],
				entityStore: "Contact",
				sortInfo: {
					field: 'firstName',
					direction: 'ASC' 
				},
				filters: {
					combo: comboFilter
				}
			})
		});
		
		this.tpl = new Ext.XTemplate(
				'<tpl for=".">',
				'<div class="x-combo-list-item"><div class="user">\
					 <div class="avatar" style="{[this.getStyle(values)]}">{[this.getHtml(values)]}</div>\
					 <div class="wrap">\
						 <div><tpl if="!values.id"><b>' + t("Create new") + ':</b> </tpl>{name}</div>\
						 <tpl if="values.emailAddresses && values.emailAddresses[0]"><small>{[values.emailAddresses[0].email]}</small></tpl>\\n\
						 <small>{[values.organizations ? values.organizations.column("name").join(", ") : ""]}</small>\
					 </div>\
				 </div></div>',
				'</tpl>', {
				getHtml: function (v) {
					if(v.photoBlobId) {
						return "";
					}
					return v.isOrganization  ? '<i class="icon">business</i>' : go.util.initials(v.name);
				},
				getStyle: function (v) {
					return v.photoBlobId ? 'background-image: url(' + go.Jmap.thumbUrl(v.photoBlobId, {w: 40, h: 40, zc: 1})  + ')"' : "background-image:none;background-color: #" + v.color;;
				}
			}
		 );

		go.modules.community.addressbook.ContactCombo.superclass.initComponent.call(this);

	}
});

Ext.reg("contactcombo", go.modules.community.addressbook.ContactCombo);
