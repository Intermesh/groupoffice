go.modules.community.task.TaskDialog = Ext.extend(go.form.Dialog, {
	title: t("Task"),
	entityStore: "TasksTask",
	width: dp(800),
	height: dp(800),
	
	onLoad: function (values) {
		this.recurrencePanel.setStartDate(values.start);
		this.recurrencePanel.changeRepeat(values.recurrenceRule.freq);
		this.recurrencePanel.setDaysButtons(values.recurrenceRule);

		go.modules.community.task.TaskDialog.superclass.onLoad.call(this,values);
	},

	initFormItems: function () {
		var formFieldSets = go.customfields.CustomFields.getFormFieldSets("Task").filter(function(fs) {
			return !fs.fieldSet.isTab;
		});
		var fieldSetAnchor = formFieldSets.length ? '100% 80%' : '100% 100%';
		
		this.titleField = new Ext.form.TextField({
			name : 'title',
			allowBlank : false,
			fieldLabel : t("Subject")
		});

		var checkDateInput = function(field) {
			if (field.name == 'due') {
				if (start.getValue() > due.getValue()) {
					start.setValue(due.getValue());
				}
			} else {
				if (start.getValue() > due.getValue()) {
					due.setValue(start.getValue());
				}
			}
		}

		var now = new Date();

		var start = new Ext.form.DateField({
			name : 'start',
			fieldLabel : t("Starts at", "tasks"),
			value : now.format(go.User.dateFormat),
			listeners : {
				change : {
					fn : checkDateInput,
					scope : this
				}
			}
		});

		var due = new Ext.form.DateField({
			name : 'due',
			allowBlank : false,
			fieldLabel : t("Due at", "tasks"),
			value : now.format(go.User.dateFormat),
			listeners : {
				change : {
					fn : checkDateInput,
					scope : this
				}
			}
		});

		// this.selectCategory = new go.form.ComboBoxReset({
		// 	hiddenName:'categories',
		// 	fieldLabel:t("Category", "tasks"),
		// 	valueField:'id',
		// 	displayField:'name',			
		// 	store: new go.data.Store({
		// 		fields: ['id', 'name'],
		// 		entityStore: "TasksCategory",
		// 		displayField: 'name'
		// 	}),
		// 	mode:'local',
		// 	triggerAction:'all',
		// 	emptyText:t("Select category"),
		// 	editable:false,
		// 	selectOnFocus:true,
		// 	forceSelection:true,
		// 	pageSize: parseInt(GO.settings['max_rows_list'])
		// });

		this.selectCategory = new go.form.Chips({
			anchor: '-20',
			xtype: "chips",
			entityStore: "TasksCategory",
			displayField: "name",
			valueField: 'id',
			storeBaseParams: {
				filter: {
					//isOrganization: true
				}
			},
			name: "categories",
			fieldLabel:t("Category", "tasks")
		}),

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
		

		this.taskCombo = new go.modules.community.task.TaskCombo({});
		this.selectPriority = new go.modules.community.task.SelectPriority();
		this.descriptionField = new Ext.form.TextField({
			name : 'description',
			allowBlank : false,
			fieldLabel : t("Description")
		});

		var propertiesPanel = new Ext.Panel({
			hideMode : 'offsets',
			title : t("Properties"),
			
			layout : 'form',
			autoScroll : true,
			items : [{
					xtype: "fieldset",
					defaults : {
					anchor : '100%'
			},
			labelWidth:120,
					items:[
				this.titleField, 
				start,
				due,
				this.selectTasklist,
				this.selectCategory,
				this.selectPriority,
				this.descriptionField
				]
			}
			]
		});

		this.recurrencePanel = new go.form.RecurrencePanel();

		// this.recurrencePanel.setStartDate(start.getValue());
		// console.error(this.values);
		// go.Db.store("TasksTask").get().then(function(result) {
		// 	console.log(result);
		// 	this.recurrencePanel.setthis.recurrencePanel.setStartDate(start.getValue());
		// console.error(this.values);DaysButtons(result);
		// 	//alert(result);this.recurrencePanel.setStartDate(start.getValue());
		// console.error(this.values);
		// 	//console.log(result.entthis.recurrencePanel.setStartDate(start.getValue());
		// console.error(this.values);ities);
		// });
		// var store = go.Db.store("TasksTask");
		// //this.recurrencePanel.changeRepeat(action.result.data.freq);
		// this.recurrencePanel.setDaysButtons(store.data);

		//var remindDate = now.add(Date.DAY, -GO.tasks.reminderDaysBefore);
		var remindDate = now;
		// start other options tab
		this.optionsPanel = new Ext.Panel({

			title : t("Options", "tasks"),
			defaults : {
				anchor : '100%'
			},
			bodyStyle : 'padding:5px',
			layout : 'form',
			hideMode : 'offsets',
			autoScroll : true,

			items: [{
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
										anchor: "100%"
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
			],
		});

		var items = [
			propertiesPanel,this.recurrencePanel,this.optionsPanel
		];

		this.tabPanel = new Ext.TabPanel({
			activeTab : 0,
			deferredRender : false,
			border : false,
			anchor : '100% 100%',
			hideLabel : true,
			items : items
		});
		
		//this.selectCategory.store.load();
		this.selectTasklist.store.load();
		return this.tabPanel;
	}
});
