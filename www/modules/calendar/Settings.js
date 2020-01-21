/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: Settings.js 22311 2018-02-02 09:20:27Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
GO.calendar.SettingsPanel = Ext.extend(Ext.Panel, {

	autoScroll: true,
	hideLabel: true,
	title: t("Calendar", "calendar"),
	labelWidth: 140,
	iconCls: 'ic-event',

	onLoadStart: function (userId) {

	},

	initComponent: function () {

		var reminderValues = [[null, t("No reminder", "calendar")]];

		for (var i = 0; i < 60; i++) {
			reminderValues.push([i, i]);
		}

		this.reminder = new Ext.form.Hidden({
			name: 'calendarSettings.reminder'
		});

		this.reminderValue = new Ext.form.ComboBox({
			submit: false,
			fieldLabel: t("Reminder", "calendar"),

			triggerAction: 'all',
			editable: false,
			selectOnFocus: true,
			width: 148,
			forceSelection: true,
			mode: 'local',
			value: '0',
			valueField: 'value',
			displayField: 'text',
			store: new Ext.data.SimpleStore({
				fields: ['value', 'text'],
				data: reminderValues
			}),
			listeners: {
				scope: this,
				change: function () {
					this.reminder.setValue(this.reminderValue.getValue() * this.reminderMultiplier.getValue());
				}
			}
		});

		this.reminderMultiplier = new Ext.form.ComboBox({
			submit: false,
			triggerAction: 'all',
			editable: false,
			selectOnFocus: true,
			width: 148,
			forceSelection: true,
			mode: 'local',
			value: '60',
			valueField: 'value',
			displayField: 'text',
			store: new Ext.data.SimpleStore({
				fields: ['value', 'text'],
				data: [['60', t("Minutes")],
					['3600', t("Hours")],
					['86400', t("Days")]

				]
			}),
			hideLabel: true,
			labelSeperator: '',
			listeners: {
				scope: this,
				change: function () {
					this.reminder.setValue(this.reminderValue.getValue() * this.reminderMultiplier.getValue());
				}
			}
		});

		this.items = [{
				forceLayout: true,
				xtype: 'fieldset',
				autoHeight: true,
				layout: 'form',
				title: t("Defaults settings for appointments", "calendar"),
				items: [this.reminder, {
						border: false,
						layout: 'table',
						defaults: {
							border: false,
							layout: 'form'
						},
						items: [{
								items: this.reminderValue
							}, {
								items: this.reminderMultiplier
							}]
					}, this.colorField = new GO.form.ColorField({
						fieldLabel: t("Color"),
						value: 'EBF1E2',
						name: 'calendarSettings.background'
					}),
					this.selectCalendar = new GO.calendar.SelectCalendar({
						fieldLabel: t("Default calendar", "calendar"),
						hiddenName: 'calendarSettings.calendar_id',
						anchor: '-20',
						emptyText: t("Please select...")
					}), {
						xtype: 'xcheckbox',
						name: 'calendarSettings.show_statuses',
						boxLabel: t("Show event statuses in views", "calendar"),
						hideLabel: true
					}, {
						xtype: 'xcheckbox',
						name: 'calendarSettings.check_conflict',
						boxLabel: t("Check for conflicts", "calendar"),
						hideLabel: true
					}

				]
			}];

		this.selectCalendar.store.baseParams = {
			ownedBy: GO.settings.user_id
		};

		GO.calendar.SettingsPanel.superclass.initComponent.call(this);
	},

	onSubmitComplete: function () {

		if (GO.calendar.eventDialog) {
			GO.calendar.eventDialog.reminderValue.originalValue = this.reminderValue
							.getValue();
			GO.calendar.eventDialog.reminderMultiplier.originalValue = this.reminderMultiplier
							.getValue();
			GO.calendar.eventDialog.colorField.originalValue = this.colorField
							.getValue();
		} else
		{
			GO.calendar.defaultReminderValue = this.reminderValue.getValue();
			GO.calendar.defaultReminderMultiplier = this.reminderMultiplier.getValue();
		}
	},
	//needed for override in freebusypermissions
	onLoadComplete: function (action) {
		this.splitReminder();
	},

	splitReminder: function () {
		var secs = this.reminder.getValue();
		var multipliers = this.reminderMultiplier.getStore().getRange();
		for (var i = 0, l = multipliers.length; i < l; i++) {
			var devided = secs / multipliers[i].get('value');
			var match = parseInt(devided);
			if (match == devided) {
				this.reminderMultiplier.setValue(multipliers[i].get('value'));
				this.reminderValue.setValue(devided);
				break;
			}
		}
	}

});
