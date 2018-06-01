GO.moduleManager.on('moduleconstructed',function(mm,moduleName,panel){
	if(moduleName=='settings'){

		var fieldset =new Ext.form.FieldSet({
			title:t("Calendar", "calendar"),
			items:[{
				xtype:'textfield',
				name:'calendar_name_template',
				fieldLabel:t("Template", "calendar"),
				width: 300
			},{
				xtype:'checkbox',
				name:'calendar_change_all_names',
				fieldLabel:t("Rename all existing", "calendar"),
				listeners: {
	  			 "check": function(cb, isenabled) {
						if(isenabled && !confirm(t("Are you sure you want to rename all default user calendars?", "calendar")))
							cb.setValue(false);
					},
					scope:this
				}
			}]
		});

		panel.add(fieldset);
	}
});
