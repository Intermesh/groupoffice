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

		var filterPanel = new go.NavMenu({
			region:'north',
			store: new Ext.data.ArrayStore({
				fields: ['name', 'icon', 'inputValue'],
				data: [
					[t("Today"), 'content_paste', 'active'],
					[t("Due in seven days"), 'filter_7', 'sevendays'],
					[t("Overdue"), 'schedule', 'overdue'],
					[t("Incomplete tasks"), 'assignment_late', 'incomplete'],
					[t("Completed"), 'assignment_turned_in', 'completed'],
					[t("Future tasks"), 'assignment_return', 'future'],
					[t("Unplanned"), 'event_busy', 'unplanned'],
					[t("All"), 'assignment', 'all'],
				]
			}),
			listeners: {
				selectionchange: function(view, nodes) {
					switch(nodes[0].viewIndex) {

						case 0: // tasks today
							var now = new Date(),
							nowYmd = now.format("Y-m-d");
							this.taskGrid.store.setFilter("tasklist", {
								due: nowYmd,
								complete: false
							});
							break;

						case 1: // ends this week
							var now = new Date();
							var nextWeek = now.add(Date.DAY, 7);

							nowYmd = now.format("Y-m-d");
							nextWeekYmd = nextWeek.format("Y-m-d");
							this.taskGrid.store.setFilter("tasklist", {
								nextWeekStart: now,
								nextWeekEnd: nextWeekYmd,
								percentComplete: 0
							});
							break;

						case 2: // tasks too late
							var now = new Date(),
							nowYmd = now.format("Y-m-d");
							this.taskGrid.store.setFilter('tasklist',{
								late: nowYmd,
								percentComplete: 0
							});
							break;

						case 3: // non completed tasks
							this.taskGrid.store.setFilter("tasklist", {
								percentComplete: 0
							});
							break;

						case 4: // completed tasks
							this.taskGrid.store.setFilter("tasklist", {
								percentComplete: 100
							});
						break;

						case 5: // future tasks
							var now = new Date(),
							nowYmd = now.format("Y-m-d");
							this.taskGrid.store.setFilter('tasklist',{
								future: nowYmd,
								percentComplete: 0
							});
							break;
						case 6:
							this.taskGrid.store.setFilter('tasklist',{
								unplanned: true
							});
							break;
						case 7: // all
							this.taskGrid.store.setFilter("tasklist", null);
							break;
					}
					this.taskGrid.store.load();
					// var record = view.store.getAt(nodes[0].viewIndex);
				},
				scope: this
			}
		});

		this.sidePanel = new Ext.Panel({
			width: dp(300),
			cls: 'go-sidenav',
			region: "west",
			layout:'anchor',
			split: true,
			autoScroll: true,
			items: [
				filterPanel,
				this.tasklistsGrid,
				this.categoriesGrid
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
		this.taskGrid.store.load();
		this.categoriesGrid.store.load();
	},
	
	runModule : function() {
		//load task lists and select the first
		this.tasklistsGrid.getStore().load({
			callback: function (store) {
				//this.tasklistsGrid.getSelectionModel().selectRow(0);
			},
			scope: this
		});
	},
	
	createCategoriesGrid: function() {
		this.categoriesGrid = new go.modules.community.tasks.CategoriesGrid({
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

		this.categoriesGrid.getSelectionModel().on('selectionchange', this.onCategorySelectionChange, this, {buffer: 1}); //add buffer because it clears selection first
	},
	createTasklistGrid : function() {
		this.tasklistsGrid = new go.modules.community.tasks.TasklistsGrid({
			autoHeight: true,
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

		this.tasklistsGrid.getSelectionModel().on('selectionchange', this.onTasklistSelectionChange, this, {buffer: 1}); //add buffer because it clears selection first
	},
	checkValues: function() {
		if(this.taskDateField.getValue() != null && this.taskNameTextField.getValue() != "") {
			this.addTaskButton.setDisabled(false);
		} else {
			this.addTaskButton.setDisabled(true);
		}
	},
	
	createTaskGrid : function() {

		this.tasksStore = new go.data.Store({
			fields: [
				'id', 
				'title',
				'start',
				'due',
				'description', 
				'repeatEndTime',
				{name: 'responsible', type: 'relation'},
				{name: 'createdAt', type: 'date'}, 
				{name: 'modifiedAt', type: 'date'}, 
				{name: 'creator', type: "relation"},
				{name: 'modifier', type: "relation"},
				'percentComplete',
				'progress'
			],
			entityStore: "Task"
		});

		this.taskGrid = new go.modules.community.tasks.TaskGrid({
			store: this.tasksStore,
			region: 'center',
			tbar: new Ext.Toolbar({items:[
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
						xtype: 'tbsearch',
						store: this.tasksStore,
						filters: [
							'text',
							'name',
							'content',
							{name: 'modified', multiple: false},
							{name: 'created', multiple: false}
						],
						listeners: {
							scope: this,
							search: function(btn, query, filters) {
								//this.taskGrid.store.baseParams
								var filters =  [
									'text',
									'name',
									'content',
									{name: 'modified', multiple: false},
									{name: 'created', multiple: false}
								];


								this.taskGrid.store.setFilter("tbsearch", filters);
								this.taskGrid.store.load();
							},
							reset: function() {
								this.taskGrid.store.setFilter("tbsearch", null);
								this.taskGrid.store.load();
							}
						}
					},
					// {
					// 	xtype: 'tbsearch',
					// 	filters: [
					// 		// 'text',
					// 		'title'
					// 		// 'content',
					// 		// {name: 'modified', multiple: false},
					// 		// {name: 'created', multiple: false}
					// 	]
					// },
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

				]}),
			bbar: new Ext.Toolbar({
				layout:'hbox',
				layoutConfig: {
					align: 'middle',
					defaultMargins: {left: dp(4), right: dp(4),bottom:0,top:0}
				},
				items:[this.taskNameTextField = new Ext.form.TextField({
					enableKeyEvents: true,
					emptyText: t("Add a task..."),

					flex:1
				}),
					this.taskDateField = new go.form.DateField({
						value: new Date(),
						fieldLabel:t("Due date"),
						enableKeyEvents: true,
						listeners: {
							scope: this,
							keyup: function(field, e) {
								this.checkValues();
							},
							select: function(field,date) {
								this.checkValues();
							}

						}
					}),
					this.addTaskButton = new Ext.Button({
						disabled: true,
						iconCls: 'ic-add',
						cls:'primary',
						handler:function(){
							go.Db.store("Task").set({
								create: {"client-id-1" : {
										title: this.taskNameTextField.getValue(),
										start: this.taskDateField.getValue(),
										tasklistId: this.addTasklistId

									}}
							});
						},
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
	onTasklistSelectionChange : function (sm) {
		var ids = [];

		this.addTasklistId = false;

		Ext.each(sm.getSelections(), function (r) {
			ids.push(r.id);
			if (!this.addTasklistId && r.json.permissionLevel >= go.permissionLevels.write) {
			// is dit goed? r.get('permissionLevel')
			// if (!this.addTasklistId && r.get('permissionLevel') >= go.permissionLevels.write) {
				this.addTasklistId = r.id;
			}
		}, this);

		this.addButton.setDisabled(!this.addTasklistId);
		this.addTaskButton.setDisabled(!this.addTasklistId)
		this.taskGrid.store.setFilter("tasklist", {tasklistId: ids});
		this.taskGrid.store.load();
		this.categoriesGrid.store.load();
	},
	onCategorySelectionChange : function (sm) {
		var ids = [];

		this.categoryId = false;

		Ext.each(sm.getSelections(), function (r) {
			ids.push(r.id);
			if (!this.addTasklistId && r.json.permissionLevel >= go.permissionLevels.write) {
			// is dit goed? r.get('permissionLevel')
			// if (!this.addTasklistId && r.get('permissionLevel') >= go.permissionLevels.write) {
				this.categoryId = r.id;
			}
		}, this);
		this.taskGrid.store.setFilter("categories", {categories: ids});
		this.taskGrid.store.load();
		this.categoriesGrid.store.load();
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

