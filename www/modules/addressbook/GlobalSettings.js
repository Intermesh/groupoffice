GO.moduleManager.on('moduleconstructed',function(mm,moduleName,panel){
	if(moduleName=='settings'){

		var fieldset =new Ext.form.FieldSet({
			title:GO.addressbook.lang.addressbook,
			items:[{
				xtype:'textfield',
				name:'addressbook_name_template',
				fieldLabel:GO.addressbook.lang.globalsettings_templatelabel,
				width: 300
			}
//			,{
//				xtype:'checkbox',
//				name:'change_all_addressbook_names',
//				fieldLabel:GO.addressbook.lang.globalsettings_allchangelabel,
//				listeners: {
//	  			 "check": function(cb, isenabled) {
//						if(isenabled && !confirm(GO.addressbook.lang.globalsettings_renameall))
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