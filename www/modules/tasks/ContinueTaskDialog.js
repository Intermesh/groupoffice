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

GO.tasks.ContinueTaskDialog = Ext.extend(GO.dialog.TabbedFormDialog, {

	initComponent: function () {

		Ext.apply(this, {
			//autoHeight:true,
			height: 640,
			goDialogId: 'continuetask',
			title: t("Continue task", "tasks"),
			formControllerUrl: 'tasks/task'
		});

		GO.tasks.ContinueTaskDialog.superclass.initComponent.call(this);

		this.formPanel.baseParams.remind = 'on';
	},
	beforeSubmit: function (params) {
		this.formPanel.baseParams.remind_date = this.formPanel.baseParams.due_time;
	},

	beforeLoad: function (remoteModelId, config) {
		this.formPanel.baseParams.due_time = this.datePicker.getValue().format(GO.settings.date_format);
	},

	buildForm: function () {

		var now = new Date();
		var tomorrow = now.add(Date.DAY, 1);
		var eight = Date.parseDate(tomorrow.format('Y-m-d') + ' 08:00', 'Y-m-d G:i');

		this.datePicker = new Ext.DatePicker({
			internalRender: true,
			xtype: 'datepicker',
			name: 'due_time',
			format: GO.settings.date_format,
			fieldLabel: t("Date")
		});

		this.datePicker.setValue(tomorrow);

		this.datePicker.on("select", function (DatePicker, DateObj) {
			this.formPanel.baseParams.due_time = DateObj.format(GO.settings.date_format);
		}, this);
		this.propertiesPanel = new Ext.Panel({
			border: false,
			
			layout: 'border',
			waitMsgTarget: true,
			items: [
				{
					region: 'north',
					layout: 'form',
					cls: 'go-form-panel',
					autoHeight: true,
					items: [{
							items: this.datePicker,
							width: 240,
							style: 'margin:auto;'
						},
						new GO.form.HtmlComponent({html: '<br />'}),
						{
							xtype: 'timefield',
							name: 'remind_time',
							width: 220,
							format: GO.settings.time_format,
							value: eight.format(GO.settings['time_format']),
							fieldLabel: t("Time"),
							anchor: '100%'
						},
						this.statusProgressField = new GO.tasks.StatusProgressField({}),
						this.selectTaskList = new GO.tasks.SelectTasklist({fieldLabel: t("Tasklist", "tasks"), anchor: '100%'}),
					]
				},
				{
					region: 'center',
					layout: 'anchor',
					cls: 'go-form-panel',
					items: [
						{
							xtype: 'textarea',
							name: 'comment',
							anchor: '100% 100%',							
							fieldLabel: t("Description")
						}]
				}

			]


		});

		this.addPanel(this.propertiesPanel);
	}
});
