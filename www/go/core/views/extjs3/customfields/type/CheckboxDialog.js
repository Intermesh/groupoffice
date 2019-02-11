 go.modules.core.core.type.CheckboxDialog = Ext.extend(go.modules.core.core.FieldDialog, {
	 initFormItems : function() {		 
		 var items =  go.modules.core.core.type.CheckboxDialog.superclass.initFormItems.call(this);
		 
		 items[0].items  = items[0].items.concat([{
				xtype: "checkbox",
				name: "default",
				fieldLabel: t("Default")
			}]);
		
		 return items;
	 }
 });
