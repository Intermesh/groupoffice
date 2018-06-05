GO.moduleManager.on('moduleconstructed',function(mm,moduleName,panel){
	if(moduleName=='settings'){

		var fieldset =new Ext.form.FieldSet({
			title:t("Address book", "addressbook"),
			items:[{
				xtype:'textfield',
				name:'addressbook_name_template',
				fieldLabel:t("Template", "addressbook"),
				width: 300
			}
//			,{
//				xtype:'checkbox',
//				name:'change_all_addressbook_names',
//				fieldLabel:t("Change all?", "addressbook"),
//				listeners: {
//	  			 "check": function(cb, isenabled) {
//						if(isenabled && !confirm(t("Rename all?", "addressbook")))
//							cb.setValue(false);
//					},
//					scope:this
//				}
//			}
		]
		});
		
		panel.add(fieldset);
	}
});
