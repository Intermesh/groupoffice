
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
go.modules.community.task.RecurrencePanel = Ext.extend(go.form.FormContainer, {
	
	/**
	 * The start date of the event/task/etc.
	 * Can be set with the setStartDate function
	 * 
	 * {Date} The startDate
	 */
	startDate: null,

	getName: function () {
		return 'recurrenceRule';
	},

	getValue: function() {
		if(Ext.isEmpty(this.repeatType.getValue())) {
			return null;
		}
		return go.modules.community.task.RecurrencePanel.superclass.getValue.call(this);	
	},

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
			value: '',
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
			hiddenName : 'bySetPos',
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

		this.dayButtons = [];
		this.bySetDays = "";

		this.byDay = new Ext.form.Hidden({
			name : 'byDay'
		});

		for (var day = 0; day < 7; day++) {
			this.dayButtons[day] = new Ext.Button({
				text : t("short_days")[day],
				day:day,
				enableToggle: true,
				pressed : false,
				listeners: {
					toggle:function(btn,pressed) {
						if(pressed) {
							if(this.bySetDays == "") {
								this.bySetDays += days[btn.day];
							} else {
								this.bySetDays += "|" + days[btn.day];
							}
						} else {
							this.bySetDays = this.bySetDays.replace("|" + days[btn.day],"");
							this.bySetDays = this.bySetDays.replace(days[btn.day],"");
						}
						this.byDay.setValue(this.bySetDays);
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
		
		this.repeatNumber = new Ext.form.NumberField({
			name: 'count',
			maxLength: 1000,
			width : 50,
			decimals:0
		});

		this.recurrenceGroup = new go.form.RadioGroup({
			xtype: 'radiogroup',
			fieldLabel: t("Repeat"),
			name: "radiogroup",
			submit: false,
			width:160,
			columns: 1,
			items: [
				{ boxLabel: t("Repeat forever"), inputValue: 'forever'},
				{ boxLabel: t("Repeat"), inputValue: 'count'},
				{ boxLabel: t("Repeat until"), inputValue: 'until' }
			],
			listeners: {
				scope: this,
				change: function(group, checked) {
					this.repeatNumber.setDisabled(true);
					this.repeatEndDate.setDisabled(true);
					if(checked.inputValue == 'count') {
						this.repeatNumber.setDisabled(false);
					} else if(checked.inputValue == 'until') {
						this.repeatEndDate.setDisabled(false);
					}
				}
			}
		});

		Ext.apply(this, {
			layout : 'form',
			
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
					this.dayButtons[1],this.dayButtons[2],this.dayButtons[3],this.dayButtons[4],this.dayButtons[5],this.dayButtons[6],this.dayButtons[0]
				]
			}),
			this.byDay,
			{
				xtype:'container',
				layout:'hbox',
				items:[{
					xtype:'container',
					layout:'form',
					items: [this.recurrenceGroup]
				},this.rightContainer = new Ext.Container({
					disabled: true,
					xtype:'container',
					layout:'form',
					defaults:{hideLabel:true},
					items:[
						{ xtype:'displayfield', value: t('times')},
						this.repeatNumber,
						this.repeatEndDate
					]
				})]
			}
		]
		});
		
		go.modules.community.task.RecurrencePanel.superclass.initComponent.call(this);	
	},

	onLoad: function(start, recurrenceRule) {
		this.setStartDate(start);
		if(recurrenceRule) {
			this.changeRepeat(recurrenceRule.freq);
			this.setDaysButtons(recurrenceRule);
		} else {
			this.changeRepeat('');
		}
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
			var isEnabled = (responseData.byDay.indexOf(days[btn.day]) !== -1);
			btn.toggle(isEnabled);
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
		switch (value) {
			
			default :
				this.disableDays(true);
				this.recurrenceGroup.setDisabled(true);
				this.monthTime.setDisabled(true);
				this.rightContainer.setDisabled(true);
			break;
			case 'DAILY' :
				this.disableDays(true);
				this.recurrenceGroup.setDisabled(false);
				this.monthTime.setDisabled(true);
				this.repeatEvery.setDisabled(false);
				this.rightContainer.setDisabled(false);
			break;

			case 'WEEKLY' :
				this.disableDays(false);
				this.recurrenceGroup.setDisabled(false);
				this.monthTime.setDisabled(true);
				this.repeatEvery.setDisabled(false);
				this.rightContainer.setDisabled(false);
			break;

			case 'MONTHLY_DATE' :
				this.disableDays(true);
				this.recurrenceGroup.setDisabled(false);
				this.monthTime.setDisabled(true);
				this.repeatEvery.setDisabled(false);
				this.rightContainer.setDisabled(false);
			break;

			case 'MONTHLY' :
				this.disableDays(false);
				this.recurrenceGroup.setDisabled(false);
				this.monthTime.setDisabled(false);
				this.repeatEvery.setDisabled(false);
				this.rightContainer.setDisabled(false);
			break;

			case 'YEARLY' :
				this.disableDays(true);
				this.recurrenceGroup.setDisabled(false);
				this.monthTime.setDisabled(true);
				this.repeatEvery.setDisabled(false);
				this.rightContainer.setDisabled(false);
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

		if (this.repeatType.getValue() != "" && GO.util.empty(this.repeatEndDate.getValue())) {
			//this.repeatForeverXCheckbox.setValue(true);
		} else if(this.startDate && this.repeatEndDate.getValue() < this.startDate ){
			this.repeatEndDate.setValue(this.startDate.add(Date.DAY, 1));
		}

	}
});
