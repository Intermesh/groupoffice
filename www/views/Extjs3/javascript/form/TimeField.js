GO.form.TimeField = Ext.extend(Ext.form.TimeField, {
	format:GO.settings.time_format,
	width: 80
});

// register xtype
Ext.reg('gotimefield', GO.form.TimeField);