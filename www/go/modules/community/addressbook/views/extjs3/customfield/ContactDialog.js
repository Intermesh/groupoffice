
go.modules.community.addressbook.customfield.ContactDialog = Ext.extend(go.customfields.FieldDialog, {
	 initFormItems : function() {
		 let items =  go.modules.community.addressbook.customfield.ContactDialog.superclass.initFormItems.call(this);
		 
		 items[0].items  = items[0].items.concat([{
			 xtype: 'radiogroup',
			 fieldLabel: t("Type"),
			 name: 'type',
			 value: "Contact",
			 items: [
				 {boxLabel: t("Contact"), inputValue: "Contact"},
				 {boxLabel: t("Multiple Contacts"), inputValue: "MultiContact"}
			 ],
			 listeners: {
				 change: function(me, checked) {
					 const disablePanelOptions = checked.inputValue === "MultiContact";
					 this.showInfoPanelCB.setDisabled(disablePanelOptions);
					 this.expandByDefaultCB.setDisabled(disablePanelOptions);
					 this.infoPanelTitle.setDisabled(disablePanelOptions);
					 this.infoPanelTitle.allowBlank = !disablePanelOptions;
				 },
				 scope: this
			}
		    },{
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
		 },this.showInfoPanelCB = new Ext.form.Checkbox({
			 xtype: "checkbox",
			 fieldLabel: t("Show selections in the information panel"),
			 name: "options.showInformationPanel",
			 value: true,
			 handler: function(cb, checked) {
				 this.infoPanelTitle.allowBlank = !checked;
			 },
			 scope: this
		 }),
			 this.expandByDefaultCB = new Ext.form.Checkbox({
			 xtype: "checkbox",
			 fieldLabel: t("Expand panel by default"),
			 name: "options.expandByDefault",
			 value: true
		 }),this.infoPanelTitle = new Ext.form.TextField({
			 xtype: "textfield",
			 name: "options.informationPanelTitle",
			 fieldLabel: t("Title"),
			 anchor: "100%"
		 })]);


		 return items;
	 },

	initComponent:  function() {
		go.modules.community.addressbook.customfield.ContactDialog.superclass.initComponent.call(this);
		this.formPanel.on("load", function (form, entity) {
			if(entity.id) {
				form.getForm().findField('type').hide().disable();
			}
		});
	}
 });