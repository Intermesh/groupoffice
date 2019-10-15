 go.customfields.type.DateTimeDialog = Ext.extend(go.customfields.FieldDialog, {
	 initFormItems : function() {		 
		 var items =  go.customfields.type.DateTimeDialog.superclass.initFormItems.call(this);
		 
		 items[0].items  = items[0].items.concat([{
				xtype: "datetimefield",
				name: "default",
				fieldLabel: t("Default value"),
				width: dp(340),
				allowBlank: true
			}]);
		
		 return items;
	 }
 });
