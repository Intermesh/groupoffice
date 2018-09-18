 go.modules.core.customfields.type.DateTimeDialog = Ext.extend(go.modules.core.customfields.FieldDialog, {
	 initFormItems : function() {		 
		 var items =  go.modules.core.customfields.type.DateTimeDialog.superclass.initFormItems.call(this);
		 
		 items[0].items  = items[0].items.concat([{
				xtype: "datetime",
				name: "default",
				fieldLabel: t("Default value")				
			}]);
		
		 return items;
	 }
 });
