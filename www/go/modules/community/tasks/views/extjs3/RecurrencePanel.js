
/**
 * Fieldset to handle the UI for the Recurrence of an event or task.
 * 
 * This component needs the following (form)data to work correctly:
 * 						
 * interval								int
 * frequency										"" / "DAILY" / "WEEKLY" / "MONTHLY_DATE" / "MONTHLY" / "YEARLY"
 * bysetposition								1 / 2 / 3 / 4 / -1
 * until										date ( example: 23-11-2018 )
 * repeat_forever					1 / 0
 * repeat_UntilDate				1 / 0
 * count									int
 * repeat_count						1 / 0
 * 
 * @type {Ext.extend.cls}
 */
go.modules.community.tasks.RecurrencePanel = Ext.extend(go.form.FormContainer, {
	
	/**
	 * The start date of the event/task/etc.
	 * Can be set with the setStartDate function
	 * 
	 * {Date} The startDate
	 */
	startDate: null,
	days: ['MO','TU','WE','TH','FR','SA','SU'],
	getName: function () {
		return 'recurrenceRule';
	},

	getValue: function() {
		if(Ext.isEmpty(this.repeatType.getValue())) {
			return null;
		}
		var v = go.modules.community.tasks.RecurrencePanel.superclass.getValue.call(this);
		if(this.bySetDays) {
			v.byDay = [];
			for(var i = 0; i < this.days.length;i++) {
				if(this.bySetDays[this.days[i]]) {
					v.byDay.push(this.bySetDays[this.days[i]]);
				}
				
			}
		}
		return v;	
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
			submit: false,
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
				['MONTHLY', t("Months by date")],
				['MONTHLY_DAY', t("Months by day")],
				['YEARLY', t("Years")]]
			}),
			hideLabel : true

		});

		this.frequency = new Ext.form.Hidden({
			name : 'frequency'
		});

		
		this.repeatType.on('select', function(combo, record) {
			this.changeRepeat(record.data.value);
		}, this);

		this.monthTime = new Ext.form.ComboBox({
			hiddenName : 'bySetPosition',
			triggerAction : 'all',
			selectOnFocus : true,
			disabled : true,
			width : 80,
			forceSelection : true,
			mode : 'local',
			value : '-1',
			valueField : 'value',
			displayField : 'text',
			listeners: {
				select:function(me,value) {
					for(var i in this.bySetDays) {
						this.bySetDays[i].position = value.data.value;
					}
					
				},
				scope:this
			},
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

		
		this.dayButtons = {};
		this.bySetDays = {};
		
		for (var day = 0; day < 7; day++) {
			this.dayButtons[this.days[day]] = new Ext.Button({
				text : t("short_days")[(day + 1) % 7],
				day:day,
				enableToggle: true,
				pressed : false,
				listeners: {
					toggle:function(btn,pressed) {
						var key = this.days[btn.day];
						if(pressed) {
							var nDay = {day: key},
							position = this.monthTime.getValue();
							if(position) {
								nDay.position = position;
							}
							this.bySetDays[key] = nDay;
						} else {
							delete this.bySetDays[key];
						}
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
			disabled: true,
			name: 'count',
			maxLength: 1000,
			width : 100,
			decimals:0,
			suffix: t('times')
		});

		this.recurrenceGroup = new go.form.RadioGroup({
			xtype: 'radiogroup',
			fieldLabel: t("Repeat"),
			disabled: true,
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
					this.dayButtons['MO'],this.dayButtons['TU'],this.dayButtons['WE'],this.dayButtons['TH'],
					this.dayButtons['FR'],this.dayButtons['SA'],this.dayButtons['SU']
				]
			}),
			this.frequency,
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
						{ xtype:'displayfield', html:'. '},
						this.repeatNumber,
						this.repeatEndDate
					]
				})]
			}
		]
		});
		
		go.modules.community.tasks.RecurrencePanel.superclass.initComponent.call(this);
	},

	onLoad: function(start, recurrenceRule) {
		this.setStartDate(start);
		this.rrule = recurrenceRule;
		if(recurrenceRule) {
			if(recurrenceRule.bySetPosition) {
				this.repeatType.setValue("MONTHLY_DAY");
				this.changeRepeat("MONTHLY_DAY");
			} else {
				this.repeatType.setValue(recurrenceRule.frequency);
				this.changeRepeat(recurrenceRule.frequency);
			}
			
			this.setDaysButtons(recurrenceRule);
			this.setRadioButtonCheck();
		} else {
			this.changeRepeat('');
		}
	},
	setRadioButtonCheck: function() {
		var isNumberEmpty = Ext.isEmpty(this.repeatNumber.getValue());
		var isDateEmpty = Ext.isEmpty(this.repeatEndDate.getValue());

		var items = this.recurrenceGroup.items.items;
		if(!isNumberEmpty) {
			items[1].setValue(true);
		} else if(!isDateEmpty) {
			items[2].setValue(true);
		} else {
			items[0].setValue(true);
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
		if(responseData.byDay) {
			//this.bySetDays = responseData.byDay;
			
			for(var i = 0;i < responseData.byDay.length;i++) {
				var day = responseData.byDay[i].day;
				this.dayButtons[day].toggle(true);
			}
		}

	},

	/**
	 * Reset this fieldset to "No recurring"
	 */
	reset: function(){
		this.changeRepeat("");
	},

	/**
	 * Enable and disable the correct fields based on the "frequency" parameter
	 * 
	 * @param String value	"" / "DAILY" / "WEEKLY" / "MONTHLY_DATE" / "MONTHLY" / "YEARLY"
	 */
	changeRepeat : function(value) {
		this.frequency.setValue(value);
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

			case 'MONTHLY' :
				this.disableDays(true);
				this.recurrenceGroup.setDisabled(false);
				this.monthTime.setDisabled(true);
				this.repeatEvery.setDisabled(false);
				this.rightContainer.setDisabled(false);
			break;

			case 'MONTHLY_DAY' :
				this.frequency.setValue("MONTHLY");
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
