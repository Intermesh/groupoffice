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
 * @author Richard van Dartel <rvdartel@intermesh.nl>
 */

go.modules.community.task.ContinueTaskDialog = Ext.extend(go.form.Dialog, {
	entityStore: "TasksTask",
	initComponent: function () {

		Ext.apply(this, {
			height: 640,
			title: t("Continue task", "tasks"),
		});

		//this.selectTaskList.store.load();
		go.modules.community.task.ContinueTaskDialog.superclass.initComponent.call(this);
	},
	beforeSubmit: function (params) {
		alert("Before submit");
	},

	beforeLoad: function (remoteModelId, config) {
		alert("Before load");
	},
	initFormItems: function () {
		var now = new Date();
		var tomorrow = now.add(Date.DAY, 1);
		var eight = Date.parseDate(tomorrow.format('Y-m-d') + ' 08:00', 'Y-m-d G:i');

		this.datePicker = new Ext.DatePicker({
			internalRender: true,
			xtype: 'datepicker',
			name: 'remindDate',
			format: GO.settings.date_format,
			fieldLabel: t("Date")
		});

		this.datePicker.setValue(tomorrow);

		var now = new Date();

		this.hiddenCheck = new Ext.form.Hidden({
			name: 'alert.checkbox',
			value: 'true'
		});

		this.hiddenField = new Ext.form.Hidden({
			name: 'alert.remindDate'
		});

		this.hiddenField.setValue(now.format(GO.settings.date_format));

		this.selectTasklist = new go.form.ComboBoxReset({
			hiddenName:'tasklistId',
			fieldLabel:t("Tasklist"),
			valueField:'id',
			displayField:'name',			
			store: new go.data.Store({
				fields:['id','name','user_name'],
				entityStore: "TasksTasklist",
				displayField: "name",
				// baseParams:{
				// 	//permissionLevel: GO.permissionLevels.create
				// },
			}),
			mode:'local',
			triggerAction:'all',
			emptyText:t("Select tasklist"),
			editable:false,
			selectOnFocus:true,
			forceSelection:true,
			pageSize: parseInt(GO.settings['max_rows_list'])
		});
		
		this.selectTasklist.store.load();
		
		// this.items = [
		// 	{
		// 		region: 'north',
		// 		layout: 'form',
		// 		cls: 'go-form-panel',
		// 		autoHeight: true,
		// 		items: [{
		// 				items: this.datePicker,
		// 				width: 240,
		// 				style: 'margin:auto;'
		// 				},
		// 				this.hiddenField,
		// 				this.hiddenCheck,
		// 				new GO.form.HtmlComponent({html: '<br />'}),
		// 				{
		// 					xtype: 'timefield',
		// 					name: 'alert.remindTime',
		// 					width: 220,
		// 					format: GO.settings.time_format,
		// 					value: eight.format(GO.settings['time_format']),
		// 					fieldLabel: t("Time"),
		// 					anchor: '100%'
		// 				},
		// 				this.selectTasklist,
		// 			]
		// 	},
		// 	{
		// 		region: 'center',
		// 		layout: 'anchor',
		// 		cls: 'go-form-panel',
		// 		items: [
		// 			{
		// 				xtype: 'textarea',
		// 				name: 'description',
		// 				anchor: '100% 100%',							
		// 				fieldLabel: t("Description")
		// 			}]
		// 	}
		// ];
		var remindDate = new Date();
		this.items = [{
			//For relational properties we can use the "go.form.FormGroup" component.
			//It's a sub form for the "alerts" array property.

			xtype: "formgroup",
			name: "alerts",
			hideLabel: true,

			// this will add dp(16) padding between rows.
			pad: true,

			//the itemCfg is used to create a component for each "album" in the array.
			itemCfg: {
					layout: "form",
					defaults: {
							anchor: "90%"
					},
					items: [{
								xtype : 'xcheckbox',
								boxLabel : t("Remind me", "tasks"),
								hideLabel : true,
								name : 'checkbox',
								listeners : {
									'check' : function(field, checked) {
										var nextDate = field.nextSibling();
										nextDate.setDisabled(!checked);
										nextDate.nextSibling().setDisabled(!checked);
									},
									scope : this
								}
							},
							{
								xtype : 'datefield',
								name : 'remindDate',
								format : GO.settings.date_format,
								value : remindDate.format(GO.settings['date_format']),
								fieldLabel : t("Date"),
								disabled : true
							},

							{
								xtype : 'timefield',
								name : 'remindTime',
								format : GO.settings.time_format,
								value : "02:01 AM",
								fieldLabel : t("Time"),
								disabled : true
							}
					]
			}
	}
]
		return this.items;
	}
});
