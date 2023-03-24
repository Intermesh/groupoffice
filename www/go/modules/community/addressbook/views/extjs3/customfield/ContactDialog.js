
 go.modules.community.addressbook.customfield.ContactDialog = Ext.extend(go.customfields.FieldDialog, {
	 initFormItems : function() {		 
		 let items =  go.customfields.type.CheckboxDialog.superclass.initFormItems.call(this);
		 
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
			 xtype: 'checkbox',
			 fieldLabel: t("Allow new"),
			 name: "options.allowNew",
			 value: true
		 },{
			 anchor: '100%',
			 xtype: "chips",
			 entityStore: "AddressBook",
			 displayField: "name",
			 name: "options.addressBookId",
			 fieldLabel: t("Address books")
		 }]);

		 items[1].items = items[1].items.concat([{
			 xtype: "label",
			 text: t("Information panel"),
			 autoEl: "legend",
			 itemCls: "x-fieldset-header"
		 },{
			 xtype: "checkbox",
			 fieldLabel: t("Show selections in the information panel"),
			 name: "options.showInformationPanel",
			 value: true
		 },{
			 xtype: "checkbox",
			 fieldLabel: t("Expand panel by default"),
			 name: "options.expandByDefault",
			 value: true
		 },{
			 xtype: "textfield",
			 name: "options.informationPanelTitle",
			 fieldLabel: t("Title"),
			 anchor: "100%",
			 allowBlank: false
		 }]);
		
		 return items;
	 }
 });