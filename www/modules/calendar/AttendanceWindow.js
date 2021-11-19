GO.calendar.AttendanceWindow = Ext.extend(GO.dialog.TabbedFormDialog, {
	initComponent : function(){
		
		
		Ext.apply(this, {
			title:t("Attendance", "calendar"),
			height: 460,
			
			width: 600,
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
		if(!this.origTitle) {
			this.origTitle = this.title;
		}
		this.setTitle(this.origTitle + ": " + Ext.util.Format.htmlEncode(action.result.data.name) + " &lt;" +Ext.util.Format.htmlEncode(action.result.data.email) + "&gt;");
	},
	buildForm : function(){
		
		
		var reminderValues = [['0', t("No reminder", "calendar")]];

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
				data : [['60', t("Minutes")],
				['3600', t("Hours")],
				['86400', t("Days")],
				['604800', t("Weeks")]

				]
			}),
			hideLabel : true,
			labelSeperator : ''
		});
		
		this.reminderComposite = new Ext.form.CompositeField({
			style:'margin-top:10px;',
			fieldLabel : t("Reminder", "calendar"),
			items : [this.reminderValue,this.reminderMultiplier]
		});
		
		this.enableReminderCheckbox = new Ext.ux.form.XCheckbox({
			boxLabel : t("Enable reminder for this event", "calendar"),
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
					boxLabel: t("I will attend", "calendar"),
					name: 'status',
					inputValue: 'ACCEPTED'
				},{
					boxLabel: t("I will not attend", "calendar"),
					name: 'status',
					inputValue: 'DECLINED'
				},{
					boxLabel: t("I might attend", "calendar"),
					name: 'status',
					inputValue: 'TENTATIVE'
				},{
					boxLabel: t("I haven't decided yet", "calendar"),
					name: 'status',
					inputValue: 'NEEDS-ACTION'
				}
				]
			},{
				hideLabel:true,
				name:'notify_organizer',
				xtype:'xcheckbox',
				boxLabel:t("Notify the organizer by e-mail about my decision", "calendar")
			}
//			{
//				xtype:'plainfield',
//				fieldLabel:t("Organizer", "calendar"),
//				name:'organizer'
//			}
			,this.infoPanel = new Ext.form.FieldSet({
				title:t("Event details", "calendar")
			}),{
				xtype : 'fieldset',
				autoHeight : true,
				layout : 'form',
				title : t("Reminder", "calendar"),
				items : [
					this.enableReminderCheckbox,
					this.reminderComposite
			]}]
		});
	}
});
