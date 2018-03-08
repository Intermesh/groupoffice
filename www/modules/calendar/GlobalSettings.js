GO.moduleManager.on('moduleconstructed',function(mm,moduleName,panel){
	if(moduleName=='settings'){

		var fieldset =new Ext.form.FieldSet({
			title:GO.calendar.lang.calendar,
			items:[{
				xtype:'textfield',
				name:'calendar_name_template',
				fieldLabel:GO.calendar.lang.globalsettings_templatelabel,
				width: 300
			},{
				xtype:'checkbox',
				name:'calendar_change_all_names',
				fieldLabel:GO.calendar.lang.globalsettings_allchangelabel,
				listeners: {
	  			 "check": function(cb, isenabled) {
						if(isenabled && !confirm(GO.calendar.lang.globalsettings_renameall))
							cb.setValue(false);
					},
					scope:this
				}
			}]
		});

		panel.add(fieldset);
	}
});