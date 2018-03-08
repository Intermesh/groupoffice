GO.form.DateTime = Ext.extend(Ext.ux.form.DateTime, {
	
	hiddenFormat:GO.settings.date_format+' '+GO.settings.time_format,
	dateFormat:GO.settings.date_format,
	timeFormat:GO.settings.time_format,
	dtSeparator:' '

});

// register xtype
Ext.reg('datetime', GO.form.DateTime);