
go.modules.community.tasks.Portlet = Ext.extend(go.grid.GridPanel, {
	//id: 'su-tasks-portlet',

	autoExpandColumn : 'task-portlet-name',
	stateful: true,
	stateId: 'tasks-portlet',
	// autoExpandMax : 2500,
	// enableColumnHide : false,
	// enableColumnMove : false,
	loadMask: true,
	autoHeight: true,
	maxHeight: dp(600),
	//saveListenerAdded: false,

	initComponent: function() {

		this.store = new go.data.GroupingStore({
			groupField: 'tasklist',
			remoteGroup:true,
			remoteSort:true,
			fields: [
				'id', 'title', 'status',
				{name: 'start', type: "date"},
				'recurrenceRule', 'filesFolderId', 'alerts','priority',
				{name: 'due', type: "date"}, 'description',
				{name: 'tasklist', type: "relation"},
				'late', 'percentComplete',
				'progress',
				{name: "complete", convert: (v, data) => data.progress === 'completed'}
			],
			entityStore: "Task",
			sortInfo: {
				field: 'tasklist',
				direction: 'ASC'
			}
		});

		this.view = new go.grid.GroupingView({
			hideGroupedColumn: true,
			emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
		});

		var checkColumn = new GO.grid.CheckColumn({
			dataIndex: 'complete',
			hideable:false,
			menuDisabled: true,
			sortable:false,
			groupable:false,
			header: '<i class="icon ic-check"></i>',
			listeners: {change: function (record) {
					var wasComplete = record.json.progress == 'completed' || record.json.progress == 'cancelled';
					go.Db.store("Task").set({update: {
							[record.data.id]: {progress: (!wasComplete ? 'completed' : 'needs-action')}}
					});
				},scope:this}
		});
		this.plugins = checkColumn;

		const now = new Date();

		this.columns = [
			checkColumn,
			{
				hideable: false,
				id: 'icons',
				width: dp(60),
				renderer: function(v,m,rec) {
					var v = "";
					if(rec.json.priority != 0) {
						if (rec.json.priority < 5) {
							v += '<i class="icon small orange">priority_high</i>';
						}
						if (rec.json.priority > 5) {
							v += '<i class="icon small blue">low_priority</i>';
						}
					}
					if(rec.json.recurrenceRule) {
						v += '<i class="icon small">repeat</i>';
					}
					if(rec.json.filesFolderId) {
						v += '<i class="icon small">attachment</i>';
					}
					if(!Ext.isEmpty(rec.json.alerts)) {
						v += '<i class="icon small">alarm</i>';
					}

					return v;
				}
			},	{
				id: 'task-portlet-name',
				header: t("Subject"),
				dataIndex: 'title',
				sortable:false,
				renderer: function(v,m,rec) {
					if(rec.json.color) {
						m.style += 'color:#'+rec.json.color+';';
					}

					if(rec.data.progress == "needs-action" && rec.get("start") <= now) {
						m.style += 'font-weight: bold;';
					}

					return v;
				}
			},{
				width:dp(150),
				header: t('% complete', "tasks", "community"),
				dataIndex: 'percentComplete',
				sortable:false,
				renderer:function (value, meta, rec, row, col, store){
					return '<div class="go-progressbar"><div style="width:'+Math.ceil(value)+'%"></div></div>';
				}
			},{
				hidden: true,
				id:"progress",
				width:dp(150),
				header: t('Progress', "tasks", "community"),
				dataIndex: 'progress',
				sortable:false,
				renderer:function (value, meta, rec, row, col, store){
					return `<div class="status tasks-task-status-${value}">${go.modules.community.tasks.progress[value]}</div>`;
				}
			},{
				xtype:"datecolumn",
				header: t("Due date", "tasks"),
				dataIndex: 'due',
				width: 100,
				sortable: false
			}, {
				header: t("Tasklist"),
				dataIndex: 'tasklist',
				renderer: function(v) {
					return v ? v.name : '';
				},
				sortable: false,
				hidden: true,
				width: 150
			}, {
				header: 'ID',
				dataIndex: 'id',
				sortable: false,
				width: 50,
				hidden: true
			}];

		this.on("rowclick", function (grid, rowClicked, e) {
			go.Router.goto('task/' + grid.selModel.selections.keys[0]);
		});

		//this.sm = new Ext.grid.RowSelectionModel();
		this.supr().initComponent.call(this);
	},

	afterRender: function () {
		this.supr().afterRender.call(this);
		const lists = go.User.taskPortletTaskLists.length ? go.User.taskPortletTaskLists : [go.User.tasksSettings.defaultTasklistId];
		this.store.setFilter('tasklistIds', {tasklistId: lists});
		this.store.setFilter('incomplete', {complete: false, start: "<=now"});
		this.store.load();
	}
});

go.modules.community.tasks.PortletSettingsDialog = Ext.extend(go.form.Dialog, {
	title: t("Visible tasklists"),
	entityStore: "User",
	width: dp(500),
	height: dp(500),
	modal: true,
	collapsible: false,
	maximizable: false,
	layout: 'fit',
	showCustomfields: false,

	initFormItems: function() {
		return [
			new Ext.form.FieldSet({
				xtype: 'fieldset',
				items: [
					{
						layout: "form",
						items: [
							new go.form.multiselect.Field({
								valueIsId: true,
								idField: 'taskListId',
								displayField: 'name',
								entityStore: 'TaskList',
								name: 'taskPortletTaskLists',
								hideLabel: true,
								emptyText: t("Please select..."),
								pageSize: 50,
								fields: ['id', 'name']

							})
						]
					}
				]
			})
		];
	}
});


GO.mainLayout.onReady(function () {
	if (go.Modules.isAvailable("legacy", "summary") && go.Modules.isAvailable("community", "tasks"))
	{
		var tasksGrid = new go.modules.community.tasks.Portlet();

		GO.summary.portlets['portlet-tasks'] = new GO.summary.Portlet({
			id: 'portlet-tasks',
			//iconCls: 'go-module-icon-tasks',
			title: t("Tasks", "tasks"),
			layout: 'fit',
			tools: [{
					id: 'gear',
					handler: function () {
						const dlg = new go.modules.community.tasks.PortletSettingsDialog({
							listeners: {
								hide: function () {
									setTimeout(function() {
										tasksGrid.store.setFilter('tasklistIds', {tasklistId: go.User.taskPortletTaskLists})
										tasksGrid.store.reload();
									})
								},
								scope: this
							}
						});
						dlg.load(go.User.id).show();
					}
				}, {
					id: 'close',
					handler: function (e, target, panel) {
						panel.removePortlet();
					}
				}],
			items: tasksGrid,
			autoHeight: true
		});
	}
});
