
 go.modules.community.addressbook.customfield.ContactDialog = Ext.extend(go.modules.core.core.FieldDialog, {
	 initFormItems : function() {		 
		 var items =  go.modules.core.core.type.CheckboxDialog.superclass.initFormItems.call(this);
		 
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

			}]);
		
		 return items;
	 }
 });