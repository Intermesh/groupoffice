 go.customfields.type.CheckboxDialog = Ext.extend(go.customfields.FieldDialog, {
	 initFormItems : function() {		 
		 var items =  go.customfields.type.CheckboxDialog.superclass.initFormItems.call(this);
		 
		 items[0].items  = items[0].items.concat([{
				xtype: "checkbox",
				name: "default",
				fieldLabel: t("Default")
			}]);
		
		 return items;
	 }
 });
