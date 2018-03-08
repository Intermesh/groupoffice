GO.moduleManager.on('moduleconstructed',function(mm,moduleName,panel){
	if(moduleName=='settings'){

		var fieldset =new Ext.form.FieldSet({
			title:GO.tasks.lang.tasks,
			items:[{
				xtype:'textfield',
				name:'task_name_template',
				fieldLabel:GO.tasks.lang.globalsettings_templatelabel,
				width: 300
			},{
				xtype:'checkbox',
				name:'GO_Tasks_Model_Tasklist_change_all_names',
				fieldLabel:GO.tasks.lang.globalsettings_allchangelabel,
				listeners: {
	  			 "check": function(cb, isenabled) {
						if(isenabled && !confirm(GO.tasks.lang.globalsettings_renameall))
							cb.setValue(false);
					},
					scope:this
				}
			}]
		});

		panel.add(fieldset);
	}
});