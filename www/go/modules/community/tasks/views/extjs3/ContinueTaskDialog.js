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
	title: t("Continue task", "tasks", "community"),
	entityStore: 'Task',
	showLinks: false,
	showCustomfields: false,
	formPanelLayout:'border',
	maximizable:false,

	onBeforeSubmit: function() {

		//set alert
		this.formPanel.values.alerts = [{
			trigger: {when: this.datePicker.getValue().format('Y-m-d') + ' ' +  this.timeField.getValue()}
		}];
		this.formPanel.values.due = this.datePicker.getValue().format('Y-m-d')

		// add comment
		const comment = this.commentField.getValue();
		if(!Ext.isEmpty(comment)) {
			go.Db.store('Comment').set({create:
				{'continueComment':{text: comment, entity: 'Task', entityId: this.currentId}}
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
					submit:false
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
				{
					xtype: 'fieldset',
					layout: 'hbox',
					style: {
						padding: '7px 0'
					},
					items: [
						this.progressCombo = new go.modules.community.tasks.ProgressCombo({
							flex: 30,
							value: 'needs-action',
							style: {
								paddingRight: dp(32)
							}


						}),
						this.percentCompleteFld = new Ext.form.SliderField({
							fieldLabel: t("Percent complete"),
							name: 'percentComplete',
							minValue: 0,
							maxValue: 100,
							increment: 10,
							value: 0,
							flex: 70
						}),
					]
				},

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
