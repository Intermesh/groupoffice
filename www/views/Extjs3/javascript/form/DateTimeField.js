GO.form.DateTime = Ext.extend(Ext.ux.form.DateTime, {

	initComponent: function () {
		Ext.apply(this, {
			hiddenFormat: GO.settings.date_format + ' ' + GO.settings.time_format,
			dateFormat: GO.settings.date_format,
			timeFormat: GO.settings.time_format,
			dtSeparator: ' '
		});
		
		GO.form.DateTime.superclass.initComponent.call(this);		
	}

});

// register xtype
Ext.reg('datetime', GO.form.DateTime);
