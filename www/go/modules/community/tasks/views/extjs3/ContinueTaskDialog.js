/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ContinueTaskDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
go.modules.community.tasks.ContinueTaskDialog = Ext.extend(go.form.Dialog, {

	height: 640,
	width:600,
	goDialogId: 'continuetask',
	title: t("Continue task", "tasks"),
	entityStore: 'Task',
	showLinks: false,
	formPanelLayout:'border',
	maximizable:false,

	onBeforeSubmit: function() {

		//set alert
		this.formPanel.values.alerts = [{
			trigger: {when: this.datePicker.getValue().format('Y-m-d') + ' ' +  this.timeField.getValue()}
		}];

		// add comment
		const comment = this.commentField.getValue();
		if(!Ext.isEmpty(comment)) {
			go.Db.store('Comment').set({create:
				{'#continueComment':{text: comment, entity: 'Task', entityId: this.currentId}}
			});
		}
		return true;
	},

	initFormItems: function () {

		const tomorrow = (new Date()).add(Date.DAY, 1);

	//	this.datePicker.setValue(tomorrow);
		return [{
			region: 'north',
			layout: 'form',
			cls: 'go-form-panel',
			autoHeight: true,
			items: [{
				items: [this.datePicker = new Ext.DatePicker({
					internalRender: true,
					xtype: 'datepicker',
					name: 'due',
					//hiddenFormat:'Y-m-d',
					format: GO.settings.date_format,
					fieldLabel: t("Date"),
					value: tomorrow,
					submit:false,
					listeners: {
						"select": (me, date) => {
							this.formPanel.baseParams.due_time = date.format(GO.settings.date_format);
						}
					}
				})],
				width: 240,
				style: 'margin:auto;'
			},
				//new GO.form.HtmlComponent({html: '<br />'}),
				this.timeField = new go.form.TimeField({
					//name: 'alertTime',
					width: 220,
					asInteger: false,
					submit:false,
					//hiddenFormat:'H:i:s',
					//format: GO.settings.time_format,
					value: '08:00',
					fieldLabel: t("Time"),
					anchor: '100%'
				}),
				this.statusProgressField = this.statusProgressField = new Ext.form.CompositeField({
						fieldLabel: t("Status", "tasks"),
						items: [new go.modules.community.tasks.ProgressCombo ({
							width:dp(150),
							value : 'needs-action'
						}),new Ext.form.SliderField({
							flex: 1,
							name: 'percentComplete',
							minValue: 0,
							maxValue: 100,
							increment: 10,
							value: 0
						})]
				}),
				this.selectTaskList = new go.modules.community.tasks.TasklistCombo({
					anchor: '100%',
					value: go.User.tasksSettings.defaultTasklistId
				})
			]
		}, {
			region: 'center',
			layout: 'anchor',
			cls: 'go-form-panel',
			items: [this.commentField = new Ext.form.TextArea({
				anchor: '100% 100%',
				submit:false,
				fieldLabel: t("Description")
			})]
		}];
	}
});
