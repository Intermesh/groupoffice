 go.modules.core.customfields.type.NumberDialog = Ext.extend(go.modules.core.customfields.FieldDialog, {
	 initFormItems : function() {		 
		 var items =  go.modules.core.customfields.type.NumberDialog.superclass.initFormItems.call(this);
		 
		 items[0].items.push({
			 xtype:"numberfield",
			 decimals: 0,
			 name: "options.numberDecimals",
			 value: 2,
			 fieldLabel: t("Decimals")
		 });
		 
		 return items;
	 }
 });
