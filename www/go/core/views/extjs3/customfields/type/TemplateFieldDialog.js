 /* global Ext, go */

go.customfields.type.TemplateFieldDialog = Ext.extend(go.customfields.FieldDialog, {
	 height: dp(500),
	 initFormItems : function() {		 
		 var items =  go.customfields.type.TemplateFieldDialog.superclass.initFormItems.call(this);
		 
		 items[0].items  = items[0].items.concat([{
				 xtype: "box",
				 html: t("You can create a function using other number fields. Use the 'databaseName' property as tag. For example {foo} + {bar}. You can use the following operators: / , * , + and -")
		 },{
				xtype: "textarea",
				name: "options.template",
				fieldLabel: t("Templates"),
				grow: true,
				anchor: "100%",
			 	value: '[assign firstContactLink = entity | links:Contact | first]{{firstContactLink.name}}'
			}]);
		
		 return items;
	 }
 });
