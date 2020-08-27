GO.calendar.SelectDateDialog = function(config){

	if(!config)
	{
		config = {};
	}

	this.buildForm();

	var focusFirstField = function(){
		this.formPanel.items.items[0].focus();
	};

	config.layout='fit';
	config.modal=false;
	config.border=false;
	config.width=400;
	config.autoHeight=true;
	config.resizable=false;
	config.plain=true;
	config.shadow=false,
	config.title=t("Copy event", "calendar");
	config.closeAction='hide';
	config.items=this.formPanel;
	config.focus=focusFirstField.createDelegate(this);
	config.buttons=[{
		text:t("Ok"),
		handler: function()
		{
			this.beforeSubmit()
		},
		scope: this
	},{
		text:t("Close"),
		handler: function()
		{
			this.hide()
		},
		scope: this
	}];

	GO.calendar.SelectDateDialog.superclass.constructor.call(this,config);

	this.addEvents({'updateEvent' : true});
}

Ext.extend(GO.calendar.SelectDateDialog, Ext.Window, {

	isCopy: false,
	event : null,
	repeats : false,
	offset : 0,
	view_id : 0,

	show : function(event, isCopy, repeats, view_id)
	{
		if(!this.rendered) {
			this.render(Ext.getBody());
		}
		this.event = event;

		this.isCopy = (isCopy) ? true : false;
		this.repeats = (repeats) ? true : false;
		this.view_id = (view_id) ? view_id : 0;

		var title = (this.isCopy) ? t("Copy event", "calendar") : t("Move event", "calendar");
		this.setTitle(title);

		this.datePicker.setValue(this.event.startDate.add(Date.DAY, 1));
		
		if (!this.event.read_only) {
			this.selectCalendar.setValue(this.event.calendar_id);
			this.selectCalendar.setRemoteText(this.event.calendar_name);
		}

		GO.calendar.SelectDateDialog.superclass.show.call(this);
	},
	beforeSubmit : function()
	{
		delete this.formPanel.form.baseParams.exception_for_event_id ;
		delete this.formPanel.form.baseParams.exception_date ;
		
		// use daylight saving times		
		this.offset = parseInt(this.datePicker.getValue().calculateDaysBetweenDates(new Date(this.event.startDate.clearTime())));
				
		// This one does not check daylight saving times
		//this.offset = Math.ceil((this.datePicker.getValue() - this.event.startDate) / (86400000));
		
		var calendar_id = this.selectCalendar.getValue();
		var update_calendar_id = (calendar_id != this.event.calendar_id) ? calendar_id : 0;
		if(update_calendar_id) {
			this.event.calendar_id = this.formPanel.form.baseParams.update_calendar_id = update_calendar_id;
		}
		
		this.formPanel.form.baseParams.id = this.event.event_id;
			this.formPanel.form.baseParams.offset_days = this.offset;
			
		if(this.isCopy) {
			this.formPanel.form.baseParams.duplicate = true;		
		} else {
			delete this.formPanel.form.baseParams.duplicate;			
			
			if(this.event.repeats && !this.repeats) {
				this.formPanel.form.baseParams.exception_for_event_id = this.event.event_id;
				this.formPanel.form.baseParams.exception_date = this.event.startDate.format("U");
			}
		}
		
		this.formPanel.form.baseParams.view_id = this.view_id;

		this.submitForm(update_calendar_id);
	},
	submitForm : function(update_calendar_id)
	{
		this.formPanel.form.submit(
		{
			waitMsg:t("Saving..."),			
			success:function(form, action)
			{
				var new_event_id = (action.result.event_id) ? action.result.event_id : 0;

				var is_visible = (action.result.is_visible) ? action.result.is_visible : 0;


				this.fireEvent('updateEvent', this, new_event_id, is_visible);
				
				if(this.isCopy) {
					delete(this.formPanel.form.baseParams.event_id);
					delete(this.formPanel.form.baseParams.offset);
				} else {
					GO.calendar.handleMeetingRequest(action.result);

					delete(this.formPanel.form.baseParams.update_event_id);
					delete(this.formPanel.form.baseParams.offsetDays);

					if(this.formPanel.form.baseParams.repeats) {
						delete(this.formPanel.form.baseParams.repeats);
						delete(this.formPanel.form.baseParams.createException);
						delete(this.formPanel.form.baseParams.exceptionDate);
					}
				}
				if(update_calendar_id)
				{
					delete(this.formPanel.form.baseParams.update_calendar_id);					
				}				

				this.hide();
			},
			failure: function(form, action)
			{
				var error = '';
				if(action.failureType=='client')
				{
					error = t("You have errors in your form. The invalid fields are marked.");
				}else
				{
					error = action.result.feedback;
				}
				if(error)
				{
					Ext.MessageBox.alert(t("Error"), error);
				}
			},
			scope:this
		});
	},
	buildForm : function ()
	{
		this.datePicker = new Ext.DatePicker({
	    		xtype:'datepicker',
	    		format: GO.settings.date_format,
	    		fieldLabel:t("Date")
	    	});
		
		this.formPanel = new Ext.form.FormPanel({
			url: GO.url("calendar/event/submit"),
			baseParams:{},
			cls:'go-form-panel',
			labelWidth:75,
			waitMsgTarget:true,
			autoHeight:true,
			items:[
			{
				items:this.datePicker,
				width:220,
				style:'margin:auto;'
			},
			new GO.form.HtmlComponent({html:'<br />'}),
			this.selectCalendar = new GO.calendar.SelectCalendar({fieldLabel: t("Calendar", "calendar"), anchor:'100%'})
			]
		});
	}
});
