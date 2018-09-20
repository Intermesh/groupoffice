go.form.DateField = Ext.extend(Ext.form.DateField, {
	width: dp(140),
	
	initComponent : function(){
		this.altFormats =  "Y-m-d|c|" + GO.settings.date_format.replace("Y","y");
		this.format = GO.settings.date_format;
		this.startDay = parseInt(GO.settings.first_weekday);
		
		go.form.DateField.superclass.initComponent.call(this);
	}
});

Ext.reg('datefield', go.form.DateField);
