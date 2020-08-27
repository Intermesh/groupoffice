GO.WeekPicker = function(config){
	config = config || {};

	config.cls='go-weekpicker';

	config.items=[{
		iconCls : 'btn-left-arrow',
		text : t("Previous"),
		cls : 'x-btn-text-icon',
		handler : function() {
			this.setDate(this.sunday.add(Date.DAY, -7));
		},
		scope : this
	},this.periodInfoPanel = new Ext.Panel({
		plain : true,
		border : true,
		cls : 'cal-period'
	}),{
		iconCls : 'btn-right-arrow',
		text : t("Next"),
		cls : 'x-btn-text-icon',
		handler : function() {
			this.setDate(this.sunday.add(Date.DAY, 7));
		},
		scope : this
	},{
		text : t("Today"),
		handler : function() {
			this.setDate();
		},
		scope : this
	}];

	GO.WeekPicker.superclass.constructor.call(this, config);

	this.addEvents({change:true});
}

Ext.extend(GO.WeekPicker ,Ext.Toolbar, {
	sunday : false,
	
	setDate : function(date, suppress) {
		
		if (!date) {
			var now = new Date();
			date = now.getLastSunday();
		}

		if(this.fireEvent('beforechange', this, date, this.sunday) !== false){
			this.sunday = date;

			var displayDate = this.sunday.add(Date.DAY, 1);
			this.goToLink.update(
				t("Week")
				+ ' '
				+ displayDate.format('W')
				+ ' ('
				+ displayDate
				.format(GO.settings.date_format)
				+ ')');

			if(!suppress)
				this.fireEvent('change', this, this.sunday);
		}
	},

	getDate : function(){
		return this.sunday;
	},

	afterRender : function(){
		
		GO.WeekPicker.superclass.afterRender.call(this);		

		this.goToLink = Ext.DomHelper.append(this.periodInfoPanel.body,
			{
				tag:'a',
				href:"#",
				html:''
			},true);

		this.goToLink.on("click", function() {
			if(!this.gotoWeekDialog)
			{
				this.gotoWeekDialog= new GO.GoToWeekDialog();
			}
			this.gotoWeekDialog.show(this);
		}, this);

		this.setDate();
	}

});



GO.GoToWeekDialog = function(config) {

	if (!config) {
		config = {};
	}

	this.buildForm();

	var focusFirstField = function() {
		this.formPanel.items.items[0].focus();
	};

	config.layout = 'fit';
	config.title = t("Go to week", "projects");
	config.modal = false;
	config.width = 335;
	config.height = 135;
	config.resizable = false;
	config.closeAction = 'hide';
	config.items = this.formPanel;
	config.focus = focusFirstField.createDelegate(this);
	config.buttons = [{
		text : t("Close"),
		handler : function() {
			this.hide();
		},
		scope : this
	}];

	GO.GoToWeekDialog.superclass.constructor.call(this, config);

}
Ext.extend(GO.GoToWeekDialog, Ext.Window, {

	show : function(wp) {
		this.wp = wp;

		if (!this.rendered) {
			this.render(Ext.getBody());
		}

		var pos = this.wp.periodInfoPanel.getPosition();
		this.setPosition(pos[0], pos[1] + 20);

		this.formPanel.form.reset();

		var now = new Date(this.wp.sunday);
		var thisyear = now.format('Y');
		this.year.setValue(thisyear);

		this.setYear(thisyear);

		GO.GoToWeekDialog.superclass.show.call(this);
	},
	buildForm : function() {
		this.formPanel = new Ext.form.FormPanel({
			cls : 'go-form-panel',
			layout : 'form',
			waitMsgTarget : true,
			labelWidth : 50,
			border : false,
			anchor : '95% 95%',
			items : [this.year = new Ext.form.TextField({
				name : 'year',
				fieldLabel : t("Year"),
				allowBlank : false,
				anchor : '95%',
				selectOnFocus : true
			}), this.selectWeek = new GO.form.ComboBox({
				fieldLabel : t("Week"),
				hiddenName : 'date',
				emptyText : t("Select week"),
				store : new GO.data.JsonStore({
					url : BaseHref + 'json.php',
					baseParams : {
						task : 'get_weeks'
					},
					root : 'results',
					id : 'value',
					totalProperty : 'total',
					fields : ['value', 'text'],
					remoteSort : true
				}),
				valueField : 'value',
				displayField : 'text',
				mode : 'local',
				triggerAction : 'all',
				editable : true,
				selectOnFocus : true,
				forceSelection : true,
				allowBlank : false,
				anchor : '95%'
			})]
		});

		this.year.on('change', function() {
			this.setYear(this.year.getValue());
		}, this);

		this.selectWeek.on('select', function(combo, record) {
			var newDate = new Date(this.selectWeek.getValue() * 1000);
			//var dateString = newDate.toUTCString();

			this.wp.setDate(newDate);

			this.hide();

		}, this);

	},
	setYear : function(year) {
		this.selectWeek.store.baseParams.year = year;
		this.selectWeek.store.load();
	}
});
