 /* global Ext, go */

go.customfields.type.FunctionFieldDialog = Ext.extend(go.customfields.FieldDialog, {
	 height: dp(500),
	 initFormItems : function() {		 
		 var items =  go.customfields.type.FunctionFieldDialog.superclass.initFormItems.call(this);
		 
		 items[0].items  = items[0].items.concat([{
				 xtype: "box",
				 html: t("You can create a function using other number fields. Use the 'databaseName' property as tag. For example {foo} + {bar}. You can use the following operators: / , * , + and -")
		 },{
				xtype: "textarea",
				name: "options.function",
				fieldLabel: t("Function"),
				grow: true,
				anchor: "100%"
			}, {
				xtype: "numberfield",
				decimals: 0,
				name: "options.numberDecimals",
				value: 2,
				fieldLabel: t("Decimals")
				
			}]);
		
		 return items;
	 }
 });
