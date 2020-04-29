
 go.modules.community.addressbook.customfield.ContactDialog = Ext.extend(go.customfields.FieldDialog, {
	 initFormItems : function() {		 
		 var items =  go.customfields.type.CheckboxDialog.superclass.initFormItems.call(this);
		 
		 items[0].items  = items[0].items.concat([{
				xtype: 'radiogroup',
				fieldLabel: t("Show"),
				name: "options.isOrganization",				
				value: false,
				items: [
					{boxLabel: t("All"), inputValue: null},
					{boxLabel: t("Contacts"), inputValue: false},
					{boxLabel: t("Organizations"), inputValue: true}
				]

			},{
			 anchor: '100%',
			 xtype: "chips",
			 entityStore: "AddressBook",
			 displayField: "name",
			 name: "options.addressBookId",
			 fieldLabel: t("Address books")
		 }]);
		
		 return items;
	 }
 });