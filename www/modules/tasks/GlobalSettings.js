GO.moduleManager.on('moduleconstructed',function(mm,moduleName,panel){
	if(moduleName=='settings'){

		var fieldset =new Ext.form.FieldSet({
			title:t("Tasks", "tasks"),
			items:[{
				xtype:'textfield',
				name:'task_name_template',
				fieldLabel:t("Task template", "tasks"),
				width: 300
			},{
				xtype:'checkbox',
				name:'GO_Tasks_Model_Tasklist_change_all_names',
				fieldLabel:t("Change existing?", "tasks"),
				listeners: {
	  			 "check": function(cb, isenabled) {
						if(isenabled && !confirm(t("Rename all?", "tasks")))
							cb.setValue(false);
					},
					scope:this
				}
			}]
		});

		panel.add(fieldset);
	}
});