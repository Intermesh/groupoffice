GO.calendar.SelectCalendarDialog = function(config){

	if(!config)
	{
		config = {};
	}

	this.buildForm();

	var focusFirstField = function(){
		this.formPanel.items.items[0].focus();
	};

	config.layout='fit';
	config.title=t("Select calendar", "calendar");
	config.modal=false;
	config.border=false;
	config.width=400;
	config.autoHeight=true;
	config.resizable=false;
	config.plain=true;
	config.shadow=false,
	config.closeAction='hide';
	config.items=this.formPanel;
	config.focus=focusFirstField.createDelegate(this);
	config.buttons=[{
		text:t("Ok"),
		handler: function()
		{
			this.submitForm(true)
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

	GO.calendar.SelectCalendarDialog.superclass.constructor.call(this,config);

	this.addEvents({'calendar_selected' : true});
}

Ext.extend(GO.calendar.SelectCalendarDialog, Ext.Window, {
	
	submitForm : function(hide)
	{
		this.fireEvent('calendar_selected', this.selectCalendar.getValue());

		this.hide();
	},
	
	buildForm : function ()
	{
		 this.selectCalendar = new GO.form.ComboBox({
                        hiddenName:'cal_id',
			fieldLabel:t("Calendar", "calendar"),
			valueField:'id',
			displayField:'name',
			store: new Ext.data.ArrayStore({
                                fields: ['id', 'name']
                        }),
			mode:'local',
			triggerAction:'all',
                        emptyText:t("Select calendar", "calendar"),
			editable:false,
			selectOnFocus:true,
			forceSelection:true
                });

		this.formPanel = new Ext.FormPanel({
			cls:'go-form-panel',
			anchor:'100% 100%',
			bodyStyle:'padding:5px',
			defaults:{anchor: '95%'},
			defaultType:'textfield',
			autoHeight:true,
			waitMsgTarget:true,
			labelWidth:75,
			items: this.selectCalendar
		});
	},

	populateComboBox : function(records)
        {		
                var data = [];

                for(var i=0; i<records.length; i++)
                {
                        var calendar = []
                        calendar.push(records[i].id);
                        calendar.push(records[i].name);

                        data.push(calendar);
                }

                this.selectCalendar.store.loadData(data);
		var rec = this.selectCalendar.store.getAt(0);
		if(rec)
		{
			this.selectCalendar.setValue(rec.data.id);
		}
        }
	
});
