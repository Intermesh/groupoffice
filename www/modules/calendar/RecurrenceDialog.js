GO.calendar.RecurrenceDialog = function(config){

	if(!config)
	{
		config = {};
	}

	config.width=400;
	config.autoHeight=true;
	config.closeable=true;
	config.closeAction='hide';
	config.plain=true;
	config.border=false;
	config.title=GO.calendar.lang.recurringEvent;
	config.modal=false;
	config.html=config.forDelete ? GO.calendar.lang.deleteRecurringEvent : GO.calendar.lang.editRecurringEvent;
	config.focus=function(){
		this.getFooterToolbar().items.get('single').focus();
	};
	config.buttons=[{
		itemId:'single',
		text: GO.calendar.lang.singleOccurence,
		handler: function()
		{
			this.fireEvent('single', this);
		},
		scope: this
	},{
		text: GO.calendar.lang.entireSeries,
		handler: function()
		{		
			this.fireEvent('entire', this);
		},
		scope: this
	},this.thisAndFutureButton = new Ext.Button({
		text: GO.calendar.lang.thisAndFuture,
		handler: function()
		{
			this.fireEvent('thisandfuture', this);
		},
		scope: this
//	},{
//		text: GO.lang.cmdCancel,
//		handler: function()
//		{
//			this.fireEvent('cancel', this);
//		},
//		scope: this
	})]

	GO.calendar.RecurrenceDialog.superclass.constructor.call(this,config);

	this.addEvents({
		'single' : true,
		'entire': true,
		'thisandfuture': true,
		'cancel': true
	});
}

Ext.extend(GO.calendar.RecurrenceDialog, Ext.Window, {

	show : function()
	{	
		GO.calendar.RecurrenceDialog.superclass.show.call(this);
	}
});