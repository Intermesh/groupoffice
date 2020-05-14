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

go.modules.community.tasks.ContinueTaskDialog = Ext.extend(go.form.Dialog, {
	entityStore: "Task",
	initComponent: function () {

		Ext.apply(this, {
			height: 640,
			title: t("Continue task", "tasks"),
		});

		//this.selectTaskList.store.load();
		go.modules.community.tasks.ContinueTaskDialog.superclass.initComponent.call(this);
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
				entityStore: "Tasklist",
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
		
		var remindDate = new Date();
		this.items = [
			new go.modules.community.tasks.AlertFields()
		]
		return this.items;
	}
});
