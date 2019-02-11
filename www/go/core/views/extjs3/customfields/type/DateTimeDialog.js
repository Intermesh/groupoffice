 go.modules.core.core.type.DateTimeDialog = Ext.extend(go.modules.core.core.FieldDialog, {
	 initFormItems : function() {		 
		 var items =  go.modules.core.core.type.DateTimeDialog.superclass.initFormItems.call(this);
		 
		 items[0].items  = items[0].items.concat([{
				xtype: "datetime",
				name: "default",
				fieldLabel: t("Default value"),
				width: dp(340)
			}]);
		
		 return items;
	 }
 });
