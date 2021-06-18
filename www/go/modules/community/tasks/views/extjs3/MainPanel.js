/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
go.modules.community.tasks.MainPanel = Ext.extend(go.modules.ModulePanel, {
	title: t("Tasks"),
	layout: 'responsive',
	layoutConfig: {
		triggerWidth: 1000
	},

	initComponent: function () {
		this.createTaskGrid();
		this.createTasklistGrid();	
		this.createCategoriesGrid();

		this.taskDetail = new go.modules.community.tasks.TaskDetail({
			region: 'east',
			split: true,
			tbar: [{
				cls: 'go-narrow',
				iconCls: "ic-arrow-back",
				handler: function () {
					//this.westPanel.show();
					go.Router.goto("task");
				},
				scope: this
			}]
		});

		this.filterPanel = new go.NavMenu({
			region:'north',
			store: new Ext.data.ArrayStore({
				fields: ['name', 'icon', 'iconCls', 'inputValue'],
				data: [
					[t("Today"), 'content_paste', 'green', 'today'],
					[t("Completed"), 'assignment_turned_in', 'grey', 'completed'],
					[t("Unscheduled"), 'event_busy', 'blue','unscheduled'],
					[t("Scheduled"), 'events', 'orange', 'scheduled'],
					[t("All"), 'assignment', 'red', 'all'],
				]
			})
		});


		this.sidePanel = new Ext.Panel({
			width: dp(300),
			cls: 'go-sidenav',
			region: "west",
			layout:"border",
			split: true,

			items: [
				{
					autoScroll: true,
					region: "center",
					layout:'anchor',
					items:[
						this.filterPanel,
						this.categoriesGrid,
						this.createFilterPanel()
					]
				},
				this.tasklistsGrid

			]
		});


		this.items = [
			this.centerPanel = new Ext.Panel({
				layout:'border',
				stateId: "go-tasks-west",
				region: "center",
				split: true,

				width: dp(700),
				narrowWidth: dp(300),
				height:dp(800), //this will only work for panels inside another panel with layout=responsive. Not ideal but at the moment the only way I could make it work
				items:[
					this.taskGrid,
					this.taskDetail
				]
			}), //first is default in narrow mode
			this.sidePanel
		];

		go.modules.community.tasks.MainPanel.superclass.initComponent.call(this);

		this.on("afterrender", this.runModule, this);
	},
	
	runModule : function() {
		this.categoriesGrid.store.load();

		this.filterPanel.on("afterrender", () => {
			this.filterPanel.selectRange(0,0);
			this.setStatusFilter("today");
			this.filterPanel.on("selectionchange", this.onStatusSelectionChange, this);

			this.setDefaultSelection();



			this.tasklistsGrid.store.load();
			this.taskGrid.store.load();
		});

	},

	setDefaultSelection : function() {
		let selectedListIds = [];
		if(go.User.tasksSettings.rememberLastItems && go.User.tasksSettings.lastTasklistIds) {
			selectedListIds = go.User.tasksSettings.lastTasklistIds;
		}
		if(!selectedListIds.length && go.User.tasksSettings.defaultTasklistId) {
			selectedListIds.push(go.User.tasksSettings.defaultTasklistId);
		}

		this.tasklistsGrid.setDefaultSelection(selectedListIds)
		this.checkCreateTaskList();
	},

	onStatusSelectionChange: function(view, nodes) {

		const rec = view.store.getAt(nodes[0].viewIndex);
		this.setStatusFilter(rec.data.inputValue);
		this.taskGrid.store.load();
	},


	setStatusFilter : function(inputValue) {
		switch(inputValue) {

			case "today": // tasks today
				const now = new Date(),
					nowYmd = now.format("Y-m-d");

				this.taskGrid.store.setFilter("status", {
					start: "<=" + nowYmd,
					complete: false
				});

				break;

			// case 2: // tasks too late
			// 	var now = new Date(),
			// 	nowYmd = now.format("Y-m-d");
			// 	this.taskGrid.store.setFilter('status',{
			// 		late: nowYmd,
			// 		percentComplete: "<100"
			// 	});
			// 	break;

			// case 3: // non completed tasks
			// 	this.taskGrid.store.setFilter("status", {
			// 		percentComplete: "<100"
			// 	});
			// 	break;

			case "completed": // completed tasks
				this.taskGrid.store.setFilter("status", {
					complete: true
				});
				break;

			// case 5: // future tasks
			// 	var now = new Date(),
			// 	nowYmd = now.format("Y-m-d");
			// 	this.taskGrid.store.setFilter('status',{
			// 		future: nowYmd,
			// 		percentComplete: "<100"
			// 	});
			// 	break;
			case "unscheduled":
				this.taskGrid.store.setFilter('status',{
					scheduled: false
				});
				break;

			case "scheduled":
				this.taskGrid.store.setFilter('status',{
					scheduled: true
				});
				break;


			case "all": // all
				this.taskGrid.store.setFilter("status", null);
				break;
		}
	},

	createFilterPanel: function () {


		return new Ext.Panel({
			region: "center",
			minHeight: dp(200),
			autoScroll: true,
			tbar: [
				{
					xtype: 'tbtitle',
					text: t("Filters")
				},
				'->',
				{
					xtype: 'filteraddbutton',
					entity: 'Task'
				}
			],
			items: [
				{
					xtype: 'filtergrid',
					filterStore: this.taskGrid.store,
					entity: "Task"
				},
				{
					xtype: 'variablefilterpanel',
					filterStore: this.taskGrid.store,
					entity: "Task"
				}
			]
		});


	},


	createCategoriesGrid: function() {
		this.categoriesGrid = new go.modules.community.tasks.CategoriesGrid({
			filterName: "categories",
			filteredStore: this.taskGrid.store,
			autoHeight: true,
			split: true,
			tbar: [{
					xtype: 'tbtitle',
					text: t('Categories')
				}, '->', {
					//disabled: go.Modules.get("community", 'notes').permissionLevel < go.permissionLevels.write,
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (e, toolEl) {
						var dlg = new go.modules.community.tasks.CategoryDialog();
						dlg.show();
					}
				}],
			listeners: {
				rowclick: function(grid, row, e) {
					if(e.target.className != 'x-grid3-row-checker') {
						//if row was clicked and not the checkbox then switch to grid in narrow mode
						this.categoriesGrid.show();
					}
				},
				scope: this
			}
		});

	},

	createTasklistGrid : function() {
		this.tasklistsGrid = new go.modules.community.tasks.TasklistsGrid({
			region: "south",
			height: dp(300),

			filteredStore: this.taskGrid.store,
			filterName: 'tasklistId',

			split: true,
			tbar: [{
					xtype: 'tbtitle',
					text: t('Tasklist')
				}, '->', {
					//disabled: go.Modules.get("community", 'notes').permissionLevel < go.permissionLevels.write,
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (e, toolEl) {
						var dlg = new go.modules.community.tasks.TasklistDialog();
						dlg.show();
					}
				}],
			listeners: {
				rowclick: function(grid, row, e) {
					if(e.target.className != 'x-grid3-row-checker') {
						//if row was clicked and not the checkbox then switch to grid in narrow mode
						this.taskGrid.show();
					}
				},
				scope: this
			}
		});

		this.tasklistsGrid.on('selectionchange', this.onTasklistSelectionChange, this); //add buffer because it clears selection first
	},
	checkValues: function() {
		if(this.taskDateField.getValue() != null && this.taskNameTextField.getValue() != "") {
			this.addTaskButton.setDisabled(false);
		} else {
			this.addTaskButton.setDisabled(true);
		}
	},
	
	createTaskGrid : function() {

		this.taskGrid = new go.modules.community.tasks.TaskGrid({

			region: 'center',
			tbar: [
					{
						cls: 'go-narrow',
						iconCls: "ic-menu",
						handler: function () {
//						this.westPanel.getLayout().setActiveItem(this.noteBookGrid);
							this.tasklistsGrid.show();
						},
						scope: this
					},
					'->',
					{
						xtype: 'tbsearch'
					},
					this.addButton = new Ext.Button({
						disabled: true,
						iconCls: 'ic-add',
						tooltip: t('Add'),
						cls: 'primary',
						handler: function (btn) {
							var dlg = new go.modules.community.tasks.TaskDialog();
							dlg.show();
							dlg.setValues({
								tasklistId: this.addTasklistId
							});
						},
						scope: this
					}),
					{
						iconCls: 'ic-more-vert',
						menu: [
							{
								iconCls: 'ic-cloud-upload',
								text: t("Import"),
								handler: function() {
									var dlg = new go.modules.community.tasks.ChooseTasklistDialog();
									dlg.show();
								},
								scope: this
							},
							{
								iconCls: 'ic-cloud-download',
								text: t("Export"),
								menu: [
									{
										text: 'vCalendar',
										iconCls: 'ic-contacts',
										handler: function() {
											go.util.exportToFile(
												'Task',
												Ext.apply(this.taskGrid.store.baseParams, this.taskGrid.store.lastOptions.params, {limit: 0, start: 0}),
												'ics');
										},
										scope: this
									},{
										text: 'CSV',
										iconCls: 'ic-description',
										handler: function() {
											go.util.exportToFile(
												'Task',
												Ext.apply(this.taskGrid.store.baseParams, this.taskGrid.store.lastOptions.params, {limit: 0, start: 0}),
												'csv');
										},
										scope: this
									}
								]
							},
							{
								itemId: "delete",
								iconCls: 'ic-delete',
								text: t("Delete"),
								handler: function () {
									this.taskGrid.deleteSelected();
								},
								scope: this
							},
							{
								iconCls: 'ic-refresh',
								tooltip: t("Refresh"),
								text: t("Refresh"),
								handler: function(){
									this.taskGrid.store.load();
									this.categoriesGrid.store.load();
								},
								scope: this
							}
						]
					}

				],
			bbar: new Ext.Toolbar({
				layout:'hbox',
				layoutConfig: {
					align: 'middle',
					defaultMargins: {left: dp(4), right: dp(4),bottom:0,top:0}
				},
				items:[this.taskNameTextField = new Ext.form.TextField({
					enableKeyEvents: true,
					emptyText: t("Add a task..."),
					flex:1,
					listeners: {
						specialkey: (field, e) => {
							// e.HOME, e.END, e.PAGE_UP, e.PAGE_DOWN,
							// e.TAB, e.ESC, arrow keys: e.LEFT, e.RIGHT, e.UP, e.DOWN
							if (e.getKey() == e.ENTER) {
								this.createTask();
							}
						}
					}
				}),
					this.taskDateField = new go.form.DateField({
						fieldLabel:t("Due date"),
						enableKeyEvents: true,
						listeners: {
							scope: this,
							keyup: function(field, e) {
								this.checkValues();
							},
							select: function(field,date) {
								this.checkValues();
							},
							specialkey: (field, e) => {
								// e.HOME, e.END, e.PAGE_UP, e.PAGE_DOWN,
								// e.TAB, e.ESC, arrow keys: e.LEFT, e.RIGHT, e.UP, e.DOWN
								if (e.getKey() == e.ENTER) {
									this.addTaskButton.handler.call(this);
								}
							}
						}
					}),
					this.addTaskButton = new Ext.Button({
						disabled: true,
						iconCls: 'ic-add',
						cls:'primary',
						handler: this.createTask,
						scope: this
					})
				]
			}),
			listeners: {				
				rowdblclick: this.onTaskGridDblClick,
				scope: this,				
				keypress: this.onTaskGridKeyPress
			}
		});
		//this.quickAddTaskListCombo.store.load();
		this.taskGrid.on('navigate', function (grid, rowIndex, record) {
			go.Router.goto("task/" + record.id);
		}, this);
		
	
	},

	createTask : function() {

		go.Db.store("Task").set({
			create: {"client-id-1" : {
					title: this.taskNameTextField.getValue(),
					start: this.taskDateField.getValue(),
					tasklistId: this.addTasklistId
				}}
		}).then (() => {
				this.taskNameTextField.reset();
		});

	},

	onTasklistSelectionChange : function (ids, sm) {

		this.checkCreateTaskList();

		this.taskGrid.store.setFilter("role", ids.length == 0 ? {role:  go.modules.community.tasks.listTypes.List} : null);

		if(go.User.tasksSettings.rememberLastItems && go.User.tasksSettings.lastTasklistIds.join(",") != ids.join(",")) {

			go.Db.store("User").save({
				tasksSettings: {
					lastTasklistIds: ids
				}
			}, go.User.id);

		}
	},

	checkCreateTaskList: function() {

		this.addTasklistId = false;

		go.Db.store("Tasklist").get(this.tasklistsGrid.getSelectedIds()).then((result) => {

			result.entities.forEach((tasklist) => {
				if (!this.addTasklistId && tasklist.permissionLevel >= go.permissionLevels.write) {
					this.addTasklistId = tasklist.id;
				}
			});

			this.addButton.setDisabled(!this.addTasklistId);
			this.addTaskButton.setDisabled(!this.addTasklistId)
		});
	},

	
	onTaskGridDblClick : function (grid, rowIndex, e) {

		var record = grid.getStore().getAt(rowIndex);
		if (record.get('permissionLevel') < go.permissionLevels.write) {
			return;
		}

		var dlg = new go.modules.community.tasks.TaskDialog();
		dlg.load(record.id).show();
	},
	
	onTaskGridKeyPress : function(e) {
		if(e.keyCode != e.ENTER) {
			return;
		}
		var record = this.taskGrid.getSelectionModel().getSelected();
		if(!record) {
			return;
		}

		if (record.get('permissionLevel') < go.permissionLevels.write) {
			return;
		}

		var dlg = new go.modules.community.tasks.TaskDialog();
		dlg.load(record.id).show();
	}	
});

