/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: TaskDialog.js 22317 2018-02-02 15:27:30Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.tasks.TaskDialog = function() {

	this.buildForm();

	var focusName = function() {
		this.nameField.focus();
	};

	this.goDialogId='task';
	
	this.remind_before = '';

	this.win = new GO.Window({
		layout : 'fit',
		modal : false,
		resizable : true,
		stateId: 'task-dialog',
		width : dp(640),
		height : dp(560),
		closeAction : 'close',
		collapsible: true,
		title : t("Task", "tasks"),
		items : this.formPanel,
		focus : focusName.createDelegate(this),
		bbar : [
			this.createLinkButton = new go.links.CreateLinkButton(),		
			"->",
			{
				text : t("Save"),
				handler : function() {
					this.submitForm(true);

				},
				scope : this
			}]
	/*
 * , keys: [{ key: Ext.TaskObject.ENTER, fn: function(){ this.submitForm();
 * this.win.hide(); }, scope:this }]
 */
	});

	this.win.render(Ext.getBody());

	this.win.on("hide", function() {
		this.createLinkButton.reset();
	}, this);

	GO.tasks.TaskDialog.superclass.constructor.call(this);

	this.win.on("hide", function() {
		this.createLinkButton.reset();
	}, this);
}

Ext.extend(GO.tasks.TaskDialog, Ext.util.Observable, {

	isVisible : function() {
		return this.win.isVisible();
	},

	setLinkEntity : function(cfg) {
		
		switch(cfg.entity) {
			case 'Project':
			case "Contact":				
				this.formPanel.getForm().findField("name").setValue(cfg.data.name);
			break;

			case 'Order':
				this.formPanel.getForm().findField("name").setValue(cfg.data.order_id);
			break;
		} 
	},
	
	show : function(config) {

		if (!config) {
			config = {};
		}		
		
		
		
		this.showConfig=config;

		GO.dialogListeners.apply(this);

		if(!GO.tasks.categoriesStore.loaded)
			GO.tasks.categoriesStore.load();

		//tmpfiles on the server ({name:'Name',tmp_file:/tmp/name.ext} will be attached)
		this.formPanel.baseParams.tmp_files = config.tmp_files ? Ext.encode(config.tmp_files) : '';

		if(config.projectName)
			this.formPanel.baseParams.project_name=config.projectName;
		else
			delete this.formPanel.baseParams.project_name;
		
		delete this.link_config;
		this.formPanel.form.reset();

		//		this.formPanel.form.findField('remind').setValue(!GO.util.empty(GO.tasks.remind));
		//		this.formPanel.form.findField('remind_date').setDisabled(GO.util.empty(GO.tasks.remind));
		//		this.formPanel.form.findField('remind_time').setDisabled(GO.util.empty(GO.tasks.remind));

		this.tabPanel.setActiveTab(0);

		if (!config.task_id) {
			config.task_id = 0;
		}

		this.setTaskId(config.task_id);
		
		var params = {};
		if (!GO.util.empty(config.tasklist_id))
			params.tasklist_id=config.tasklist_id;
		
		if (config.link_config && config.link_config.model_name=="GO\\Projects\\Model\\Project")
			params.project_id=config.link_config.model_id;	
		
		// this.selectTaskList.container.up('div.x-form-item').setDisplayed(false);

		//		if (config.task_id > 0) {

		this.formPanel.load({
			url : GO.url('tasks/task/load'),
			params:params,
			success : function(form, action) {
				this.win.show();
				
				this.recurrencePanel.reset();

				this.setValues(config.values);
					
				this.remind_before = action.result.data.remind_before;

				//	this.selectTaskList.setRemoteText(action.result.data.tasklist_name);
				this.selectTaskList.setRemoteText(action.result.remoteComboTexts.tasklist_id);

				if(this.selectProject){
					if(config.link_config && config.link_config.model_name=="GO\\Projects2\\Model\\Project"){			

						this.selectProject.setValue(config.link_config.model_id);
						this.selectProject.setRemoteText(config.link_config.text);
					}else
					{
						this.selectProject.setRemoteText(action.result.remoteComboTexts.project_id);
					}
				}
			
				var startDate = this.formPanel.form.findField('start_time');
			
				this.recurrencePanel.setStartDate(startDate.getValue());
				this.recurrencePanel.changeRepeat(action.result.data.freq);
				this.recurrencePanel.setDaysButtons(action.result.data);
			
				if(action.result.data.category_id == 0)
				{
					//this.selectCategory.setRemoteText();
					this.selectCategory.setValue("");
				}else
				{
					this.selectCategory.setRemoteText(action.result.remoteComboTexts.category_id);
				}
					
				this.formPanel.form.clearInvalid();
					
			},
			failure : function(form, action) {
				GO.errorDialog.show(action.result.feedback)
			},
			scope : this

		});
		//		} else {
		//			delete this.formPanel.form.baseParams['exception_task_id'];
		//			delete this.formPanel.form.baseParams['exceptionDate'];
		//
		//			this.lastTaskListId = this.selectTaskList.getValue();
		//
		//			this.selectTaskList.setValue(this.lastTaskListId);
		//
		//			this.setWritePermission(true);
		//
		//			this.win.show();
		//			this.setValues(config.values);
		//
		//			if (GO.util.empty(config.tasklist_id)) {
		//				config.tasklist_id = GO.tasks.defaultTasklist.id;
		//				config.tasklist_name = GO.tasks.defaultTasklist.name;
		//			}
		//			this.selectTaskList.setValue(config.tasklist_id);
		//			if (config.tasklist_name) {
		//				this.selectTaskList.setRemoteText(config.tasklist_name);
		//				this.selectTaskList.container.up('div.x-form-item').setDisplayed(true);
		//			}else
		//			{
		//				this.selectTaskList.container.up('div.x-form-item').setDisplayed(false);
		//			}
		//		}

		
	},


	setValues : function(values) {
		if (values) {
			for (var key in values) {
				var field = this.formPanel.form.findField(key);
				if (field) {
					field.setValue(values[key]);
				}
			}
		}

	},
	setTaskId : function(task_id) {
		this.formPanel.form.baseParams['id'] = task_id;
		this.task_id = task_id;		
		this.createLinkButton.setEntity("Task", task_id);
		
	},

	setCurrentDate : function() {
		var formValues = {};

		var date = new Date();

		formValues['start_time'] = formValues['remind_date'] = date
		.format(GO.settings['date_format']);
		formValues['start_hour'] = date.format("H");
		formValues['start_min'] = '00';

		formValues['end_date'] = date.format(GO.settings['date_format']);
		formValues['end_hour'] = date.add(Date.HOUR, 1).format("H");
		formValues['end_min'] = '00';

		this.formPanel.form.setValues(formValues);
	},

	submitForm : function(hide) {
		this.formPanel.form.submit({
			url : GO.url('tasks/task/submit'),
			waitMsg : t("Saving..."),
			success : function(form, action) {

				if (action.result.id) {
					this.setTaskId(action.result.id);
					
					this.createLinkButton.save();
				}

				if (this.link_config && this.link_config.callback) {
					this.link_config.callback.call(this);
				}

				GO.tasks.tasksObservable.fireEvent('save', this, this.task_id);
				this.fireEvent('save', this, this.task_id);

				GO.dialog.TabbedFormDialog.prototype.refreshActiveDisplayPanels.call(this);

				if (hide) {
					this.win[this.win.closeAction]();
				}
			},
			failure : function(form, action) {
				if (action.failureType == 'client') {
					GO.errorDialog.show(t("You have errors in your form. The invalid fields are marked."));
				} else {
					GO.errorDialog.show(action.result.feedback);
				}
			},
			scope : this
		});

	},

	buildForm : function() {

		this.nameField = new Ext.form.TextField({
			name : 'name',
			allowBlank : false,
			fieldLabel : t("Subject")
		});

		
		var checkDateInput = function(field) {

			if (field.name == 'due_time') {
				if (startDate.getValue() > dueDate.getValue()) {
					startDate.setValue(dueDate.getValue());
				}
			} else {
				if (startDate.getValue() > dueDate.getValue()) {
					dueDate.setValue(startDate.getValue());
				}
			}

			var remindDate = startDate.getValue().add(Date.DAY, -this.remind_before);
			
			this.formPanel.form.findField('remind_date').setValue(remindDate);

			if (this.recurrencePanel.repeatType.getValue() != '') {
				if (this.recurrencePanel.repeatEndDate.getValue() == '') {
					this.recurrencePanel.repeatForeverXCheckbox.setValue(true);
				} else {
					var eD = dueDate.getValue();
					if (this.recurrencePanel.repeatEndDate.getValue() < eD) {
						this.recurrencePanel.repeatEndDate.setValue(eD.add(Date.DAY, 1));
					}
				}
			}
		}

		var now = new Date();

		var startDate = new Ext.form.DateField({
			name : 'start_time',
			format : GO.settings['date_format'],
			fieldLabel : t("Starts at", "tasks"),
			value : now.format(GO.settings.date_format),
			listeners : {
				change : {
					fn : checkDateInput,
					scope : this
				}
			}
		});

		var dueDate = new Ext.form.DateField({
			name : 'due_time',
			format : GO.settings['date_format'],
			allowBlank : false,
			fieldLabel : t("Due at", "tasks"),
			value : now.format(GO.settings.date_format),
			listeners : {
				change : {
					fn : checkDateInput,
					scope : this
				}
			}
		});

		var taskStatus = new GO.tasks.SelectTaskStatus({
			flex:3,
			listeners:{
				scope:this,
				select:function(combo, record){
					if(record.data.value=='COMPLETED')
						this.formPanel.form.findField('percentage_complete').setValue(100);
				}
			}
		});

		this.selectTaskList = new GO.tasks.SelectTasklist({
			fieldLabel : t("Tasklist", "tasks"),
			allowBlank:false
		});

		this.selectCategory = new GO.form.ComboBoxReset({
			hiddenName:'category_id',
			fieldLabel:t("Category", "tasks"),
			valueField:'id',
			displayField:'name',			
			store: GO.tasks.categoriesStore,
			mode:'local',
			triggerAction:'all',
			emptyText:t("Select category", "tasks"),
			editable:false,
			selectOnFocus:true,
			forceSelection:true,
			pageSize: parseInt(GO.settings['max_rows_list'])
		});

		this.selectPriority = new GO.form.SelectPriority();
		
		var descAnchor = -320;

		var propertiesPanel = new Ext.Panel({
			hideMode : 'offsets',
			title : t("Properties"),
			
			//cls:'go-form-panel',
			//waitMsgTarget:true,
			layout : 'form',
			autoScroll : true,
			items : [{
					xtype: "fieldset",
					defaults : {
				anchor : '100%'
			},
			labelWidth:120,
					items:[
			
				this.nameField, 
				startDate,
				dueDate,
				this.statusProgressField = new GO.tasks.StatusProgressField({}),
				this.selectTaskList,
				this.selectCategory,
				this.selectPriority	
				]
			}
			]

		});

		if(GO.moduleManager.userHasModule("projects2")){
			descAnchor-=40;
			this.selectProject = new GO.projects2.SelectProject();
			propertiesPanel.items.first().add(this.selectProject);
		} else if(GO.moduleManager.userHasModule("projects")) {
			descAnchor-=40;
			this.selectProject = new GO.projects.SelectProject();
			propertiesPanel.items.first().add(this.selectProject);
		}
		
		propertiesPanel.items.first().add({
				xtype:'textarea',
				fieldLabel:t("Description"),
				name : 'description',
				anchor: '100%',
				grow: true,
				preventScrollbars: true
			});
			
	
		
		// Start of recurrence tab
		this.recurrencePanel = new go.form.RecurrenceFieldset();

		var remindDate = now.add(Date.DAY, -GO.tasks.reminderDaysBefore);
		// start other options tab
		var optionsPanel = new Ext.Panel({

			title : t("Options", "tasks"),
			defaults : {
				anchor : '100%'
			},
			bodyStyle : 'padding:5px',
			layout : 'form',
			hideMode : 'offsets',
			autoScroll : true,
			items : [{
				xtype : 'xcheckbox',
				boxLabel : t("Remind me", "tasks"),
				hideLabel : true,
				name : 'remind',
				listeners : {
					'check' : function(field, checked) {
						this.formPanel.form.findField('remind_date')
						.setDisabled(!checked);
						this.formPanel.form.findField('remind_time')
						.setDisabled(!checked);
					},
					scope : this
				}
			}, {
				xtype : 'datefield',
				name : 'remind_date',
				format : GO.settings.date_format,
				value : remindDate.format(GO.settings['date_format']),
				fieldLabel : t("Date"),
				disabled : true
			}, {
				xtype : 'timefield',
				name : 'remind_time',
				format : GO.settings.time_format,
				value : GO.tasks.reminderTime,
				fieldLabel : t("Time"),
				disabled : true
			}]
		});

		var items = [propertiesPanel, this.recurrencePanel, optionsPanel];

		this.tabPanel = new Ext.TabPanel({
			activeTab : 0,
			deferredRender : false,
			// layoutOnTabChange:true,
			border : false,
			anchor : '100% 100%',
			hideLabel : true,
			items : items
		});
		
		
		go.customfields.CustomFields.getFormFieldSets("Task").forEach(function(fs) {
			//console.log(fs);
			if(fs.fieldSet.isTab) {
				fs.title = null;
				fs.collapsible = false;
				var pnl = new Ext.Panel({
					autoScroll: true,
					hideMode: 'offsets', //Other wise some form elements like date pickers render incorrectly.
					title: fs.fieldSet.name,
					items: [fs]
				});
				this.tabPanel.add(pnl);
			}else
			{			
				propertiesPanel.add(fs);
			}
		}, this);

		var formPanel = this.formPanel = new Ext.form.FormPanel({
			waitMsgTarget : true,
			url : GO.settings.modules.tasks.url + 'action.php',
			border : false,
			baseParams : {
				task : 'task'
			},
			items : this.tabPanel
		});

	}
});
