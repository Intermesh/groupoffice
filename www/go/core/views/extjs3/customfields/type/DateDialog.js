 go.customfields.type.DateDialog = Ext.extend(go.customfields.FieldDialog, {
	 initFormItems : function() {		 
		 var items =  go.customfields.type.DateDialog.superclass.initFormItems.call(this);
		 
		 items[0].items  = items[0].items.concat([{
				xtype: "datefield",
				name: "default",
				fieldLabel: t("Default value")				
			}]);
		
		 return items;
	 }
 });
