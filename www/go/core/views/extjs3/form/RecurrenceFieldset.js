
/**
 * Fieldset to handle the UI for the Recurrence of an event or task.
 * 
 * This component needs the following (form)data to work correctly:
 * 						
 * MO											1 / 0
 * TU											1 / 0
 * WE											1 / 0
 * TH											1 / 0
 * FR											1 / 0
 * SA											1 / 0
 * SU											1 / 0
 * interval								int
 * freq										"" / "DAILY" / "WEEKLY" / "MONTHLY_DATE" / "MONTHLY" / "YEARLY"
 * bysetpos								1 / 2 / 3 / 4 / -1
 * until										date ( example: 23-11-2018 )
 * repeat_forever					1 / 0
 * repeat_UntilDate				1 / 0
 * count									int
 * repeat_count						1 / 0
 * 
 * @type {Ext.extend.cls}
 */
go.form.RecurrenceFieldset = Ext.extend(Ext.form.FieldSet, {
	
	/**
	 * The start date of the event/task/etc.
	 * Can be set with the setStartDate function
	 * 
	 * {Date} The startDate
	 */
	startDate: null,

	initComponent : function(){
				
		this.repeatEvery = new GO.form.NumberField({
			decimals:0,
			name : 'interval',
			minValue:1,
			width : 50,
			value : '1'
		});

		this.repeatType = new Ext.form.ComboBox({
			hiddenName : 'freq',
			triggerAction : 'all',
			editable : false,
			selectOnFocus : true,
			width : 200,
			forceSelection : true,
			mode : 'local',
			value : '',
			valueField : 'value',
			displayField : 'text',
			store : new Ext.data.SimpleStore({
				fields : ['value', 'text'],
				data : [['', t("No recurrence")],
				['DAILY', t("Days")],
				['WEEKLY', t("Weeks")],
				['MONTHLY_DATE', t("Months by date")],
				['MONTHLY', t("Months by day")],
				['YEARLY', t("Years")]]
			}),
			hideLabel : true

		});

		this.repeatType.on('select', function(combo, record) {
			this.changeRepeat(record.data.value);
		}, this);

		this.monthTime = new Ext.form.ComboBox({
			hiddenName : 'bysetpos',
			triggerAction : 'all',
			selectOnFocus : true,
			disabled : true,
			width : 80,
			forceSelection : true,
			mode : 'local',
			value : '1',
			valueField : 'value',
			displayField : 'text',
			store : new Ext.data.SimpleStore({
				fields : ['value', 'text'],
				data : [['1', t("First")],
				['2', t("Second")],
				['3', t("Third")],
				['4', t("Fourth")],
				['-1', t("Last")]
			]
			})
		});

		var days = ['SU','MO','TU','WE','TH','FR','SA'];

		this.cb = [];
		this.dayButtons = [];
		for (var day = 0; day < 7; day++) {
			this.cb[day] = new Ext.form.Hidden({
				name : days[day],
				value : 0
			});
			this.dayButtons[day] = new Ext.Button({
				text : t("short_days")[day],
				day:day,
				enableToggle: true,
				pressed : false,
				listeners: {
					toggle:function(btn,pressed) {
						this.cb[btn.day].setValue(pressed?1:0);
					},
					scope:this
				}
			});
		}

		this.repeatEndDate = new Ext.form.DateField({
			name : 'until',
			width : 120,
			disabled : true,
			format : GO.settings['date_format'],
			allowBlank : true,			
			listeners : {
				change : {
					fn : this.checkDateInput,
					scope : this
				}
			}
		});

		this.repeatForeverXCheckbox = new Ext.ux.form.XCheckbox({
			boxLabel : t("Repeat forever"),
			name : 'repeat_forever',
			width : 'auto',
			hideLabel : true,
			listeners : {
				check : {
					fn : function(cb, checked){
						if(!checked && !this.repeatUntilDateXCheckbox.getValue() && !this.repeatCountXCheckbox.getValue()) {
							this.repeatForeverXCheckbox.setValue(true);
						} else {
							this.repeatUntilDateXCheckbox.setValue(false);
							this.repeatCountXCheckbox.setValue(false);

						}
					},
					scope : this
				}
			}
		});
		
		this.repeatUntilDateXCheckbox = new Ext.ux.form.XCheckbox({
			boxLabel : t("Repeat until"),
			name : 'repeat_UntilDate',
			width: 100,
			hideLabel : true,
			listeners : {
				check : {

					fn : function(cb, checked){


						if(!checked && !this.repeatForeverXCheckbox.getValue() && !this.repeatCountXCheckbox.getValue()) {
							this.repeatUntilDateXCheckbox.setValue(true);
							return;
						} else {

							this.repeatForeverXCheckbox.setValue(false);
							this.repeatCountXCheckbox.setValue(false);
							this.repeatEndDate.setDisabled(!checked);
						}
					},
					scope : this
				}
			}
		});
		
		this.repeatNumber = new Ext.form.NumberField({
			name: 'count',
			disabled : true,
			maxLength: 1000,
			width : 50,
			allowBlank:false,
			value: 1,
			minValue: 1,
			decimals:0
		});
		
		this.repeatCountXCheckbox = new Ext.ux.form.XCheckbox({
			boxLabel : t("Repeat"),
			name : 'repeat_count',
			width: 100,
			hideLabel : true,
			listeners : {
				check : {

					fn : function(cb, checked){

						if(!checked && !this.repeatForeverXCheckbox.getValue() && !this.repeatUntilDateXCheckbox.getValue()) {
							this.repeatCountXCheckbox.setValue(true);
							return;
						} else {
							this.repeatForeverXCheckbox.setValue(false);
							this.repeatUntilDateXCheckbox.setValue(false);
							this.repeatNumber.setDisabled(!checked);
						}
					},
					scope : this
				}
			}
		});
		
		Ext.apply(this, {
			title : t("Recurrence"),
			cls:'go-form-panel',
			layout : 'form',
			hideMode : 'offsets',
			defaults:{
				forceLayout:true,
				border:false
			},
			items : [{
				fieldLabel : t("Repeat every"),
				xtype : 'compositefield',
				items : [this.repeatEvery,this.repeatType,this.monthTime]
			}, 
			this.daysGroup = new Ext.ButtonGroup({
				disabled:true,
				fieldLabel : t("At days"),
				items : [
					this.cb[1],this.cb[2],this.cb[3],this.cb[4],this.cb[5],this.cb[6],this.cb[0],
					this.dayButtons[1],this.dayButtons[2],this.dayButtons[3],this.dayButtons[4],this.dayButtons[5],this.dayButtons[6],this.dayButtons[0]
				]
			}),
			this.repeatForeverXCheckbox, 
			{
				hideLabel: true,
				xtype : 'compositefield',
				items : [this.repeatCountXCheckbox, this.repeatNumber,{xtype:'plainfield', value: t("times")}]
			}, {
				hideLabel: true,
				xtype : 'compositefield',
				items : [this.repeatUntilDateXCheckbox, this.repeatEndDate]
			}]
		});
		
		go.form.RecurrenceFieldset.superclass.initComponent.call(this);	
	},
	
	/**
	 * Disable or enable the Days group field
	 * 
	 * @param Boolean disabled
	 */
	disableDays : function(disabled) {
		this.daysGroup.setDisabled(disabled);
	},
	
	/**
	 * The event/task/etc. start date
	 * Example: 22-11-2018
	 * 
	 * @param {Date} startDate
	 */
	setStartDate : function(startDate){
		this.startDate = startDate;
		this.checkDateInput();
	},

	/**
	 * Set the day buttons to the correct value
	 * Based on the response from the server
	 * 
	 * @param Array responseData
	 */
	setDaysButtons : function(responseData){
		var days = ['SU','MO','TU','WE','TH','FR','SA'];
		Ext.each(this.dayButtons, function(btn) {
			btn.toggle(!!responseData[days[btn.day]]);
		});
	},

	/**
	 * Reset this fieldset to "No recurring"
	 */
	reset: function(){
		this.changeRepeat("");
	},

	/**
	 * Enable and disable the correct fields based on the "freq" parameter
	 * 
	 * @param String value	"" / "DAILY" / "WEEKLY" / "MONTHLY_DATE" / "MONTHLY" / "YEARLY"
	 */
	changeRepeat : function(value) {
			
		var repeatForever = this.repeatForeverXCheckbox.getValue();

		switch (value) {
			default :
				this.disableDays(true);
				this.monthTime.setDisabled(true);
				this.repeatForeverXCheckbox.setDisabled(true);
				this.repeatCountXCheckbox.setDisabled(true);
				this.repeatUntilDateXCheckbox.setDisabled(true);
				this.repeatNumber.setDisabled(true);
				this.repeatEndDate.setDisabled(true);
				this.repeatEvery.setDisabled(true);
				break;

			case 'DAILY' :
				this.disableDays(true);
				this.monthTime.setDisabled(true);
				this.repeatForeverXCheckbox.setDisabled(false);
				this.repeatCountXCheckbox.setDisabled(false);
				this.repeatUntilDateXCheckbox.setDisabled(false);
				this.repeatNumber.setDisabled(false);
				this.repeatEndDate.setDisabled(repeatForever);
				this.repeatEvery.setDisabled(false);

				break;

			case 'WEEKLY' :
				this.disableDays(false);
				this.monthTime.setDisabled(true);
				this.repeatForeverXCheckbox.setDisabled(false);
				this.repeatCountXCheckbox.setDisabled(false);
				this.repeatUntilDateXCheckbox.setDisabled(false);
				this.repeatNumber.setDisabled(false);
				this.repeatEndDate.setDisabled(repeatForever);
				this.repeatEvery.setDisabled(false);

				break;

			case 'MONTHLY_DATE' :
				this.disableDays(true);
				this.monthTime.setDisabled(true);
				this.repeatForeverXCheckbox.setDisabled(false);
				this.repeatCountXCheckbox.setDisabled(false);
				this.repeatUntilDateXCheckbox.setDisabled(false);
				this.repeatNumber.setDisabled(false);
				this.repeatEndDate.setDisabled(repeatForever);
				this.repeatEvery.setDisabled(false);

				break;

			case 'MONTHLY' :
				this.disableDays(false);
				this.monthTime.setDisabled(false);
				this.repeatForeverXCheckbox.setDisabled(false);
				this.repeatCountXCheckbox.setDisabled(false);
				this.repeatUntilDateXCheckbox.setDisabled(false);
				this.repeatNumber.setDisabled(false);
				this.repeatEndDate.setDisabled(repeatForever);
				this.repeatEvery.setDisabled(false);
				break;

			case 'YEARLY' :
				this.disableDays(true);
				this.monthTime.setDisabled(true);
				this.repeatForeverXCheckbox.setDisabled(false);
				this.repeatCountXCheckbox.setDisabled(false);
				this.repeatUntilDateXCheckbox.setDisabled(false);
				this.repeatNumber.setDisabled(false);
				this.repeatEndDate.setDisabled(repeatForever);
				this.repeatEvery.setDisabled(false);
				break;
		}
	},
	
	/**
	 * Check the "repeat until" field based on the start date
	 * The value must be greater than the start date.
	 * 
	 */
	checkDateInput : function() {
		
		if(!this.startDate){
			console.warn("Cannot check validity. No startDate given. Please set the startDate with the setStartDate() function.");
		}
		
		if (this.repeatType.getValue() != "" && GO.util.empty(this.repeatEndDate.getValue()) && !this.repeatCountXCheckbox.getValue()) {
			this.repeatForeverXCheckbox.setValue(true);
		} else if(this.startDate && this.repeatEndDate.getValue() < this.startDate ){
			this.repeatEndDate.setValue(this.startDate.add(Date.DAY, 1));
		}

	}
});
