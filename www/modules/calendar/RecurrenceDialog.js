GO.calendar.RecurrenceDialog = function(config){

	if(!config)
	{
		config = {};
	}

	config.width=450;
	config.autoHeight=true;
	config.closeable=true;
	config.cls = 'go-form-panel';
	config.closeAction='hide';
	config.plain=true;
	config.border=false;
	config.title=t("Recurring event", "calendar");
	config.modal=false;
	config.html=config.forDelete ? t("Do you want to delete a single instance or all instances of this recurring event?", "calendar") : t("Do you want to edit this occurence or the entire series?", "calendar");
	config.focus=function(){
		this.getFooterToolbar().items.get('single').focus();
	};
	config.buttons=[{
		itemId:'single',
		text: t("Single occurence", "calendar"),
		handler: function()
		{
			this.fireEvent('single', this);
		},
		scope: this
	},{
		text: t("Entire series", "calendar"),
		handler: function()
		{		
			this.fireEvent('entire', this);
		},
		scope: this
	},this.thisAndFutureButton = new Ext.Button({
		text: text: t("This and future", "calendar"),
		handler: function()
		{
			this.fireEvent('thisandfuture', this);
		},
		scope: this
//	},{
//		text: t("Cancel"),
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
