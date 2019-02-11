 go.modules.core.core.type.DateDialog = Ext.extend(go.modules.core.core.FieldDialog, {
	 initFormItems : function() {		 
		 var items =  go.modules.core.core.type.DateDialog.superclass.initFormItems.call(this);
		 
		 items[0].items  = items[0].items.concat([{
				xtype: "datefield",
				name: "default",
				fieldLabel: t("Default value")				
			}]);
		
		 return items;
	 }
 });
