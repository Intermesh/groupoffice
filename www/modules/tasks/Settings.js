/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: Settings.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.tasks.SettingsPanel = function(config) {
	if (!config) {
		config = {};
	}

	var now = new Date();
	var eight = Date.parseDate(now.format('Y-m-d') + ' 08:00', 'Y-m-d G:i');

	config.autoScroll = true;
	config.border = false;
	config.hideLabel = true;
	config.title = GO.tasks.lang.tasks;
	config.hideMode='offsets';
	config.layout = 'form';
	config.labelWidth=125;
	config.bodyStyle='padding:5px;';
	config.items = {
		xtype:'fieldset',
		autoHeight:true,
		layout:'form',
		forceLayout:true,
		title:GO.tasks.lang.taskDefaults,
		items:[this.remindCheck=new Ext.form.Checkbox({
				boxLabel : GO.tasks.lang.remindMe,
				hideLabel : true,
				name : 'remind',
				listeners : {
					'check' : function(field, checked) {
						this.numberField.setDisabled(!checked);
						this.timeField.setDisabled(!checked);
					},
					scope : this
				}
			}), this.numberField = new GO.form.NumberField({
				decimals:0,
				name : 'reminder_days',			
				value : '0',
				fieldLabel : GO.tasks.lang.daysBeforeStart,
				disabled : true
			}),this.timeField = new Ext.form.TimeField({
				name : 'reminder_time',
				format : GO.settings.time_format,
				value : eight.format(GO.settings['time_format']),
				fieldLabel : GO.lang.strTime,
				disabled : true
			}),
			new GO.form.HtmlComponent({html:'<br />'}),
			this.selectTaskList = new GO.tasks.SelectTasklist({
					fieldLabel : GO.tasks.lang.defaultTasklist,
					hiddenName : 'default_tasklist_id'
				})]
		};
	
	GO.tasks.SettingsPanel.superclass.constructor.call(this, config);
};

Ext.extend(GO.tasks.SettingsPanel, Ext.Panel, {
			onLoadSettings : function(action) {
				//this.selectTaskList.setRemoteText(action.result.data.default_tasklist_name);
			},
			
			onSaveSettings : function(){
				var t = GO.tasks.taskDialog;
				
				var now = new Date();
				
				GO.tasks.reminderDaysBefore=parseInt(this.numberField.getValue());
				GO.tasks.reminderTime=this.timeField.getValue();
				if(t){				
					var remindDate = now.add(Date.DAY, -GO.tasks.reminderDaysBefore);

					t.formPanel.form.findField('remind').originalValue=this.remindCheck.getValue();
					t.formPanel.form.findField('remind_time').originalValue=this.timeField.getValue();
					t.formPanel.form.findField('remind_date').originalValue=remindDate;
				}
			}

		});

GO.mainLayout.onReady(function() {
			GO.moduleManager.addSettingsPanel('tasks',
					GO.tasks.SettingsPanel);
		});