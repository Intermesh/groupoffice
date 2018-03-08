



GO.dialog.date = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	initComponent : function(){
		
		Ext.apply(this, {
			title: t("Date"),
			height:310,
			loadOnNewModel : false,
			enableApplyButton: false,
			enableOkButton : false,
			enableCloseButton : false,
			width:260,
			border:false,
			maximizable:false,
			collapsible:false,
			closeAction:'hide'
			
		});
		
		this.on('afterrender', function (){
			this.focus();
		});
		
		
		GO.dialog.date.superclass.initComponent.call(this);
		
	},
	
	
	buildForm : function () {
		this.datePicker = new Ext.DatePicker({
//			itemId: 'date',
			name : 'date',
			format: GO.settings.date_format,
			hideLabel: true
		});
		
		this.datePicker.on('select', function(field,date){
			
			this.fireEvent('select', this, date);
			
			this.hide();
		}, this);
		
		
		this.datePickerWrapper = new Ext.Panel({
			autoHeight:true,
			cls:'go-date-picker-wrap-outer',
			baseCls:'x-plain',
			items:[
				new Ext.Panel({
					cls:'go-date-picker-wrap',
					items:[this.datePicker]
				})
			]
		});
		
		this.addPanel(this.datePickerWrapper);
	},
	
	
	show : function(value){
		
		if(value) {
			this.setValue(value);
		}
	
		GO.dialog.date.superclass.show.call(this);
	},
	
	getValue: function () {
		return this.datePicker.getValue();
	},
	
	setValue: function (value) {
		this.datePicker.setValue(value);
	},
	
	reset: function () {
		this.setValue(new Date());
	}
	
});



