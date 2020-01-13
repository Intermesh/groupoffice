/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: Settings.js 22307 2018-02-01 14:07:32Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
GO.tasks.SettingsPanel = Ext.extend(Ext.Panel, {
	autoScroll: true,
	border: false,
	hideLabel: true,
	title : t("Tasks", "tasks"),
	iconCls: 'ic-assignment-turned-in',
	labelWidth: 125,
	
	onLoadStart: function (userId) {

	},
	
	initComponent: function() {

		var now = new Date();
		var eight = Date.parseDate(now.format('Y-m-d') + ' 08:00', 'Y-m-d G:i');
		
		this.items = {
		xtype:'fieldset',
		autoHeight:true,
		layout:'form',
		forceLayout:true,
		title:t("Defaults settings for tasks", "tasks"),
		items:[this.remindCheck=new Ext.form.Checkbox({
				boxLabel : t("Remind me", "tasks"),
				hideLabel : true,
				name : 'taskSettings.remind',
				listeners : {
					'check' : function(field, checked) {
						this.numberField.setDisabled(!checked);
						this.timeField.setDisabled(!checked);
					},
					scope : this
				}
			}), this.numberField = new GO.form.NumberField({
				decimals:0,
				name : 'taskSettings.reminder_days',			
				value : '0',
				fieldLabel : t("Days before start", "tasks"),
				disabled : true
			}),this.timeField = new Ext.form.TimeField({
				name : 'taskSettings.reminder_time',
				format : GO.settings.time_format,
				value : eight.format(GO.settings['time_format']),
				fieldLabel : t("Time"),
				disabled : true
			}),
			new GO.form.HtmlComponent({html:'<br />'}),
			this.selectTaskList = new GO.tasks.SelectTasklist({
					fieldLabel : t("Default tasklist", "tasks"),
					hiddenName : 'taskSettings.default_tasklist_id'
				})]
		};
		GO.tasks.SettingsPanel.superclass.initComponent.call(this);
	},
	
	onLoadComplete : function(action) {
		//this.selectTaskList.setRemoteText(action.result.data.default_tasklist_name);
	},

	onSubmitComplete : function(){

		var now = new Date();

		GO.tasks.reminderDaysBefore=parseInt(this.numberField.getValue());
		GO.tasks.reminderTime=this.timeField.getValue();

	}

});
