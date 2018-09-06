GO.form.TimeField = Ext.extend(Ext.form.TimeField, {
	width: dp(100),
	
	
	initComponent: function () {
		Ext.apply(this, {
			format: GO.settings.time_format			
		});
		
		GO.form.TimeField.superclass.initComponent.call(this);	
	}
});

// register xtype
Ext.reg('gotimefield', GO.form.TimeField);
