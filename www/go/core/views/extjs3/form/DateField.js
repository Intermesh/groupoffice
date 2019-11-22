GO.mainLayout.on("authenticated", function() {

		//Override this when authenticated and mainlayout initializes
		Ext.override(Ext.DatePicker, {
			startDay: go.User.firstWeekday
		});

		Ext.override(Ext.form.DateField, {
			format: go.User.dateFormat,
			startDay: go.User.firstWeekday,
			altFormats: "Y-m-d|c|" + go.User.dateFormat.replace("Y","y"),
			dtSeparator:' '
		});
});

go.form.DateField = Ext.extend(Ext.form.DateField, {
	width: dp(140),
	
	initComponent : function(){
		this.altFormats =  "Y-m-d|c|" + go.User.dateFormat.replace("Y","y");
		this.format = go.User.dateFormat;
		this.startDay = parseInt(GO.settings.first_weekday);
		
		go.form.DateField.superclass.initComponent.call(this);
	}
});

Ext.reg('datefield', go.form.DateField);
