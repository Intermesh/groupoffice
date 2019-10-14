go.form.DateTimeField = Ext.extend(Ext.ux.form.DateTime, {

	initComponent: function () {
		Ext.apply(this, {
			hiddenFormat: "c",
			dateFormat: GO.settings.date_format,
			timeFormat: GO.settings.time_format,
			dtSeparator: ' '
		});
		
		go.form.DateTimeField.superclass.initComponent.call(this);		
	}

});

// register xtype
Ext.reg('datetimefield', go.form.DateTimeField);

