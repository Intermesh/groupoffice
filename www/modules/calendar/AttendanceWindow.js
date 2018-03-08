GO.calendar.AttendanceWindow = Ext.extend(GO.dialog.TabbedFormDialog, {
	initComponent : function(){
		
		
		Ext.apply(this, {
			title:GO.calendar.lang.attendance,
			height: 460,
			
			width: 400,
			modal:true,
			enableApplyButton:false,
			formControllerUrl: 'calendar/attendance'
		});
		

		GO.calendar.AttendanceWindow.superclass.initComponent.call(this);
		
	},
	setExceptionDate : function(date){
		if(!date)
			delete this.formPanel.baseParams.exception_date;
		else
			this.formPanel.baseParams.exception_date=date;
	},
	
	afterLoad : function(remoteModelId, config, action){
		this.infoPanel.update(action.result.data.info)
	},
	buildForm : function(){
		
		
		var reminderValues = [['0', GO.calendar.lang.noReminder]];

		for (var i = 1; i < 60; i++) {
			reminderValues.push([i, i]);
		}
		
		this.reminderValue = new GO.form.NumberField({
			decimals:0,
			name : 'reminder_value',
//			minValue:1,
			width : 50,
			value : GO.calendar.defaultReminderValue
		});

		this.reminderMultiplier = new Ext.form.ComboBox({
			hiddenName : 'reminder_multiplier',
			triggerAction : 'all',
			editable : false,
			selectOnFocus : true,
			width : 148,
			forceSelection : true,
			mode : 'local',
			value : GO.calendar.defaultReminderMultiplier,
			valueField : 'value',
			displayField : 'text',
			store : new Ext.data.SimpleStore({
				fields : ['value', 'text'],
				data : [['60', GO.lang.strMinutes],
				['3600', GO.lang.strHours],
				['86400', GO.lang.strDays],
				['604800', GO.lang.strWeeks]

				]
			}),
			hideLabel : true,
			labelSeperator : ''
		});
		
		this.reminderComposite = new Ext.form.CompositeField({
			style:'margin-top:10px;',
			fieldLabel : GO.calendar.lang.reminder,
			items : [this.reminderValue,this.reminderMultiplier]
		});
		
		this.enableReminderCheckbox = new Ext.ux.form.XCheckbox({
			boxLabel : GO.calendar.lang.useReminder,
			name : 'enable_reminder',
			width : 'auto',
			hideLabel : true,
			listeners : {
				check : {
					fn : function(cb, checked) {
						this.reminderComposite.setDisabled(!checked);
					},
					scope : this
				}
			}
		});
		
		this.addPanel({
			cls:'go-form-panel',
			layout:'form',
			autoScroll:true,
			defaults:{
				anchor:'-20'
			},
			items:[{
				xtype:'radiogroup',
				hideLabel:true,
				columns:1,
				items:[
				{
					boxLabel: GO.calendar.lang.iWillAttend,
					name: 'status',
					inputValue: 'ACCEPTED'
				},{
					boxLabel: GO.calendar.lang.iWillNotAttend,
					name: 'status',
					inputValue: 'DECLINED'
				},{
					boxLabel: GO.calendar.lang.iMightAttend,
					name: 'status',
					inputValue: 'TENTATIVE'
				},{
					boxLabel: GO.calendar.lang.iWillDecideLater,
					name: 'status',
					inputValue: 'NEEDS-ACTION'
				}
				]
			},{
				hideLabel:true,
				name:'notify_organizer',
				xtype:'xcheckbox',
				boxLabel:GO.calendar.lang.notifyOrganizer
			}
//			{
//				xtype:'plainfield',
//				fieldLabel:GO.calendar.lang.organizer,
//				name:'organizer'
//			}
			,this.infoPanel = new Ext.form.FieldSet({
				title:GO.calendar.lang.eventInfo
			}),{
				xtype : 'fieldset',
				autoHeight : true,
				layout : 'form',
				title : GO.calendar.lang.reminder,
				items : [
					this.enableReminderCheckbox,
					this.reminderComposite
			]}]
		});
	}
});