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
	support: false,
	title: t("Tasks"),
	layout: 'responsive',
	layoutConfig: {
		triggerWidth: 1000
	},

	initComponent: function () {
		this.statePrefix = this.support ? 'support-' : 'tasks-';
		this.createTaskGrid();
		this.createTasklistGrid();
		this.createCategoriesGrid();

		this.taskDetail = new go.modules.community.tasks.TaskDetail({
			support: this.support,
			region: 'east',
			split: true,
			stateId: this.statePrefix  + '-task-detail',
			tbar: [this.taskBackButton = new Ext.Button({
				//cls: 'go-narrow',
				hidden: true,
				iconCls: "ic-arrow-back",
				handler: function () {
					go.Router.goto(this.support ? "support" : "tasks");
				},
				scope: this
			})]
		});

		const showCompleted = Ext.state.Manager.get(this.statePrefix + "show-completed");
		const assignedToMe = Ext.state.Manager.get(this.statePrefix + "assigned-to-me");

		if(this.support) {
			this.filterPanel = new go.modules.community.tasks.ProgressGrid({
				tbar: [{
					xtype: "tbtitle",
					text: t("Status")
				}],
				filterName: "progress",
				filteredStore: this.taskGrid.store

			});

		} else {

			this.filterPanel = new go.NavMenu({
				region: 'north',
				store: new Ext.data.ArrayStore({
					fields: ['name', 'icon', 'iconCls', 'inputValue'],
					data: [
						[t("Today"), 'content_paste', 'green', 'today'],
						[t("Due in seven days"), 'filter_7', 'purple', '7days'],
						[t("All"), 'assignment', 'red', 'all'],
						// [t("Completed"), 'assignment_turned_in', 'grey', 'completed'],
						[t("Unscheduled"), 'event_busy', 'blue', 'unscheduled'],
						[t("Scheduled"), 'events', 'orange', 'scheduled'],

					]
				})
			});


		}


		this.sidePanel = new Ext.Panel({
			width: dp(300),
			cls: 'go-sidenav',
			region: "west",
			split: true,
			stateId: this.support ? "support-west" : "tasks-west",
			tbar: this.sidePanelTbar = new Ext.Toolbar({
				//cls: 'go-narrow',
				hidden: true,
				items: ["->",  {

					iconCls: "ic-arrow-forward",
					tooltip: t("Tasks"),
					handler: function () {
						this.taskGrid.show();
					},
					scope: this
				}]
			}),

			bodyStyle: 'overflow-y: auto',

			layout:'fitwidth',

			items:[
				this.filterPanel,
				this.tasklistsGrid,
				this.categoriesGrid,
				this.createFilterPanel(),

			]
		});
		if(!this.support) {
			this.sidePanel.items.insert(1, Ext.create({
				xtype: "fieldset",
				items: [
					{
						hideLabel: true,
						xtype: "checkbox",
						boxLabel: t("Show completed"),
						checked: showCompleted,
						listeners: {
							scope: this,
							check: function(cb, checked) {
								this.showCompleted(checked);
								Ext.state.Manager.set(this.statePrefix + "show-completed", checked);
								this.taskGrid.store.load();
							}
						}
					}
				]
			}));
		} else {
			this.sidePanel.items.insert(1, Ext.create({
				xtype: "panel",
				layout: "form",
				bodyStyle: "padding-left: 18px;", // TODO wtf 18px?
				tbar: [
					{
						xtype:"tbtitle",
						text: t("Assigned")
					}
				],
				items: [
					{
						hideLabel: true,
						xtype: "checkbox",
						boxLabel: t("Mine"),
						checked: assignedToMe,
						listeners: {
							scope: this,
							check: function(cb, checked) {

								this.assignedToMe(checked);
								Ext.state.Manager.set(this.statePrefix + "assigned-to-me", checked);
								this.taskGrid.store.load();
							}
						}
					}
				]
			}));
		}

		this.centerPanel = new Ext.Panel({
			layout:'responsive',
			stateId: this.statePrefix + "west",
			region: "center",
			listeners: {
				afterlayout: (panel, layout) => {
					this.sidePanelTbar.setVisible(layout.isNarrow());
					this.showNavButton.setVisible(layout.isNarrow())
				}
			},
			split: true,
			narrowWidth: dp(400),
			items:[
				this.taskGrid,
				this.sidePanel
			]
		});

		this.items = [
			this.centerPanel, //first is default in narrow mode
			this.taskDetail
		];

		this.on("afterlayout", (panel, layout) => {
			this.taskBackButton.setVisible(layout.isNarrow());
		});

		go.modules.community.tasks.MainPanel.superclass.initComponent.call(this);

		this.on("afterrender", this.runModule, this);
	},

	showCompleted : function(show) {
		this.taskGrid.store.setFilter('completed', show ? null : {complete:  false});
	},

	assignedToMe : function(show) {
		this.taskGrid.store.setFilter('assignedToMe', show ?
			{
				operator: "OR",
				conditions: [
					{responsibleUserId: go.User.id},
					{responsibleUserId: null}
				]
			} : null);
	},
	
	runModule : function() {

		if(this.support) {
			this.assignedToMe(Ext.state.Manager.get(this.statePrefix + "assigned-to-me"));
		} else {

			this.filterPanel.on("afterrender", () => {

				let index = this.filterPanel.store.find('inputValue', statusFilter);
				if (index == -1) {
					index = 0;
				}

				this.filterPanel.selectRange(index, index);

			});
			this.filterPanel.on("selectionchange", this.onStatusSelectionChange, this);

			this.showCompleted(Ext.state.Manager.get(this.statePrefix + "show-completed"));

			let statusFilter = Ext.state.Manager.get(this.statePrefix + "status-filter");
			if (!statusFilter) {
				statusFilter = 'today';
			}

			this.setStatusFilter(statusFilter);
		}


		this.setDefaultSelection();

		this.tasklistsGrid.store.load();
		this.taskGrid.store.load();

	},

	getSettings : function() {
		return this.support ? go.User.supportSettings : go.User.tasksSettings;
	},

	setDefaultSelection : function() {
		let selectedListIds = [], settings = this.getSettings();
		if(settings.rememberLastItems) {
			selectedListIds = settings.lastTasklistIds;
		} else if(settings.defaultTasklistId) {
			selectedListIds.push(settings.defaultTasklistId);
		}

		this.filterCategories(selectedListIds);

		this.tasklistsGrid.setDefaultSelection(selectedListIds);

		this.taskGrid.store.setFilter("role", selectedListIds.length == 0 ? {role:  !this.support ? go.modules.community.tasks.listTypes.List : go.modules.community.tasks.listTypes.Support} : null);


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

				this.taskGrid.store.setFilter("status", {
					start: "<=now"
				});

				break;

			case '7days':
				this.taskGrid.store.setFilter("status", {
					due: "<=7days"
				});
				break;

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

		Ext.state.Manager.set(this.statePrefix + "status-filter", inputValue);
	},

	createFilterPanel: function () {


		return new Ext.Panel({
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
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (e, toolEl) {
						const dlg = new go.modules.community.tasks.CategoryDialog()
						dlg.tasklistCombo.store.setFilter("role", {role: "list"});

						const firstSelected = this.tasklistsGrid.getSelectionModel().getSelected();
						if(firstSelected) {
							dlg.setValues({tasklistId: firstSelected.id});
						}
						dlg.show();
					},
					scope: this
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
			filteredStore: this.taskGrid.store,
			filterName: 'tasklistId',
			selectFirst: false,

			split: true,
			tbar: [{
					xtype: 'tbtitle',
					text: t('Lists')
				}, '->', {
					xtype: "tbsearch"
				},{
				hidden: !go.Modules.get("community", 'tasks') || !go.Modules.get("community", 'tasks').userRights.mayChangeTasklists,
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (e, toolEl) {
						let dlg = new go.modules.community.tasks.TasklistDialog();
						dlg.setValues({role: this.support ? "support" : "list"})
						dlg.show();
					},
					scope: this
				}],
			listeners: {
				afterrender: function(grid) {
					new Ext.dd.DropTarget(grid.getView().mainBody, {
						ddGroup : 'TasklistsDD',
						notifyDrop :  (source, e, data) => {
							const selections = source.dragData.selections,
								dropRowIndex = grid.getView().findRowIndex(e.target),
								tasklistId = grid.getView().grid.store.data.items[dropRowIndex].id;

							selections.forEach((r) => {
								go.Db.store("Task").save({tasklistId: tasklistId}, r.id);
							})
						}
					});
				},
				rowclick: function(grid, row, e) {
					if(e.target.className != 'x-grid3-row-checker') {
						//if row was clicked and not the checkbox then switch to grid in narrow mode
						this.taskGrid.show();
					}
				},
				scope: this
			}
		});

		if(this.support) {
			this.tasklistsGrid.getStore().setFilter("role", {role: "support"});
		}

		this.tasklistsGrid.on('selectionchange', this.onTasklistSelectionChange, this); //add buffer because it clears selection first
	},

	// checkValues: function() {
	// 	if(this.taskDateField.getValue() != null && this.taskNameTextField.getValue() != "") {
	// 		this.addTaskButton.setDisabled(false);
	// 	} else {
	// 		this.addTaskButton.setDisabled(true);
	// 	}
	// },
	
	createTaskGrid : function() {

		this.taskGrid = new go.modules.community.tasks.TaskGrid({
			support: this.support,
			stateId: this.statePrefix  + '-tasks-grid-main',
			enableDrag: true,
			ddGroup: 'TasklistsDD',
			split: true,
			region: 'center',
			tbar: [
					this.showNavButton = new Ext.Button({
						hidden: true,
						iconCls: "ic-menu",
						handler: function () {
							this.sidePanel.show();
						},
						scope: this
					}),
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
							dlg.setValues({
								tasklistId: this.addTasklistId
							}).show();
						},
						scope: this
					}),
					{
						iconCls: 'ic-more-vert',
						menu: [
							// {
							// 	text: "refresh",
							// 	handler: function () {
							// 		const store = this.taskGrid.store, o = go.util.clone(store.lastOptions);
							// 		o.params = o.params || {};
							// 		o.params.position = 0;
							// 		o.add = false;
							// 		o.keepScrollPosition = true;
							//
							// 		if (store.lastOptions.params && store.lastOptions.params.position) {
							// 			o.params.limit = store.lastOptions.params.position + (store.lastOptions.limit || store.baseParams.limit || 20);
							// 		}
							//
							// 		store.load(o);
							// 	},
							// 	scope: this
							// }
							// ,
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
										iconCls: 'filetype filetype-ics',
										handler: function() {
											go.util.exportToFile(
												'Task',
												Object.assign(go.util.clone(this.taskGrid.store.baseParams), this.taskGrid.store.lastOptions.params, {limit: 0, position: 0}),
												'ics');
										},
										scope: this
									}, {
										text: 'Microsoft Excel',
										iconCls: 'filetype filetype-xls',
										handler: function() {
											go.util.exportToFile(
												'Task',
												Object.assign(go.util.clone(this.taskGrid.store.baseParams), this.taskGrid.store.lastOptions.params, {limit: 0, position: 0}),
												'xlsx');
										},
										scope: this
									},{
										text: 'Comma Separated Values',
										iconCls: 'filetype filetype-csv',
										handler: function() {
											go.util.exportToFile(
												'Task',
												Object.assign(go.util.clone(this.taskGrid.store.baseParams), this.taskGrid.store.lastOptions.params, {limit: 0, position: 0}),
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
							}
						]
					}

				],

			listeners: {				
				rowdblclick: this.onTaskGridDblClick,
				scope: this,				
				keypress: this.onTaskGridKeyPress
			}
		});
		//this.quickAddTaskListCombo.store.load();
		this.taskGrid.on('navigate', function (grid, rowIndex, record) {
			go.Router.goto((this.support ? "support/" : "task/") + record.id);
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


	filterCategories : function(ids) {

		const conditions = [
			{
				ownerId: go.User.id,
			},
			{
				global: true
			}
		];

		if(ids.length) {
			conditions.push({
				tasklistId: ids
			});
		}

		this.categoriesGrid.store.setFilter('tasklist',{
			operator: "or",
			conditions: conditions
		}).load();
	},

	onTasklistSelectionChange : function (ids, sm) {

		this.checkCreateTaskList();

		this.filterCategories(ids);

		//		this.taskGrid.store.setFilter("role", ids.length == 0 ? {role:  go.modules.community.tasks.listTypes.List} : null);
		this.taskGrid.store.setFilter("role", ids.length == 0 ? {role:  !this.support ? go.modules.community.tasks.listTypes.List : go.modules.community.tasks.listTypes.Support} : null);

		const settings = this.getSettings();
		if(settings.rememberLastItems && settings.lastTasklistIds.join(",") != ids.join(",")) {

			go.Db.store("User").save({
				[this.support ? "supportSettings" : "tasksSettings"]: {
					lastTasklistIds: ids
				}
			}, go.User.id);

		}
	},

	checkCreateTaskList: function() {

		this.addTasklistId = this.getSettings().defaultTasklistId;

		go.Db.store("Tasklist").get(this.tasklistsGrid.getSelectedIds()).then((result) => {

			result.entities.forEach((tasklist) => {
				if (!this.addTasklistId && tasklist.permissionLevel >= go.permissionLevels.create) {
					this.addTasklistId = tasklist.id;
				}
			});

			this.addButton.setDisabled(!this.addTasklistId);
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

