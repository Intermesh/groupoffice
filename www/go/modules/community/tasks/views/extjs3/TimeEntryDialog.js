/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: TimeEntryDialog.js 23428 2018-02-13 14:47:30Z mschering $
 * @author Joachim van de Haterd <jvdhaterd@intermesh.nl>
 */
go.modules.community.tasks.TimeEntryDialog = Ext.extend(GO.dialog.TabbedFormDialog, {

	// The duration of the standard task in minutes will be saved here after selecting a standard task
	standardTaskDuration: false,

	initComponent: function () {

		Ext.apply(this, {
			stateId: 'tasks-timeentrydialog',
			title: t("Time entry", "tasks"),
			height: 560,
			width: 720,
			formControllerUrl: 'projects2/timeEntry', // TODO?
			customFieldType: 'TimeEntry'
		});

		go.modules.community.tasks.TimeEntryDialog.superclass.initComponent.call(this);
	},

	afterLoad: function (remoteModelId, config, action) {
		if (config.loadParams && this.selectTimeUser.store.baseParams.project_id != config.loadParams.project_id) {
			this.selectTimeUser.store.baseParams.project_id = config.loadParams.project_id;
			delete this.selectTimeUser.lastQuery;
		}
		this.taskField.setProjectId(config.loadParams.project_id);
		this.taskField.setValue(config.loadParams.task_id);

		this.datePicker.setValue(Date.parseDate(action.result.data.start_date, GO.settings.date_format));

		this.projectField.store.load();
	},

	enableBreak: function (enable) {
		// The xcheckbox returns a '0' or a '1', therefore it needs to be translated to a boolean
		var nextDay = this.timeNextDay.getValue();

		this.buttonApply.setDisabled(!nextDay && enable); //with a break the dialog no longer represents 1 entry
		this.startBreakField.setVisible(!nextDay && enable);
		this.startBreakField.setValue("12:30");
		this.startBreakLabel.setVisible(!nextDay && enable);
		this.endBreakField.setVisible(!nextDay && enable);
		this.endBreakLabel.setVisible(!nextDay && enable);
		this.endBreakField.setValue("13:00");
		this.doLayout();
	},

	setEndTime: function () {
		if (this.standardTaskDuration != false) {
			if (this.remoteModelId == "0") {
				var startTime = new Date(this.datePicker.getValue());
				startTime = startTime.add(Date.DAY, this.standardTaskDuration);
				this.endDate.setValue(startTime);
			}
		}
	},

	durationToEndTime: function () {
		if (!this.durationField.disabled) {
			var durationArray = this.durationField.getValue().split(':');
		} else {
			var durationArray = new Array();
			durationArray.push(0);
			durationArray.push(0);
		}

		var startTimeArray = this.startTime.getValue().split(':');
		var startDate = Date.parseDate(this.dateField.getValue() + ' ' + this.startTime.getValue(), GO.settings.date_format + ' ' + GO.settings.time_format);
		var endTimeDate = startDate
			.add(Date.HOUR, +durationArray[0])
			.add(Date.MINUTE, +durationArray[1]);

		this.endTime.setValue(endTimeDate.format(GO.settings.time_format));
	},


	timesToDuration: function () {

		var startDate = Date.parseDate(this.dateField.getValue() + ' ' + this.startTime.getValue(), GO.settings.date_format + ' ' + GO.settings.time_format);
		var endDate = Date.parseDate(this.dateField.getValue() + ' ' + this.endTime.getValue(), GO.settings.date_format + ' ' + GO.settings.time_format);
		var totalMins = startDate.getElapsed(endDate) / 60000;

		var durationHours = Math.floor(totalMins / 60) + "";
		var durationMins = totalMins - (durationHours * 60) + "";

		if (durationHours.length == 1) {
			durationHours = "0" + durationHours;
		}

		if (durationMins.length == 1) {
			durationMins = "0" + durationMins;
		}
		this.durationField.setValue(durationHours + ':' + durationMins);

	},


	focus: function () {
		return;
//		if(!this.remoteModelId){
//			return go.modules.community.tasks.TimeEntryDialog.superclass.focus.call(this);
//		}else
//		{
//			return; // prevent selecting first field so selectOnFocus will work
//		}
	},

	buildForm: function () {
		this.projectField = new GO.projects2.SelectProject({
			anchor: '100%',
			store: GO.projects2.selectBookableProjectStore,
			listeners: {
				change: function (cmp, newVal) {
					this.taskField.setProjectId(newVal);
					var record = GO.projects2.selectBookableProjectStore.getById(newVal);

					if (record && !this.remoteModelId)
						this.travelDistanceField.setValue(record.data.default_distance);

					this.taskField.setValue("");

				},
				scope: this
			}
		});

		this.taskField = new go.modules.community.tasks.ProjectTaskCombo({
			anchor: '100%'
		});

		GO.projects2.selectBookableProjectStore.on('load', function (store, records, options) {
			if (this.projectField.getValue() > 0) {
				var record = store.getById(this.projectField.getValue());
				if (record) {
					this.taskField.setProjectId(record.data.id);
					var travelDistance = this.travelDistanceField.getValue();
					if (GO.util.empty(travelDistance)) {
						this.travelDistanceField.setValue(record.data.default_distance);
					}
				}
			}
		}, this);


		this.datePicker = new Ext.DatePicker({
			name: 'due_time',
			width: dp(256),
			format: GO.settings.date_format,
			hideLabel: true
		});

		this.datePicker.on("select", function (DatePicker, DateObj) {
			this.dateField.setValue(DateObj.format(GO.settings.date_format));
		}, this);

		var items = [new Ext.form.FieldSet({
			items: [
				this.projectField,
				this.taskField,
				this.activityField = new go.modules.business.business.ActivityCombo({
					fieldLabel: t("Activity type", "projects2"),
					emptyText: t("Standard working hours", "projects2"),
					anchor: '100%',
					activityType: 'work',
					hiddenName: 'standard_task_id'
					// listeners: {
					// 	select: function(combo, record, index ){
					// 		this.standardTaskDuration = Math.round(record.json.units*60);
					// 		var d = go.util.Format.duration(this.standardTaskDuration);
					// 		this.durationField.setValue(d);
					// 		this.durationToEndTime();
					// 	},
					// 	scope: this
					// }
				})
			]
		}),
			{
				xtype: "fieldset",
				layout: 'hbox',
				anchor: '100%',
				mobile: {
					layout: "anchor",
					defaults: {
						anchor: "100%"
					}
				},
				border: false,
				items: [
					this.datePicker,
					this.timePanel = new Ext.Panel({
						layout: 'form',
						flex: 1,
						items: [

							this.dateField = new Ext.form.Hidden({
								name: 'start_date',
								allowBlank: false
							}),
							this.endDate = new Ext.form.DateField({
								name: 'end_date',
								anchor: '100%',
								format: GO.settings['date_format'],
								fieldLabel: t("End Date", "timeregistration2"),
								hidden: true
							}),
							{
								xtype: 'compositefield',
								fieldLabel: t("Start time", "projects2"),
								items: [

									this.startTime = new GO.form.TimeField({
										name: 'start_time',
										//format: GO.settings['date_format'],
										allowBlank: false,
										listeners: {
											scope: this,
											change: function (combo, newValue, oldValue) {
												this.setEndTime();
												this.timesToDuration();
											},
											select: function (combo, record, index) {
												this.setEndTime();
												this.timesToDuration();
											}
										}
									}),
									{
										xtype: 'plainfield',
										value: t("Duration", "projects2") + ':'
									},
									this.durationField = new GO.form.TimeField({
										format: 'H:i',
										name: 'duration_human',
										listeners: {
											scope: this,
											change: function (numberfield, newValue, oldValue) {
												this.durationToEndTime();
											}
										}
									})
								]
							},
							{
								xtype: 'compositefield',
								fieldLabel: t("End time", "projects2"),
								items: [
									this.endTime = new GO.form.TimeField({
										name: 'end_time',
										//format: GO.settings['date_format'],
										allowBlank: false,
										listeners: {
											scope: this,
											select: function () {
												this.timesToDuration();
											},
											change: function (combo, newValue, oldValue) {

												this.timesToDuration();
											}
										}
									}),
									this.timeNextDay = new Ext.ux.form.XCheckbox({
										name: 'end_next_day',
										boxLabel: t("Time is next day", "projects2"),
										listeners: {
											scope: this,
											check: function (self, checked) {
												this.includeBreak.setDisabled(checked);
												this.durationField.setDisabled(checked);
											}
										}
									})
								]
							},
							this.includeBreakComposite = new Ext.form.CompositeField({
								fieldLabel: t("Include break", "projects2"),
								//layout: 'form',
								items: [
									this.includeBreak = new Ext.ux.form.XCheckbox({
										name: 'include_break',
										listeners: {
											scope: this,
											check: function (self, checked) {
												this.enableBreak(checked);
											}
										}
									}), this.startBreakLabel = new Ext.form.DisplayField({
										value: t("Start"),
										hidden: true
									}), this.startBreakField = new GO.form.TimeField({
										name: 'start_break',
										hidden: true
									}), this.endBreakLabel = new Ext.form.DisplayField({
										value: t("End"),
										hidden: true
									}), this.endBreakField = new GO.form.TimeField({
										//fieldLabel: 'End',
										name: 'end_break',
										hidden: true
									})
								]
							}),
							new Ext.form.TextArea({
								name: 'comments',
								anchor: '100%',
								height: 140,
								fieldLabel: t("Description")
							}),
							this.travelDistanceField = new go.form.NumberField({
								xtype: 'textfield',
								fieldLabel: t("Travel distance", "projects2"),
								name: 'travel_distance',
								anchor: '100%',
								decimals: 2
							})
						]
					})
				]
			}

		];

		if (GO.settings.modules.timeregistration2.permission_level === go.permissionLevels.manage) {
			this.selectTimeUser = new go.modules.business.business.EmployeeCombo({
				anchor: '100%',
				hiddenName: 'user_id'
			});

			items[0].insert(3, this.selectTimeUser);
		}

		this.formPanel = new Ext.Panel({
			title: t("General"),
			layout: 'form',
			autoScroll: true,
			items: items
		});
		this.on("submit", function(dlg, success, serverId) {
			// Find a task ID
			var values = this.formPanel.getForm().getValues(false);
			var taskId = values['task_id'], duration = values['duration_human'];

			// Retrieve task
			go.Db.store('Task').single(taskId).then(function(task) {
				// Update duration; fire up the store
				var durationMinutes = go.util.Format.minutes(duration);
				var percentComplete = Math.min(100, Math.round(((durationMinutes / task.estimatedDuration) * 100) ));
				var update = {};
				update[taskId] = {percentComplete: percentComplete}
				go.Db.store('Task').set({update: update});
			});

			// Set progress to 100% or a percentage based on duration of hours registration


		});
		this.addPanel(this.formPanel);

	}
});
